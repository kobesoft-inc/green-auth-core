<?php

namespace Green\Auth\Models\Concerns\User;

use Green\Auth\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ユーザーのログインログ機能を提供するトレイト
 *
 * @mixin Model
 */
trait HasLoginLogs
{
    use HasModelConfig;

    /**
     * ログインログとの一対多リレーション
     */
    public function loginLogs(): HasMany
    {
        return $this->hasMany(static::getLoginLogClass(), 'user_id');
    }

    /**
     * 最新のログインログを取得
     */
    public function latestLoginLog()
    {
        return $this->hasOne(static::getLoginLogClass(), 'user_id')->latest('login_at');
    }

    /**
     * 特定期間のログインログを取得
     */
    public function loginLogsInPeriod($startDate, $endDate)
    {
        return $this->loginLogs()
            ->whereBetween('login_at', [$startDate, $endDate])
            ->orderBy('login_at', 'desc');
    }

    /**
     * 最後のログイン日時を取得
     */
    public function getLastLoginAt()
    {
        $latestLog = $this->latestLoginLog()->first();
        return $latestLog ? $latestLog->login_at : null;
    }

    /**
     * 特定ガードでのログインログを取得
     */
    public function loginLogsForGuard(string $guardName)
    {
        return $this->loginLogs()->where('guard_name', $guardName);
    }

    /**
     * 最近のログインログを取得（デフォルト24時間）
     */
    public function recentLoginLogs(int $hours = 24)
    {
        return $this->loginLogs()
            ->where('login_at', '>=', now()->subHours($hours))
            ->orderBy('login_at', 'desc');
    }

    /**
     * 特定IPアドレスからのログインログを取得
     */
    public function loginLogsFromIp(string $ipAddress)
    {
        return $this->loginLogs()->where('ip_address', $ipAddress);
    }

    /**
     * ログイン回数を取得
     */
    public function getLoginCount(): int
    {
        return $this->loginLogs()->count();
    }

    /**
     * 今日のログイン回数を取得
     */
    public function getTodayLoginCount(): int
    {
        return $this->loginLogs()
            ->whereDate('login_at', today())
            ->count();
    }
}
