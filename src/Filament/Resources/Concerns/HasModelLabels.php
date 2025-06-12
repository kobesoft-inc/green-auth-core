<?php

namespace Green\AuthCore\Filament\Resources\Concerns;

trait HasModelLabels
{
    /**
     * Get the displayable singular label of the resource.
     */
    public static function getModelLabel(): string
    {
        return static::getTranslatedModelLabel(false);
    }

    /**
     * Get the displayable plural label of the resource.
     */
    public static function getPluralModelLabel(): string
    {
        return static::getTranslatedModelLabel(true);
    }

    /**
     * Get the translated model label (singular or plural).
     */
    protected static function getTranslatedModelLabel(bool $plural = false): string
    {
        $modelClass = static::getModel();
        $modelBaseName = class_basename($modelClass);
        $modelKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $modelBaseName));
        
        $translationKey = 'green-auth::admin.models.' . $modelKey;
        $translation = __($translationKey);
        
        // If translation exists, return it
        if ($translation !== $translationKey) {
            return $translation;
        }
        
        // Fallback to parent method
        return $plural ? parent::getPluralModelLabel() : parent::getModelLabel();
    }
}