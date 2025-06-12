<?php

namespace Green\AuthCore\Filament\Actions;

use Filament\Tables\Actions\Action;
use Green\AuthCore\Filament\Actions\Concerns\ManagesUserPasswords;
use Illuminate\Database\Eloquent\Model;

class ChangeUserPasswordAction extends Action
{
    use ManagesUserPasswords;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->name('changePassword')
            ->label(__('green-auth::passwords.change_password'))
            ->icon('heroicon-o-key')
            ->form(fn($record) => static::makePasswordFields($record::class))
            ->modalWidth('md')
            ->action(function (Model $record, array $data) {
                $this->resetPassword($record, $data);
            });
    }

}