<?php

namespace Green\Auth\Filament\Resources\Concerns\User;

/**
 * ユーザーモデルのトレイト存在チェック機能を提供するトレイト
 */
trait HasUserTraitChecks
{
    /**
     * モデルがアバタートレイトを持っているかチェック
     *
     * @return bool アバタートレイトの有無
     */
    protected static function hasAvatarTrait(): bool
    {
        return method_exists(static::getModel(), 'getAvatarUrl');
    }

    /**
     * モデルがグループトレイトを持っているかチェック
     *
     * @return bool グループトレイトの有無
     */
    protected static function hasGroupsTrait(): bool
    {
        return method_exists(static::getModel(), 'groups');
    }

    /**
     * モデルがロールトレイトを持っているかチェック
     *
     * @return bool ロールトレイトの有無
     */
    protected static function hasRolesTrait(): bool
    {
        return method_exists(static::getModel(), 'roles');
    }

    /**
     * モデルが停止トレイトを持っているかチェック
     *
     * @return bool 停止トレイトの有無
     */
    protected static function hasSuspensionTrait(): bool
    {
        return method_exists(static::getModel(), 'isSuspended');
    }

    /**
     * モデルがログインログトレイトを持っているかチェック
     *
     * @return bool ログインログトレイトの有無
     */
    protected static function hasLoginLogTrait(): bool
    {
        return method_exists(static::getModel(), 'loginLogs');
    }

    /**
     * モデルがユーザー名トレイトを持っているかチェック
     *
     * @return bool ユーザー名トレイトの有無
     */
    protected static function hasUsernameTrait(): bool
    {
        return method_exists(static::getModel(), 'getUsernameColumn');
    }
}
