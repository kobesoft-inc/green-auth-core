<?php

namespace Green\Auth\Console\Commands\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait GeneratesModels
{
    /**
     * モデル生成
     */
    protected function generateModels(): void
    {
        $this->info('Generating models...');

        foreach ($this->config['models'] as $type => $name) {
            if ($name === null) {
                continue;
            }
            $this->generateModel($type, $name);
            $this->line("   {$name} model created");
        }
    }

    /**
     * 個別モデル生成
     */
    protected function generateModel(string $type, string $name): void
    {
        $namespace = $this->config['namespace'];
        $baseClass = $this->getBaseModelClass($type);
        $traits = $this->getModelTraits($type);
        $table = $this->config['tables'][Str::plural(strtolower($type))] ?? null;

        $content = $this->generateModelContent($name, $namespace, $baseClass, $traits, $table, $type);

        $path = app_path(str_replace('App\\', '', $namespace) . "/{$name}.php");
        $this->ensureDirectoryExists(dirname($path));

        if (!file_exists($path) || $this->option('force')) {
            File::put($path, $content);
        }
    }

    /**
     * モデルの内容を生成
     */
    protected function generateModelContent(string $name, string $namespace, string $baseClass, array $traits, ?string $table, string $type): string
    {
        // LoginLogの場合は、BaseLoginLogを継承するだけのシンプルなクラスを生成
        if ($type === 'login_log') {
            return $this->generateSimpleLoginLogModel($name, $namespace, $baseClass, $table);
        }

        // Userモデルで、Laravelの標準UserモデルをベースとしFilamentを使用する場合の特別処理
        if ($type === 'user' && $baseClass === 'Illuminate\\Foundation\\Auth\\User') {
            return $this->generateLaravelUserModel($name, $namespace, $baseClass, $traits, $table);
        }

        $useStatements = collect($traits)->map(fn($trait) => "use {$trait};")->implode("\n");
        $traitNames = collect($traits)->map(fn($trait) => "    use " . class_basename($trait) . ";")->implode("\n");

        // デフォルトのテーブル名と異なる場合のみtableプロパティを生成
        $defaultTableName = $this->getDefaultTableName($type);
        $tableProperty = $table && $table !== $defaultTableName ? "\n    protected \$table = '$table';" : '';

        // 機能に基づいたプロパティを生成
        $constantsProperty = $this->generateConstantsProperty($type);
        $fillableProperty = $this->generateFillableProperty($type);
        $hiddenProperty = $this->generateHiddenProperty($type);
        $castsProperty = $this->generateCastsProperty($type);

        return "<?php

namespace $namespace;

$useStatements

class $name extends \\$baseClass
{
$traitNames$tableProperty$constantsProperty$fillableProperty$hiddenProperty$castsProperty
}
";
    }

    /**
     * Laravel標準UserモデルをベースとしたUserモデルを生成
     */
    protected function generateLaravelUserModel(string $name, string $namespace, string $baseClass, array $traits, ?string $table): string
    {
        // Filament用のインターフェースを追加
        $interfaces = ['FilamentUser'];
        if ($this->config['features']['avatar']) {
            $interfaces[] = 'HasAvatar';
        }
        $implementsClause = 'implements ' . implode(', ', $interfaces);

        // use文を生成
        $useStatements = collect($traits)->map(fn($trait) => "use {$trait};")->implode("\n");
        $filamentUseStatements = "use Filament\\Models\\Contracts\\FilamentUser;";
        if ($this->config['features']['avatar']) {
            $filamentUseStatements .= "\nuse Filament\\Models\\Contracts\\HasAvatar;";
        }

        // トレイト使用を生成
        $traitNames = collect($traits)->map(fn($trait) => "    use " . class_basename($trait) . ";")->implode("\n");

        // デフォルトのテーブル名と異なる場合のみtableプロパティを生成
        $defaultTableName = $this->getDefaultTableName('user');
        $tableProperty = $table && $table !== $defaultTableName ? "\n    protected \$table = '$table';" : '';

        // 機能に基づいたプロパティを生成
        $constantsProperty = $this->generateConstantsProperty('user');
        $fillableProperty = $this->generateFillableProperty('user');
        $hiddenProperty = $this->generateHiddenProperty('user');
        $castsProperty = $this->generateCastsProperty('user');

        return "<?php

namespace $namespace;

$filamentUseStatements
$useStatements

class $name extends \\$baseClass $implementsClause
{
$traitNames$tableProperty$constantsProperty$fillableProperty$hiddenProperty$castsProperty
}
";
    }

