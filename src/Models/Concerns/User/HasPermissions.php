<?php

namespace Green\Auth\Models\Concerns\User;

trait HasPermissions
{
    /**
     * ユーザーが特定の権限を持っているかチェック
     * 直接所属するロール、所属グループのロール（先祖まで遡る）をすべてチェック
     */
    public function hasPermission(string $permission): bool
    {
        // 直接所属するロールの権限をチェック
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }

        // 所属グループから権限をチェック
        foreach ($this->groups as $group) {
            if ($group->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 複数の権限をすべて持っているかチェック
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * 複数の権限のうち少なくとも一つを持っているかチェック
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Laravel標準のcanメソッドをオーバーライド
     * 文字列の場合は権限チェック、それ以外はLaravel標準の動作
     */
    public function can($abilities, $arguments = []): bool
    {
        // 単一の文字列の場合は権限チェック
        if (is_string($abilities) && empty($arguments)) {
            return $this->hasPermission($abilities);
        }

        // それ以外はLaravel標準の動作
        return parent::can($abilities, $arguments);
    }

    /**
     * 権限チェック用のヘルパーメソッド（cannot エイリアス）
     */
    public function cannot($abilities, $arguments = []): bool
    {
        return ! $this->can($abilities, $arguments);
    }
}
