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
        $model = static::getModel();

        return method_exists($model, 'groups')
            && method_exists($model, 'isModelEnabled')
            && $model::isModelEnabled('group');
    }

    /**
     * モデルがロールトレイトを持っているかチェック
     *
     * @return bool ロールトレイトの有無
     */
    protected static function hasRolesTrait(): bool
    {
        $model = static::getModel();

        return method_exists($model, 'roles')
            && method_exists($model, 'isModelEnabled')
            && $model::isModelEnabled('role');
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
        $model = static::getModel();

        return method_exists($model, 'loginLogs')
            && method_exists($model, 'isModelEnabled')
            && $model::isModelEnabled('login_log');
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
