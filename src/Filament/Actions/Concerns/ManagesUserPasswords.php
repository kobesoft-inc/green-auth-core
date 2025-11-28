<?php

namespace Green\Auth\Filament\Actions\Concerns;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Green\Auth\Mail\UserPasswordNotification;
use Green\Auth\Password\PasswordComplexity;
use Green\Auth\Password\PasswordGenerator;
use Green\Auth\Rules\PasswordRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

trait ManagesUserPasswords
{
    /**
     * パスワード管理フィールドを作成
     *
     * @param  string  $modelClass  モデルクラス名
     * @return array フォームコンポーネント配列
     */
    public static function getPasswordFormComponents(string $modelClass): array
    {
        return [
            Checkbox::make('auto_generate_password')
                ->label(__('green-auth::passwords.auto_generate_password'))
                ->default(true)
                ->reactive(),

            TextInput::make('password')
                ->label(__('green-auth::passwords.password'))
                ->password()
                ->maxLength(255)
                ->hidden(fn (callable $get) => $get('auto_generate_password'))
                ->required(fn (callable $get) => ! $get('auto_generate_password'))
                ->reactive()
                ->helperText(function () use ($modelClass) {
                    $guard = $modelClass::getGuardName();
                    $complexity = PasswordComplexity::fromAppConfig($guard);
                    $requirements = $complexity->getRequirements();

                    return implode(' / ', $requirements);
                })
                ->rules([new PasswordRule(PasswordComplexity::fromAppConfig($modelClass::getGuardName()))]),

            Checkbox::make('send_email_notification')
                ->label(__('green-auth::passwords.send_email_notification'))
                ->default(true),

            Checkbox::make('require_password_change')
                ->label(__('green-auth::passwords.require_password_change'))
                ->default(true),
        ];
    }

    /**
     * パスワードを生成または指定されたパスワードを使用
     *
     * @param  array  $data  フォームデータ
     * @param  mixed  $modelClass  モデルクラス
     * @return string パスワード
     */
    protected function generateOrUsePassword(array $data, $modelClass = null): string
    {
        if (! ($data['auto_generate_password'] ?? true) && ! empty($data['password'])) {
            return $data['password'];
        }

        // 自動生成
        return $this->generatePassword($modelClass);
    }

    /**
     * パスワードを自動生成
     *
     * @param  mixed  $modelClass  モデルクラス
     * @return string 生成されたパスワード
     */
    protected function generatePassword($modelClass = null): string
    {
        // モデルからガード名を取得
        $guard = $modelClass::getGuardName();

        // ガードベースでジェネレーターを作成
        return PasswordGenerator::fromGuard($guard)->generate();
    }

    /**
     * ユーザー作成用のデータを準備
     *
     * @param  array  $data  フォームデータ
     * @param  mixed  $modelClass  モデルクラス
     * @return array [処理済みデータ, 平文パスワード, パスワード設定]
     */
    protected function prepareUserData(array $data, $modelClass = null): array
    {
        // パスワード管理関連データを分離
        $passwordData = [
            'auto_generate_password' => $data['auto_generate_password'] ?? true,
            'password' => $data['password'] ?? null,
            'send_email_notification' => $data['send_email_notification'] ?? true,
            'require_password_change' => $data['require_password_change'] ?? true,
        ];

        // パスワードを生成してデータに追加（モデルには平文で渡す）
        $plainPassword = $this->generateOrUsePassword($passwordData, $modelClass);
        $data['password'] = $plainPassword;

        // パスワード関連設定は作成後に処理するため、ここでは何もしない
        // require_password_changeフラグは$passwordDataに保持

        // メインデータからパスワード管理関連データを削除
        unset($data['auto_generate_password'], $data['send_email_notification'], $data['require_password_change']);

        return [$data, $plainPassword, $passwordData];
    }

    /**
     * 作成後のユーザー通知を処理
     *
     * @param  Model  $record  ユーザーレコード
     * @param  string  $plainPassword  平文パスワード
     * @param  array  $passwordData  パスワード設定データ
     */
    protected function notifyUser(Model $record, string $plainPassword, array $passwordData): void
    {
        // パスワード有効期限を設定
        $this->handlePasswordExpiration($record, $passwordData);

        if ($passwordData['send_email_notification'] ?? true) {
            $this->sendEmail(
                $record,
                $plainPassword,
                __('green-auth::mail.account_created.subject'),
                __('green-auth::mail.account_created.message')
            );
        } else {
            // 作成後の表示用にパスワードをセッションに一時保存
            session()->flash('new_user_password', $plainPassword);
            session()->flash('new_user_email', $record->email ?? $record->username);
        }
    }

    /**
     * パスワード有効期限設定を処理
     *
     * @param  Model  $record  ユーザーレコード
     * @param  array  $passwordData  パスワード設定データ
     */
    protected function handlePasswordExpiration(Model $record, array $passwordData): void
    {
        if (! ($passwordData['require_password_change'] ?? false)) {
            return;
        }
        if (method_exists($record, 'resetPasswordExpiration')) {
            $record->resetPasswordExpiration();
            $record->save();
        }
    }

    /**
     * パスワードリセットを処理
     *
     * @param  Model  $record  ユーザーレコード
     * @param  array  $data  フォームデータ
     */
    protected function resetPassword(Model $record, array $data): void
    {
        $password = $this->generateOrUsePassword($data, get_class($record));

        // パスワードを保存（モデルにより暗号化）
        $record->password = $password;

        // パスワード有効期限をリセット
        if ($data['require_password_change'] ?? false) {
            if (method_exists($record, 'resetPasswordExpiration')) {
                $record->resetPasswordExpiration();
            }
        }

        $record->save();

        if ($data['send_email_notification'] ?? false) {
            $this->sendEmail(
                $record,
                $password,
                __('green-auth::mail.password_reset.subject'),
                __('green-auth::mail.password_reset.message')
            );
            Notification::make()
                ->success()
                ->title(__('green-auth::notifications.password_reset_complete'))
                ->body(__('green-auth::notifications.password_sent_by_email'))
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title(__('green-auth::notifications.password_reset_complete'))
                ->body(__('green-auth::notifications.new_password_display', ['password' => $password]))
                ->persistent()
                ->send();
        }
    }

    /**
     * パスワード通知メールを送信
     *
     * @param  Model  $user  ユーザー
     * @param  string  $password  パスワード
     * @param  string  $subject  件名
     * @param  string  $message  メッセージ
     */
    protected function sendEmail(Model $user, string $password, string $subject, string $message): void
    {
        Mail::to($user->email)
            ->send(new UserPasswordNotification($user, $password, $subject, $message));
    }
}
