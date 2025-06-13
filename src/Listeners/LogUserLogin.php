<?php

namespace Green\Auth\Listeners;

use Green\Auth\Models\BaseLoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * ユーザーログイン履歴記録リスナー
 * 
 * Laravelの標準Loginイベントを受信してログイン履歴をデータベースに記録
 */
class LogUserLogin implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * ログイン履歴記録処理を実行
     *
     * @param Login $event ログインイベント
     * @return void
     */
    public function handle(Login $event): void
    {
        // ログインログクラスを取得
        $loginLogClass = $this->getLoginLogClass($event->guard);
        
        if (!$loginLogClass) {
            // ログインログが設定されていない場合はスキップ
            return;
        }

        // ログイン履歴を作成
        $loginLogClass::createLog(
            $event->user,
            $event->guard,
            request()
        );
    }

    /**
     * 指定されたガードのログインログクラスを取得
     *
     * @param string $guard ガード名
     * @return string|null ログインログクラス名またはnull
     */
    protected function getLoginLogClass(string $guard): ?string
    {
        // 設定からガード固有のログインログクラスを取得
        $loginLogClass = config("green-auth.guards.{$guard}.login_log_model");
        
        if ($loginLogClass && class_exists($loginLogClass)) {
            return $loginLogClass;
        }

        // フォールバック: デフォルトのログインログクラスを取得
        $defaultLoginLogClass = config('green-auth.models.login_log');
        
        if ($defaultLoginLogClass && class_exists($defaultLoginLogClass)) {
            return $defaultLoginLogClass;
        }

        return null;
    }

    /**
     * ジョブの失敗をハンドル
     *
     * @param Login $event ログインイベント
     * @param \Throwable $exception 例外
     * @return void
     */
    public function failed(Login $event, \Throwable $exception): void
    {
        // ログイン履歴記録の失敗をログに記録
        \Log::error('Failed to log user login', [
            'user_id' => $event->user?->id ?? 'unknown',
            'guard' => $event->guard,
            'error' => $exception->getMessage(),
        ]);
    }
}