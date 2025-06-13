<?php

namespace Green\Auth\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;

abstract class BaseUser extends \Illuminate\Foundation\Auth\User implements FilamentUser, HasAvatar
{
    use Concerns\User\HasPasswordExpiration;
    use Concerns\User\HasAvatar;
    use Concerns\User\HasSuspension;
    use Concerns\User\BelongsToGroups;
    use Concerns\User\HasRoles;
    use Concerns\User\HasPermissions;
    use Concerns\User\HasLoginLogs;
    use Concerns\User\HasPanelAccess;
}
