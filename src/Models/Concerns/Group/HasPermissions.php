<?php

namespace Green\Auth\Models\Concerns\Group;

trait HasPermissions
{
    public function hasPermission(string $permission): bool
    {
        return $this->getAllGroupRoles()->contains(fn ($role) => $role->hasPermission($permission));
    }

    public function getAllPermissions(): array
    {
        return array_unique(
            $this->getAllGroupRoles()->flatMap(fn ($role) => $role->getPermissions())->all()
        );
    }

    public function getAllGroupRoles()
    {
        return collect([$this, ...$this->ancestors])
            ->flatMap(fn ($group) => $group->roles)
            ->unique('id');
    }
}
