<?php

namespace Green\Auth\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    /**
     * @var string
     */
    protected static string $view = 'green-auth::filament.pages.auth.login';

    /**
     * @return array<int | string, string | Form>
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getLoginFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getRememberFormComponent(),
                    ])
                    ->statePath('data'),
            ),
        ];
    }

    /**
     * @throws ValidationException
     */
    public function login(): mixed
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();
            return null;
        }

        $data = $this->form->getState();
        $credentials = $this->getCredentialsFromFormData($data);

        if (!$this->attemptAuthentication($credentials, $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = Auth::user();

        // アカウント停止チェック
        if (method_exists($user, 'isSuspended') && $user->isSuspended()) {
            Auth::logout();
            $this->throwAccountSuspendedException();
        }

        // パスワード有効期限チェック
        if (method_exists($user, 'isPasswordExpired') && $user->isPasswordExpired()) {
            return $this->handlePasswordExpired($user);
        }

        // Filamentパネルアクセス権限チェック
        if (($user instanceof FilamentUser) && (!$user->canAccessPanel($this->getPanel()))) {
            Auth::logout();
            $this->throwFailureValidationException();
        }

        session()->regenerate();
        return app(LoginResponse::class);
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('green-auth::auth.login.invalid_credentials'),
        ]);
    }

    /**
     * アカウント停止例外をスロー
     */
    protected function throwAccountSuspendedException(): never
    {
        Notification::make()
            ->title(__('green-auth::auth.login.account_suspended'))
            ->body(__('green-auth::auth.login.account_suspended_message'))
            ->danger()
            ->persistent()
            ->send();

        $this->throwFailureValidationException();
    }

    /**
     * パスワード期限切れ処理
     */
    protected function handlePasswordExpired($user)
    {
        $sessionKey = $this->getPasswordExpiredSessionKey();
        session([$sessionKey => $user->id]);
        Auth::logout();

        return redirect()->to(route('filament.' . filament()->getId() . '.password-expired'));
    }

    /**
     * Rate limit通知を取得（親クラスのメソッドを使用）
     */
    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('green-auth::auth.login.rate_limit_exceeded'))
            ->body(__('green-auth::auth.login.rate_limit_message', ['seconds' => $exception->secondsUntilAvailable]))
            ->danger();
    }

    /**
     * 現在のパネルを取得
     */
    protected function getPanel()
    {
        return filament()->getCurrentPanel();
    }

    public function getTitle(): string|Htmlable
    {
        return __('green-auth::auth.login.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('green-auth::auth.login.heading');
    }

    /**
     * ログイン用フォームコンポーネントを取得（設定に基づいて動的に変更）
     */
    protected function getLoginFormComponent(): Component
    {
        $label = $this->getLoginFieldLabel();
        $placeholder = $this->getLoginFieldPlaceholder();

        $input = TextInput::make('login')
            ->label($label)
            ->placeholder($placeholder)
            ->required()
            ->autocomplete()
            ->autofocus()
            ->extraInputAttributes(['tabindex' => 1]);

        // バリデーションルールを設定
        if ($this->canLoginWithEmail() && !$this->canLoginWithUsername()) {
            $input->email();
        }

        return $input;
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('green-auth::auth.login.fields.password'))
            ->password()
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label(__('green-auth::auth.login.remember_me'));
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('login')
            ->label(__('green-auth::auth.login.submit'))
            ->submit('login');
    }

    /**
     * 現在のガード名を取得
     */
    protected function getGuard(): string
    {
        return filament()->getAuthGuard();
    }

    /**
     * 認証設定を取得
     */
    protected function config(string $key, $default = null)
    {
        $guard = $this->getGuard();
        return config("green-auth.guards.{$guard}.auth.{$key}", config("green-auth.auth.{$key}", $default));
    }

    /**
     * パスワード期限切れセッションキーを取得
     */
    protected function getPasswordExpiredSessionKey(): string
    {
        $guard = $this->getGuard();
        return "password_expired_user_id_{$guard}";
    }

    /**
     * メールアドレスでのログインが可能かチェック
     */
    protected function canLoginWithEmail(): bool
    {
        return $this->config('login_with_email', true);
    }

    /**
     * ユーザー名でのログインが可能かチェック
     */
    protected function canLoginWithUsername(): bool
    {
        return $this->config('login_with_username', false);
    }

    /**
     * ログインフィールドのラベルを取得
     */
    protected function getLoginFieldLabel(): string
    {
        if ($this->canLoginWithEmail() && $this->canLoginWithUsername()) {
            return __('green-auth::auth.login.fields.username_or_email');
        } elseif ($this->canLoginWithUsername()) {
            return __('green-auth::auth.login.fields.username');
        } else {
            return __('green-auth::auth.login.fields.email');
        }
    }

    /**
     * ログインフィールドのプレースホルダーを取得
     */
    protected function getLoginFieldPlaceholder(): string
    {
        if ($this->canLoginWithEmail() && $this->canLoginWithUsername()) {
            return __('green-auth::auth.login.fields.placeholders.username_or_email');
        } elseif ($this->canLoginWithUsername()) {
            return __('green-auth::auth.login.fields.placeholders.username');
        } else {
            return __('green-auth::auth.login.fields.placeholders.email');
        }
    }

    /**
     * 認証を試行
     */
    protected function attemptAuthentication(array $credentials, bool $remember = false): bool
    {
        if ($this->canLoginWithEmail() && $this->canLoginWithUsername()) {
            return $this->attemptWithUsernameOrEmail($credentials, $remember);
        } elseif ($this->canLoginWithUsername()) {
            return $this->attemptWithUsername($credentials, $remember);
        }

        return Auth::attempt($credentials, $remember);
    }

    /**
     * ユーザー名またはメールアドレスでの認証を試行
     */
    protected function attemptWithUsernameOrEmail(array $credentials, bool $remember = false): bool
    {
        $login = $credentials['login'];
        $password = $credentials['password'];

        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : $this->getUsernameColumn();

        return Auth::attempt([$field => $login, 'password' => $password], $remember);
    }

    /**
     * ユーザー名での認証を試行
     */
    protected function attemptWithUsername(array $credentials, bool $remember = false): bool
    {
        $usernameColumn = $this->getUsernameColumn();

        return Auth::attempt([
            $usernameColumn => $credentials['login'],
            'password' => $credentials['password']
        ], $remember);
    }

    /**
     * フォームデータから認証情報を取得
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return $this->canLoginWithEmail() && !$this->canLoginWithUsername()
            ? ['email' => $data['login'], 'password' => $data['password']]
            : ['login' => $data['login'], 'password' => $data['password']];
    }

    /**
     * ログインログモデルクラスを取得
     */
    protected function getLoginLogModel(): ?string
    {
        $guard = $this->getGuard();
        $class = config("green-auth.guards.{$guard}.models.login_log", config('green-auth.models.login_log'));

        return $class && class_exists($class) ? $class : null;
    }

    /**
     * ユーザーモデルクラスを取得
     */
    protected function getUserModel(): string
    {
        $guard = $this->getGuard();
        return config("green-auth.guards.{$guard}.models.user", config('green-auth.models.user', 'App\\Models\\User'));
    }

    /**
     * ユーザー名カラム名を取得（モデルのUSERNAME_COLUMNコンスタンから）
     */
    protected function getUsernameColumn(): string
    {
        $userModel = $this->getUserModel();

        if (defined($userModel . '::USERNAME_COLUMN')) {
            return constant($userModel . '::USERNAME_COLUMN');
        }

        return 'username';
    }
}
