<?php

namespace Green\AuthCore\Models\Concerns\Group;

trait HasPermissions
{
    /**
     * グループが特定の権限を持っているかチェック
     * グループのロール（先祖まで遡る）をすべてチェック
     */
    public function hasPermission(string $permission): bool
    {
        // 自分のロールの権限をチェック
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        // 先祖グループのロールの権限をチェック
        foreach ($this->ancestors() as $ancestor) {
            foreach ($ancestor->roles as $role) {
                if ($role->hasPermission($permission)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * グループが持つすべての権限を取得（先祖まで遡る）
     */
    public function getAllPermissions(): array
    {
        $permissions = [];

        // 自分のロールの権限
        foreach ($this->roles as $role) {
            $permissions = array_merge($permissions, $role->getPermissions());
        }

        // 先祖グループのロールの権限
        foreach ($this->ancestors as $ancestor) {
            foreach ($ancestor->roles as $role) {
                $permissions = array_merge($permissions, $role->getPermissions());
            }
        }

        // 重複を除去して返す
        return array_unique($permissions);
    }

    /**
     * グループが持つロール一覧（先祖まで遡る）を取得
     */
    public function getAllGroupRoles()
    {
        $allRoles = collect();

        // 自分のロール
        $allRoles = $allRoles->merge($this->roles);

        // 先祖グループのロール
        foreach ($this->ancestors as $ancestor) {
            $allRoles = $allRoles->merge($ancestor->roles);
        }

        return $allRoles->unique('id');
    }
}