<?php

namespace Green\Auth\Models\Concerns\User;

use Carbon\Carbon;
use Green\Auth\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Model;

/**
 * パスワード有効期限機能を提供するトレイト
 *
 * @mixin Model
 */
trait HasPasswordExpiration
{
    use HasModelConfig;

    /**
     * パスワード有効期限カラム名を取得
     */
    protected function getPasswordExpiresAtColumn(): string
    {
        return static::config('password_expires_at_column', 'password_expires_at');
    }

    /**
     * パスワード有効期限日数を取得
     */
    protected function getPasswordExpirationDays(): int
    {
        return static::config('password_expiration.days', 90);
    }

    /**
     * パスワード有効期限トレイトの初期化
     */
    public static function bootHasPasswordExpiration(): void
    {
        static::saving(function ($model) {
            if ($model->isDirty('password') && ! $model->isDirty($model->getPasswordExpiresAtColumn())) {
                $model->extendPasswordExpiration();
            }
        });
    }

    /**
     * パスワード有効期限のタイムスタンプを取得
     */
    public function getPasswordExpiresAt(): ?Carbon
    {
        $column = $this->getPasswordExpiresAtColumn();

        return $this->{$column} ? Carbon::parse($this->{$column}) : null;
    }

    /**
     * パスワードが期限切れかチェック
     */
    public function isPasswordExpired(): bool
    {
        $expiresAt = $this->getPasswordExpiresAt();

        if (! $expiresAt) {
            return false;
        }

        return $expiresAt->isPast();
    }

    /**
     * パスワード有効期限を延長
     */
    public function extendPasswordExpiration(): void
    {
        $expirationDays = $this->getPasswordExpirationDays();
        $column = $this->getPasswordExpiresAtColumn();

        if ($expirationDays > 0) {
            $this->{$column} = Carbon::now()->addDays($expirationDays);
        } else {
            $this->{$column} = null;
        }
    }

    /**
     * パスワード有効期限をリセット（過去の日付に設定）
     */
    public function resetPasswordExpiration(): void
    {
        $column = $this->getPasswordExpiresAtColumn();
        $this->{$column} = Carbon::now()->subDay();
    }
}
