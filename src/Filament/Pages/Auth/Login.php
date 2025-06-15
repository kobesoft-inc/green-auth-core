<?php

namespace Green\Auth\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Green\Auth\Filament\Pages\Auth\Concerns\InteractsWithGreenAuth;

class Login extends BaseLogin
{
    use InteractsWithGreenAuth;

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
        if (!filament()->auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            $this->throwFailureValidationException();
        }

        $user = filament()->auth()->user();

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

        return redirect()->to($this->getPasswordExpiredUrl());
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
     * フォーム入力から認証情報を取得する
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email' => function (Builder $query) use ($data) {
                if ($this->canLoginWithEmail()) {
                    $query->orWhere('email', $data['login']);
                }
                if ($this->canLoginWithUsername() && ($usernameColumn = $this->getUsernameColumn())) {
                    $query->orWhere($usernameColumn, $data['login']);
                }
            },
            'password' => $data['password'],
        ];
    }
}
