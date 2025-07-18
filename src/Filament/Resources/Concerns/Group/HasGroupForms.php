<?php

namespace Green\Auth\Filament\Resources\Concerns\Group;

use Filament\Forms;
use Green\Auth\Rules\ParentGroupRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasGroupForms
{
    use HasGroupTraitChecks;

    /**
     * 名前入力コンポーネントをカスタマイズできるように
     *
     * @return Forms\Components\TextInput 名前入力コンポーネント
     */
    public static function getNameFormComponent(): Forms\Components\TextInput
    {
        return Forms\Components\TextInput::make('name')
            ->label(static::getLocalizedFieldLabel('group_name'))
            ->required()
            ->maxLength(255)
            ->unique(ignoreRecord: true);
    }

    /**
     * 説明入力コンポーネントをカスタマイズできるように
     *
     * @return Forms\Components\Textarea 説明入力コンポーネント
     */
    public static function getDescriptionFormComponent(): Forms\Components\Textarea
    {
        return Forms\Components\Textarea::make('description')
            ->label(__('green-auth::groups.description'))
            ->maxLength(65535)
            ->columnSpanFull();
    }

    /**
     * 親グループ選択コンポーネントをカスタマイズできるように
     *
     * @return Forms\Components\Select|null 親グループ選択コンポーネント
     */
    public static function getParentFormComponent(): ?Forms\Components\Select
    {
        if (!static::hasParentGroupTrait()) {
            return null;
        }

        return Forms\Components\Select::make('parent_id')
            ->label(static::getLocalizedFieldLabel('parent_group'))
            ->relationship(
                'parent',
                'name',
                fn(Builder $query, ?Model $record) => $query->availableAsParentFor($record)
            )
            ->placeholder('')
            ->rules([fn(?Model $record = null) => ParentGroupRule::for(static::getModel(), $record)])
            ->searchable()
            ->preload()
            ->placeholder('')
            ->getOptionLabelFromRecordUsing(fn($record) => $record->getOptionLabel());
    }

    /**
     * ロール選択コンポーネントをカスタマイズできるように
     *
     * @return Forms\Components\Select|null ロール選択コンポーネント（トレイトがない場合はnull）
     */
    public static function getRolesFormComponent(): ?Forms\Components\Select
    {
        if (!static::hasRolesTrait()) {
            return null;
        }

        return Forms\Components\Select::make('roles')
            ->label(static::getTranslatedModelLabel('role', true))
            ->relationship('roles', 'name')
            ->placeholder('')
            ->multiple()
            ->searchable()
            ->preload()
            ->columnSpanFull();
    }

    /**
     * フォームスキーマを取得
     *
     * @return array フォームコンポーネント配列
     */
    public static function getFormSchema(): array
    {
        $schema = [];

        // 基本フィールド
        if ($parentSelect = static::getParentFormComponent()) {
            $schema[] = $parentSelect;
        }

        $schema[] = static::getNameFormComponent();
        $schema[] = static::getDescriptionFormComponent();

        // ロール選択（存在する場合）
        if ($rolesSelect = static::getRolesFormComponent()) {
            $schema[] = $rolesSelect;
        }

        return $schema;
    }

    /**
     * Filamentフォームを取得
     *
     * @param Forms\Form $form フォームインスタンス
     * @return Forms\Form 設定済みフォーム
     */
    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema(static::getFormSchema())->columns(1);
    }
}
