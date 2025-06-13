<?php

namespace Green\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

/**
 * 親グループバリデーションルール
 *
 * グループの親子関係において循環参照を防ぐためのバリデーション
 */
class ParentGroupRule implements ValidationRule
{
    protected ?Model $currentGroup;
    protected string $groupModelClass;

    /**
     * コンストラクタ
     *
     * @param string $groupModelClass グループモデルクラス名
     * @param Model|null $currentGroup 現在編集中のグループ（新規作成時はnull）
     */
    public function __construct(string $groupModelClass, ?Model $currentGroup = null)
    {
        $this->groupModelClass = $groupModelClass;
        $this->currentGroup = $currentGroup;
    }

    /**
     * 親グループの選択が有効かを検証
     *
     * @param string $attribute 属性名
     * @param mixed $value 検証対象の値（親グループID）
     * @param Closure $fail 失敗時のコールバック
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 値が空の場合は有効（ルートグループ）
        if (empty($value)) {
            return;
        }

        // 親グループが存在するかチェック
        $parentGroup = $this->findGroupById($value);
        if (!$parentGroup) {
            $fail(__('green-auth::validation.parent_group.not_found'));
            return;
        }

        // 編集中のグループの場合
        if ($this->currentGroup) {
            // 自分自身を親に設定しようとしていないかチェック
            if ($this->currentGroup->getKey() === $parentGroup->getKey()) {
                $fail(__('green-auth::validation.parent_group.self_reference'));
                return;
            }

            // 循環参照をチェック（自分の子孫を親に設定しようとしていないか）
            if ($this->wouldCreateCircularReference($parentGroup)) {
                $fail(__('green-auth::validation.parent_group.circular_reference'));
                return;
            }
        }

        // 深度制限をチェック（設定可能な最大深度を超えないか）
        if ($this->exceedsMaxDepth($parentGroup)) {
            $fail(__('green-auth::validation.parent_group.max_depth_exceeded'));
            return;
        }
    }

    /**
     * IDでグループを検索
     *
     * @param mixed $id グループID
     * @return Model|null グループモデル
     */
    protected function findGroupById($id): ?Model
    {
        return $this->groupModelClass::find($id);
    }

    /**
     * 循環参照が発生するかチェック
     *
     * @param Model $potentialParent 親にしようとしているグループ
     * @return bool 循環参照が発生する場合はtrue
     */
    protected function wouldCreateCircularReference(Model $potentialParent): bool
    {
        // 現在のグループが親グループの祖先になっているかチェック
        if (method_exists($this->currentGroup, 'descendants')) {
            $descendantIds = $this->currentGroup->descendants->pluck('id')->toArray();
            return in_array($potentialParent->getKey(), $descendantIds);
        }

        // ネストセットトレイトがない場合は、シンプルな親子関係のみチェック
        return $this->isDescendantOf($potentialParent, $this->currentGroup);
    }

    /**
     * 指定されたグループが別のグループの子孫かどうかチェック
     *
     * @param Model $group チェック対象のグループ
     * @param Model $ancestor 祖先候補のグループ
     * @return bool 子孫関係にある場合はtrue
     */
    protected function isDescendantOf(Model $group, Model $ancestor): bool
    {
        $current = $group;

        // 最大10階層まで遡って循環参照をチェック（無限ループ防止）
        for ($i = 0; $i < 10; $i++) {
            if (!isset($current->parent_id) || !$current->parent_id) {
                break;
            }

            $parent = $this->findGroupById($current->parent_id);
            if (!$parent) {
                break;
            }

            if ($parent->getKey() === $ancestor->getKey()) {
                return true;
            }

            $current = $parent;
        }

        return false;
    }

    /**
     * 最大深度を超えるかチェック
     *
     * @param Model $parentGroup 親グループ
     * @return bool 最大深度を超える場合はtrue
     */
    protected function exceedsMaxDepth(Model $parentGroup): bool
    {
        $maxDepth = config('green-auth.groups.max_depth', 10);

        if (method_exists($parentGroup, 'getDepth')) {
            return $parentGroup->getDepth() >= $maxDepth;
        }

        // ネストセットトレイトがない場合は親を辿って深度を計算
        $depth = $this->calculateDepth($parentGroup);
        return $depth >= $maxDepth;
    }

    /**
     * グループの深度を計算
     *
     * @param Model $group グループ
     * @return int 深度
     */
    protected function calculateDepth(Model $group): int
    {
        $depth = 0;
        $current = $group;

        // 最大20階層まで遡る（無限ループ防止）
        for ($i = 0; $i < 20; $i++) {
            if (!isset($current->parent_id) || !$current->parent_id) {
                break;
            }

            $parent = $this->findGroupById($current->parent_id);
            if (!$parent) {
                break;
            }

            $depth++;
            $current = $parent;
        }

        return $depth;
    }

    /**
     * ファクトリメソッド：グループモデルと現在のレコードから作成
     *
     * @param string $groupModelClass グループモデルクラス名
     * @param Model|null $currentGroup 現在編集中のグループ
     * @return static
     */
    public static function for(string $groupModelClass, ?Model $currentGroup = null): static
    {
        return new static($groupModelClass, $currentGroup);
    }

    /**
     * ファクトリメソッド：Filamentリソースから作成
     *
     * @param string $resourceClass Filamentリソースクラス名
     * @param Model|null $currentRecord 現在編集中のレコード
     * @return static
     */
    public static function forResource(string $resourceClass, ?Model $currentRecord = null): static
    {
        $modelClass = $resourceClass::getModel();
        return new static($modelClass, $currentRecord);
    }
}
