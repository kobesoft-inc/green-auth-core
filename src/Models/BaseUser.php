<?php

namespace Green\Auth\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\HasName;
use Green\Auth\Models\Concerns\User\BelongsToGroups;
use Green\Auth\Models\Concerns\User\HasLoginLogs;
use Green\Auth\Models\Concerns\User\HasPanelAccess;
use Green\Auth\Models\Concerns\User\HasPasswordExpiration;
use Green\Auth\Models\Concerns\User\HasPermissions;
use Green\Auth\Models\Concerns\User\HasRoles;
use Green\Auth\Models\Concerns\User\HasSuspension;
use Illuminate\Foundation\Auth\User;

abstract class BaseUser extends User implements FilamentUser, HasAvatar, HasName
{
    use BelongsToGroups;
    use Concerns\User\HasAvatar;
    use HasLoginLogs;
    use HasPanelAccess;
    use HasPasswordExpiration;
    use HasPermissions;
    use HasRoles;
    use HasSuspension;

    /**
     * Filamentで表示するユーザー名を取得
     */
    public function getFilamentName(): string
    {
        return $this->name ?? $this->email ?? '';
    }
}
