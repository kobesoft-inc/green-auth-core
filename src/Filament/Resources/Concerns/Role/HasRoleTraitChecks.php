<?php

namespace Green\Auth\Filament\Resources\Concerns\Role;

/**
 * ロールモデルのトレイト存在チェック機能を提供するトレイト
 */
trait HasRoleTraitChecks
{
    /**
     * モデルがユーザートレイトを持っているかチェック
     *
     * @return bool ユーザートレイトの有無
     */
    protected static function hasUsersTrait(): bool
    {
        return method_exists(static::getModel(), 'users');
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
     * モデルが権限トレイトを持っているかチェック
     *
     * @return bool 権限トレイトの有無
     */
    protected static function hasPermissionsTrait(): bool
    {
        return method_exists(static::getModel(), 'getPermissions');
    }

}
