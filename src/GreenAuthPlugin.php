<?php

namespace Green\Auth;

use Filament\Contracts\Plugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Green\Auth\Filament\Pages\Auth\ChangePassword;
use Green\Auth\Filament\Pages\Auth\PasswordExpired;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/**
 * Green Auth用Filamentプラグイン
 *
 * 認証関連のページ（パスワード変更、パスワード期限切れ）のルート登録と
 * 設定管理を提供するプラグインクラス
 */
class GreenAuthPlugin implements Plugin
{
    /**
     * プラグインID
     *
     * @return string プラグインの識別子
     */
    public function getId(): string
    {
        return 'green-auth';
    }

    /**
     * プラグインをパネルに登録
     *
     * パスワード変更とパスワード期限切れページのルートを登録し、
     * 認証フロー用のページを利用可能にする
     *
     * @param Panel $panel Filamentパネルインスタンス
     * @return void
     */
    public function register(Panel $panel): void
    {
        $routes = $panel->getRoutes();
        $panel
            ->pages([
                ChangePassword::class,
            ])
            ->routes(function (Panel $panel) use ($routes) {
                if ($routes) {
                    $routes($panel);
                }
                Route::get('/password-expired', PasswordExpired::class)->name('password-expired');
            })
            ->userMenuItems($this->getUserMenuItems($panel));
    }

    /**
     * プラグインの初期化処理
     *
     * 設定の検証や初期化処理を実行
     * 現在は何も実行しないが、将来的な拡張のために用意
     *
     * @param Panel $panel Filamentパネルインスタンス
     * @return void
     */
    public function boot(Panel $panel): void
    {
        // 将来的な初期化処理のために用意
    }

    /**
     * プラグインインスタンスを作成
     *
     * シンプルなファクトリメソッド
     *
     * @return static プラグインインスタンス
     */
    public static function make(): static
    {
        return app(static::class);
    }

    /**
     * 現在のガードからユーザーモデルクラスを取得
     *
     * Laravelの認証ガード設定からEloquentモデルクラスを取得する
     *
     * @return string ユーザーモデルクラス名
     */
    public function getUserClass(): string
    {
        $guard = filament()->getCurrentPanel()->getAuthGuard();
        $provider = config("auth.guards.{$guard}.provider");

        if (!$provider) {
            throw new \InvalidArgumentException("Guard '{$guard}' does not exist or has no provider configured.");
        }

        $model = config("auth.providers.{$provider}.model");

        if (!$model) {
            throw new \InvalidArgumentException("Provider '{$provider}' does not have a model configured.");
        }

        if (!class_exists($model)) {
            throw new \InvalidArgumentException("User model class '{$model}' does not exist.");
        }

        return $model;
    }

    /**
     * ユーザーメニュー項目を取得
     *
     * 設定に基づいてユーザーメニューに表示する項目を決定する
     *
     * @param Panel $panel Filamentパネルインスタンス
     * @return array<MenuItem> メニュー項目の配列
     */
    protected function getUserMenuItems(Panel $panel): array
    {
        $menuItems = [];

        // パスワード変更メニューの追加判定
        if ($this->shouldShowPasswordChangeMenuItem($panel)) {
            $menuItems[] = MenuItem::make()
                ->label(fn(): string => __('green-auth::auth.change_password.title'))
                ->url(fn(): string => ChangePassword::getUrl())
                ->icon('heroicon-o-key');
        }

        return $menuItems;
    }

    /**
     * パスワード変更メニュー項目を表示するかどうかを判定
     *
     * @param Panel $panel Filamentパネルインスタンス
     * @return bool 表示フラグ
     */
    protected function shouldShowPasswordChangeMenuItem(Panel $panel): bool
    {
        $guard = $panel->getAuthGuard();
        $configKey = "green-auth.guards.{$guard}.user_menu.allow_password_change";

        return config($configKey, true); // デフォルトは true
    }
}
