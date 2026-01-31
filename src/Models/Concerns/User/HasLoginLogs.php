<?php

namespace Green\Auth\Models\Concerns\User;

use Green\Auth\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    public function loginLogs(): ?HasMany
    {
        $logClass = static::getLoginLogClass();
        if ($logClass === null) {
            return null;
        }

        return $this->hasMany($logClass, $this->getForeignKey());
    }

    /**
     * 最新のログインログを取得
     */
    public function latestLoginLog(): ?HasOne
    {
        $logClass = static::getLoginLogClass();
        if ($logClass === null) {
            return null;
        }

        return $this->hasOne($logClass, $this->getForeignKey())->latest();
    }

    /**
     * 特定期間のログインログを取得
     */
    public function loginLogsInPeriod($startDate, $endDate): ?HasMany
    {
        $relation = $this->loginLogs();
        if ($relation === null) {
            return null;
        }

        return $relation
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc');
    }

    /**
     * 最後のログイン日時を取得
     */
    public function getLastLoginAt()
    {
        $relation = $this->latestLoginLog();
        if ($relation === null) {
            return null;
        }

        $latestLog = $relation->first();

        return $latestLog ? $latestLog->created_at : null;
    }

    /**
     * 最近のログインログを取得（デフォルト24時間）
     */
    public function recentLoginLogs(int $hours = 24): ?HasMany
    {
        $relation = $this->loginLogs();
        if ($relation === null) {
            return null;
        }

        return $relation
            ->where('created_at', '>=', now()->subHours($hours))
            ->orderBy('created_at', 'desc');
    }

    /**
     * 特定IPアドレスからのログインログを取得
     */
    public function loginLogsFromIp(string $ipAddress): ?HasMany
    {
        $relation = $this->loginLogs();
        if ($relation === null) {
            return null;
        }

        return $relation->where('ip_address', $ipAddress);
    }

    /**
     * ログイン回数を取得
     */
    public function getLoginCount(): int
    {
        $relation = $this->loginLogs();

        return $relation ? $relation->count() : 0;
    }

    /**
     * 今日のログイン回数を取得
     */
    public function getTodayLoginCount(): int
    {
        $relation = $this->loginLogs();
        if ($relation === null) {
            return 0;
        }

        return $relation
            ->whereDate('created_at', today())
            ->count();
    }
}
