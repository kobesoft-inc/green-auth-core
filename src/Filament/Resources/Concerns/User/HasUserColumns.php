<?php

namespace Green\Auth\Filament\Resources\Concerns\User;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
    public static function getNameColumn(): Column
    {
        if (static::hasAvatarTrait()) {
            return UserColumn::make('name')
                ->label(__('green-auth::users.name'))
                ->circular();
        } else {
            return TextColumn::make('name')
                ->label(__('green-auth::users.name'))
                ->searchable()
                ->sortable();
        }
    }

    /**
     * メールアドレスカラム
     *
     * @return TextColumn メールアドレス表示用カラム
     */
    public static function getEmailColumn(): TextColumn
    {
        return TextColumn::make('email')
            ->label(__('green-auth::users.email'))
            ->searchable()
            ->sortable();
    }

    /**
     * ユーザー名カラム
     *
     * @return TextColumn|null ユーザー名表示用カラム（トレイトがない場合はnull）
     */
    public static function getUsernameColumn(): ?TextColumn
    {
        if (! static::hasUsernameTrait()) {
            return null;
        }

        $modelInstance = new (static::getModel())();
        $usernameColumn = $modelInstance->getUsernameColumn();

        return TextColumn::make($usernameColumn)
            ->label(__('green-auth::users.username'))
            ->searchable()
            ->sortable();
    }

    /**
     * グループカラム
     *
     * @return TextColumn|null グループ表示用カラム（トレイトがない場合はnull）
     */
    public static function getGroupsColumn(): ?TextColumn
    {
        if (! static::hasGroupsTrait()) {
            return null;
        }

        return TextColumn::make('groups.name')
            ->label(static::getLocalizedFieldLabel('groups', true))
            ->badge()
            ->separator(', ');
    }

    /**
     * ロールカラム
     *
     * @return TextColumn|null ロール表示用カラム（トレイトがない場合はnull）
     */
    public static function getRolesColumn(): ?TextColumn
    {
        if (! static::hasRolesTrait()) {
            return null;
        }

        return TextColumn::make('roles.name')
            ->label(static::getLocalizedFieldLabel('roles', true))
            ->badge()
            ->separator(', ');
    }

    /**
     * 停止ステータスカラム
     *
     * @return IconColumn|null 停止状態表示用アイコンカラム（トレイトがない場合はnull）
     */
    public static function getSuspendedColumn(): ?IconColumn
    {
        if (! static::hasSuspensionTrait()) {
            return null;
        }

        return IconColumn::make('suspended_at')
            ->label(__('green-auth::users.status'))
            ->boolean()
            ->trueIcon('heroicon-o-x-circle')
            ->falseIcon('heroicon-o-check-circle')
            ->trueColor('danger')
            ->falseColor('success')
            ->getStateUsing(fn ($record) => $record->isSuspended());
    }

    /**
     * 最終ログイン日時カラム
     *
     * @return TextColumn|null 最終ログイン日時表示用カラム（トレイトがない場合はnull）
     */
    public static function getLastLoginColumn(): ?TextColumn
    {
        if (! static::hasLoginLogTrait()) {
            return null;
        }

        return TextColumn::make('latestLoginLog.created_at')
            ->label(__('green-auth::users.last_login'))
            ->since()
            ->sortable();
    }

    /**
     * 作成日時カラム
     *
     * @return TextColumn 作成日時表示用カラム
     */
    public static function getCreatedAtColumn(): TextColumn
    {
        return TextColumn::make('created_at')
            ->label(__('green-auth::users.created_at'))
            ->dateTime('Y/m/d H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * 更新日時カラム
     *
     * @return TextColumn 更新日時表示用カラム
     */
    public static function getUpdatedAtColumn(): TextColumn
    {
        return TextColumn::make('updated_at')
            ->label(__('green-auth::users.updated_at'))
            ->dateTime('Y/m/d H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * グループフィルター
     *
     * @return SelectFilter|null グループフィルター（トレイトがない場合はnull）
     */
    public static function getGroupsFilter(): ?SelectFilter
    {
        if (! static::hasGroupsTrait()) {
            return null;
        }

        return SelectFilter::make('groups')
            ->label(__('green-auth::users.filters.groups'))
            ->relationship('groups', 'name')
            ->multiple()
            ->preload();
    }

    /**
     * ロールフィルター
     *
     * @return SelectFilter|null ロールフィルター（トレイトがない場合はnull）
     */
    public static function getRolesFilter(): ?SelectFilter
    {
        if (! static::hasRolesTrait()) {
            return null;
        }

        return SelectFilter::make('roles')
            ->label(__('green-auth::users.filters.roles'))
            ->relationship('roles', 'name')
            ->multiple()
            ->preload();
    }

    /**
     * 停止ステータスフィルター
     *
     * @return TernaryFilter|null 停止状態フィルター（トレイトがない場合はnull）
     */
    public static function getSuspendedFilter(): ?TernaryFilter
    {
        if (! static::hasSuspensionTrait()) {
            return null;
        }

        return TernaryFilter::make('suspended')
            ->label(__('green-auth::users.filters.suspended'))
            ->queries(
                true: fn (Builder $query) => $query->whereNotNull('suspended_at'),
                false: fn (Builder $query) => $query->whereNull('suspended_at'),
            );
    }

    /**
     * メール認証フィルター
     *
     * @return TernaryFilter メール認証フィルター
     */
    public static function getEmailVerifiedFilter(): TernaryFilter
    {
        return TernaryFilter::make('email_verified')
            ->label(__('green-auth::users.filters.email_verified'))
            ->queries(
                true: fn (Builder $query) => $query->whereNotNull('email_verified_at'),
                false: fn (Builder $query) => $query->whereNull('email_verified_at'),
            );
    }

    /**
     * テーブル設定
     *
     * @param  Table  $table  テーブルインスタンス
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
            ->recordActions(static::getRecordActions())
            ->toolbarActions(static::getBulkActions());
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
}
