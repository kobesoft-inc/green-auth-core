<?php

namespace Green\Auth\Filament\Pages\Auth;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Component;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Green\Auth\Rules\PasswordRule;
use Green\Auth\Filament\Pages\Auth\Concerns\InteractsWithGreenAuth;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;
    use InteractsWithGreenAuth;

    protected string $view = 'green-auth::filament.pages.auth.change-password';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    /**
     * フォームの定義を構築
     *
     * パスワード変更画面のフォームを定義し、現在のパスワード、
     * 新しいパスワード、パスワード確認の入力フィールドを配置する
     *
     * @param Schema $schema Filamentフォームインスタンス
     * @return Schema 設定済みのフォームインスタンス
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getCurrentPasswordFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ])
            ->statePath('data');
    }

    /**
     * 現在のパスワード入力フィールドコンポーネントを取得
     *
     * ユーザーの現在のパスワードを入力するためのフィールドを生成する。
     * 本人確認のために必須項目として設定される。
     *
     * @return Component 現在のパスワード入力用TextInputコンポーネント
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
     * 新しいパスワードの入力フィールドを生成し、パスワード複雑性要件を
     * ヘルパーテキストとして表示する。パスワードルールの検証も設定する。
     *
     * @return Component 新しいパスワード入力用TextInputコンポーネント
     */
    protected function getPasswordFormComponent(): Component
    {
        $complexity = $this->getPasswordComplexity();
        $requirements = $complexity->getRequirements();

        return TextInput::make('password')
            ->label(__('green-auth::auth.change_password.fields.new_password'))
            ->password()
            ->required()
            ->rule(PasswordRule::fromCurrentPanel())
            ->same('passwordConfirmation')
            ->validationAttribute(__('green-auth::auth.change_password.fields.new_password'))
            ->helperText(implode(' / ', $requirements))
            ->extraInputAttributes(['tabindex' => 2]);
    }

    /**
     * パスワード確認入力フィールドコンポーネントを取得
     *
     * 新しいパスワードの確認用入力フィールドを生成する。
     * 新しいパスワードと一致する必要がある。
     *
     * @return Component パスワード確認用TextInputコンポーネント
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
     * パスワード変更処理を実行
     * 
     * フォーム入力値を検証し、現在のパスワードが正しいことを確認後、
     * 新しいパスワードに更新する。成功時は通知を表示しフォームをリセットする。
     * 
     * @return void
     */
    public function changePassword(): void
    {
        $data = $this->form->getState();
        $user = Auth::user();

        if (!$user) {
            redirect()->to($this->getLoginUrl());
            return;
        }

        // 現在のパスワードをチェック
        if (!Hash::check($data['current_password'], $user->password)) {
            Notification::make()
                ->title(__('green-auth::auth.change_password.error'))
                ->body(__('green-auth::auth.change_password.current_password_incorrect'))
                ->danger()
                ->send();

            return;
        }

        // 新しいパスワードを設定
        $user->password = Hash::make($data['password']);

        $user->save();

        Notification::make()
            ->title(__('green-auth::auth.change_password.success'))
            ->body(__('green-auth::auth.change_password.success_message'))
            ->success()
            ->send();

        // フォームをリセット
        $this->form->fill();
    }

    /**
     * ナビゲーションメニューに表示するラベルを取得
     * 
     * サイドバーなどのナビゲーションメニューに表示される
     * このページのラベルテキストを返す
     * 
     * @return string ナビゲーションラベル
     */
    public static function getNavigationLabel(): string
    {
        return __('green-auth::auth.change_password.title');
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
        return __('green-auth::auth.change_password.title');
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
        return __('green-auth::auth.change_password.heading');
    }

    /**
     * ページのサブ見出しを取得
     * 
     * メイン見出しの下に表示される補足説明文を返す。
     * nullを返すとサブ見出しは表示されない。
     * 
     * @return string|Htmlable|null サブ見出しテキスト
     */
    public function getSubheading(): string|Htmlable|null
    {
        return __('green-auth::auth.change_password.subheading');
    }

    /**
     * フォームアクション（ボタン）の配列を取得
     * 
     * フォームの下部に表示されるアクションボタンを定義する。
     * この画面では「パスワード変更」ボタンのみを表示する。
     * 
     * @return array<Action> アクションの配列
     */
    protected function getFormActions(): array
    {
        return [
            $this->getChangePasswordFormAction(),
        ];
    }

    /**
     * パスワード変更アクションボタンを取得
     * 
     * フォーム送信用の「パスワード変更」ボタンを生成する。
     * クリック時にchangePasswordメソッドが実行される。
     * 
     * @return Action パスワード変更アクション
     */
    protected function getChangePasswordFormAction(): Action
    {
        return Action::make('changePassword')
            ->label(__('green-auth::auth.change_password.submit'))
            ->submit('changePassword');
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

}
