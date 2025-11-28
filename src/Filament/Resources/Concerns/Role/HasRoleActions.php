<?php

namespace Green\Auth\Filament\Resources\Concerns\Role;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;

trait HasRoleActions
{
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
            ->slideOver()
            ->modalWidth('2xl')
            ->schema(static::getFormSchema());
    }

    /**
     * 削除アクションを作成
     */
    public static function makeDeleteAction(): DeleteAction
    {
        return DeleteAction::make()
            ->button();
    }
}
