<?php

namespace Green\AuthCore\Models\Concerns\Role;

use Green\AuthCore\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ロールのユーザー機能を提供するトレイト
 *
 * @mixin Model
 */
trait HasUsers
{
    use HasModelConfig;

    /**
     * ユーザーとの多対多リレーション
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            static::getUserClass(),
            static::getUserRolesPivotTable()
        )->withTimestamps();
    }
}