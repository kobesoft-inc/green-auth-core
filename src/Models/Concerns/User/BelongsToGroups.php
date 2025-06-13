<?php

namespace Green\Auth\Models\Concerns\User;

use Green\Auth\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * グループ所属機能を提供するトレイト
 *
 * @mixin Model
 *
 * @property-read Collection $groups ユーザーが所属するグループ
 */
trait BelongsToGroups
{
    use HasModelConfig;

    /**
     * グループとの多対多リレーション
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            static::getGroupClass(),
            static::getUserGroupsPivotTable()
        )->withTimestamps();
    }

    /**
     * 複数グループに所属できるかどうかを確認
     * 設定から取得
     *
     * @return bool
     */
    public function canBelongToMultipleGroups(): bool
    {
        $guard = static::getGuardName();
        return config("green-auth.guards.{$guard}.user_permissions.multiple_groups", true);
    }

}
