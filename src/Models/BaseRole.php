<?php

namespace Green\Auth\Models;

use Green\Auth\Models\Concerns\Role\HasGroups;
use Green\Auth\Models\Concerns\Role\HasPermissions;
use Green\Auth\Models\Concerns\Role\HasUsers;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRole extends Model
{
    use HasGroups;
    use HasPermissions;
    use HasUsers;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'permissions',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'permissions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
