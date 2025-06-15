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
    public static function getUsernameColumn(): string
    {
        return static::config('username_column', 'username');
    }
}
