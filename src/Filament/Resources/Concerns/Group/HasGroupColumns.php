<?php

namespace Green\AuthCore\Filament\Resources\Concerns\Group;

use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

trait HasGroupColumns
{
    use HasGroupTraitChecks;

    /**
     * カスタマイズ可能な名前カラムを作成
     * 
     * @return Tables\Columns\TextColumn 名前カラム
     */
    public static function makeNameColumn(): Tables\Columns\TextColumn
    {
        return Tables\Columns\TextColumn::make('name')
            ->label(__('green-auth::groups.name'))
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
     * @return Tables\Columns\TextColumn|null 親グループカラム（トレイトがない場合はnull）
     */
    public static function makeParentColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasParentGroupTrait()) {
            return null;
        }

        return Tables\Columns\TextColumn::make('parent.name')
            ->label(__('green-auth::groups.parent_group'))
            ->searchable()
            ->sortable();
    }

    /**
     * カスタマイズ可能な子グループ数カラムを作成
     * 
     * @return Tables\Columns\TextColumn|null 子グループ数カラム（トレイトがない場合はnull）
     */
    public static function makeChildrenCountColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasParentGroupTrait()) {
            return null;
        }

        return Tables\Columns\TextColumn::make('children_count')
            ->label(__('green-auth::groups.child_groups_count'))
            ->counts('children')
            ->sortable();
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
            ->label(__('green-auth::groups.users'))
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
     * @return Tables\Columns\TextColumn|null ロールカラム（トレイトがない場合はnull）
     */
    public static function makeRolesColumn(): ?Tables\Columns\TextColumn
    {
        if (!static::hasRolesTrait()) {
            return null;
        }

        return Tables\Columns\TextColumn::make('roles.name')
            ->label(__('green-auth::groups.roles'))
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
            ->label(__('green-auth::groups.created_at'))
            ->dateTime('Y/m/d H:i')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);
    }

    /**
     * ユーザーフィルターを作成
     * 
     * @return Tables\Filters\SelectFilter|null ユーザーフィルター（トレイトがない場合はnull）
     */
    public static function makeUsersFilter(): ?Tables\Filters\SelectFilter
    {
        if (!static::hasUsersTrait()) {
            return null;
        }

        return Tables\Filters\SelectFilter::make('users')
            ->label(__('green-auth::groups.users'))
            ->relationship('users', 'name')
            ->multiple()
            ->preload();
    }

    /**
     * ロールフィルターを作成
     * 
     * @return Tables\Filters\SelectFilter|null ロールフィルター（トレイトがない場合はnull）
     */
    public static function makeRolesFilter(): ?Tables\Filters\SelectFilter
    {
        if (!static::hasRolesTrait()) {
            return null;
        }

        return Tables\Filters\SelectFilter::make('roles')
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
            static::makeUsersFilter(),
            static::makeRolesFilter(),
        ]);
    }

    public static function table(Table $table): Table
    {
        $columns = [];

        // 基本カラム（説明は名前カラムに統合）
        $columns[] = static::makeNameColumn();

        // ユーザーカラム（存在する場合）
        if ($usersColumn = static::makeUsersColumn()) {
            $columns[] = $usersColumn;
        }

        // ロールカラム（存在する場合）
        if ($rolesColumn = static::makeRolesColumn()) {
            $columns[] = $rolesColumn;
        }

        // タイムスタンプカラム
        $columns[] = static::makeCreatedAtColumn();

        return $table
            ->columns($columns)
            ->filters(static::getTableFilters())
            ->actions(static::makeRecordActions())
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
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
                                \Filament\Notifications\Notification::make()
                                    ->title(__('green-auth::groups.cannot_delete_groups_with_children'))
                                    ->warning()
                                    ->send();
                                return;
                            }

                            // 削除可能なレコードを削除
                            $deletableRecords->each(fn($record) => $record->delete());

                            // 一部のみ削除された場合の通知
                            if ($deletableRecords->count() < count($records)) {
                                \Filament\Notifications\Notification::make()
                                    ->title(__('green-auth::groups.partial_delete_completed'))
                                    ->body(__('green-auth::groups.some_groups_not_deleted_due_to_children'))
                                    ->warning()
                                    ->send();
                            }
                        }),
                ]),
            ])
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