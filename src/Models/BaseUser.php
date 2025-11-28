<?php

namespace Green\Auth\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Green\Auth\Models\Concerns\User\BelongsToGroups;
use Green\Auth\Models\Concerns\User\HasLoginLogs;
use Green\Auth\Models\Concerns\User\HasPanelAccess;
use Green\Auth\Models\Concerns\User\HasPasswordExpiration;
use Green\Auth\Models\Concerns\User\HasPermissions;
use Green\Auth\Models\Concerns\User\HasRoles;
use Green\Auth\Models\Concerns\User\HasSuspension;
use Illuminate\Foundation\Auth\User;

abstract class BaseUser extends User implements FilamentUser, HasAvatar
{
    use BelongsToGroups;
    use Concerns\User\HasAvatar;
    use HasLoginLogs;
    use HasPanelAccess;
    use HasPasswordExpiration;
    use HasPermissions;
    use HasRoles;
    use HasSuspension;
}
