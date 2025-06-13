<?php

namespace Green\Auth\Permission;

use Illuminate\Support\Collection;

/**
 * パーミッション管理クラス
 *
 * ガードが指定されない場合、パーミッションは全てのガードに対して有効な
 * ワイルドカード '*' ガードに登録される
 */
class PermissionManager
{
    /**
     * ガード別のパーミッションクラス名配列
     * '*' ガードは全てのガードに共通のパーミッション
     * @var array<string, array<string, string>>
     */
    protected array $permissions = [];

    /**
     * デフォルトガード
     */
    protected ?string $defaultGuard = null;

    /**
     * パーミッションを登録
     * BasePermissionを派生させたクラスのクラス名、またはその配列を受け取る
     * $guardが指定されない場合は、全てのガードに対して登録される
     */
    public function register(string|array $permission, ?string $guard = null): self
    {
        // guardが指定されない場合は、ワイルドカード '*' に登録
        $guard = $guard ?? '*';

        if (!isset($this->permissions[$guard])) {
            $this->permissions[$guard] = [];
        }

        if (is_array($permission)) {
            // 複数のパーミッションクラス名
            foreach ($permission as $p) {
                $this->register($p, $guard);
            }
        } else if (is_string($permission)) {
            // 単一のパーミッションクラス名
            if (!is_subclass_of($permission, BasePermission::class)) {
                throw new \InvalidArgumentException("Class {$permission} must extend BasePermission");
            }

            $permissionId = $permission::getId();
            $this->permissions[$guard][$permissionId] = $permission;
        } else {
            throw new \RuntimeException("permission must be string or array.");
        }

        return $this;
    }

    /**
     * 指定ガードのすべてのパーミッションを取得
     * ワイルドカード '*' に登録されたパーミッションも含める
     */
    public function all(?string $guard = null): Collection
    {
        $guard = $guard ?? $this->defaultGuard;

        if ($guard === null) {
            throw new \RuntimeException('Guard name is required');
        }

        // ワイルドカード '*' のパーミッションを取得
        $wildcardPermissions = $this->permissions['*'] ?? [];

        // 指定ガード固有のパーミッションを取得
        $guardSpecificPermissions = $this->permissions[$guard] ?? [];

        // 両方をマージ（ガード固有のパーミッションが優先）
        $allPermissions = array_merge($wildcardPermissions, $guardSpecificPermissions);

        return collect($allPermissions)->sortBy('sort');
    }

    /**
     * ガード固有の操作用に現在のガードを設定
     */
    public function guard(string $guard): self
    {
        $manager = clone $this;
        $manager->defaultGuard = $guard;
        return $manager;
    }
}
