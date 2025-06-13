<?php

namespace Green\Auth\Models\Concerns\User;

use Filament\Panel;

trait HasPanelAccess
{
    /**
     * ユーザーがFilamentパネルにアクセスできるかどうかを判定
     *
     * デフォルトでは全てのユーザーがアクセス可能
     * 必要に応じてモデルでオーバーライドしてください
     *
     * @param Panel $panel Filamentパネル
     * @return bool アクセス可能かどうか
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
