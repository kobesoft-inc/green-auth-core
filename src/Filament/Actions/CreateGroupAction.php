<?php

namespace Green\Auth\Filament\Actions;

use Filament\Actions\CreateAction;

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
