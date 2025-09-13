<?php

namespace Green\Auth\Filament\Resources\Concerns\Role;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Schema;
use Filament\Forms;
use Green\Auth\Facades\PermissionManager;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

/**
 * ロールリソース用のフォーム定義を提供するトレイト
 */
trait HasRoleForms
{
    use HasRoleTraitChecks;

    /**
     * ロール名入力フィールドを作成
     */
    public static function getNameFormComponent(): TextInput
    {
        return TextInput::make('name')
            ->label(static::getLocalizedFieldLabel('role_name'))
            ->required()
            ->unique(ignoreRecord: true);
    }

    /**
     * 説明入力フィールドを作成
     */
    public static function getDescriptionFormComponent(): Textarea
    {
        return Textarea::make('description')
            ->label(__('green-auth::roles.description'))
            ->columnSpanFull();
    }

    /**
     * 権限選択コンポーネントを作成
     * HasPermissionsトレイトを使用している場合のみ権限選択UIを返す
     */
    public static function getPermissionsFormComponents(): array
    {
        if (!static::hasPermissionsTrait()) {
            return [];
        }

        // 定義された権限を取得
        $guard = static::getModel()::getGuardName();
        $permissions = PermissionManager::all($guard);

        // 権限が未定義の場合はプレースホルダーを表示
        if ($permissions->isEmpty()) {
            return [];
        }

        // 権限をグループごとに分類し、各グループのセクションを作成
        return $permissions
            ->groupBy(fn($permission) => $permission::getGroup() ?? __('green-auth::roles.other'))
            ->map(fn($permissions, $group) => static::getPermissionCheckboxListFormComponent($group, $permissions))
            ->toArray();
    }

    /**
     * 権限グループのセクションを作成
     */
    protected static function getPermissionCheckboxListFormComponent(string $groupName, Collection $permissions): CheckboxList
    {
        return CheckboxList::make("permissions")
            ->label($groupName)
            ->options($permissions->mapWithKeys(fn($permission) => [$permission::getId() => $permission::getName()]))
            ->columns(['default' => 1, 'sm' => 2, 'lg' => 3])
            ->gridDirection('row');
    }

    /**
     * フォームスキーマを取得
     */
    public static function getFormSchema(): array
    {
        return array_filter([
            static::getNameFormComponent(),
            static::getDescriptionFormComponent(),
            ...static::getPermissionsFormComponents()
        ]);
    }

    /**
     * Filamentフォームを構築
     */
    public static function form(Schema $schema): Schema
    {
        return $schema->components(static::getFormSchema())->columns(1);
    }
}
