<?php

namespace Green\Auth\Filament\Resources\Concerns;

trait HasModelLabels
{
    /**
     * リソースの表示用単数ラベルを取得
     */
    public static function getModelLabel(): string
    {
        return static::getTranslatedModelLabel(null, false);
    }

    /**
     * リソースの表示用複数形ラベルを取得
     */
    public static function getPluralModelLabel(): string
    {
        return static::getTranslatedModelLabel(null, true);
    }

    /**
     * 翻訳されたモデルラベルを取得（単数形または複数形）
     */
    protected static function getTranslatedModelLabel(?string $key = null, bool $plural = false): string
    {
        $modelKey = $key ?? strtolower(static::getModel()::getModelType());
        $guard = static::getModel()::getGuardName();

        // ガードを特定してlabels設定からキーを取得
        $labelKeyPath = $plural ? "{$modelKey}_plural" : $modelKey;
        $labelKey = config("green-auth.guards.{$guard}.labels.{$labelKeyPath}");

        if ($labelKey) {
            $translation = __($labelKey);
            if ($translation !== $labelKey) {
                return $translation;
            }
        }

        // ラベルの定義なし
        $translationKey = 'green-auth::admin.models.'.$modelKey;
        $translation = __($translationKey);

        // 翻訳が存在する場合は返す
        if ($translation !== $translationKey) {
            return $translation;
        }

        // 親メソッドにフォールバック
        return $plural ? parent::getPluralModelLabel() : parent::getModelLabel();
    }

    /**
     * フィールド名をローカライズして取得（user/role/group自動置換対応）
     */
    protected static function getLocalizedFieldLabel(string $fieldKey, bool $pluralModels = false): string
    {
        $guard = static::getModel()::getGuardName();

        // デフォルトのローカライズ文字列を準備
        $variables = [
            'user' => static::getTranslatedModelLabel('user', $pluralModels),
            'group' => static::getTranslatedModelLabel('group', $pluralModels),
            'role' => static::getTranslatedModelLabel('role', $pluralModels),
        ];

        // ガード固有の設定を確認
        $labelKey = config("green-auth.guards.{$guard}.field_labels.{$fieldKey}");

        if ($labelKey) {
            $translation = __($labelKey, $variables);
            if ($translation !== $labelKey) {
                return $translation;
            }
        }

        // フォールバック: デフォルトの翻訳キー
        $fallbackKey = "green-auth::fields.{$fieldKey}";
        $fallbackTranslation = __($fallbackKey, $variables);

        if ($fallbackTranslation !== $fallbackKey) {
            return $fallbackTranslation;
        }

        // 最終フォールバック: フィールドキーをそのまま返す
        return ucfirst(str_replace('_', ' ', $fieldKey));
    }
}
