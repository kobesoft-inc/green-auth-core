<?php

namespace Green\Auth;

use Green\Auth\Console\Commands\InstallCommand;
use Green\Auth\Facades\PermissionManager;
use Green\Auth\Listeners\LogUserLogin;
use Green\Auth\Permission\Super;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Green Authコアパッケージ用サービスプロバイダー
 *
 * 認証機能、Filamentプラグイン、ルート、設定の登録を管理
 */
class GreenAuthServiceProvider extends ServiceProvider
{
    /**
     * サービスの登録
     *
     * 設定ファイルのマージ、PermissionManagerのシングルトン登録、
     * Filamentプラグインの登録を実行
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/green-auth.php', 'green-auth'
        );

        // PermissionManagerを登録
        $this->app->singleton('green-auth.permission-manager', function () {
            return new \Green\Auth\Permission\PermissionManager();
        });

        $this->app->alias('green-auth.permission-manager', \Green\Auth\Permission\PermissionManager::class);

        // 権限マネージャーに登録
        PermissionManager::register([
            Super::class,
        ]);

        // イベントリスナーの登録
        Event::listen(Login::class, LogUserLogin::class);
    }

    /**
     * サービスの起動
     *
     * ビュー、言語ファイル、ルートの読み込み、
     * コマンドとパブリッシュ可能リソースの登録を実行
     *
     * @return void
     */
    public function boot(): void
    {
        // ビューの登録
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'green-auth');

        // 言語ファイルの登録
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'green-auth');

        // Livewireコンポーネントの手動登録
        \Livewire\Livewire::component('green-auth.password-expired', \Green\Auth\Filament\Pages\Auth\PasswordExpired::class);


        // パブリッシュ可能なリソース
        if ($this->app->runningInConsole()) {
            // コマンドの登録
            $this->commands([
                InstallCommand::class,
            ]);

            // 設定ファイル
            $this->publishes([
                __DIR__ . '/../config/green-auth.php' => config_path('green-auth.php'),
            ], 'green-auth-config');

            // ビュー
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/green-auth'),
            ], 'green-auth-views');

            // マイグレーション
            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'green-auth-migrations');

            // 言語ファイル
            $this->publishes([
                __DIR__ . '/../lang' => resource_path('lang/vendor/green-auth'),
            ], 'green-auth-lang');
        }
    }
}
