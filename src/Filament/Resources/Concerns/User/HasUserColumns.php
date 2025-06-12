<?php

namespace Green\AuthCore\Filament\Resources\Concerns\User;

use Filament\Tables;
use Filament\Tables\Table;
use Green\AuthCore\Filament\Tables\Columns\UserColumn;
use Illuminate\Database\Eloquent\Builder;

trait HasUserColumns
{
    use HasUserTraitChecks;

    /**
     * 名前カラム
     * 
     * @return Tables\Columns\Column 名前表示用カラム
     */
    public static function makeNameColumn(): Tables\Columns\Column
    {
        if (static::hasAvatarTrait()) {
            return UserColumn::make('name')
                ->label(__('green-auth::users.name'))
                ->size(40)
                ->circular();
        } else {
            return Tables\Columns\TextColumn::make('name')
                ->label(__('green-auth::users.name'))
                ->searchable()
                ->sortable();
        }
    }

    /**
     * メールアドレスカラム
     * 
     * @return Tables\Columns\TextColumn メールアドレス表示用カラム
     */
    public static function makeEmailColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('email')
            ->label(__('green-auth::users.email'))
            ->searchable()
            ->sortable();
    }

    /**
     * ユーザー名カラム
     * 
     * @return Tables\Columns\TextColumn|null ユーザー名表示用カラム（トレイトがない場合はnull）
     */
    public static function makeUsernameColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasUsernameTrait()) {
            return null;
        }

        $modelInstance = new (static::getModel())();
        $usernameColumn = $modelInstance->getUsernameColumn();

        return Tables\Columns\TextColumn::make($usernameColumn)
            ->label(__('green-auth::users.username'))
            ->searchable()
            ->sortable();
    }

    /**
     * グループカラム
     * 
     * @return Tables\Columns\TextColumn|null グループ表示用カラム（トレイトがない場合はnull）
     */
    public static function makeGroupsColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasGroupsTrait()) {
            return null;
        }

        return Tables\Columns\TextColumn::make('groups.name')
            ->label(__('green-auth::users.groups'))
            ->badge()
            ->separator(', ');
    }

    /**
     * ロールカラム
     * 
     * @return Tables\Columns\TextColumn|null ロール表示用カラム（トレイトがない場合はnull）
     */
    public static function makeRolesColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasRolesTrait()) {
            return null;
        }

        return Tables\Columns\TextColumn::make('roles.name')
            ->label(__('green-auth::users.roles'))
            ->badge()
            ->separator(', ');
    }

    /**
     * 停止ステータスカラム
     * 
     * @return Tables\Columns\IconColumn|null 停止状態表示用アイコンカラム（トレイトがない場合はnull）
     */
    public static function makeSuspendedColumn(): ?Tables\Columns\IconColumn
    {
        if (!static::hasSuspensionTrait()) {
            return null;
        }

        return Tables\Columns\IconColumn::make('suspended_at')
            ->label(__('green-auth::users.status'))
            ->boolean()
            ->trueIcon('heroicon-o-x-circle')
            ->falseIcon('heroicon-o-check-circle')
            ->trueColor('danger')
            ->falseColor('success')
            ->getStateUsing(fn($record) => $record->isSuspended());
    }

    /**
     * 最終ログイン日時カラム
     * 
     * @return Tables\Columns\TextColumn|null 最終ログイン日時表示用カラム（トレイトがない場合はnull）
     */
    public static function makeLastLoginColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasLoginLogTrait()) {
            return null;
        }

        return Tables\Columns\TextColumn::make('last_login_at')
            ->label(__('green-auth::users.last_login'))
            ->dateTime('Y/m/d H:i')
            ->sortable()
            ->getStateUsing(function ($record) {
                return $record->loginLogs()->latest('login_at')->first()?->login_at;
            });
    }

    /**
     * 作成日時カラム
     * 
     * @return Tables\Columns\TextColumn 作成日時表示用カラム
     */
    public static function makeCreatedAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('created_at')
            ->label(__('green-auth::users.created_at'))
            ->dateTime('Y/m/d H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * 更新日時カラム
     * 
     * @return Tables\Columns\TextColumn 更新日時表示用カラム
     */
    public static function makeUpdatedAtColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('updated_at')
            ->label(__('green-auth::users.updated_at'))
            ->dateTime('Y/m/d H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * グループフィルター
     * 
     * @return Tables\Filters\SelectFilter|null グループフィルター（トレイトがない場合はnull）
     */
    public static function makeGroupsFilter(): ?Tables\Filters\SelectFilter
    {
        if (!static::hasGroupsTrait()) {
            return null;
        }

        return Tables\Filters\SelectFilter::make('groups')
            ->label(__('green-auth::users.filters.groups'))
            ->relationship('groups', 'name')
            ->multiple()
            ->preload();
    }

    /**
     * ロールフィルター
     * 
     * @return Tables\Filters\SelectFilter|null ロールフィルター（トレイトがない場合はnull）
     */
    public static function makeRolesFilter(): ?Tables\Filters\SelectFilter
    {
        if (!static::hasRolesTrait()) {
            return null;
        }

        return Tables\Filters\SelectFilter::make('roles')
            ->label(__('green-auth::users.filters.roles'))
            ->relationship('roles', 'name')
            ->multiple()
            ->preload();
    }

    /**
     * 停止ステータスフィルター
     * 
     * @return Tables\Filters\TernaryFilter|null 停止状態フィルター（トレイトがない場合はnull）
     */
    public static function makeSuspendedFilter(): ?Tables\Filters\TernaryFilter
    {
        if (!static::hasSuspensionTrait()) {
            return null;
        }

        return Tables\Filters\TernaryFilter::make('suspended')
            ->label(__('green-auth::users.filters.suspended'))
            ->queries(
                true: fn(Builder $query) => $query->whereNotNull('suspended_at'),
                false: fn(Builder $query) => $query->whereNull('suspended_at'),
            );
    }

    /**
     * メール認証フィルター
     * 
     * @return Tables\Filters\TernaryFilter メール認証フィルター
     */
    public static function makeEmailVerifiedFilter(): Tables\Filters\TernaryFilter
    {
        return Tables\Filters\TernaryFilter::make('email_verified')
            ->label(__('green-auth::users.filters.email_verified'))
            ->queries(
                true: fn(Builder $query) => $query->whereNotNull('email_verified_at'),
                false: fn(Builder $query) => $query->whereNull('email_verified_at'),
            );
    }

    /**
     * テーブル設定
     * 
     * @param Table $table テーブルインスタンス
     * @return Table 設定済みテーブル
     */
    public static function table(Table $table): Table
    {
        $columns = [];

        // 基本カラム
        $columns[] = static::makeNameColumn();
        $columns[] = static::makeEmailColumn();

        // ユーザー名カラム
        if ($usernameColumn = static::makeUsernameColumn()) {
            $columns[] = $usernameColumn;
        }

        // グループカラム
        if ($groupsColumn = static::makeGroupsColumn()) {
            $columns[] = $groupsColumn;
        }

        // ロールカラム
        if ($rolesColumn = static::makeRolesColumn()) {
            $columns[] = $rolesColumn;
        }

        // 停止ステータスカラム
        if ($suspendedColumn = static::makeSuspendedColumn()) {
            $columns[] = $suspendedColumn;
        }

        // 最終ログイン日時カラム
        if ($lastLoginColumn = static::makeLastLoginColumn()) {
            $columns[] = $lastLoginColumn;
        }

        // タイムスタンプカラム
        $columns[] = static::makeCreatedAtColumn();
        $columns[] = static::makeUpdatedAtColumn();

        // フィルターの収集
        $filters = array_filter([
            static::makeGroupsFilter(),
            static::makeRolesFilter(),
            static::makeSuspendedFilter(),
            static::makeEmailVerifiedFilter(),
        ]);

        return $table
            ->columns($columns)
            ->filters($filters)
            ->actions(static::makeRecordActions())
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}