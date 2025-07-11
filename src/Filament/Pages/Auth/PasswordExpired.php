<?php

namespace Green\Auth\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\SimplePage;
use Green\Auth\Password\PasswordComplexity;
use Green\Auth\Rules\PasswordRule;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Green\Auth\Filament\Pages\Auth\Concerns\InteractsWithGreenAuth;

class PasswordExpired extends SimplePage implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;
    use InteractsWithGreenAuth;

    protected static string $view = 'green-auth::filament.pages.auth.password-expired';

    public ?array $data = [];

    /**
     * ページのマウント処理
     *
     * パスワード期限切れセッションの有効性をチェックし、
     * 無効な場合はログインページにリダイレクトする。
     * 有効な場合はフォームを初期化する。
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
     * パスワード変更用のフォームスキーマを構築する。
     * 現在のパスワード、新しいパスワード、パスワード確認の
     * 3つの入力フィールドを含むフォームを返す。
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
     * ユーザー認証のために現在のパスワードを入力する
     * 必須フィールドを生成する。パスワードはマスク表示される。
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
     * バリデーションルールとして複雑性チェックを組み込む。
     * パスワード確認フィールドとの一致検証も設定する。
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
     * 新しいパスワードの入力ミスを防ぐための確認用フィールドを生成する。
     * 新しいパスワードフィールドと一致する必要がある。
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
     * 全て成功した場合にパスワードを更新する。
     * パスワード複雑性検証はフォームレベルで実行済み。
     * 成功時はセッションをクリアしてログイン画面へリダイレクトする。
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

        // パスワード更新
        $this->updateUserPassword($user, $data['password']);

        // 成功処理
        $this->handlePasswordChangeSuccess();
        return null;
    }

    /**
     * セッション検証とユーザー取得
     *
     * パスワード期限切れセッションからユーザーIDを取得し、
     * 対応するユーザーモデルを返す。セッションが無効またはユーザーが
     * 存在しない場合はnullを返し、適切なエラー通知を表示する。
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

        $userClass = $this->getUserClass();
        $user = $userClass::find($userId);

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
     * 入力された現在のパスワードがユーザーの実際のパスワードと一致するかチェックする。
     * 不一致の場合はエラー通知を表示してfalseを返す。
     *
     * @param string $currentPassword 入力された現在のパスワード（平文）
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
     * 新しいパスワードをユーザーモデルに設定して保存する。
     * パスワードのハッシュ化はモデルのミューテーターで自動的に処理される。
     * パスワード変更日時の更新もモデル側で自動的に行われる。
     *
     * @param Model $user ユーザーモデルインスタンス
     * @param string $newPassword 新しいパスワード（平文）
     * @return void
     */
    protected function updateUserPassword(Model $user, string $newPassword): void
    {
        $user->password = $newPassword;
        $user->save();
    }

    /**
     * パスワード変更成功処理
     *
     * パスワード変更が成功した際の後処理を実行する。
     * セッションクリア、成功通知の表示、ログインページへのリダイレクトを
     * 順番に実行する。
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
     * 統一されたフォーマットでエラー通知を表示する。
     * タイトルは固定で、本文は引数で指定されたメッセージまたは
     * 詳細メッセージを表示する。
     *
     * @param string $message エラーメッセージ
     * @param string|null $body 詳細メッセージ（省略可、省略時は$messageを使用）
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
     * 現在のパネルに設定されているログインURLへリダイレクトする。
     * getLoginUrl()メソッドはInteractsWithGreenAuthトレイトから提供される。
     *
     * @return void
     */
    protected function redirectToLogin(): void
    {
        $this->redirect($this->getLoginUrl());
    }


    /**
     * ページタイトルを取得
     *
     * ブラウザのタブやページヘッダーに表示される
     * タイトルテキストを返す。
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
     * ページコンテンツの上部に表示される
     * メインの見出しテキストを返す。
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
     * メイン見出しの下に表示される補足説明文を返す。
     * nullを返すとサブヘッディングは表示されない。
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
     * フォームの下部に表示されるアクションボタンを定義する。
     * この画面では「パスワード変更」ボタンのみを表示する。
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
     * フォーム送信用の「パスワード変更」ボタンを生成する。
     * クリック時にchangePasswordメソッドが実行される。
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
     * trueを返すとフォームのアクションボタンが
     * フォームの全幅で表示される。
     *
     * @return bool 全幅表示フラグ（true: 全幅表示）
     */
    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

}
