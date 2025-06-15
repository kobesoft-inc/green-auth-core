<?php

namespace Green\Auth\Models\Concerns\Group;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait HasHierarchy
{
    /**
     * 階層構造を含む表示名を取得
     *
     * 祖先グループがある場合は「祖先 > 子 > 孫」のように階層を表示し、
     * ない場合は単純にname属性を返す
     *
     * @return string 階層構造を含む表示名
     */
    public function getHierarchicalName(): string
    {
        if ($this->ancestors->isNotEmpty()) {
            $ancestors = $this->ancestors->pluck('name')->join(' > ');
            return $ancestors . ' > ' . $this->name;
        }

        return $this->name;
    }

    /**
     * 親として選択可能なグループのクエリスコープ
     *
     * 指定されたレコードが存在する場合、循環参照を防ぐために
     * 以下を除外したクエリを返す：
     * - レコード自身
     * - レコードの全ての子孫
     *
     * @param Builder $query クエリビルダー
     * @param Model|null $record 除外対象のレコード
     * @return Builder 親として選択可能なグループのクエリ
     */
    public function scopeAvailableAsParentFor(Builder $query, ?Model $record = null): Builder
    {
        return $query->when(
            $record,
            fn(Builder $q) => $q
                ->where('id', '!=', $record->id)
                ->whereNotIn('id', $record->descendants->pluck('id'))
        );
    }

    /**
     * Filamentフォームの選択肢表示用のラベルを取得
     *
     * getHierarchicalName()のエイリアスメソッド
     * Filamentフォームでの使用を想定
     *
     * @return string 選択肢用のラベル
     */
    public function getOptionLabel(): string
    {
        return $this->getHierarchicalName();
    }
}