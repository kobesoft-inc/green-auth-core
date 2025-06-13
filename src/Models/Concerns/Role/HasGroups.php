<?php

namespace Green\Auth\Models\Concerns\Role;

use Green\Auth\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ロールのグループ機能を提供するトレイト
 *
 * @mixin Model
 */
trait HasGroups
{
    use HasModelConfig;

    /**
     * グループとの多対多リレーション
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            static::getGroupClass(),
            static::getGroupRolesPivotTable()
        )->withTimestamps();
    }
}
