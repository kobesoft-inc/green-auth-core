<?php

namespace Green\Auth\Filament\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;

/**
 * ユーザー表示用カラム（アバター + 名前）
 */
class UserColumn extends Column
{
    protected string $view = 'green-auth::filament.tables.columns.user-column';

    protected int | Closure $size = 32;

    protected bool | Closure $isCircular = true;

    /**
     * アバターのサイズを設定（ピクセル単位）
     */
    public function size(int | Closure $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * 円形表示するかを設定
     */
    public function circular(bool | Closure $circular = true): static
    {
        $this->isCircular = $circular;

        return $this;
    }

    /**
     * ビューデータを取得
     */
    public function getViewData(): array
    {
        $record = $this->getRecord();
        $size = $this->evaluate($this->size);

        return array_merge(parent::getViewData(), [
            'imageUrl' => method_exists($record, 'getAvatarUrl') ? $record->getAvatarUrl() : null,
            'name' => $record?->name,
            'size' => $size . 'px',
            'isCircular' => $this->evaluate($this->isCircular),
            'isBlank' => blank($this->getState()),
            'placeholder' => $this->getPlaceholder(),
        ]);
    }
}
