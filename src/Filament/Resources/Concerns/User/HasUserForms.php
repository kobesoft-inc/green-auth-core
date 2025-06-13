<?php

namespace Green\Auth\Filament\Resources\Concerns\User;

use Filament\Forms;
use Green\Auth\Filament\Actions\Concerns\ManagesUserPasswords;

trait HasUserForms
{
    use ManagesUserPasswords, HasUserTraitChecks;

    /**
     * 名前入力コンポーネントを作成
     *
     * @return Forms\Components\TextInput 名前入力用TextInputコンポーネント
     */
    public static function getNameFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('name')
            ->label(__('green-auth::users.name'))
            ->required()
            ->maxLength(255);
    }

    /**
     * アバター入力コンポーネントを作成
     *
     * @return Forms\Components\FileUpload|null アバター入力用FileUploadコンポーネント（トレイトがない場合はnull）
     */
    public static function getAvatarFormComponent(): ?Forms\Components\FileUpload
    {
        if (!static::hasAvatarTrait()) {
            return null;
        }

        $modelClass = static::getModel();
        $disk = $modelClass::getAvatarDisk();
        $directory = $modelClass::getAvatarDirectory();

        return Forms\Components\FileUpload::make('avatar')
            ->hiddenLabel()
            ->image()
            ->disk($disk)
            ->directory($directory)
            ->avatar()
            ->imageEditor()
            ->circleCropper()
            ->alignCenter();
    }

    /**
     * グループ選択コンポーネントを作成
     *
     * @return Forms\Components\Select|null グループ選択用Selectコンポーネント（トレイトがない場合はnull）
     */
    public static function getGroupsFormComponent(): ?Forms\Components\Select
    {
        if (!static::hasGroupsTrait()) {
            return null;
        }

        $modelInstance = new (static::getModel())();
        $allowMultiple = $modelInstance->canBelongToMultipleGroups();

        return Forms\Components\Select::make('groups')
            ->label(static::getLocalizedFieldLabel('groups', true))
            ->relationship('groups', 'name')
            ->placeholder('')
            ->multiple($allowMultiple)
            ->preload()
            ->searchable()
            ->getOptionLabelFromRecordUsing(function ($record) {
                if (method_exists($record, 'ancestors')) {
                    $ancestors = $record->ancestors->pluck('name')->join(' > ');
                    if ($ancestors) {
                        return $ancestors . ' > ' . $record->name;
                    }
                }
                return $record->name;
            });
    }

    /**
     * ロール選択コンポーネントを作成
     *
     * @return Forms\Components\Select|null ロール選択用Selectコンポーネント（トレイトがない場合はnull）
     */
    public static function getRolesFormComponent(): ?Forms\Components\Select
    {
        if (!static::hasRolesTrait()) {
            return null;
        }

        $modelInstance = new (static::getModel())();
        $allowMultiple = method_exists($modelInstance, 'canHaveMultipleRoles') ? $modelInstance->canHaveMultipleRoles() : true;

        return Forms\Components\Select::make('roles')
            ->label(static::getLocalizedFieldLabel('roles', true))
            ->relationship('roles', 'name')
            ->placeholder('')
            ->multiple($allowMultiple)
            ->preload()
            ->searchable();
    }

    /**
     * フォームスキーマを取得
     *
     * @return array フォームコンポーネントの配列
     */
    public static function getFormSchema(): array
    {
        $schema = [];

        // Avatar
        if ($avatarInput = static::getAvatarFormComponent()) {
            $schema[] = $avatarInput;
        }

        // Basic fields
        $schema[] = static::getNameFormComponent();

        // Email address input
        $schema[] = static::getEmailFormComponent();

        // Username input (if available)
        if ($usernameInput = static::getUsernameFormComponent()) {
            $schema[] = $usernameInput;
        }

        // Password management fields (create only)
        $passwordFields = static::getPasswordFormComponents(static::getModel());
        foreach ($passwordFields as $field) {
            $schema[] = $field->visibleOn('create');
        }

        // Access control fields
        if ($groupsSelect = static::getGroupsFormComponent()) {
            $schema[] = $groupsSelect;
        }

        if ($rolesSelect = static::getRolesFormComponent()) {
            $schema[] = $rolesSelect;
        }

        return $schema;
    }

    /**
     * メールアドレス入力コンポーネントを作成
     *
     * @return Forms\Components\TextInput メールアドレス入力用TextInputコンポーネント
     */
    protected static function getEmailFormComponent(): Forms\Components\TextInput
    {
        $modelClass = static::getModel();

        return Forms\Components\TextInput::make('email')
            ->label(__('green-auth::users.email'))
            ->email()
            ->maxLength(255)
            ->unique(
                table: (new $modelClass)->getTable(),
                column: 'email',
                ignoreRecord: true,
                modifyRuleUsing: fn($rule) => static::applySoftDeleteScope($rule)
            )
            ->rules([
                function () {
                    return function (string $attribute, $value, \Closure $fail) {
                        $data = request()->all();
                        if (empty($value) && empty($data['username'])) {
                            $fail(__('green-auth::users.validation.email_or_username_required'));
                        }
                    };
                }
            ]);
    }


    /**
     * ユーザー名入力コンポーネントを作成
     *
     * @return Forms\Components\TextInput|null ユーザー名入力用TextInputコンポーネント（トレイトがない場合はnull）
     */
    public static function getUsernameFormComponent(): ?Forms\Components\TextInput
    {
        if (!static::hasUsernameTrait()) {
            return null;
        }

        $modelClass = static::getModel();
        $modelInstance = new $modelClass();
        $usernameColumn = $modelInstance->getUsernameColumn();

        return Forms\Components\TextInput::make($usernameColumn)
            ->label(__('green-auth::users.username'))
            ->maxLength(255)
            ->unique(
                table: $modelInstance->getTable(),
                column: $usernameColumn,
                ignoreRecord: true,
                modifyRuleUsing: fn($rule) => static::applySoftDeleteScope($rule)
            )
            ->rules([
                function () {
                    return function (string $attribute, $value, \Closure $fail) {
                        $data = request()->all();
                        if (empty($value) && empty($data['email'])) {
                            $fail(__('green-auth::users.validation.email_or_username_required'));
                        }
                    };
                }
            ]);
    }


    /**
     * ソフトデリートスコープを適用
     *
     * @param mixed $rule バリデーションルール
     * @return mixed 修正されたバリデーションルール
     */
    protected static function applySoftDeleteScope($rule)
    {
        $modelClass = static::getModel();

        if (method_exists($modelClass, 'withTrashed')) {
            // ソフトデリートが有効な場合、削除済みレコードを除外
            return $rule->withoutTrashed();
        }

        return $rule;
    }

    /**
     * Filamentフォームを取得
     *
     * @param Forms\Form $form フォームインスタンス
     * @return Forms\Form 設定済みフォームインスタンス
     */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema(static::getFormSchema())->columns(1);
    }
}
