<?php

namespace Green\AuthCore\Filament\Resources\Concerns\Role;

use Filament\Tables;

trait HasRoleActions
{
    /**
     * レコードアクションを作成
     */
    public static function makeRecordActions(): array
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
    public static function makeEditAction(): Tables\Actions\EditAction
    {
        return Tables\Actions\EditAction::make()
            ->button()
            ->slideOver()
            ->modalWidth('2xl')
            ->form(static::getFormSchema());
    }

    /**
     * 削除アクションを作成
     */
    public static function makeDeleteAction(): Tables\Actions\DeleteAction
    {
        return Tables\Actions\DeleteAction::make()
            ->button();
    }
}