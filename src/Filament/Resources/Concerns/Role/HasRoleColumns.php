<?php

namespace Green\AuthCore\Filament\Resources\Concerns\Role;

use Exception;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

trait HasRoleColumns
{
    use HasRoleTraitChecks;

    /**
     * カスタマイズ可能な名前カラムを作成
     * 
     * @return Tables\Columns\TextColumn 名前カラム
     */
    public static function makeNameColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('name')
            ->label(__('green-auth::roles.name'))
            ->searchable()
            ->sortable()
            ->description(fn ($record) => $record->description);
    }

    /**
     * カスタマイズ可能なユーザーカラムを作成
     * 
     * @return Tables\Columns\ImageColumn|null ユーザーカラム（トレイトがない場合はnull）
     */
    public static function makeUsersColumn(): ?Tables\Columns\ImageColumn
    {
        if (!static::hasUsersTrait()) {
            return null;
        }

        return Tables\Columns\ImageColumn::make('users')
            ->label(__('green-auth::roles.users'))
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
     * @return Tables\Columns\TextColumn|null グループカラム（トレイトがない場合はnull）
     */
    public static function makeGroupsColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasGroupsTrait()) {
            return null;
        }

        return Tables\Columns\TextColumn::make('groups.name')
            ->label(__('green-auth::roles.groups'))
            ->badge();
    }

    /**
     * カスタマイズ可能な作成日時カラムを作成
     * 
     * @return Tables\Columns\TextColumn 作成日時カラム
     */
    public static function makeCreatedAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('created_at')
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
    public static function makeUsersFilter(): ?SelectFilter
    {
        if (!static::hasUsersTrait()) {
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
     * @throws Exception
     */
    public static function makeGroupsFilter(): ?SelectFilter
    {
        if (!static::hasGroupsTrait()) {
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
     * @throws Exception
     */
    public static function getTableFilters(): array
    {
        return array_filter([
            static::makeUsersFilter(),
            static::makeGroupsFilter(),
        ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [];

        // Basic columns (description integrated into name column)
        $columns[] = static::makeNameColumn();

        // Users column (if exists)
        if ($usersColumn = static::makeUsersColumn()) {
            $columns[] = $usersColumn;
        }

        // Groups column (if exists)
        if ($groupsColumn = static::makeGroupsColumn()) {
            $columns[] = $groupsColumn;
        }

        // Timestamp column
        $columns[] = static::makeCreatedAtColumn();

        return $table
            ->columns($columns)
            ->filters(static::getTableFilters())
            ->actions(static::makeRecordActions())
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name');
    }
}