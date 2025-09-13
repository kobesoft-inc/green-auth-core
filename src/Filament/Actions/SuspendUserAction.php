<?php

namespace Green\Auth\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SuspendUserAction
{
    public static function make(): Action
    {
        return Action::make('suspend')
            ->label(__('green-auth::users.actions.suspend'))
            ->icon('heroicon-o-pause')
            ->requiresConfirmation()
            ->modalHeading(__('green-auth::users.actions.modals.suspend_user.heading'))
            ->modalDescription(__('green-auth::users.actions.modals.suspend_user.description'))
            ->modalSubmitActionLabel(__('green-auth::users.actions.modals.suspend_user.submit'))
            ->visible(fn ($record) => method_exists($record, 'isSuspended') && !$record->isSuspended())
            ->action(function ($record) {
                if (method_exists($record, 'suspend')) {
                    $record->suspend();

                    Notification::make()
                        ->success()
                        ->title(__('green-auth::notifications.user_suspended'))
                        ->body(__('green-auth::notifications.user_suspended_message', ['name' => $record->name]))
                        ->send();
                }
            });
    }
}
