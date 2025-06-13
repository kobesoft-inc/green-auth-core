<?php

namespace Green\Auth\Permission;

class Super extends BasePermission
{
    static string $id = '*';

    public static function getName(): string
    {
        return __('green-auth::permissions.super');
    }
}
