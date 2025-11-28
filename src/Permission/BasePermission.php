<?php

namespace Green\Auth\Permission;

/**
 * パーミッション定義のベースクラス
 *
 * このクラスを継承して具体的なPermissionクラスを作成します
 */
abstract class BasePermission
{
    /**
     * パーミッションID
     */
    protected static string $id = '';

    /**
     * 表示名
     */
    protected static string $name = '';

    /**
     * 説明
     */
    protected static ?string $description = null;

    /**
     * グループ名
     */
    protected static ?string $group = null;

    /**
     * 並び順
     */
    protected static int $sort = 0;

    /**
     * パーミッションIDを取得
     */
    public static function getId(): string
    {
        return static::$id ?: static::getDefaultId();
    }

    /**
     * 表示名を取得
     */
    public static function getName(): string
    {
        return static::$name ?: static::getDefaultName();
    }

    /**
     * 説明を取得
     */
    public static function getDescription(): ?string
    {
        return static::$description;
    }

    /**
     * グループ名を取得
     */
    public static function getGroup(): ?string
    {
        return static::$group;
    }

    /**
     * 並び順を取得
     */
    public static function getSort(): int
    {
        return static::$sort;
    }

    /**
     * デフォルトIDを生成（クラス名から自動生成）
     */
    protected static function getDefaultId(): string
    {
        $className = class_basename(static::class);

        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $className));
    }

    /**
     * デフォルト表示名を生成（クラス名から自動生成）
     */
    protected static function getDefaultName(): string
    {
        $className = class_basename(static::class);

        return preg_replace('/([a-z])([A-Z])/', '$1 $2', $className);
    }
}
