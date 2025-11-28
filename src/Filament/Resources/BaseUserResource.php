<?php

namespace Green\Auth\Filament\Resources;

use Filament\Resources\Resource;
use Green\Auth\Filament\Resources\Concerns\HasModelLabels;
use Green\Auth\Filament\Resources\Concerns\User\HasUserActions;
use Green\Auth\Filament\Resources\Concerns\User\HasUserColumns;
use Green\Auth\Filament\Resources\Concerns\User\HasUserForms;

abstract class BaseUserResource extends Resource
{
    use HasModelLabels, HasUserActions, HasUserColumns, HasUserForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?int $navigationSort = 10;
}
