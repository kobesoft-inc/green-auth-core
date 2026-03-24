<?php

namespace Green\Auth\Models;

use Green\Auth\Models\Concerns\Group\HasHierarchy;
use Green\Auth\Models\Concerns\Group\HasPermissions;
use Green\Auth\Models\Concerns\Group\HasRoles;
use Green\Auth\Models\Concerns\Group\HasUsers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

abstract class BaseGroup extends Model
{
    use HasHierarchy;
    use HasPermissions;
    use HasRoles;
    use HasUsers;

    protected $fillable = [
        'name',
        'description',
        'parent_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(static::class, 'parent_id');
    }

    /**
     * 祖先グループをルートから順に取得
     */
    public function getAncestorsAttribute()
    {
        if (array_key_exists('ancestors', $this->relations)) {
            return $this->relations['ancestors'];
        }

        $ancestors = collect();
        $current = $this;

        for ($i = 0; $i < 20; $i++) {
            if (! $current->parent_id) {
                break;
            }
            $current = $current->parent;
            if (! $current) {
                break;
            }
            $ancestors->prepend($current);
        }

        $this->setRelation('ancestors', $ancestors);

        return $ancestors;
    }

    /**
     * 子孫グループを全て取得
     */
    public function getDescendantsAttribute()
    {
        if (array_key_exists('descendants', $this->relations)) {
            return $this->relations['descendants'];
        }

        $descendants = collect();
        $this->collectDescendants($this, $descendants);
        $this->setRelation('descendants', $descendants);

        return $descendants;
    }

    protected function collectDescendants(Model $group, &$descendants): void
    {
        foreach ($group->children as $child) {
            $descendants->push($child);
            $this->collectDescendants($child, $descendants);
        }
    }

    public function getDepth(): int
    {
        return $this->ancestors->count();
    }
}
