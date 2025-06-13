<?php

namespace Green\Auth\Rules;

use Closure;
use Green\Auth\Password\PasswordComplexity;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * パスワード複雑性検証ルール
 *
 * ガード設定やユーザーモデルに基づいてパスワードの複雑性を検証する
 */
class PasswordRule implements ValidationRule
{
    protected PasswordComplexity $complexity;

    /**
     * コンストラクタ
     *
     * @param PasswordComplexity $complexity パスワード複雑性設定
     */
    public function __construct(PasswordComplexity $complexity)
    {
        $this->complexity = $complexity;
    }

    /**
     * パスワード複雑性の検証
     *
     * @param string $attribute 属性名
     * @param mixed $value 検証対象の値
     * @param Closure $fail 失敗時のコールバック
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->complexity->isValid($value)) {
            $requirements = $this->complexity->getRequirements();
            $message = __('green-auth::passwords.password_requirements', [
                'requirements' => implode('、', $requirements)
            ]);
            $fail($message);
        }
    }

    /**
     * 詳細なエラーメッセージを取得
     *
     * @param string $password 検証対象のパスワード
     * @return array エラーメッセージ配列
     */
    public function getDetailedErrors(string $password): array
    {
        return $this->complexity->validate($password);
    }

    /**
     * ガード設定からパスワードルールを作成
     *
     * @param string $guard ガード名
     * @return static パスワードルールインスタンス
     */
    public static function fromGuard(string $guard): static
    {
        $complexity = PasswordComplexity::fromAppConfig($guard);
        return new static($complexity);
    }

    /**
     * ユーザーモデルからパスワードルールを作成
     *
     * ユーザーモデルのガード設定を自動検出してパスワードルールを作成
     *
     * @param string $userModelClass ユーザーモデルクラス名
     * @return static パスワードルールインスタンス
     * @throws \InvalidArgumentException ユーザーモデルが見つからない場合
     */
    public static function fromUserModel(string $userModelClass): static
    {
        if (!class_exists($userModelClass)) {
            throw new \InvalidArgumentException("User model class '{$userModelClass}' does not exist.");
        }

        // ユーザーモデルからガード名を取得
        $guard = static::getGuardFromUserModel($userModelClass);

        return static::fromGuard($guard);
    }

    /**
     * 現在のFilamentパネルからパスワードルールを作成
     *
     * @return static パスワードルールインスタンス
     */
    public static function fromCurrentPanel(): static
    {
        $guard = filament()->getCurrentPanel()->getAuthGuard();
        return static::fromGuard($guard);
    }

    /**
     * デフォルトガードからパスワードルールを作成
     *
     * @return static パスワードルールインスタンス
     */
    public static function fromDefaultGuard(): static
    {
        $guard = config('auth.defaults.guard');
        return static::fromGuard($guard);
    }

    /**
     * ユーザーモデルクラスからガード名を取得
     *
     * @param string $userModelClass ユーザーモデルクラス名
     * @return string ガード名
     * @throws \InvalidArgumentException 対応するガードが見つからない場合
     */
    protected static function getGuardFromUserModel(string $userModelClass): string
    {
        // すべての認証設定を確認
        foreach (config('auth.guards', []) as $guardName => $guardConfig) {
            $provider = $guardConfig['provider'] ?? null;
            if (!$provider) {
                continue;
            }

            $model = config("auth.providers.{$provider}.model");
            if ($model === $userModelClass) {
                return $guardName;
            }
        }

        throw new \InvalidArgumentException("No guard found for user model '{$userModelClass}'.");
    }
}
