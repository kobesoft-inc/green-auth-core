<?php

namespace Green\Auth\Listeners;

use Throwable;
use Log;
use Green\Auth\Models\BaseLoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * ユーザーログイン履歴記録リスナー
 *
 * Laravelの標準Loginイベントを受信してログイン履歴をデータベースに記録
 */
class LogUserLogin
{
    /**
     * ログイン履歴記録処理を実行
     *
     * @param Login $event ログインイベント
     * @return void
     */
    public function handle(Login $event): void
    {
        try {
            // ログインログクラスを取得
            $loginLogClass = $this->getLoginLogClass($event->guard);

            if (!$loginLogClass) {
                // ログインログが設定されていない場合はスキップ
                return;
            }

            // クラスが存在するか確認
            if (!class_exists($loginLogClass)) {
                Log::warning("Login log class not found: {$loginLogClass} for guard: {$event->guard}");
                return;
            }

            // createLogメソッドが存在するか確認
            if (!method_exists($loginLogClass, 'createLog')) {
                Log::warning("createLog method not found in {$loginLogClass}");
                return;
            }

            // ログイン履歴を作成
            $loginLogClass::createLog(
                $event->user,
                request()
            );
        } catch (Throwable $exception) {
            // エラーをログに記録するが、処理は続行
            Log::error('Failed to log user login', [
                'user_id' => $event->user?->id ?? 'unknown',
                'guard' => $event->guard,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
        }
    }

    /**
     * 指定されたガードのログインログクラスを取得
     *
     * @param string $guard ガード名
     * @return string|null ログインログクラス名またはnull
     */
    protected function getLoginLogClass(string $guard): ?string
    {
        return config("green-auth.guards.{$guard}.models.login_log");
    }

    /**
     * ジョブの失敗をハンドル
     *
     * @param Login $event ログインイベント
     * @param Throwable $exception 例外
     * @return void
     */
    public function failed(Login $event, Throwable $exception): void
    {
        // ログイン履歴記録の失敗をログに記録
        Log::error('Failed to log user login', [
            'user_id' => $event->user?->id ?? 'unknown',
            'guard' => $event->guard,
            'error' => $exception->getMessage(),
        ]);
    }
}
