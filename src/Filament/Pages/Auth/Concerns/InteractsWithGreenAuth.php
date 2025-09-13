<?php

namespace Green\Auth\Filament\Pages\Auth\Concerns;

use Filament\Panel;
use Filament\Facades\Filament;
use Green\Auth\Password\PasswordComplexity;

trait InteractsWithGreenAuth
{
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
     * 現在のパネルを取得
     *
     * @return \Filament\Panel 現在のFilamentパネル
     */
    protected function getPanel(): Panel
    {
        return filament()->getCurrentOrDefaultPanel();
    }

    // ===== ユーザーモデル関連 =====

    /**
     * ユーザーモデルクラスを取得
     *
     * プラグインからガード設定に基づくユーザーモデルクラス名を取得
     * PasswordExpiredページで使用されているgetUserClass()の実装
     *
     * @return string ユーザーモデルクラス名
     */
    protected function getUserClass(): string
    {
        return filament()->getPlugin('green-auth')->getUserClass();
    }

    /**
     * ユーザー名カラム名を取得
     */
    protected function getUsernameColumn(): ?string
    {
        $userModel = $this->getUserClass();
        if (method_exists($userModel, 'getUsernameColumn')) {
            return $userModel::getUsernameColumn();
        }
        return null;
    }

    /**
     * 認証設定を取得
     *
     * ガード固有の設定を優先し、存在しない場合はグローバル設定にフォールバック
     *
     * @param string $key 設定キー
     * @param mixed|null $default デフォルト値
     * @return mixed 設定値
     */
    protected function getGuardConfig(string $key, mixed $default = null): mixed
    {
        $guard = $this->getCurrentGuard();
        return config("green-auth.guards.{$guard}.auth.{$key}", config("green-auth.auth.{$key}", $default));
    }

    /**
     * メールアドレスでのログインが可能かチェック
     */
    protected function canLoginWithEmail(): bool
    {
        return $this->getGuardConfig('login_with_email', true);
    }

    /**
     * ユーザー名でのログインが可能かチェック
     */
    protected function canLoginWithUsername(): bool
    {
        return $this->getGuardConfig('login_with_username', false);
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
     * パスワード有効期限切れのURLを取得
     *
     * @return string ログインページURL
     */
    protected function getPasswordExpiredUrl(): string
    {
        return route('filament.' . filament()->getId() . '.password-expired');
    }
}
