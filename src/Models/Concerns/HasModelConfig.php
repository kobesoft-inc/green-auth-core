<?php

namespace Green\Auth\Models\Concerns;

use Illuminate\Support\Str;

/**
 * モデル設定とリレーションシップ管理を提供するトレイト
 */
trait HasModelConfig
{
    /**
     * ガード名のキャッシュ
     *
     * @var string|null ガード名
     */
    private static ?string $guardName = null;

    /**
     * 設定値を取得（guard対応、クラス定数フォールバック）
     *
     * @param string $key 設定キー
     * @param mixed $default デフォルト値
     * @return mixed 設定値
     */
    protected static function config(string $key, $default = null)
    {
        $guard = static::getGuardName();

        // 設定ファイル > クラス定数 > デフォルト値の順で取得
        return config("green-auth.guards.{$guard}.{$key}")
            ?? config("green-auth.{$key}")
            ?? static::constant($key)
            ?? $default;
    }

    /**
     * クラス定数を取得
     *
     * @param string $name 定数名
     * @return mixed 定数値、存在しない場合はnull
     */
    protected static function constant(string $name)
    {
        $constantName = static::class . '::' . $name;

        return defined($constantName) ? constant($constantName) : null;
    }

    /**
     * ユーザーモデルクラスを取得
     *
     * @return string ユーザーモデルのクラス名
     * @throws \RuntimeException クラスが見つからない場合
     */
    protected static function getUserClass(): string
    {
        return static::getRelatedModelClass('user');
    }

    /**
     * グループモデルクラスを取得
     *
     * @return string グループモデルのクラス名
     * @throws \RuntimeException クラスが見つからない場合
     */
    protected static function getGroupClass(): string
    {
        return static::getRelatedModelClass('group');
    }

    /**
     * ロールモデルクラスを取得
     *
     * @return string ロールモデルのクラス名
     * @throws \RuntimeException クラスが見つからない場合
     */
    protected static function getRoleClass(): string
    {
        return static::getRelatedModelClass('role');
    }

    /**
     * ログインログモデルクラスを取得
     *
     * @return string ログインログモデルのクラス名
     * @throws \RuntimeException クラスが見つからない場合
     */
    protected static function getLoginLogClass(): string
    {
        return static::getRelatedModelClass('loginlog');
    }

    /**
     * 関連するモデルクラスを取得
     *
     * @param string $modelType モデルタイプ（'user', 'group', 'role'）
     * @return string モデルクラス名
     * @throws \RuntimeException クラスが見つからない場合
     */
    protected static function getRelatedModelClass(string $modelType): string
    {
        $modelTypeLower = strtolower($modelType);
        $modelTypeUcfirst = ucfirst($modelTypeLower);

        // 設定から取得（guard対応、クラス定数フォールバック）
        $configClass = static::config("models.{$modelTypeLower}");

        if ($configClass && class_exists($configClass)) {
            return $configClass;
        }

        // クラス名から推測（例: AdminUser → AdminGroup, AdminRole）
        $currentType = static::getModelType();
        $guessedClass = str_replace($currentType, $modelTypeUcfirst, static::class);

        if (class_exists($guessedClass)) {
            return $guessedClass;
        }

        throw new \RuntimeException("Cannot find {$modelTypeUcfirst} class. Tried config and guessed [{$guessedClass}].");
    }

    /**
     * ユーザー・グループ間のピボットテーブル名を取得
     *
     * @return string ピボットテーブル名
     */
    protected static function getUserGroupsPivotTable(): string
    {
        return static::getPivotTableName('user_groups');
    }

    /**
     * ユーザー・ロール間のピボットテーブル名を取得
     *
     * @return string ピボットテーブル名
     */
    protected static function getUserRolesPivotTable(): string
    {
        return static::getPivotTableName('user_roles');
    }

    /**
     * グループ・ロール間のピボットテーブル名を取得
     *
     * @return string ピボットテーブル名
     */
    protected static function getGroupRolesPivotTable(): string
    {
        return static::getPivotTableName('group_roles');
    }

    /**
     * ピボットテーブル名を取得
     *
     * @param string $tableKey テーブルキー（'user_groups', 'user_roles', 'group_roles'）
     * @return string テーブル名
     */
    private static function getPivotTableName(string $tableKey): string
    {
        return static::config("tables.{$tableKey}", $tableKey);
    }

    /**
     * 外部キー名を推測
     *
     * @param string $modelClass モデルクラス名
     * @return string 外部キー名（例：'user_id'）
     */
    protected static function getForeignKeyName(string $modelClass): string
    {
        return Str::snake(class_basename($modelClass)) . '_id';
    }

    /**
     * 現在のガードを取得
     *
     * @return string ガード名
     * @throws \RuntimeException ガードが特定できない場合
     */
    public static function getGuardName(): string
    {
        // 設定からモデルクラスを検索してガードを特定
        $modelClass = static::class;
        $guards = config('green-auth.guards', []);

        foreach ($guards as $guardName => $guardConfig) {
            $models = $guardConfig['models'] ?? [];
            foreach ($models as $configuredClass) {
                if ($configuredClass === $modelClass) {
                    return static::$guardName = $guardName;
                }
            }
        }

        // ガードが見つからない場合はエラー
        throw new \RuntimeException("Unable to determine guard for model: " . static::class);
    }

    /**
     * 現在のモデルタイプを取得 ('User', 'Group', 'Role')
     *
     * @return string モデルタイプ（'User', 'Group', 'Role'）
     * @throws \RuntimeException モデルタイプが特定できない場合
     */
    protected static function getModelType(): string
    {
        $className = class_basename(static::class);

        if (str_contains($className, 'User')) {
            return 'User';
        } elseif (str_contains($className, 'Group')) {
            return 'Group';
        } elseif (str_contains($className, 'Role')) {
            return 'Role';
        }

        // デフォルトは末尾から推測
        if (preg_match('/(.+)(User|Group|Role)$/', $className, $matches)) {
            return $matches[2];
        }

        throw new \RuntimeException("Cannot determine model type from class name: {$className}");
    }
}
