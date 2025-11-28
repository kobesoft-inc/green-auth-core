<?php

namespace Green\Auth\Filament\Tables\Columns;

use Closure;
use Filament\Facades\Filament;
use Filament\Models\Contracts\HasName;
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
            $name = $state->name ?? null;
            $imageUrl = $this->safeGetAvatarUrl($state);
        }
        // stateが文字列の場合（直接属性を指定）、recordから取得
        elseif ($record) {
            $name = $state;
            $imageUrl = $this->safeGetAvatarUrl($record);
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

    /**
     * 安全にアバターURLを取得
     *
     * HasNameを実装しているか、nameがnullでない場合のみFilament::getUserAvatarUrl()を呼ぶ
     */
    protected function safeGetAvatarUrl(object $user): ?string
    {
        // HasNameを実装している場合は安全に呼べる
        if ($user instanceof HasName) {
            return Filament::getUserAvatarUrl($user);
        }

        // nameがnullでない場合のみ呼ぶ
        if (! empty($user->name)) {
            return Filament::getUserAvatarUrl($user);
        }

        return null;
    }
}
