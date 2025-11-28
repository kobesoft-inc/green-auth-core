<?php

namespace Green\Auth\Filament\Actions;

use Filament\Actions\CreateAction;

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
