<?php

namespace Green\Auth\Models;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRole extends Model
{
    use Concerns\Role\HasPermissions;
    use Concerns\Role\HasUsers;
    use Concerns\Role\HasGroups;

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
