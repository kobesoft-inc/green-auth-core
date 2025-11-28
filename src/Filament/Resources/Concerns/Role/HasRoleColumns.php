<?php

namespace Green\Auth\Filament\Resources\Concerns\Role;

use Exception;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

trait HasRoleColumns
{
    use HasRoleTraitChecks;

    /**
     * カスタマイズ可能な名前カラムを作成
     *
     * @return TextColumn 名前カラム
     */
    public static function getNameColumn(): TextColumn
    {
        return TextColumn::make('name')
            ->label(static::getLocalizedFieldLabel('role_name'))
            ->searchable()
            ->sortable()
            ->description(fn ($record) => $record->description);
    }

    /**
     * カスタマイズ可能なユーザーカラムを作成
     *
     * @return ImageColumn|null ユーザーカラム（トレイトがない場合はnull）
     */
    public static function getUsersColumn(): ?ImageColumn
    {
        if (! static::hasUsersTrait()) {
            return null;
        }

        return ImageColumn::make('users')
            ->label(static::getTranslatedModelLabel('user', true))
            ->circular()
            ->stacked()
            ->limit(5)
            ->limitedRemainingText(__('green-auth::roles.users_count'))
            ->getStateUsing(function ($record) {
                return $record->users->map(function ($user) {
                    if (method_exists($user, 'getAvatarUrl')) {
                        return $user->getAvatarUrl();
                    }
                    // Check common avatar attribute names
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
     * カスタマイズ可能なグループカラムを作成
     *
     * @return TextColumn|null グループカラム（トレイトがない場合はnull）
     */
    public static function getGroupsColumn(): ?TextColumn
    {
        if (! static::hasGroupsTrait()) {
            return null;
        }

        return TextColumn::make('groups.name')
            ->label(static::getTranslatedModelLabel('group', true))
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
            ->label(__('green-auth::roles.created_at'))
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
        if (! static::hasUsersTrait()) {
            return null;
        }

        return SelectFilter::make('users')
            ->label(__('green-auth::roles.users'))
            ->relationship('users', 'name')
            ->multiple()
            ->preload();
    }

    /**
     * グループフィルターを作成
     *
     * @return SelectFilter|null グループフィルター（トレイトがない場合はnull）
     *
     * @throws Exception
     */
    public static function getGroupsFilter(): ?SelectFilter
    {
        if (! static::hasGroupsTrait()) {
            return null;
        }

        return SelectFilter::make('groups')
            ->label(__('green-auth::roles.groups'))
            ->relationship('groups', 'name')
            ->multiple()
            ->preload();
    }

    /**
     * フィルター配列を取得
     *
     * @return array フィルター配列
     *
     * @throws Exception
     */
    public static function getTableFilters(): array
    {
        return array_filter([
            static::getUsersFilter(),
            static::getGroupsFilter(),
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
                DeleteBulkAction::make(),
            ]),
        ];
    }

    public static function table(Table $table): Table
    {
        $columns = [];

        // Basic columns (description integrated into name column)
        $columns[] = static::getNameColumn();

        // Users column (if exists)
        if ($usersColumn = static::getUsersColumn()) {
            $columns[] = $usersColumn;
        }

        // Groups column (if exists)
        if ($groupsColumn = static::getGroupsColumn()) {
            $columns[] = $groupsColumn;
        }

        // Timestamp column
        $columns[] = static::getCreatedAtColumn();

        return $table
            ->columns($columns)
            ->filters(static::getTableFilters())
            ->recordActions(static::getRecordActions())
            ->toolbarActions(static::getBulkActions())
            ->defaultSort('name');
    }
}
