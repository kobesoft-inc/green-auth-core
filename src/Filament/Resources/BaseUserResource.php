<?php

namespace Green\AuthCore\Filament\Resources;

use Filament\Resources\Resource;
use Green\AuthCore\Filament\Resources\Concerns\HasModelLabels;
use Green\AuthCore\Filament\Resources\Concerns\User\HasUserActions;
use Green\AuthCore\Filament\Resources\Concerns\User\HasUserColumns;
use Green\AuthCore\Filament\Resources\Concerns\User\HasUserForms;

abstract class BaseUserResource extends Resource
{
    use HasModelLabels, HasUserActions, HasUserColumns, HasUserForms;
    
    protected static ?string $navigationIcon = 'heroicon-o-user';
    
    protected static ?int $navigationSort = 10;
}