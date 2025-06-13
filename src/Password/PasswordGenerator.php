<?php

namespace Green\Auth\Password;

use InvalidArgumentException;

/**
 * パスワード生成クラス
 */
class PasswordGenerator
{
    protected PasswordComplexity $complexity;
    protected int $maxAttempts = 100;

    public function __construct(PasswordComplexity $complexity)
    {
        $this->complexity = $complexity;
    }

    /**
     * ガードを指定してPasswordGeneratorを作成
     */
    public static function fromGuard(string $guard): self
    {
        $config = config("green-auth.guards.{$guard}.password", []);
        $complexity = PasswordComplexity::fromArray($config);
        return new self($complexity);
    }

    /**
     * アプリケーション設定からPasswordGeneratorを作成
     */
    public static function fromAppConfig(string $guard): self
    {
        return static::fromGuard($guard);
    }

    /**
     * デフォルト設定でPasswordGeneratorを作成
     */
    public static function default(): self
    {
        return new self(PasswordComplexity::default());
    }

    /**
     * 強固な設定でPasswordGeneratorを作成
     */
    public static function strong(): self
    {
        return new self(PasswordComplexity::strong());
    }

    /**
     * 簡単な設定でPasswordGeneratorを作成
     */
    public static function simple(): self
    {
        return new self(PasswordComplexity::simple());
    }

    /**
     * パスワードを生成
     */
    public function generate(?int $length = null): string
    {
        $length = $length ?? $this->complexity->toArray()['min_length'];

        for ($attempt = 0; $attempt < $this->maxAttempts; $attempt++) {
            $password = $this->generatePassword($length);

            if ($this->complexity->isValid($password)) {
                return $password;
            }
        }

        throw new InvalidArgumentException("Could not generate a password that meets complexity requirements after {$this->maxAttempts} attempts.");
    }

    /**
     * パスワード生成の内部実装
     */
    protected function generatePassword(int $length): string
    {
        $config = $this->complexity->toArray();
        $characterSets = $this->getCharacterSets($config);

        if (empty($characterSets)) {
            throw new InvalidArgumentException('No available character sets.');
        }

        $password = '';
        $allCharacters = implode('', $characterSets);

        // 各必須文字セットから最低1文字ずつ選択
        $requiredChars = $this->getRequiredCharacters($config, $characterSets);

        // 残りの長さを全文字セットから選択
        $remainingLength = $length - count($requiredChars);
        for ($i = 0; $i < $remainingLength; $i++) {
            $requiredChars[] = $allCharacters[random_int(0, strlen($allCharacters) - 1)];
        }

        // 文字をシャッフル
        $passwordArray = str_split(implode('', $requiredChars));
        shuffle($passwordArray);

        return implode('', $passwordArray);
    }

    /**
     * 利用可能な文字セットを取得
     */
    protected function getCharacterSets(array $config): array
    {
        $sets = [];

        if ($config['require_lowercase']) {
            $sets['lowercase'] = 'abcdefghijklmnopqrstuvwxyz';
        }

        if ($config['require_uppercase']) {
            $sets['uppercase'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($config['require_numbers']) {
            $sets['numbers'] = '0123456789';
        }

        if ($config['require_symbols']) {
            $sets['symbols'] = implode('', $config['allowed_symbols']);
        }

        // 何も要件がない場合はデフォルトで小文字を使用
        if (empty($sets)) {
            $sets['lowercase'] = 'abcdefghijklmnopqrstuvwxyz';
        }

        return $sets;
    }

    /**
     * 必須文字を各セットから1文字ずつ取得
     */
    protected function getRequiredCharacters(array $config, array $characterSets): array
    {
        $requiredChars = [];

        foreach ($characterSets as $setName => $characters) {
            $requiredChars[] = $characters[random_int(0, strlen($characters) - 1)];
        }

        return $requiredChars;
    }

    /**
     * パスワードが要件を満たしているかチェック
     */
    public function isValid(string $password): bool
    {
        return $this->complexity->isValid($password);
    }

    /**
     * パスワードのバリデーションエラーを取得
     */
    public function validate(string $password): array
    {
        return $this->complexity->validate($password);
    }

    /**
     * 複雑度要件の説明を取得
     */
    public function getRequirements(): array
    {
        return $this->complexity->getRequirements();
    }

    /**
     * 最大試行回数を設定
     */
    public function setMaxAttempts(int $attempts): self
    {
        $this->maxAttempts = $attempts;
        return $this;
    }

    /**
     * 現在の複雑度設定を取得
     */
    public function getComplexity(): PasswordComplexity
    {
        return $this->complexity;
    }

    /**
     * 複雑度設定を更新
     */
    public function setComplexity(PasswordComplexity $complexity): self
    {
        $this->complexity = $complexity;
        return $this;
    }
}
