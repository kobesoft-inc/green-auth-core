<?php

namespace Green\Auth\Password;

/**
 * パスワード複雑さの要件を定義するクラス
 */
class PasswordComplexity
{
    protected int $minLength = 8;

    protected ?int $maxLength = null;

    protected bool $requireLowercase = true;

    protected bool $requireUppercase = true;

    protected bool $requireNumbers = true;

    protected bool $requireSymbols = false;

    protected array $allowedSymbols = ['!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+', '[', ']', '{', '}', '|', '\\', ':', ';', '"', "'", '<', '>', ',', '.', '?', '/', '~', '`'];

    protected array $forbiddenPatterns = [];

    /**
     * 最小文字数を設定
     */
    public function minLength(int $length): self
    {
        $this->minLength = $length;

        return $this;
    }

    /**
     * 最大文字数を設定
     */
    public function maxLength(?int $length): self
    {
        $this->maxLength = $length;

        return $this;
    }

    /**
     * 小文字の要件を設定
     */
    public function requireLowercase(bool $require = true): self
    {
        $this->requireLowercase = $require;

        return $this;
    }

    /**
     * 大文字の要件を設定
     */
    public function requireUppercase(bool $require = true): self
    {
        $this->requireUppercase = $require;

        return $this;
    }

    /**
     * 数字の要件を設定
     */
    public function requireNumbers(bool $require = true): self
    {
        $this->requireNumbers = $require;

        return $this;
    }

    /**
     * 記号の要件を設定
     */
    public function requireSymbols(bool $require = true): self
    {
        $this->requireSymbols = $require;

        return $this;
    }

    /**
     * 許可する記号を設定
     */
    public function allowedSymbols(array $symbols): self
    {
        $this->allowedSymbols = $symbols;

        return $this;
    }

    /**
     * 禁止パターンを追加
     */
    public function forbidPattern(string $pattern): self
    {
        $this->forbiddenPatterns[] = $pattern;

        return $this;
    }

    /**
     * 複数の禁止パターンを設定
     */
    public function forbidPatterns(array $patterns): self
    {
        $this->forbiddenPatterns = array_merge($this->forbiddenPatterns, $patterns);

        return $this;
    }

    /**
     * パスワードが要件を満たしているかチェック
     */
    public function validate(string $password): array
    {
        $errors = [];

        // 文字数チェック
        $length = mb_strlen($password);
        if ($length < $this->minLength) {
            $errors[] = __('green-auth::passwords.password_min_length', ['min' => $this->minLength]);
        }

        if ($this->maxLength !== null && $length > $this->maxLength) {
            $errors[] = __('green-auth::passwords.password_max_length', ['max' => $this->maxLength]);
        }

        // 小文字チェック
        if ($this->requireLowercase && ! preg_match('/[a-z]/', $password)) {
            $errors[] = __('green-auth::passwords.password_require_lowercase');
        }

        // 大文字チェック
        if ($this->requireUppercase && ! preg_match('/[A-Z]/', $password)) {
            $errors[] = __('green-auth::passwords.password_require_uppercase');
        }

        // 数字チェック
        if ($this->requireNumbers && ! preg_match('/[0-9]/', $password)) {
            $errors[] = __('green-auth::passwords.password_require_numbers');
        }

        // 記号チェック
        if ($this->requireSymbols) {
            $symbolPattern = '/['.preg_quote(implode('', $this->allowedSymbols), '/').']/';
            if (! preg_match($symbolPattern, $password)) {
                $symbolList = implode(' ', $this->allowedSymbols);
                $errors[] = __('green-auth::passwords.password_require_symbols', ['symbols' => $symbolList]);
            }
        }

        // 禁止パターンチェック
        foreach ($this->forbiddenPatterns as $pattern) {
            if (preg_match($pattern, $password)) {
                $errors[] = __('green-auth::passwords.password_forbidden_pattern');
                break;
            }
        }

        return $errors;
    }

