<?php

namespace Green\AuthCore\Models;

use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

abstract class BaseGroup extends Model
{
    use NodeTrait;
    use Concerns\Group\HasUsers;
    use Concerns\Group\HasRoles;
    use Concerns\Group\HasPermissions;

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