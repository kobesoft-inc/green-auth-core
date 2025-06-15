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
     * フォームの配列を取得
     * 
     * ログイン画面に表示するフォームを定義する。
     * ログインID、パスワード、記憶オプションのフィールドを含む。
     * 
     * @return array<int | string, string | Form> フォームの配列
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
     * ログイン処理を実行
     * 
     * レート制限、認証、アカウント状態チェック、パスワード有効期限チェック、
     * パネルアクセス権限チェックなどの一連のログインプロセスを実行する。
     * 
     * @return mixed ログインレスポンス、リダイレクトレスポンス、またはnull
     * @throws ValidationException 認証失敗時
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

    /**
     * 認証失敗のバリデーション例外をスロー
     * 
     * ログイン認証が失敗した場合に呼ばれ、
     * 適切なエラーメッセージを含むバリデーション例外をスローする。
     * 
     * @return never このメソッドは常に例外をスローし、2を返さない
     * @throws ValidationException 認証失敗のバリデーション例外
     */
    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('green-auth::auth.login.invalid_credentials'),
        ]);
    }

    /**
     * アカウント停止例外をスロー
     * 
     * アカウントが停止されている場合に呼ばれ、
     * エラー通知を表示してから認証失敗例外をスローする。
     * 
     * @return never このメソッドは常に例外をスローし、値を返さない
     * @throws ValidationException 認証失敗のバリデーション例外
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
     * 
     * パスワードが期限切れの場合、セッションにユーザーIDを保存し、
     * ログアウト後にパスワード変更画面へリダイレクトする。
     * 
     * @param mixed $user ユーザーモデルインスタンス
     * @return \Illuminate\Http\RedirectResponse パスワード変更画面へのリダイレクト
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
     * 
     * レート制限に達した場合に表示する通知を生成する。
     * 通知には待機時間の情報が含まれる。
     * 
     * @param TooManyRequestsException $exception レート制限例外
     * @return Notification|null レート制限通知オブジェクト
     */
    protected function getRateLimitedNotification(TooManyRequestsException $exception): ?Notification
    {
        return Notification::make()
            ->title(__('green-auth::auth.login.rate_limit_exceeded'))
            ->body(__('green-auth::auth.login.rate_limit_message', ['seconds' => $exception->secondsUntilAvailable]))
            ->danger();
    }


    /**
     * ページタイトルを取得
     * 
     * ブラウザのタブやページヘッダーに表示される
     * タイトルテキストを返す
     * 
     * @return string|Htmlable ページタイトル
     */
    public function getTitle(): string|Htmlable
    {
        return __('green-auth::auth.login.title');
    }

    /**
     * ページの見出しを取得
     * 
     * ページコンテンツの上部に表示される
     * メインの見出しテキストを返す
     * 
     * @return string|Htmlable ページ見出し
     */
    public function getHeading(): string|Htmlable
    {
        return __('green-auth::auth.login.heading');
    }

    /**
     * ログイン用フォームコンポーネントを取得（設定に基づいて動的に変更）
     * 
     * システム設定に基づいて、メールアドレス、ユーザー名、
     * またはその両方でログインできるようにフィールドを動的に設定する。
     * 
     * @return Component ログイン入力用TextInputコンポーネント
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

    /**
     * パスワード入力フィールドコンポーネントを取得
     * 
     * パスワード入力用のフィールドを生成する。
     * マスク処理された必須入力フィールドとして設定される。
     * 
     * @return Component パスワード入力用TextInputコンポーネント
     */
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('green-auth::auth.login.fields.password'))
            ->password()
            ->required()
            ->extraInputAttributes(['tabindex' => 2]);
    }

    /**
     * 「ログインを維持する」チェックボックスコンポーネントを取得
     * 
     * 親クラスのコンポーネントを継承し、
     * ラベルをローカライズしたテキストに変更する。
     * 
     * @return Component 「ログインを維持する」チェックボックスコンポーネント
     */
    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->label(__('green-auth::auth.login.remember_me'));
    }

    /**
     * フォームアクションを全幅で表示するかを判定
     * 
     * trueを返すとフォームのアクションボタンが
     * フォームの全幅で表示される
     * 
     * @return bool 全幅表示フラグ（true: 全幅表示）
     */
    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * フォームアクション（ボタン）の配列を取得
     * 
     * フォームの下部に表示されるアクションボタンを定義する。
     * この画面では「ログイン」ボタンのみを表示する。
     * 
     * @return array<Action | ActionGroup> アクションまたはアクショングループの配列
     */
    protected function getFormActions(): array
    {
        return [
            $this->getAuthenticateFormAction(),
        ];
    }

    /**
     * 認証フォームアクションボタンを取得
     * 
     * フォーム送信用の「ログイン」ボタンを生成する。
     * クリック時にloginメソッドが実行される。
     * 
     * @return Action ログインアクション
     */
    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('login')
            ->label(__('green-auth::auth.login.submit'))
            ->submit('login');
    }

    /**
     * ログインフィールドのラベルを取得
     * 
     * システム設定に基づいて、適切なラベルテキストを返す。
     * メールとユーザー名両方、ユーザー名のみ、メールのみの
     * 3パターンに対応する。
     * 
     * @return string ログインフィールドのラベルテキスト
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
     * 
     * システム設定に基づいて、入力例を示すプレースホルダーテキストを返す。
     * メールとユーザー名両方、ユーザー名のみ、メールのみの
     * 3パターンに対応する。
     * 
     * @return string ログインフィールドのプレースホルダーテキスト
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
     * フォームで入力されたデータから認証に必要な情報を抽出し、
     * Laravelの認証システムに渡す形式に変換する。
     * メールアドレスまたはユーザー名でのログインに対応する。
     *
     * @param array<string, mixed> $data フォーム入力データ
     * @return array<string, mixed> 認証用の資格情報配列
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
