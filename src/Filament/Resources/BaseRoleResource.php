<?php

namespace Green\AuthCore\Filament\Resources;

use Filament\Resources\Resource;
use Green\AuthCore\Filament\Resources\Concerns\HasModelLabels;
use Green\AuthCore\Filament\Resources\Concerns\Role\HasRoleActions;
use Green\AuthCore\Filament\Resources\Concerns\Role\HasRoleColumns;
use Green\AuthCore\Filament\Resources\Concerns\Role\HasRoleForms;

abstract class BaseRoleResource extends Resource
{
    use HasModelLabels, HasRoleActions, HasRoleColumns, HasRoleForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-key';
    
    protected static ?int $navigationSort = 30;
}