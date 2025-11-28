<?php

namespace Green\Auth\Models\Concerns\Group;

use Green\Auth\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * ユーザー管理機能を提供するトレイト
 *
 * @mixin Model
 *
 * @property-read Collection $users グループに所属するユーザー
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
            static::getUserGroupsPivotTable()
        )->withTimestamps();
    }

    /**
     * ユーザーがグループに所属しているか確認
     */
    public function hasUser($user): bool
    {
        if ($user instanceof Model) {
            return $this->users()->where($this->users()->getRelated()->getTable().'.id', $user->id)->exists();
        }

        return $this->users()->where($this->users()->getRelated()->getTable().'.id', $user)->exists();
    }
}
