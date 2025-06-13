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
            ->label('グループ名')
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
            ->label('説明')
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
            ->label('親グループ')
            ->relationship(
                'parent',
                'name',
                fn(Builder $query, ?Model $record) => $query
                    ->when(
                        $record,
                        fn(Builder $query) => $query
                            ->where('id', '!=', $record->id)
                            ->whereNotIn('id', $record->descendants->pluck('id'))
                    )
            )
            ->rules([
                function (?Model $record = null) {
                    return ParentGroupRule::for(static::getModel(), $record);
                }
            ])
            ->searchable()
            ->preload()
            ->placeholder('なし（ルートグループ）')
            ->getOptionLabelFromRecordUsing(function ($record) {
                if (method_exists($record, 'ancestors')) {
                    $ancestors = $record->ancestors->pluck('name')->join(' > ');
                    if ($ancestors) {
                        return $ancestors . ' > ' . $record->name;
                    }
                }
                return $record->name;
            })
;
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
            ->label('ロール')
            ->relationship('roles', 'name')
            ->multiple()
            ->searchable()
            ->preload()
            ->placeholder('ロールを選択してください')
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