    /**
     * パスワードが有効かどうか
     */
    public function isValid(string $password): bool
    {
        return empty($this->validate($password));
    }

    /**
     * 要件の説明を生成
     */
    public function getRequirements(): array
    {
        $requirements = [];

        $maxPart = $this->maxLength ? __('green-auth::passwords.requirements_length_max', ['max' => $this->maxLength]) : '';
        $requirements[] = __('green-auth::passwords.requirements_length', ['min' => $this->minLength, 'max' => $maxPart]);

        if ($this->requireLowercase) {
            $requirements[] = __('green-auth::passwords.requirements_lowercase');
        }

        if ($this->requireUppercase) {
            $requirements[] = __('green-auth::passwords.requirements_uppercase');
        }

        if ($this->requireNumbers) {
            $requirements[] = __('green-auth::passwords.requirements_numbers');
        }

        if ($this->requireSymbols) {
            $symbolList = implode(' ', $this->allowedSymbols);
            $requirements[] = __('green-auth::passwords.requirements_symbols', ['symbols' => $symbolList]);
        }

        return $requirements;
    }

    /**
     * デフォルトの複雑さ設定
     */
    public static function default(): self
    {
        return new self;
    }

    /**
     * 簡単な複雑さ設定
     */
    public static function simple(): self
    {
        return (new self)
            ->minLength(6)
            ->requireLowercase(false)
            ->requireUppercase(false)
            ->requireNumbers(false)
            ->requireSymbols(false);
    }

    /**
     * 強固な複雑さ設定
     */
    public static function strong(): self
    {
        return (new self)
            ->minLength(12)
            ->requireLowercase(true)
            ->requireUppercase(true)
            ->requireNumbers(true)
            ->requireSymbols(true)
            ->forbidPatterns([
                '/(.)\1{2,}/', // 同じ文字の3回以上の連続
                '/123456/', // 連続した数字
                '/abcdef/', // 連続したアルファベット
                '/password/i', // 'password'という文字列
                '/qwerty/i', // 'qwerty'という文字列
            ]);
    }

    /**
     * 設定を配列で取得
     */
    public function toArray(): array
    {
        return [
            'min_length' => $this->minLength,
            'max_length' => $this->maxLength,
            'require_lowercase' => $this->requireLowercase,
            'require_uppercase' => $this->requireUppercase,
            'require_numbers' => $this->requireNumbers,
            'require_symbols' => $this->requireSymbols,
            'allowed_symbols' => $this->allowedSymbols,
            'forbidden_patterns' => $this->forbiddenPatterns,
        ];
    }

    /**
     * 配列から設定を復元
     */
    public static function fromArray(array $config): self
    {
        $complexity = new self;

        if (isset($config['min_length'])) {
            $complexity->minLength($config['min_length']);
        }

        if (isset($config['max_length'])) {
            $complexity->maxLength($config['max_length']);
        }

        if (isset($config['require_lowercase'])) {
            $complexity->requireLowercase($config['require_lowercase']);
        }

        if (isset($config['require_uppercase'])) {
            $complexity->requireUppercase($config['require_uppercase']);
        }

        if (isset($config['require_numbers'])) {
            $complexity->requireNumbers($config['require_numbers']);
        }

        if (isset($config['require_symbols'])) {
            $complexity->requireSymbols($config['require_symbols']);
        }

        if (isset($config['allowed_symbols'])) {
            $complexity->allowedSymbols($config['allowed_symbols']);
        }

        if (isset($config['forbidden_patterns'])) {
            $complexity->forbidPatterns($config['forbidden_patterns']);
        }

        return $complexity;
    }

    /**
     * アプリケーション設定から PasswordComplexity インスタンスを作成
     */
    public static function fromAppConfig(string $guard): self
    {
        $config = config("green-auth.guards.{$guard}.password", []);

        // 設定が空の場合はデフォルトを返す
        if (empty($config)) {
            return self::default();
        }

        return self::fromArray($config);
    }
}