    /**
     * シンプルなLoginLogモデルを生成
     */
    protected function generateSimpleLoginLogModel(string $name, string $namespace, string $baseClass, ?string $table): string
    {
        // デフォルトのテーブル名と異なる場合のみtableプロパティを生成
        $defaultTableName = $this->getDefaultTableName('login_log');
        $tableProperty = $table && $table !== $defaultTableName ? "\n    protected \$table = '$table';" : '';

        return "<?php

namespace $namespace;

use $baseClass;

class $name extends " . class_basename($baseClass) . "
{" . $tableProperty . "
}
";
    }

    /**
     * モデルタイプからデフォルトのテーブル名を取得
     */
    protected function getDefaultTableName(string $type): string
    {
        return match ($type) {
            'user' => 'users',
            'group' => 'groups',
            'role' => 'roles',
            'login_log' => 'login_logs',
            default => Str::plural(strtolower($type))
        };
    }

    /**
     * ベースモデルクラスを取得
     */
    protected function getBaseModelClass(string $type): string
    {
        // 全ての機能が有効な場合のみBaseモデルを使用
        if ($this->shouldUseBaseModel($type)) {
            $baseClasses = [
                'user' => 'Green\\Auth\\Models\\BaseUser',
                'group' => 'Green\\Auth\\Models\\BaseGroup',
                'role' => 'Green\\Auth\\Models\\BaseRole',
                'login_log' => 'Green\\Auth\\Models\\BaseLoginLog',
            ];
            return $baseClasses[$type] ?? 'Illuminate\\Database\\Eloquent\\Model';
        }

        if ($type === 'user') {
            return 'Illuminate\\Foundation\\Auth\\User';
        }

        return 'Illuminate\\Database\\Eloquent\\Model';
    }

    /**
     * Baseモデルを使用すべきか判定
     */
    protected function shouldUseBaseModel(string $type): bool
    {
        switch ($type) {
            case 'user':
                // UserはBaseUserに全ての機能が含まれているため、常に使用可能
                // username機能はオプショナル（トレイトで追加）
                return $this->config['features']['groups'] &&
                    $this->config['features']['roles'] &&
                    $this->config['features']['permissions'] &&
                    $this->config['features']['password_expiration'] &&
                    $this->config['features']['account_suspension'] &&
                    $this->config['features']['avatar'];

            case 'group':
                // GroupはBaseGroupに全ての機能が含まれている
                return $this->config['features']['groups'] &&
                    $this->config['features']['roles'] &&
                    $this->config['features']['permissions'];

            case 'role':
                // RoleはBaseRoleに全ての機能が含まれている
                return $this->config['features']['roles'] &&
                    $this->config['features']['permissions'] &&
                    $this->config['features']['groups'];

            case 'login_log':
                // LoginLogは単純なので常にBaseを使用
                return $this->config['features']['login_logging'];

            default:
                return false;
        }
    }

    /**
     * $fillableプロパティを生成
     */
    protected function generateFillableProperty(string $type): string
    {
        $fillable = $this->getFillableFields($type);

        if (empty($fillable)) {
            return '';
        }

        $fields = collect($fillable)
            ->map(fn($field) => "        '$field',")
            ->implode("\n");

        return "\n\n    /**\n     * The attributes that are mass assignable.\n     */\n    protected \$fillable = [\n$fields\n    ];";
    }

    /**
     * $hiddenプロパティを生成
     */
    protected function generateHiddenProperty(string $type): string
    {
        $hidden = $this->getHiddenFields($type);

        if (empty($hidden)) {
            return '';
        }

        $fields = collect($hidden)
            ->map(fn($field) => "        '$field',")
            ->implode("\n");

        return "\n\n    /**\n     * The attributes that should be hidden for serialization.\n     */\n    protected \$hidden = [\n$fields\n    ];";
    }

    /**
     * $castsプロパティを生成
     */
    protected function generateCastsProperty(string $type): string
    {
        $casts = $this->getCastFields($type);

        if (empty($casts)) {
            return '';
        }

        $fields = collect($casts)
            ->map(fn($cast, $field) => "        '$field' => '$cast',")
            ->implode("\n");

        return "\n\n    /**\n     * The attributes that should be cast.\n     */\n    protected \$casts = [\n$fields\n    ];";
    }

