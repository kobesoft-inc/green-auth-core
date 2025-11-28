<?php

namespace Green\Auth\Filament\Tables\Columns;

use Closure;
use Filament\Facades\Filament;
use Filament\Tables\Columns\Column;

/**
 * ユーザー表示用カラム（アバター + 名前）
 *
 * FilamentのAvatarProviderを使用してアバターを取得する
 */
class UserColumn extends Column
{
    protected string $view = 'green-auth::filament.tables.columns.user-column';

    protected int|Closure $size = 32;

    protected bool|Closure $isCircular = true;

    /**
     * アバターのサイズを設定（ピクセル単位）
     */
    public function size(int|Closure $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * 円形表示するかを設定
     */
    public function circular(bool|Closure $circular = true): static
    {
        $this->isCircular = $circular;

        return $this;
    }

    /**
     * ビューデータを取得
     */
    public function getViewData(): array
    {
        $state = $this->getState();
        $record = $this->getRecord();
        $size = $this->evaluate($this->size);

        $imageUrl = null;
        $name = null;

        // stateがユーザーモデルの場合（リレーション経由）
        if ($state && is_object($state)) {
            $imageUrl = Filament::getUserAvatarUrl($state);
            $name = $state->name ?? null;
        }
        // stateが文字列の場合（直接属性を指定）、recordから取得
        elseif ($record) {
            $imageUrl = Filament::getUserAvatarUrl($record);
            $name = $state;
        }

        return array_merge(parent::getViewData(), [
            'imageUrl' => $imageUrl,
            'name' => $name,
            'size' => $size.'px',
            'isCircular' => $this->evaluate($this->isCircular),
            'isBlank' => blank($state),
            'placeholder' => $this->getPlaceholder(),
        ]);
    }
}
