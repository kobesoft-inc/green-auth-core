<?php

namespace Green\Auth\Permission;

class Super extends BasePermission
{
    public static string $id = '*';

    public static function getName(): string
    {
        return __('green-auth::permissions.super');
    }
}
