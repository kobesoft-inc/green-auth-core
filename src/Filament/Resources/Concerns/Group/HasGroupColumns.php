<?php

namespace Green\Auth\Filament\Resources\Concerns\Group;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

trait HasGroupColumns
{
    use HasGroupTraitChecks;

    /**
     * カスタマイズ可能な名前カラムを作成
     *
     * @return TextColumn 名前カラム
     */
    public static function getNameColumn(): TextColumn
    {
        return TextColumn::make('name')
            ->label(static::getLocalizedFieldLabel('group_name'))
            ->searchable()
            ->sortable()
            ->description(fn ($record) => $record->description)
            ->extraAttributes(function ($record) {
                $textIndent = '0px';

                // NestedSetの深度に基づいてtext-indentを設定
                if (method_exists($record, 'getDepth')) {
                    $depth = $record->getDepth();
                    $textIndent = ($depth * 20) . 'px'; // 20pxずつインデント
                } elseif (method_exists($record, 'ancestors')) {
                    // フォールバック: 祖先を使用して深度を計算
                    $ancestorCount = $record->ancestors->count();
                    $textIndent = ($ancestorCount * 20) . 'px';
                }

                return [
                    'style' => "text-indent: {$textIndent};"
                ];
            });
    }

    /**
     * カスタマイズ可能な親グループカラムを作成
     *
     * @return TextColumn|null 親グループカラム（トレイトがない場合はnull）
     */
    public static function getParentColumn(): ?TextColumn
    {
        if (!static::hasParentGroupTrait()) {
            return null;
        }

        return TextColumn::make('parent.name')
            ->label(static::getLocalizedFieldLabel('parent_group'))
            ->searchable()
            ->sortable();
    }


    /**
     * カスタマイズ可能なユーザーカラムを作成
     *
     * @return ImageColumn|null ユーザーカラム（トレイトがない場合はnull）
     */
    public static function getUsersColumn(): ?ImageColumn
    {
        if (!static::hasUsersTrait()) {
            return null;
        }

        return ImageColumn::make('users')
            ->label(static::getTranslatedModelLabel('user', true))
            ->circular()
            ->stacked()
            ->limit(5)
            ->limitedRemainingText(__('green-auth::groups.users_count'))
            ->getStateUsing(function ($record) {
                return $record->users->map(function ($user) {
                    if (method_exists($user, 'getAvatarUrl')) {
                        return $user->getAvatarUrl();
                    }
                    // 一般的なアバター属性名をチェック
                    $avatarAttributes = ['avatar', 'avatar_url', 'image', 'photo', 'picture'];
                    foreach ($avatarAttributes as $attr) {
                        if ($avatarValue = data_get($user, $attr)) {
                            if (filter_var($avatarValue, FILTER_VALIDATE_URL)) {
                                return $avatarValue;
                            }
                            return Storage::disk(config('filament.default_filesystem_disk'))->url($avatarValue);
                        }
                    }
                    return null;
                })->filter()->toArray();
            });
    }

    /**
     * カスタマイズ可能なロールカラムを作成
     *
     * @return TextColumn|null ロールカラム（トレイトがない場合はnull）
     */
    public static function getRolesColumn(): ?TextColumn
    {
        if (!static::hasRolesTrait()) {
            return null;
        }

        return TextColumn::make('roles.name')
            ->label(static::getTranslatedModelLabel('role', true))
            ->badge();
    }

    /**
     * カスタマイズ可能な作成日時カラムを作成
     *
     * @return TextColumn 作成日時カラム
     */
    public static function getCreatedAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('green-auth::groups.created_at'))
            ->dateTime('Y/m/d H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * ユーザーフィルターを作成
     *
     * @return SelectFilter|null ユーザーフィルター（トレイトがない場合はnull）
     */
    public static function getUsersFilter(): ?SelectFilter
    {
        if (!static::hasUsersTrait()) {
            return null;
        }

        return SelectFilter::make('users')
            ->label(__('green-auth::groups.users'))
            ->relationship('users', 'name')
            ->multiple()
            ->preload();
    }

    /**
     * ロールフィルターを作成
     *
     * @return SelectFilter|null ロールフィルター（トレイトがない場合はnull）
     */
    public static function getRolesFilter(): ?SelectFilter
    {
        if (!static::hasRolesTrait()) {
            return null;
        }

        return SelectFilter::make('roles')
            ->label(__('green-auth::groups.roles'))
            ->relationship('roles', 'name')
            ->multiple()
            ->preload();
    }

    /**
     * フィルター配列を取得
     *
     * @return array フィルター配列
     */
    public static function getTableFilters(): array
    {
        return array_filter([
            static::getUsersFilter(),
            static::getRolesFilter(),
        ]);
    }

    /**
     * バルクアクション配列を取得
     *
     * @return array バルクアクション配列
     */
    public static function getBulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->deselectRecordsAfterCompletion()
                    ->action(function ($records) {
                        // 削除可能なレコードのみを削除
                        $deletableRecords = collect($records)->filter(function ($record) {
                            if (static::hasParentGroupTrait() && $record->children()->exists()) {
                                return false; // 子グループがある場合は削除しない
                            }
                            return true;
                        });

                        // 削除対象がない場合は通知
                        if ($deletableRecords->isEmpty()) {
                            Notification::make()
                                ->title(__('green-auth::groups.cannot_delete_groups_with_children'))
                                ->warning()
                                ->send();
                            return;
                        }

                        // 削除可能なレコードを削除
                        $deletableRecords->each(fn($record) => $record->delete());

                        // 一部のみ削除された場合の通知
                        if ($deletableRecords->count() < count($records)) {
                            Notification::make()
                                ->title(__('green-auth::groups.partial_delete_completed'))
                                ->body(__('green-auth::groups.some_groups_not_deleted_due_to_children'))
                                ->warning()
                                ->send();
                        }
                    }),
            ]),
        ];
    }

    public static function table(Table $table): Table
    {
        $columns = [];

        // 基本カラム（説明は名前カラムに統合）
        $columns[] = static::getNameColumn();

        // ユーザーカラム（存在する場合）
        if ($usersColumn = static::getUsersColumn()) {
            $columns[] = $usersColumn;
        }

        // ロールカラム（存在する場合）
        if ($rolesColumn = static::getRolesColumn()) {
            $columns[] = $rolesColumn;
        }

        // タイムスタンプカラム
        $columns[] = static::getCreatedAtColumn();

        return $table
            ->columns($columns)
            ->filters(static::getTableFilters())
            ->recordActions(static::getRecordActions())
            ->toolbarActions(static::getBulkActions())
            ->defaultSort(function ($query) {
                // NestedSet用のソート（_lftカラムを使用）
                if (method_exists($query->getModel(), 'getLftName')) {
                    return $query->orderBy($query->getModel()->getLftName());
                }
                // フォールバック: 通常の名前ソート
                return $query->orderBy('name');
            });
    }
}
