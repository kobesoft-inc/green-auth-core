<?php

namespace Green\Auth\Permission;

class Super extends BasePermission
{
    public static string $id = 'super';

    public static function getName(): string
    {
        return __('green-auth::permissions.super');
    }
}
