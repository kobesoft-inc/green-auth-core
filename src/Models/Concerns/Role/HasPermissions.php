<?php

namespace Green\AuthCore\Models\Concerns\Role;

trait HasPermissions
{
    /**
     * 特定の権限を持っているかチェック
     * ドット区切りの権限キーをサポート（例: aaa.bbb.ccc）
     * ワイルドカード権限もサポート（例: aaa.bbb.*）
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        // 完全一致をチェック
        if (in_array($permission, $permissions)) {
            return true;
        }

        // ワイルドカード権限をチェック
        foreach ($permissions as $rolePermission) {
            if ($this->matchesWildcardPermission($rolePermission, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 複数の権限を持っているかチェック（すべて必要）
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
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
     * すべての権限を取得
     */
    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }

    /**
     * ワイルドカード権限とのマッチングをチェック
     */
    protected function matchesWildcardPermission(string $rolePermission, string $checkPermission): bool
    {
        // ワイルドカードがない場合は完全一致のみ
        if (!str_contains($rolePermission, '*')) {
            return $rolePermission === $checkPermission;
        }

        // ワイルドカードを正規表現に変換
        $pattern = str_replace('*', '.*', preg_quote($rolePermission, '/'));
        
        return preg_match("/^{$pattern}$/", $checkPermission) === 1;
    }
}