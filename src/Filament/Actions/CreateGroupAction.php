<?php

namespace Green\AuthCore\Filament\Actions;

use Filament\Actions\CreateAction;
use Green\AuthCore\Filament\Actions\Concerns\ManagesUserPasswords;

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