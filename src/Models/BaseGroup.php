<?php

namespace Green\Auth\Models;

use Green\Auth\Models\Concerns\Group\HasHierarchy;
use Green\Auth\Models\Concerns\Group\HasPermissions;
use Green\Auth\Models\Concerns\Group\HasRoles;
use Green\Auth\Models\Concerns\Group\HasUsers;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

abstract class BaseGroup extends Model
{
    use HasHierarchy;
    use HasPermissions;
    use HasRoles;
    use HasUsers;
    use NodeTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'parent_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
