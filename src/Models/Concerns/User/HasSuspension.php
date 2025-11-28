<?php

namespace Green\Auth\Models\Concerns\User;

use Carbon\Carbon;
use Green\Auth\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Model;

/**
 * 利用停止機能を提供するトレイト
 *
 * @mixin Model
 */
trait HasSuspension
{
    use HasModelConfig;

    /**
     * 利用停止カラム名を取得
     */
    protected function getSuspendedAtColumn(): string
    {
        return static::config('suspended_at_column', 'suspended_at');
    }

    /**
     * 利用停止日時のタイムスタンプを取得
     */
    public function getSuspendedAt(): ?Carbon
    {
        $column = $this->getSuspendedAtColumn();

        return $this->{$column} ? Carbon::parse($this->{$column}) : null;
    }

    /**
     * ユーザーが利用停止中かチェック
     */
    public function isSuspended(): bool
    {
        return $this->getSuspendedAt() !== null;
    }

    /**
     * ユーザーを利用停止にする
     */
    public function suspend(): void
    {
        $column = $this->getSuspendedAtColumn();
        $this->forceFill([
            $column => Carbon::now(),
        ])->save();
    }

    /**
     * ユーザーの利用停止を解除する
     */
    public function unsuspend(): void
    {
        $column = $this->getSuspendedAtColumn();
        $this->forceFill([
            $column => null,
        ])->save();
    }
}
