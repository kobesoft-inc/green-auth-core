<?php

namespace Green\Auth\Filament\Actions;

use Filament\Actions\CreateAction;
use Green\Auth\Filament\Actions\Concerns\ManagesUserPasswords;

class CreateGroupAction extends CreateAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->modalWidth('md')
            ->createAnother(false);
    }

}
