<?php

namespace Green\Auth\Filament\Resources\Concerns\User;

use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Tables;
use Green\Auth\Filament\Actions\ChangeUserPasswordAction;
use Green\Auth\Filament\Actions\SuspendUserAction;
use Green\Auth\Filament\Actions\UnsuspendUserAction;

trait HasUserActions
{
    /**
     * レコードアクションを作成
     */
    public static function getRecordActions(): array
    {
        $actions = [];

        // 編集アクション（個別表示）
        if ($editAction = static::makeEditAction()) {
            $actions[] = $editAction;
        }

        // 操作アクション（ActionGroupでまとめる）
        $operationActions = [];

        if ($passwordAction = static::makePasswordResetAction()) {
            $operationActions[] = $passwordAction;
        }

        if ($suspendAction = static::makeSuspendAction()) {
            $operationActions[] = $suspendAction;
        }

        if ($unsuspendAction = static::makeUnsuspendAction()) {
            $operationActions[] = $unsuspendAction;
        }

        if ($deleteAction = static::makeDeleteAction()) {
            $operationActions[] = $deleteAction;
        }

        // 操作アクションがある場合はActionGroupに追加
        if (!empty($operationActions)) {
            $actions[] = ActionGroup::make($operationActions)
                ->label(__('green-auth::users.actions.operations'))
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button();
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
            ->modalWidth('lg')
            ->schema(static::getFormSchema());
    }

    /**
     * パスワード変更アクションを作成
     */
    public static function makePasswordResetAction(): ChangeUserPasswordAction
    {
        return ChangeUserPasswordAction::make();
    }

    /**
     * 停止アクションを作成
     */
    public static function makeSuspendAction(): ?Action
    {
        if (!static::hasSuspensionTrait()) {
            return null;
        }
        return SuspendUserAction::make();
    }

    /**
     * 停止解除アクションを作成
     */
    public static function makeUnsuspendAction(): ?Action
    {
        if (!static::hasSuspensionTrait()) {
            return null;
        }
        return UnsuspendUserAction::make();
    }

    /**
     * 削除アクションを作成
     */
    public static function makeDeleteAction(): DeleteAction
    {
        return DeleteAction::make();
    }
}
