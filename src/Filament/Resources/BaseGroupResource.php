<?php

namespace Green\Auth\Filament\Resources;

use Filament\Resources\Resource;
use Green\Auth\Filament\Resources\Concerns\Group\HasGroupActions;
use Green\Auth\Filament\Resources\Concerns\Group\HasGroupColumns;
use Green\Auth\Filament\Resources\Concerns\Group\HasGroupForms;
use Green\Auth\Filament\Resources\Concerns\HasModelLabels;

abstract class BaseGroupResource extends Resource
{
    use HasGroupActions, HasGroupColumns, HasGroupForms, HasModelLabels;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 20;
}
