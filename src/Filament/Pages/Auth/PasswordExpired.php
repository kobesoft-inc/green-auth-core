<?php

namespace Green\AuthCore\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Green\AuthCore\Password\PasswordComplexity;
use Green\AuthCore\Rules\PasswordRule;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordExpired extends SimplePage implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static string $view = 'green-auth::filament.pages.auth.password-expired';

    public ?array $data = [];

    /**
     * ページのマウント処理
     *
     * パスワード期限切れセッションの有効性をチェックし、
     * 無効な場合はログインページにリダイレクトする
     *
     * @return void
     */
    public function mount(): void
    {
        // パスワード期限切れでリダイレクトされてきた場合のユーザーIDをチェック
        $sessionKey = $this->getPasswordExpiredSessionKey();
        $userId = session($sessionKey);

        if (!$userId) {
            // 直接アクセスした場合はログインページにリダイレクト
            $this->redirectToLogin();
            return;
        }

        $this->form->fill();
    }

    /**
     * フォームの定義
     *
     * パスワード変更用のフォームスキーマを構築する
     *
     * @param Form $form Filamentフォームインスタンス
     * @return Form 設定済みフォームインスタンス
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getCurrentPasswordFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ])
            ->statePath('data');
    }

    /**
     * 現在のパスワード入力フィールドコンポーネントを取得
     *
     * @return Component 現在のパスワード入力用のTextInputコンポーネント
     */
    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('current_password')
            ->label(__('green-auth::auth.change_password.fields.current_password'))
            ->password()
            ->required()
            ->extraInputAttributes(['tabindex' => 1]);
    }

    /**
     * 新しいパスワード入力フィールドコンポーネントを取得
     *
     * パスワード複雑性要件をヘルパーテキストとして表示し、
     * バリデーションルールとして複雑性チェックを組み込む
     *
     * @return Component 新しいパスワード入力用のTextInputコンポーネント
     */
    protected function getPasswordFormComponent(): Component
    {
        $complexity = $this->getPasswordComplexity();
        $requirements = $complexity->getRequirements();

        return TextInput::make('password')
            ->label(__('green-auth::auth.change_password.fields.new_password'))
            ->password()
            ->required()
            ->rule(Password::default())
            ->rule(PasswordRule::fromCurrentPanel())
            ->same('passwordConfirmation')
            ->validationAttribute(__('green-auth::auth.change_password.fields.new_password'))
            ->helperText(implode(' / ', $requirements))
            ->extraInputAttributes(['tabindex' => 2]);
    }

    /**
     * パスワード確認入力フィールドコンポーネントを取得
     *
     * @return Component パスワード確認入力用のTextInputコンポーネント
     */
    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('green-auth::auth.change_password.fields.password_confirmation'))
            ->password()
            ->required()
            ->extraInputAttributes(['tabindex' => 3]);
    }

    /**
     * パスワード変更の実行
     *
     * セッション検証、現在パスワード検証を行い、
     * 全て成功した場合にパスワードを更新する
     * （パスワード複雑性検証はフォームレベルで実行済み）
     *
     * @return mixed リダイレクトレスポンスまたはnull（エラー時）
     */
    public function changePassword(): mixed
    {
        $data = $this->form->getState();

        // セッション検証
        $user = $this->validateSessionAndGetUser();
        if (!$user) {
            $this->redirectToLogin();
            return null;
        }

        // 現在のパスワード検証
        if (!$this->validateCurrentPassword($data['current_password'], $user)) {
            return null;
        }

        // パスワード更新（複雑性検証はフォームバリデーションで実行済み）
        $this->updateUserPassword($user, $data['password']);

        // 成功処理
        $this->handlePasswordChangeSuccess();
        return null;
    }

    /**
     * セッション検証とユーザー取得
     *
     * パスワード期限切れセッションからユーザーIDを取得し、
     * 対応するユーザーモデルを返す
     *
     * @return Model|null ユーザーモデルまたはnull（無効時）
     */
    protected function validateSessionAndGetUser(): ?Model
    {
        $sessionKey = $this->getPasswordExpiredSessionKey();
        $userId = session($sessionKey);

        if (!$userId) {
            return null;
        }

        $userModel = $this->getUserClass();
        $user = $userModel::find($userId);

        if (!$user) {
            $this->sendErrorNotification(
                __('green-auth::auth.change_password.user_not_found')
            );
            return null;
        }

        return $user;
    }

    /**
     * 現在のパスワード検証
     *
     * 入力された現在のパスワードがユーザーの実際のパスワードと一致するかチェック
     *
     * @param string $currentPassword 入力された現在のパスワード
     * @param Model $user ユーザーモデルインスタンス
     * @return bool 検証結果（true: 一致, false: 不一致）
     */
    protected function validateCurrentPassword(string $currentPassword, Model $user): bool
    {
        if (!Hash::check($currentPassword, $user->password)) {
            $this->sendErrorNotification(
                __('green-auth::auth.change_password.current_password_incorrect')
            );
            return false;
        }

        return true;
    }

    /**
     * ユーザーのパスワードを更新
     *
     * 新しいパスワードをハッシュ化してユーザーモデルに保存し、
     * パスワード変更日時も更新する
     *
     * @param Model $user ユーザーモデルインスタンス
     * @param string $newPassword 新しいパスワード（平文）
     * @return void
     */
    protected function updateUserPassword(Model $user, string $newPassword): void
    {
        $user->password = Hash::make($newPassword);

        // パスワード変更日時を更新（HasPasswordExpirationトレイトの場合）
        if (method_exists($user, 'setPasswordChangedAt')) {
            $user->setPasswordChangedAt(now());
        }

        $user->save();
    }

    /**
     * パスワード変更成功処理
     *
     * セッションクリア、成功通知表示、ログインページへのリダイレクトを実行
     *
     * @return void
     */
    protected function handlePasswordChangeSuccess(): void
    {
        // セッションをクリア
        $sessionKey = $this->getPasswordExpiredSessionKey();
        session()->forget($sessionKey);

        // 成功通知
        Notification::make()
            ->title(__('green-auth::auth.password_expired.success'))
            ->body(__('green-auth::auth.password_expired.success_message'))
            ->success()
            ->send();

        $this->redirectToLogin();
    }

    /**
     * エラー通知の送信
     *
     * 統一されたフォーマットでエラー通知を表示
     *
     * @param string $message エラーメッセージ
     * @param string|null $body 詳細メッセージ（省略可）
     * @return void
     */
    protected function sendErrorNotification(string $message, ?string $body = null): void
    {
        Notification::make()
            ->title(__('green-auth::auth.change_password.error'))
            ->body($body ?: $message)
            ->danger()
            ->send();
    }

    /**
     * ログインページへのリダイレクト
     *
     * @return void
     */
    protected function redirectToLogin(): void
    {
        $this->redirect($this->getLoginUrl());
    }

    /**
     * パスワード複雑性設定を取得
     *
     * 現在のガード設定に基づいてPasswordComplexityインスタンスを生成
     *
     * @return PasswordComplexity パスワード複雑性設定インスタンス
     */
    protected function getPasswordComplexity(): PasswordComplexity
    {
        $guard = $this->getCurrentGuard();
        return PasswordComplexity::fromAppConfig($guard);
    }

    /**
     * ページタイトルを取得
     *
     * @return string|Htmlable ページタイトル
     */
    public function getTitle(): string|Htmlable
    {
        return __('green-auth::auth.password_expired.title');
    }

    /**
     * ページヘッディングを取得
     *
     * @return string|Htmlable ページヘッディング
     */
    public function getHeading(): string|Htmlable
    {
        return __('green-auth::auth.password_expired.heading');
    }

    /**
     * ページサブヘッディングを取得
     *
     * @return string|Htmlable|null ページサブヘッディング
     */
    public function getSubheading(): string|Htmlable|null
    {
        return __('green-auth::auth.password_expired.subheading');
    }

    /**
     * フォームアクションを取得
     *
     * パスワード変更ボタンを含むアクション配列を返す
     *
     * @return array<Action> アクション配列
     */
    protected function getFormActions(): array
    {
        return [
            $this->getChangePasswordFormAction(),
        ];
    }

    /**
     * パスワード変更フォームアクションを取得
     *
     * @return Action パスワード変更アクション
     */
    protected function getChangePasswordFormAction(): Action
    {
        return Action::make('changePassword')
            ->label(__('green-auth::auth.password_expired.submit'))
            ->submit('changePassword');
    }

    /**
     * フォームアクションを全幅で表示するかどうか
     *
     * @return bool 全幅表示フラグ
     */
    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * 現在のガード名を取得
     *
     * Filamentパネルで使用中の認証ガード名を返す
     *
     * @return string ガード名
     */
    protected function getCurrentGuard(): string
    {
        return filament()->getAuthGuard();
    }

    /**
     * パスワード期限切れセッションキーを取得
     *
     * ガード固有のセッションキーを生成
     *
     * @return string セッションキー
     */
    protected function getPasswordExpiredSessionKey(): string
    {
        $guard = $this->getCurrentGuard();
        return "password_expired_user_id_{$guard}";
    }

    /**
     * ログインページのURLを取得
     *
     * @return string ログインページURL
     */
    protected function getLoginUrl(): string
    {
        return filament()->getLoginUrl();
    }

    /**
     * ユーザーモデルクラスを取得
     *
     * プラグインからガード設定に基づくユーザーモデルクラス名を取得
     *
     * @return string ユーザーモデルクラス名
     */
    protected function getUserClass(): string
    {
        return filament()->getPlugin('green-auth')->getUserClass();
    }
}