<?php

namespace Green\Auth\Filament\Resources\Concerns;

trait HasModelLabels
{
    /**
     * リソースの表示用単数ラベルを取得
     */
    public static function getModelLabel(): string
    {
        return static::getTranslatedModelLabel(false);
    }

    /**
     * リソースの表示用複数形ラベルを取得
     */
    public static function getPluralModelLabel(): string
    {
        return static::getTranslatedModelLabel(true);
    }

    /**
     * 翻訳されたモデルラベルを取得（単数形または複数形）
     */
    protected static function getTranslatedModelLabel(bool $plural = false): string
    {
        $modelClass = static::getModel();
        $modelBaseName = class_basename($modelClass);
        $modelKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $modelBaseName));

        // ガードを特定してlabels設定からキーを取得
        $guard = static::getGuardName();
        $labelKeyPath = $plural ? "{$modelKey}_plural" : $modelKey;
        $labelKey = config("green-auth.guards.{$guard}.labels.{$labelKeyPath}");

        if ($labelKey) {
            $translation = __($labelKey);
            if ($translation !== $labelKey) {
                return $translation;
            }
        }

        // フォールバック：旧方式
        $translationKey = 'green-auth::admin.models.' . $modelKey;
        $translation = __($translationKey);

        // 翻訳が存在する場合は返す
        if ($translation !== $translationKey) {
            return $translation;
        }

        // 親メソッドにフォールバック
        return $plural ? parent::getPluralModelLabel() : parent::getModelLabel();
    }

    /**
     * ガード名を取得
     */
    protected static function getGuardName(): string
    {
        return filament()->getAuthGuard();
    }
}