    /**
     * 定数プロパティを生成
     */
    protected function generateConstantsProperty(string $type): string
    {
        $constants = [];

        // USERNAME_COLUMNはHasUsernameトレイトで管理されるため、定数として出力しない
        // if ($type === 'user' && $this->config['features']['username']) {
        //     $constants[] = "    const USERNAME_COLUMN = 'username';";
        // }

        if (empty($constants)) {
            return '';
        }

        return "\n\n" . implode("\n", $constants);
    }

    /**
     * モデルタイプに基づいてfillableフィールドを取得
     */
    protected function getFillableFields(string $type): array
    {
        $generators = [
            'user' => fn() => $this->getUserFillableFields(),
            'group' => fn() => $this->getGroupFillableFields(),
            'role' => fn() => $this->getRoleFillableFields(),
            'login_log' => fn() => $this->getLoginLogFillableFields(),
        ];

        return isset($generators[$type]) ? $generators[$type]() : [];
    }

    /**
     * User用のfillableフィールドを取得
     */
    protected function getUserFillableFields(): array
    {
        // Laravel標準のUserモデルのフィールドを含む
        $fillable = ['name', 'email', 'password'];

        if ($this->config['features']['username']) {
            $fillable[] = 'username';
        }

        if ($this->config['features']['password_expiration']) {
            $fillable[] = 'password_expires_at';
        }

        if ($this->config['features']['account_suspension']) {
            $fillable[] = 'suspended_at';
        }

        if ($this->config['features']['avatar']) {
            $fillable[] = 'avatar';
        }

        return $fillable;
    }

    /**
     * Group用のfillableフィールドを取得
     */
    protected function getGroupFillableFields(): array
    {
        return $this->config['features']['groups'] ? ['name', 'description'] : [];
    }

    /**
     * Role用のfillableフィールドを取得
     */
    protected function getRoleFillableFields(): array
    {
        if (!$this->config['features']['roles']) {
            return [];
        }

        $fillable = ['name', 'description'];

        if ($this->config['features']['permissions']) {
            $fillable[] = 'permissions';
        }

        return $fillable;
    }

    /**
     * LoginLog用のfillableフィールドを取得
     */
    protected function getLoginLogFillableFields(): array
    {
        return $this->config['features']['login_logging']
            ? ['user_id', 'ip_address', 'user_agent']
            : [];
    }

    /**
     * モデルタイプに基づいてcastフィールドを取得
     */
    protected function getCastFields(string $type): array
    {
        $generators = [
            'user' => fn() => $this->getUserCastFields(),
            'role' => fn() => $this->getRoleCastFields(),
            'login_log' => fn() => $this->getLoginLogCastFields(),
        ];

        return isset($generators[$type]) ? $generators[$type]() : [];
    }

    /**
     * モデルタイプに基づいてhiddenフィールドを取得
     */
    protected function getHiddenFields(string $type): array
    {
        $generators = [
            'user' => fn() => $this->getUserHiddenFields(),
        ];

        return isset($generators[$type]) ? $generators[$type]() : [];
    }

    /**
     * User用のhiddenフィールドを取得
     */
    protected function getUserHiddenFields(): array
    {
        // Laravel標準のUserモデルのhiddenフィールドを含む
        return ['password', 'remember_token'];
    }

    /**
     * User用のcastフィールドを取得
     */
    protected function getUserCastFields(): array
    {
        // Laravel標準のUserモデルのcastsを含む
        $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];

        if ($this->config['features']['password_expiration']) {
            $casts['password_expires_at'] = 'datetime';
        }

        if ($this->config['features']['account_suspension']) {
            $casts['suspended_at'] = 'datetime';
        }

