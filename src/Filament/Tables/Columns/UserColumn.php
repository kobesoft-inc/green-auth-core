<?php

namespace Green\Auth\Filament\Tables\Columns;

use Closure;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns\CanFormatState;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class UserColumn extends Column
{
    use CanFormatState;

    protected string $view = 'green-auth::filament.tables.columns.user-column';

    protected string | Closure | null $nameAttribute = 'name';

    protected string | Closure | null $avatarAttribute = null;

    protected int | Closure $size = 40;

    protected string | Closure | null $defaultImageUrl = null;

    protected bool | Closure $isCircular = true;

    protected string | Filesystem | FilesystemAdapter | Closure | null $disk = null;

    protected int | string | Closure | null $imageSize = '40px';

    protected string | Closure | null $textSize = 'text-sm';

    /**
     * 名前を表示する属性を設定
     */
    public function nameAttribute(string | Closure | null $attribute): static
    {
        $this->nameAttribute = $attribute;

        return $this;
    }

    /**
     * アバター画像の属性を設定
     */
    public function avatarAttribute(string | Closure | null $attribute): static
    {
        $this->avatarAttribute = $attribute;

        return $this;
    }

    /**
     * アバターのサイズを設定（ピクセル単位）
     */
    public function size(int | Closure $size): static
    {
        $this->size = $size;

        // imageSize は動的に計算されるように変更
        if (!$size instanceof Closure) {
            $this->imageSize = $size . 'px';
        }

        return $this;
    }

    /**
     * デフォルト画像URLを設定
     */
    public function defaultImageUrl(string | Closure | null $url): static
    {
        $this->defaultImageUrl = $url;

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
     * 四角形表示にする
     */
    public function square(): static
    {
        return $this->circular(false);
    }

    /**
     * ストレージディスクを設定
     */
    public function disk(string | Filesystem | FilesystemAdapter | Closure | null $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    /**
     * 画像サイズを設定
     */
    public function imageSize(int | string | Closure | null $size): static
    {
        $this->imageSize = $size;

        return $this;
    }

    /**
     * テキストサイズを設定
     */
    public function textSize(string | Closure | null $size): static
    {
        $this->textSize = $size;

        return $this;
    }

    /**
     * 名前の属性を取得
     */
    public function getNameAttribute(mixed $state): ?string
    {
        return $this->evaluate($this->nameAttribute, [
            'state' => $state,
        ]);
    }

    /**
     * アバター属性を取得
     */
    public function getAvatarAttribute(mixed $state): ?string
    {
        return $this->evaluate($this->avatarAttribute, [
            'state' => $state,
        ]);
    }

    /**
     * サイズを取得
     */
    public function getSize(mixed $state): int
    {
        return $this->evaluate($this->size, [
            'state' => $state,
        ]);
    }

    /**
     * デフォルト画像URLを取得
     */
    public function getDefaultImageUrl(mixed $state): ?string
    {
        return $this->evaluate($this->defaultImageUrl, [
            'state' => $state,
        ]);
    }

    /**
     * 円形かどうかを取得
     */
    public function isCircular(mixed $state): bool
    {
        return $this->evaluate($this->isCircular, [
            'state' => $state,
        ]);
    }

    /**
     * アバター用ディスクを取得
     */
    public function getAvatarDisk(mixed $state): Filesystem | FilesystemAdapter
    {
        $disk = $this->evaluate($this->disk, [
            'state' => $state,
        ]);

        return Storage::disk($disk ?? config('filament.default_filesystem_disk'));
    }

    /**
     * 画像URLを取得
     */
    public function getImageUrl(mixed $state): ?string
    {
        $record = $this->getRecord();
        $avatarValue = null;

        // avatarAttribute が指定されている場合はそれを使用
        if ($avatarAttribute = $this->getAvatarAttribute($state)) {
            $avatarValue = data_get($record, $avatarAttribute);
        } else {
            // デフォルトでレコードからアバター画像を取得
            // HasAvatarトレイトのメソッドが使える場合
            if (method_exists($record, 'getAvatarUrl')) {
                return $record->getAvatarUrl();
            }

            // 一般的なアバター属性名をチェック
            $avatarAttributes = ['avatar', 'avatar_url', 'image', 'photo', 'picture'];
            foreach ($avatarAttributes as $attr) {
                if ($avatarValue = data_get($record, $attr)) {
                    break;
                }
            }
        }

        // アバター値がある場合
        if ($avatarValue) {
            // URLの場合はそのまま返す
            if (filter_var($avatarValue, FILTER_VALIDATE_URL)) {
                return $avatarValue;
            }

            // パスの場合はディスクからURLを生成
            return $this->getAvatarDisk($state)->url($avatarValue);
        }

        // デフォルト画像を返す
        if (method_exists($record, 'getDefaultAvatarUrl')) {
            return $record->getDefaultAvatarUrl();
        }

        return $this->getDefaultImageUrl($state);
    }

    /**
     * 表示名を取得
     */
    public function getDisplayName(mixed $state): ?string
    {
        $nameAttribute = $this->getNameAttribute($state);

        if (!$nameAttribute) {
            return null;
        }

        $record = $this->getRecord();
        return data_get($record, $nameAttribute);
    }

    /**
     * 画像サイズを取得
     */
    public function getImageSize(mixed $state): string
    {
        // imageSize が設定されている場合はそれを使用
        if ($this->imageSize !== null) {
            $size = $this->evaluate($this->imageSize, [
                'state' => $state,
            ]);
        } else {
            // imageSize が設定されていない場合は size から計算
            $size = $this->getSize($state);
        }

        if (is_numeric($size)) {
            return $size . 'px';
        }

        return (string) $size;
    }

    /**
     * テキストサイズを取得
     */
    public function getTextSize(mixed $state): string
    {
        return $this->evaluate($this->textSize, [
            'state' => $state,
        ]);
    }

    /**
     * ユーザーカラム固有のビューデータを取得
     */
    public function getUserColumnViewData(): array
    {
        $state = $this->getState();

        return [
            'imageUrl' => $this->getImageUrl($state),
            'name' => $this->getDisplayName($state),
            'size' => $this->getImageSize($state),
            'textSize' => $this->getTextSize($state),
            'isCircular' => $this->isCircular($state),
        ];
    }

    /**
     * ビューデータを取得（親クラスのメソッドを拡張）
     */
    public function getViewData(): array
    {
        return array_merge(
            parent::getViewData(),
            $this->getUserColumnViewData()
        );
    }
}
