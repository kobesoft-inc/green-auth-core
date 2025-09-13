<?php

namespace Green\Auth\Filament\Resources;

use Filament\Resources\Resource;
use Green\Auth\Filament\Resources\Concerns\HasModelLabels;
use Green\Auth\Filament\Resources\Concerns\Role\HasRoleActions;
use Green\Auth\Filament\Resources\Concerns\Role\HasRoleColumns;
use Green\Auth\Filament\Resources\Concerns\Role\HasRoleForms;

abstract class BaseRoleResource extends Resource
{
    use HasModelLabels, HasRoleActions, HasRoleColumns, HasRoleForms;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 30;
}
