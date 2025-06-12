<?php

namespace Green\AuthCore\Models\Concerns\Group;

use Green\AuthCore\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * グループのロール機能を提供するトレイト
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
            static::getGroupRolesPivotTable()
        )->withTimestamps();
    }
}