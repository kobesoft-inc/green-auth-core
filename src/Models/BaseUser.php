<?php

namespace Green\Auth\Models;

use Illuminate\Foundation\Auth\User;
use Green\Auth\Models\Concerns\User\HasPasswordExpiration;
use Green\Auth\Models\Concerns\User\HasSuspension;
use Green\Auth\Models\Concerns\User\BelongsToGroups;
use Green\Auth\Models\Concerns\User\HasRoles;
use Green\Auth\Models\Concerns\User\HasPermissions;
use Green\Auth\Models\Concerns\User\HasLoginLogs;
use Green\Auth\Models\Concerns\User\HasPanelAccess;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;

abstract class BaseUser extends User implements FilamentUser, HasAvatar
{
    use HasPasswordExpiration;
    use Concerns\User\HasAvatar;
    use HasSuspension;
    use BelongsToGroups;
    use HasRoles;
    use HasPermissions;
    use HasLoginLogs;
    use HasPanelAccess;
}
