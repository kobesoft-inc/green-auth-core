<?php

namespace Green\Auth\Filament\Resources;

use Filament\Resources\Resource;
use Green\Auth\Filament\Resources\Concerns\HasModelLabels;
use Green\Auth\Filament\Resources\Concerns\Group\HasGroupActions;
use Green\Auth\Filament\Resources\Concerns\Group\HasGroupColumns;
use Green\Auth\Filament\Resources\Concerns\Group\HasGroupForms;

abstract class BaseGroupResource extends Resource
{
    use HasModelLabels, HasGroupActions, HasGroupColumns, HasGroupForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 20;
}
