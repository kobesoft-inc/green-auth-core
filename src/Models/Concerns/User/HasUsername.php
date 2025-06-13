<?php

namespace Green\Auth\Models\Concerns\User;

use Green\Auth\Models\Concerns\HasModelConfig;

/**
 * ユーザー名機能を提供するトレイト
 */
trait HasUsername
{
    use HasModelConfig;

    /**
     * ユーザー名カラム名を取得
     */
    public function getUsernameColumn(): string
    {
        return static::config('username_column', 'username');
    }

    /**
     * ユーザー名を取得
     */
    public function getUsername(): ?string
    {
        $column = $this->getUsernameColumn();
        return $this->getAttribute($column);
    }

    /**
     * ユーザー名を設定
     */
    public function setUsername(?string $username): void
    {
        $column = $this->getUsernameColumn();
        $this->setAttribute($column, $username);
    }

    /**
     * ユーザー名でユーザーを検索
     */
    public static function findByUsername(string $username): ?static
    {
        $instance = new static();
        $column = $instance->getUsernameColumn();

        return static::where($column, $username)->first();
    }

    /**
     * ユーザー名またはメールアドレスでユーザーを検索
     */
    public static function findByUsernameOrEmail(string $identifier): ?static
    {
        return static::where('email', $identifier)
            ->orWhere(function ($query) use ($identifier) {
                $instance = new static();
                $column = $instance->getUsernameColumn();
                $query->where($column, $identifier);
            })
            ->first();
    }

    /**
     * ユーザー名が設定されているかチェック
     */
    public function hasUsername(): bool
    {
        return !empty($this->getUsername());
    }
}
