<?php

namespace Green\AuthCore\Filament\Resources\Concerns\Group;

/**
 * グループモデルのトレイト存在チェック機能を提供するトレイト
 */
trait HasGroupTraitChecks
{
    /**
     * モデルが親グループトレイトを持っているかチェック
     * 
     * @return bool 親グループトレイトの有無
     */
    protected static function hasParentGroupTrait(): bool
    {
        return method_exists(static::getModel(), 'parent');
    }

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
     * モデルがロールトレイトを持っているかチェック
     * 
     * @return bool ロールトレイトの有無
     */
    protected static function hasRolesTrait(): bool
    {
        return method_exists(static::getModel(), 'roles');
    }

}