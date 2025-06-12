<?php

namespace Green\AuthCore\Filament\Actions;

use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

class UnsuspendUserAction
{
    public static function make(): Action
    {
        return Action::make('unsuspend')
            ->label(__('green-auth::users.actions.unsuspend'))
            ->icon('heroicon-o-play')
            ->requiresConfirmation()
            ->modalHeading(__('green-auth::users.actions.modals.unsuspend_user.heading'))
            ->modalDescription(__('green-auth::users.actions.modals.unsuspend_user.description'))
            ->modalSubmitActionLabel(__('green-auth::users.actions.modals.unsuspend_user.submit'))
            ->visible(fn ($record) => method_exists($record, 'isSuspended') && $record->isSuspended())
            ->action(function ($record) {
                if (method_exists($record, 'unsuspend')) {
                    $record->unsuspend();
                    
                    Notification::make()
                        ->success()
                        ->title(__('green-auth::notifications.user_unsuspended'))
                        ->body(__('green-auth::notifications.user_unsuspended_message', ['name' => $record->name]))
                        ->send();
                }
            });
    }
}