<?php

namespace Green\Auth\Filament\Resources\Concerns\Group;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

trait HasGroupActions
{
    use HasGroupTraitChecks;
    /**
     * レコードアクションを作成
     */
    public static function getRecordActions(): array
    {
        $actions = [];

        if ($editAction = static::makeEditAction()) {
            $actions[] = $editAction;
        }

        if ($deleteAction = static::makeDeleteAction()) {
            $actions[] = $deleteAction;
        }

        return $actions;
    }

    /**
     * 編集アクションを作成
     */
    public static function makeEditAction(): EditAction
    {
        return EditAction::make()
            ->button()
            ->modal()
            ->modalWidth('md')
            ->schema(static::getFormSchema());
    }

    /**
     * 削除アクションを作成
     */
    public static function makeDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->button()
            ->disabled(function (Model $record) {
                // 子グループがある場合は削除ボタンを無効化
                return static::hasParentGroupTrait() && $record->children()->exists();
            })
            ->tooltip(function (Model $record) {
                // 子グループがある場合はツールチップを表示
                if (static::hasParentGroupTrait() && $record->children()->exists()) {
                    return __('green-auth::groups.cannot_delete_groups_with_children');
                }
                return null;
            });
    }
}
