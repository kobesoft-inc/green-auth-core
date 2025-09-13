<?php

namespace Green\Auth\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * PermissionManager Facade
 *
 * @method static \Green\Auth\Permission\PermissionManager register(string|array $permission, ?string $guard = null)
 * @method static Collection all(?string $guard = null)
 * @method static \Green\Auth\Permission\PermissionManager guard(string $guard)
 */
class PermissionManager extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'green-auth.permission-manager';
    }
}
