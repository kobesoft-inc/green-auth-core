<?php

namespace Green\AuthCore\Filament\Actions;

use Filament\Actions\CreateAction;
use Green\AuthCore\Filament\Actions\Concerns\ManagesUserPasswords;

class CreateRoleAction extends CreateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->slideOver()
            ->modalWidth('lg')
            ->createAnother(false);
    }

}