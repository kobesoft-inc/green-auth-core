<?php

namespace Green\AuthCore\Filament\Resources;

use Filament\Resources\Resource;
use Green\AuthCore\Filament\Resources\Concerns\HasModelLabels;
use Green\AuthCore\Filament\Resources\Concerns\Group\HasGroupActions;
use Green\AuthCore\Filament\Resources\Concerns\Group\HasGroupColumns;
use Green\AuthCore\Filament\Resources\Concerns\Group\HasGroupForms;

abstract class BaseGroupResource extends Resource
{
    use HasModelLabels, HasGroupActions, HasGroupColumns, HasGroupForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    
    protected static ?int $navigationSort = 20;
}