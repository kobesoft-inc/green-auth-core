<?php

namespace Green\AuthCore\Filament\Pages\Auth;

use Filament\Actions\Action;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Green\AuthCore\Password\PasswordComplexity;
use Green\AuthCore\Rules\PasswordRule;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;
    use InteractsWithFormActions;

    protected static string $view = 'green-auth::filament.pages.auth.change-password';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

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

    protected function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('current_password')
            ->label(__('green-auth::auth.change_password.fields.current_password'))
            ->password()
            ->required()
            ->extraInputAttributes(['tabindex' => 1]);
    }

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

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('green-auth::auth.change_password.fields.password_confirmation'))
            ->password()
            ->required()
            ->extraInputAttributes(['tabindex' => 3]);
    }

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

    public static function getNavigationLabel(): string
    {
        return __('green-auth::auth.change_password.title');
    }

    public function getTitle(): string|Htmlable
    {
        return __('green-auth::auth.change_password.title');
    }

    public function getHeading(): string|Htmlable
    {
        return __('green-auth::auth.change_password.heading');
    }

    public function getSubheading(): string|Htmlable|null
    {
        return __('green-auth::auth.change_password.subheading');
    }

    /**
     * @return array<Action>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getChangePasswordFormAction(),
        ];
    }

    protected function getChangePasswordFormAction(): Action
    {
        return Action::make('changePassword')
            ->label(__('green-auth::auth.change_password.submit'))
            ->submit('changePassword');
    }

    protected function hasFullWidthFormActions(): bool
    {
        return true;
    }

    /**
     * ログインページのURLを取得
     */
    protected function getLoginUrl(): string
    {
        return filament()->getLoginUrl();
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
}