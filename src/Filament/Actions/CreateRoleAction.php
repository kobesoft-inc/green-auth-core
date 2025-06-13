<?php

namespace Green\Auth\Filament\Actions;

use Filament\Actions\CreateAction;
use Green\Auth\Filament\Actions\Concerns\ManagesUserPasswords;

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
