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
        return $this->hasMany(static::getLoginLogClass(), $this->getForeignKey());
    }

    /**
     * 最新のログインログを取得
     */
    public function latestLoginLog(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(static::getLoginLogClass(), $this->getForeignKey())->latest();
    }

    /**
     * 特定期間のログインログを取得
     */
    public function loginLogsInPeriod($startDate, $endDate): HasMany
    {
        return $this->loginLogs()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');
    }

    /**
     * 最後のログイン日時を取得
     */
    public function getLastLoginAt()
    {
        $latestLog = $this->latestLoginLog()->first();
        return $latestLog ? $latestLog->created_at : null;
    }


    /**
     * 最近のログインログを取得（デフォルト24時間）
     */
    public function recentLoginLogs(int $hours = 24): HasMany
    {
        return $this->loginLogs()
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'desc');
    }

    /**
     * 特定IPアドレスからのログインログを取得
     */
    public function loginLogsFromIp(string $ipAddress): HasMany
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
            ->whereDate('created_at', today())
            ->count();
    }
}
