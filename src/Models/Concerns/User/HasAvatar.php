<?php

namespace Green\Auth\Models\Concerns\User;

use Green\Auth\Models\Concerns\HasModelConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * アバター機能を提供するトレイト
 *
 * @mixin Model
 */
trait HasAvatar
{
    use HasModelConfig;

    /**
     * アバターカラム名を取得
     */
    protected function getAvatarColumn(): string
    {
        return static::config('avatar_column', 'avatar');
    }

    /**
     * アバターディスク名を取得
     */
    public static function getAvatarDisk(): string
    {
        return static::config('avatar.disk', 'public');
    }

    /**
     * アバターディレクトリを取得
     */
    public static function getAvatarDirectory(): string
    {
        return static::config('avatar.directory', 'avatars');
    }

    /**
     * アバターパスを取得
     *
     * @return string|null
     */
    public function getAvatarPath(): ?string
    {
        $column = $this->getAvatarColumn();
        return $this->{$column};
    }

    /**
     * アバターの完全なURLを取得
     *
     * @return string|null
     */
    public function getAvatarUrl(): ?string
    {
        $path = $this->getAvatarPath();

        if (!$path) {
            return null;
        }

        $disk = static::getAvatarDisk();
        return Storage::disk($disk)->url($path);
    }

    /**
     * アバターを持っているかチェック
     *
     * @return bool
     */
    public function hasAvatar(): bool
    {
        return !empty($this->getAvatarPath());
    }

    /**
     * 新しいアバターを保存
     *
     * @param UploadedFile $file
     * @param array $options
     * @return string|false
     */
    public function storeAvatar(UploadedFile $file, array $options = []): string|false
    {
        $this->deleteAvatar();

        $directory = static::getAvatarDirectory();
        $disk = static::getAvatarDisk();

        $filename = $options['filename'] ?? null;

        if (!$filename) {
            $extension = $file->getClientOriginalExtension();
            $filename = $this->id . '_' . uniqid() . '.' . $extension;
        }

        $path = $file->storeAs($directory, $filename, $disk);

        if ($path) {
            $column = $this->getAvatarColumn();
            $this->forceFill([
                $column => $path,
            ])->save();
        }

        return $path;
    }

    /**
     * 現在のアバターを削除
     *
     * @return bool
     */
    public function deleteAvatar(): bool
    {
        $path = $this->getAvatarPath();

        if (!$path) {
            return false;
        }

        $disk = static::getAvatarDisk();
        $deleted = Storage::disk($disk)->delete($path);

        if ($deleted) {
            $column = $this->getAvatarColumn();
            $this->forceFill([
                $column => null,
            ])->save();
        }

        return $deleted;
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->getAvatarUrl();
    }

}