        return $casts;
    }

    /**
     * Role用のcastフィールドを取得
     */
    protected function getRoleCastFields(): array
    {
        if ($this->config['features']['roles'] && $this->config['features']['permissions']) {
            return ['permissions' => 'array'];
        }

        return [];
    }

    /**
     * LoginLog用のcastフィールドを取得
     */
    protected function getLoginLogCastFields(): array
    {
        return $this->config['features']['login_logging']
            ? ['created_at' => 'datetime']
            : [];
    }

    /**
     * モデルのトレイトを取得
     */
    protected function getModelTraits(string $type): array
    {
        // Baseモデルを使用する場合、既に含まれているトレイトは追加しない
        $isUsingBaseModel = $this->shouldUseBaseModel($type);

        $generators = [
            'user' => fn() => $this->getUserTraits($isUsingBaseModel),
            'group' => fn() => $this->getGroupTraits($isUsingBaseModel),
            'role' => fn() => $this->getRoleTraits($isUsingBaseModel),
        ];

        $traits = isset($generators[$type]) ? $generators[$type]() : [];

        // HasModelConfigは全てのBaseモデルに含まれているため、Baseモデル使用時は追加しない
        if (!$isUsingBaseModel) {
            $traits[] = 'Green\\Auth\\Models\\Concerns\\HasModelConfig';
        }

        // LoginLogの場合はSoftDeletesを適用しない
        if ($this->config['use_soft_deletes'] && $type !== 'login_log') {
            $traits[] = 'Illuminate\\Database\\Eloquent\\SoftDeletes';
        }

        return $traits;
    }

    /**
     * User用のトレイトを取得
     */
    protected function getUserTraits(bool $isUsingBaseModel = false): array
    {
        $traits = [];

        // BaseUserを使用している場合、含まれていないトレイトのみを追加
        if ($isUsingBaseModel) {
            // HasUsernameはBaseUserに含まれていないので、必要なら追加
            if ($this->config['features']['username']) {
                $traits[] = 'Green\\Auth\\Models\\Concerns\\User\\HasUsername';
            }
        } else {
            // Eloquent Modelを継承する場合は全てのトレイトを追加
            if ($this->config['features']['username']) {
                $traits[] = 'Green\\Auth\\Models\\Concerns\\User\\HasUsername';
            }

            if ($this->config['features']['groups']) {
                $traits[] = 'Green\\Auth\\Models\\Concerns\\User\\BelongsToGroups';
            }

            if ($this->config['features']['roles']) {
                $traits[] = 'Green\\Auth\\Models\\Concerns\\User\\HasRoles';
            }

            if ($this->config['features']['permissions']) {
                $traits[] = 'Green\\Auth\\Models\\Concerns\\User\\HasPermissions';
            }

            if ($this->config['features']['password_expiration']) {
                $traits[] = 'Green\\Auth\\Models\\Concerns\\User\\HasPasswordExpiration';
            }

            if ($this->config['features']['account_suspension']) {
                $traits[] = 'Green\\Auth\\Models\\Concerns\\User\\HasSuspension';
            }

            if ($this->config['features']['avatar']) {
                $traits[] = 'Green\\Auth\\Models\\Concerns\\User\\HasAvatar';
            }

            // Filament用のパネルアクセストレイト（Laravel標準UserモデルとBaseUserの両方で追加）
            $traits[] = 'Green\\Auth\\Models\\Concerns\\User\\HasPanelAccess';
        }

        return $traits;
    }

    /**
     * Group用のトレイトを取得
     */
    protected function getGroupTraits(bool $isUsingBaseModel = false): array
    {
        if (!$this->config['features']['groups']) {
            return [];
        }

        // BaseGroupを使用している場合、全てのトレイトが既に含まれているため空配列を返す
        if ($isUsingBaseModel) {
            return [];
        }

        // Eloquent Modelを継承する場合は全てのトレイトを追加
        $traits = [
            'Kalnoy\\Nestedset\\NodeTrait',
            'Green\\Auth\\Models\\Concerns\\Group\\HasUsers',
            'Green\\Auth\\Models\\Concerns\\Group\\HasHierarchy',
        ];

        if ($this->config['features']['roles']) {
            $traits[] = 'Green\\Auth\\Models\\Concerns\\Group\\HasRoles';
        }

        if ($this->config['features']['permissions']) {
            $traits[] = 'Green\\Auth\\Models\\Concerns\\Group\\HasPermissions';
        }

        return $traits;
    }

    /**
     * Role用のトレイトを取得
     */
    protected function getRoleTraits(bool $isUsingBaseModel = false): array
    {
        if (!$this->config['features']['roles']) {
            return [];
        }

        // BaseRoleを使用している場合、全てのトレイトが既に含まれているため空配列を返す
        if ($isUsingBaseModel) {
            return [];
        }

        // Eloquent Modelを継承する場合は全てのトレイトを追加
        $traits = ['Green\\Auth\\Models\\Concerns\\Role\\HasUsers'];

        if ($this->config['features']['groups']) {
            $traits[] = 'Green\\Auth\\Models\\Concerns\\Role\\HasGroups';
        }

        if ($this->config['features']['permissions']) {
            $traits[] = 'Green\\Auth\\Models\\Concerns\\Role\\HasPermissions';
        }

        return $traits;
    }
}
