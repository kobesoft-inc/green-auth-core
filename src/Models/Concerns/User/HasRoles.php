<?php

namespace Green\AuthCore\Models\Concerns\User;

use Green\AuthCore\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ロール機能を提供するトレイト
 *
 * @mixin Model
 */
trait HasRoles
{
    use HasModelConfig;

    /**
     * ロールとの多対多リレーション
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            static::getRoleClass(),
            static::getUserRolesPivotTable()
        )->withTimestamps();
    }

    /**
     * 複数ロールを持てるかどうかを確認
     * 設定から取得
     *
     * @return bool
     */
    public function canHaveMultipleRoles(): bool
    {
        $guard = static::getGuardName();
        return config("green-auth.guards.{$guard}.user_permissions.multiple_roles", true);
    }
}