<?php

namespace Green\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;

class ParentGroupRule implements ValidationRule
{
    public function __construct(
        protected string $groupModelClass,
        protected ?Model $currentGroup = null,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $parentGroup = $this->groupModelClass::find($value);
        if (! $parentGroup) {
            $fail(__('green-auth::validation.parent_group.not_found'));

            return;
        }

        if ($this->currentGroup) {
            if ($this->currentGroup->getKey() === $parentGroup->getKey()) {
                $fail(__('green-auth::validation.parent_group.self_reference'));

                return;
            }

            if ($this->currentGroup->descendants->contains('id', $parentGroup->getKey())) {
                $fail(__('green-auth::validation.parent_group.circular_reference'));

                return;
            }
        }

        $maxDepth = config('green-auth.groups.max_depth', 10);
        if ($parentGroup->getDepth() >= $maxDepth) {
            $fail(__('green-auth::validation.parent_group.max_depth_exceeded'));
        }
    }

    public static function for(string $groupModelClass, ?Model $currentGroup = null): static
    {
        return new static($groupModelClass, $currentGroup);
    }

    public static function forResource(string $resourceClass, ?Model $currentRecord = null): static
    {
        return new static($resourceClass::getModel(), $currentRecord);
    }
}
