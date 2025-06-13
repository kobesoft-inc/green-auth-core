<?php

namespace Green\Auth\Filament\Resources\Concerns\User;

use Filament\Tables;
use Filament\Tables\Table;
use Green\Auth\Filament\Tables\Columns\UserColumn;
use Illuminate\Database\Eloquent\Builder;

trait HasUserColumns
{
    use HasUserTraitChecks;

    /**
     * 名前カラム
     *
     * @return Tables\Columns\Column 名前表示用カラム
     */
    public static function getNameColumn(): Tables\Columns\Column
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
    public static function getEmailColumn(): Tables\Columns\TextColumn
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
    public static function getUsernameColumn(): ?Tables\Columns\TextColumn
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
    public static function getGroupsColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasGroupsTrait()) {
            return null;
        }

        return Tables\Columns\TextColumn::make('groups.name')
            ->label(static::getLocalizedFieldLabel('groups', true))
            ->badge()
            ->separator(', ');
    }

    /**
     * ロールカラム
     *
     * @return Tables\Columns\TextColumn|null ロール表示用カラム（トレイトがない場合はnull）
     */
    public static function getRolesColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasRolesTrait()) {
            return null;
        }

        return Tables\Columns\TextColumn::make('roles.name')
            ->label(static::getLocalizedFieldLabel('roles', true))
            ->badge()
            ->separator(', ');
    }

    /**
     * 停止ステータスカラム
     *
     * @return Tables\Columns\IconColumn|null 停止状態表示用アイコンカラム（トレイトがない場合はnull）
     */
    public static function getSuspendedColumn(): ?Tables\Columns\IconColumn
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
    public static function getLastLoginColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasLoginLogTrait()) {
            return null;
        }

        return Tables\Columns\TextColumn::make('last_login_at')
            ->label(__('green-auth::users.last_login'))
            ->dateTime('Y/m/d H:i')
            ->sortable()
            ->getStateUsing(function ($record) {
                return $record->loginLogs()->latest('created_at')->first()?->created_at;
            });
    }

    /**
     * 作成日時カラム
     *
     * @return Tables\Columns\TextColumn 作成日時表示用カラム
     */
    public static function getCreatedAtColumn(): Tables\Columns\TextColumn
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
    public static function getUpdatedAtColumn(): Tables\Columns\TextColumn
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
    public static function getGroupsFilter(): ?Tables\Filters\SelectFilter
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
    public static function getRolesFilter(): ?Tables\Filters\SelectFilter
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
    public static function getSuspendedFilter(): ?Tables\Filters\TernaryFilter
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
    public static function getEmailVerifiedFilter(): Tables\Filters\TernaryFilter
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
        $columns[] = static::getNameColumn();
        $columns[] = static::getEmailColumn();

        // ユーザー名カラム
        if ($usernameColumn = static::getUsernameColumn()) {
            $columns[] = $usernameColumn;
        }

        // グループカラム
        if ($groupsColumn = static::getGroupsColumn()) {
            $columns[] = $groupsColumn;
        }

        // ロールカラム
        if ($rolesColumn = static::getRolesColumn()) {
            $columns[] = $rolesColumn;
        }

        // 停止ステータスカラム
        if ($suspendedColumn = static::getSuspendedColumn()) {
            $columns[] = $suspendedColumn;
        }

        // 最終ログイン日時カラム
        if ($lastLoginColumn = static::getLastLoginColumn()) {
            $columns[] = $lastLoginColumn;
        }

        // タイムスタンプカラム
        $columns[] = static::getCreatedAtColumn();
        $columns[] = static::getUpdatedAtColumn();

        // フィルターの収集
        $filters = array_filter([
            static::getGroupsFilter(),
            static::getRolesFilter(),
            static::getSuspendedFilter(),
            static::getEmailVerifiedFilter(),
        ]);

        return $table
            ->columns($columns)
            ->filters($filters)
            ->actions(static::getRecordActions())
            ->bulkActions(static::getBulkActions());
    }

    /**
     * バルクアクション配列を取得
     *
     * @return array バルクアクション配列
     */
    public static function getBulkActions(): array
    {
        return [
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ];
    }
}
