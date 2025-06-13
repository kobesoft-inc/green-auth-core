<?php

namespace Green\Auth\Console\Commands\Concerns;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

trait GeneratesFiles
{
    /**
     * ファイル生成
     */
    protected function generateFiles(): void
    {
        $this->newLine();
        $this->info(__('green-auth::install.messages.generating_files'));

        $this->publishConfiguration();

        if (!$this->option('skip-models')) {
            $this->generateModels();
        }

        if (!$this->option('skip-migrations')) {
            $this->generateMigrations();
        }

        if (!$this->option('skip-resources') && $this->config['generate_resources']) {
            $this->generateFilamentResources();
        }
    }

    /**
     * 設定ファイルを公開
     */
    protected function publishConfiguration(): void
    {
        $this->info(__('green-auth::install.messages.publishing_config'));

        Artisan::call('vendor:publish', [
            '--tag' => 'green-auth-config',
            '--force' => $this->option('force'),
        ]);

        $this->updateConfigFile();
        $this->line('   ' . __('green-auth::install.messages.config_published'));
    }

    /**
     * 設定ファイルを更新
     */
    protected function updateConfigFile(): void
    {
        $configPath = config_path('green-auth.php');

        // 既存の設定を読み込む
        $existingConfig = [];
        if (File::exists($configPath)) {
            $existingConfig = require $configPath;
        }

        // 新しい設定をマージ
        $newConfig = $this->buildConfigArray();
        $mergedConfig = $this->mergeConfig($existingConfig, $newConfig);

        // 設定ファイルの内容を生成
        $configContent = $this->generateConfigContentFromArray($mergedConfig);
        File::put($configPath, $configContent);
    }

    /**
     * 設定配列を構築
     */
    protected function buildConfigArray(): array
    {
        $guard = $this->config['guard'];

        return [
            'guards' => [
                $guard => [
                    'models' => [
                        'user' => $this->config['namespace'] . '\\' . $this->config['models']['user'],
                        'group' => $this->config['models']['group'] ? $this->config['namespace'] . '\\' . $this->config['models']['group'] : null,
                        'role' => $this->config['models']['role'] ? $this->config['namespace'] . '\\' . $this->config['models']['role'] : null,
                        'login_log' => $this->config['models']['login_log'] ? $this->config['namespace'] . '\\' . $this->config['models']['login_log'] : null,
                    ],

                    'tables' => [
                        'user_groups' => $this->config['tables']['user_groups'] ?? null,
                        'user_roles' => $this->config['tables']['user_roles'] ?? null,
                        'group_roles' => $this->config['tables']['group_roles'] ?? null,
                    ],

                    'auth' => [
                        'login_with_email' => $this->config['auth']['login_with_email'],
                        'login_with_username' => $this->config['auth']['login_with_username'],
                    ],

                    'user_permissions' => [
                        'multiple_groups' => $this->config['user_permissions']['multiple_groups'],
                        'multiple_roles' => $this->config['user_permissions']['multiple_roles'],
                    ],

                    'password' => $this->config['password'] ?? [],

                    'user_menu' => [
                        'allow_password_change' => $this->config['user_menu']['allow_password_change'] ?? true,
                    ],
                ],
            ],
        ];
    }

    /**
     * 設定をマージ
     */
    protected function mergeConfig(array $existing, array $new): array
    {
        foreach ($new as $key => $value) {
            if (is_array($value) && isset($existing[$key]) && is_array($existing[$key])) {
                $existing[$key] = $this->mergeConfig($existing[$key], $value);
            } else {
                $existing[$key] = $value;
            }
        }
        return $existing;
    }

    /**
     * 配列から設定ファイルの内容を生成
     */
    protected function generateConfigContentFromArray(array $config): string
    {
        $exported = var_export($config, true);
        $exported = preg_replace('/array \(/', '[', $exported);
        $exported = preg_replace('/\)/', ']', $exported);
        $exported = preg_replace('/=> \n\s+\[/', '=> [', $exported);
        $exported = str_replace('  ', '    ', $exported);

        return "<?php\n\nreturn $exported;";
    }

    /**
     * 設定ファイルの内容を生成
     */
    protected function generateConfigContent(): string
    {
        $guard = $this->config['guard'];
        $namespace = $this->config['namespace'];

        return "<?php

return [
    'guards' => [
        '{$guard}' => [
            'models' => [
                'user' => '{$namespace}\\{$this->config['models']['user']}',
                'group' => '{$namespace}\\{$this->config['models']['group']}',
                'role' => '{$namespace}\\{$this->config['models']['role']}',
                'login_log' => '{$namespace}\\{$this->config['models']['login_log']}',
            ],

            'tables' => [
                'user_groups' => '{$this->config['tables']['user_groups']}',
                'user_roles' => '{$this->config['tables']['user_roles']}',
                'group_roles' => '{$this->config['tables']['group_roles']}',
            ],

            'auth' => [
                'login_with_email' => {$this->boolToString($this->config['auth']['login_with_email'])},
                'login_with_username' => {$this->boolToString($this->config['auth']['login_with_username'])},
            ],

            'user_permissions' => [
                'multiple_groups' => {$this->boolToString($this->config['user_permissions']['multiple_groups'])},
                'multiple_roles' => {$this->boolToString($this->config['user_permissions']['multiple_roles'])},
            ],
        ],
    ],
];
";
    }

    /**
     * boolean値を文字列に変換
     */
    protected function boolToString(bool $value): string
    {
        return $value ? 'true' : 'false';
    }

    /**
     * ディレクトリが存在することを確認
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    /**
     * マイグレーション生成
     */
    protected function generateMigrations(): void
    {
        $this->info('Generating migrations...');

        foreach ($this->config['models'] as $type => $name) {
            $this->generateMigration($type, $name);
        }

        // ピボットテーブルのマイグレーション生成
        $this->generatePivotTableMigrations();
    }

    /**
     * 個別マイグレーション生成
     */
    protected function generateMigration(string $type, string $name): void
    {
        $tableName = $this->config['tables'][Str::plural(strtolower($type))] ?? Str::plural(Str::snake($name));

        if ($type === 'user' && $tableName === 'users') {
            // Userモデルの場合は既存のusersテーブルにカラムを追加
            $this->generateAddColumnsToUsersMigration();
        } else {
            // その他のモデルは新規テーブル作成
            $this->generateCreateTableMigration($type, $name, $tableName);
        }
    }

    /**
     * usersテーブルへのカラム追加マイグレーション生成
     */
    protected function generateAddColumnsToUsersMigration(): void
    {
        $columns = $this->getUserAdditionalColumns();

        if (empty($columns)) {
            return; // 追加するカラムがない場合はマイグレーションを作成しない
        }

        $timestamp = now()->format('Y_m_d_His');
        $filename = "{$timestamp}_columns_to_users_table.php";
        $migrationPath = database_path("migrations/{$filename}");

        $content = $this->generateAddColumnsToUsersMigrationContent($columns);
        File::put($migrationPath, $content);

        $this->line("   {$filename} created");
    }

    /**
     * usersテーブルに追加するカラムを取得
     */
    protected function getUserAdditionalColumns(): array
    {
        $columns = [];

        if ($this->config['features']['username']) {
            if ($this->config['use_soft_deletes']) {
                $columns[] = "\$table->string('username')->nullable();";
            } else {
                $columns[] = "\$table->string('username')->unique()->nullable();";
            }
        }

        if ($this->config['features']['password_expiration']) {
            $columns[] = "\$table->timestamp('password_expires_at')->nullable();";
        }

        if ($this->config['features']['account_suspension']) {
            $columns[] = "\$table->timestamp('suspended_at')->nullable();";
        }

        if ($this->config['features']['avatar']) {
            $columns[] = "\$table->string('avatar')->nullable();";
        }

        if ($this->config['use_soft_deletes']) {
            $columns[] = "\$table->softDeletes();";
        }

        return $columns;
    }

    /**
     * usersテーブルへのカラム追加マイグレーション内容を生成
     */
    protected function generateAddColumnsToUsersMigrationContent(array $columns): string
    {
        $upColumns = implode("\n            ", $columns);
        $downColumns = $this->generateDownColumnsForUsers();
        $upConstraints = $this->generateUniqueConstraintsForUsers();
        $downConstraints = $this->generateDropConstraintsForUsers();

        $upMethod = "Schema::table('users', function (Blueprint \$table) {
            {$upColumns}
        });";

        $downMethod = "Schema::table('users', function (Blueprint \$table) {
            {$downColumns}
        });";

        if (!empty($upConstraints)) {
            $upMethod .= "\n\n        // 既存のunique制約を削除して、deleted_atを含む複合unique制約を追加\n        {$upConstraints}";
        }

        if (!empty($downConstraints)) {
            $downMethod = "// 複合unique制約を削除して、元のunique制約を復元\n        {$downConstraints}\n\n        " . $downMethod;
        }

        return "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        {$upMethod}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        {$downMethod}
    }
};
";
    }

    /**
     * usersテーブルのdownメソッド用のカラム削除処理を生成
     */
    protected function generateDownColumnsForUsers(): string
    {
        $dropColumns = [];

        if ($this->config['features']['username']) {
            $dropColumns[] = "\$table->dropColumn('username');";
        }

        if ($this->config['features']['password_expiration']) {
            $dropColumns[] = "\$table->dropColumn('password_expires_at');";
        }

        if ($this->config['features']['account_suspension']) {
            $dropColumns[] = "\$table->dropColumn('suspended_at');";
        }

        if ($this->config['features']['avatar']) {
            $dropColumns[] = "\$table->dropColumn('avatar');";
        }

        if ($this->config['use_soft_deletes']) {
            $dropColumns[] = "\$table->dropSoftDeletes();";
        }

        return implode("\n            ", $dropColumns);
    }

    /**
     * ソフトデリート用の複合unique制約を生成
     */
    protected function generateUniqueConstraintsForUsers(): string
    {
        if (!$this->config['use_soft_deletes']) {
            return '';
        }

        $constraints = [];

        // email unique制約の更新
        $constraints[] = "Schema::table('users', function (Blueprint \$table) {
            \$table->dropUnique(['email']);
        });";

        $constraints[] = "Schema::table('users', function (Blueprint \$table) {
            \$table->unique(['email', 'deleted_at']);
        });";

        // username unique制約の追加（有効な場合）
        if ($this->config['features']['username']) {
            $constraints[] = "Schema::table('users', function (Blueprint \$table) {
                \$table->unique(['username', 'deleted_at']);
            });";
        }

        return implode("\n\n        ", $constraints);
    }

    /**
     * 複合unique制約を削除して元のunique制約を復元
     */
    protected function generateDropConstraintsForUsers(): string
    {
        if (!$this->config['use_soft_deletes']) {
            return '';
        }

        $constraints = [];

        // username unique制約の削除（有効な場合）
        if ($this->config['features']['username']) {
            $constraints[] = "Schema::table('users', function (Blueprint \$table) {
                \$table->dropUnique(['username', 'deleted_at']);
            });";
        }

        // email unique制約の復元
        $constraints[] = "Schema::table('users', function (Blueprint \$table) {
            \$table->dropUnique(['email', 'deleted_at']);
        });";

        $constraints[] = "Schema::table('users', function (Blueprint \$table) {
            \$table->unique(['email']);
        });";

        return implode("\n        ", $constraints);
    }

    /**
     * 新規テーブル作成マイグレーション生成
     */
    protected function generateCreateTableMigration(string $type, string $name, string $tableName): void
    {
        $timestamp = now()->addSecond()->format('Y_m_d_His'); // 1秒ずらして重複を防ぐ
        $filename = "{$timestamp}_create_{$tableName}_table.php";
        $migrationPath = database_path("migrations/{$filename}");

        $content = $this->generateCreateTableMigrationContent($type, $tableName);
        File::put($migrationPath, $content);

        $this->line("   {$filename} created");
    }

    /**
     * 新規テーブル作成マイグレーション内容を生成
     */
    protected function generateCreateTableMigrationContent(string $type, string $tableName): string
    {
        $columns = $this->getTableColumns($type);
        $columnsContent = implode("\n            ", $columns);

        return "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            {$columnsContent}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
";
    }

    /**
     * テーブルのカラム定義を取得
     */
    protected function getTableColumns(string $type): array
    {
        $generators = [
            'group' => fn() => $this->getGroupTableColumns(),
            'role' => fn() => $this->getRoleTableColumns(),
            'login_log' => fn() => $this->getLoginLogTableColumns(),
        ];

        $columns = isset($generators[$type]) ? $generators[$type]() : [];

        // 基本カラム
        array_unshift($columns, "\$table->id();");

        if ($this->config['use_soft_deletes']) {
            $columns[] = "\$table->softDeletes();";
        }

        $columns[] = "\$table->timestamps();";

        return $columns;
    }

    /**
     * groupsテーブルのカラム定義を取得
     */
    protected function getGroupTableColumns(): array
    {
        if (!$this->config['features']['groups']) {
            return [];
        }

        $columns = [
            "\$table->string('name');",
            "\$table->text('description')->nullable();",
        ];

        // NestedSetのカラム
        $columns[] = "\$table->nestedSet();";

        return $columns;
    }

    /**
     * rolesテーブルのカラム定義を取得
     */
    protected function getRoleTableColumns(): array
    {
        if (!$this->config['features']['roles']) {
            return [];
        }

        $columns = [
            "\$table->string('name');",
            "\$table->text('description')->nullable();",
        ];

        if ($this->config['features']['permissions']) {
            $columns[] = "\$table->json('permissions')->nullable();";
        }

        return $columns;
    }

    /**
     * login_logsテーブルのカラム定義を取得
     */
    protected function getLoginLogTableColumns(): array
    {
        if (!$this->config['features']['login_logging']) {
            return [];
        }

        return [
            "\$table->unsignedBigInteger('user_id');",
            "\$table->string('guard_name');",
            "\$table->datetime('login_at');",
            "\$table->string('ip_address')->nullable();",
            "\$table->text('user_agent')->nullable();",
            "\$table->string('browser_name')->nullable();",
            "\$table->string('browser_version')->nullable();",
            "\$table->string('platform')->nullable();",
            "\$table->string('device_type')->nullable();",
            "\$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');",
        ];
    }

    /**
     * ピボットテーブルのマイグレーション生成
     */
    protected function generatePivotTableMigrations(): void
    {
        $pivotTables = [];

        if ($this->config['features']['groups']) {
            $pivotTables[] = [
                'name' => $this->config['tables']['user_groups'] ?? 'user_groups',
                'columns' => [
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    "\$table->foreignId('group_id')->constrained()->onDelete('cascade');",
                    "\$table->primary(['user_id', 'group_id']);",
                ]
            ];
        }

        if ($this->config['features']['roles']) {
            $pivotTables[] = [
                'name' => $this->config['tables']['user_roles'] ?? 'user_roles',
                'columns' => [
                    "\$table->foreignId('user_id')->constrained()->onDelete('cascade');",
                    "\$table->foreignId('role_id')->constrained()->onDelete('cascade');",
                    "\$table->primary(['user_id', 'role_id']);",
                ]
            ];

            if ($this->config['features']['groups']) {
                $pivotTables[] = [
                    'name' => $this->config['tables']['group_roles'] ?? 'group_roles',
                    'columns' => [
                        "\$table->foreignId('group_id')->constrained()->onDelete('cascade');",
                        "\$table->foreignId('role_id')->constrained()->onDelete('cascade');",
                        "\$table->primary(['group_id', 'role_id']);",
                    ]
                ];
            }
        }

        foreach ($pivotTables as $index => $pivotTable) {
            $timestamp = now()->addSeconds($index + 10)->format('Y_m_d_His'); // ずらして重複を防ぐ
            $filename = "{$timestamp}_create_{$pivotTable['name']}_table.php";
            $migrationPath = database_path("migrations/{$filename}");

            $content = $this->generatePivotTableMigrationContent($pivotTable['name'], $pivotTable['columns']);
            File::put($migrationPath, $content);

            $this->line("   {$filename} created");
        }
    }

    /**
     * ピボットテーブルマイグレーション内容を生成
     */
    protected function generatePivotTableMigrationContent(string $tableName, array $columns): string
    {
        $columnsContent = implode("\n            ", $columns);

        return "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            {$columnsContent}
            \$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
";
    }

    /**
     * Filamentリソース生成
     */
    protected function generateFilamentResources(): void
    {
        $this->info('Generating Filament resources...');

        $resourceNamespace = $this->config['resource_namespace'] ?? 'App\\Filament\\Resources';

        foreach ($this->config['models'] as $type => $modelName) {
            if (in_array($type, ['user', 'group', 'role'])) {
                $this->generateFilamentResource($type, $modelName, $resourceNamespace);
            }
        }
    }

    /**
     * 個別Filamentリソース生成
     */
    protected function generateFilamentResource(string $type, string $modelName, string $resourceNamespace): void
    {
        $resourceName = $modelName . 'Resource';
        $baseResourceClass = $this->getBaseResourceClass($type);
        $modelNamespace = $this->config['namespace'];

        // リソースクラス生成
        $resourceContent = $this->generateResourceContent($resourceName, $resourceNamespace, $baseResourceClass, $modelName, $modelNamespace, $type);
        $resourcePath = $this->getResourcePath($resourceNamespace, $resourceName);
        $this->ensureDirectoryExists(dirname($resourcePath));
        File::put($resourcePath, $resourceContent);

        // Manageページ生成
        $pageClassName = $this->getManagePageClassName($type);
        $pageContent = $this->generateManagePageContent($resourceName, $resourceNamespace, $modelName, $type);
        $pagePath = $this->getPagePath($resourceNamespace, $resourceName, $pageClassName);
        $this->ensureDirectoryExists(dirname($pagePath));
        File::put($pagePath, $pageContent);

        $this->line("   {$resourceName} and {$pageClassName} page created");
    }

    /**
     * リソースファイルのパスを取得
     */
    protected function getResourcePath(string $namespace, string $resourceName): string
    {
        $relativePath = str_replace(['App\\', '\\'], ['', '/'], $namespace) . "/$resourceName.php";
        return app_path($relativePath);
    }

    /**
     * ページファイルのパスを取得
     */
    protected function getPagePath(string $namespace, string $resourceName, string $pageName): string
    {
        $relativePath = str_replace(['App\\', '\\'], ['', '/'], $namespace) . "/$resourceName/Pages/$pageName.php";
        return app_path($relativePath);
    }

    /**
     * ベースリソースクラス名を取得
     */
    protected function getBaseResourceClass(string $type): string
    {
        $classMap = [
            'user' => 'Green\\AuthCore\\Filament\\Resources\\BaseUserResource',
            'group' => 'Green\\AuthCore\\Filament\\Resources\\BaseGroupResource',
            'role' => 'Green\\AuthCore\\Filament\\Resources\\BaseRoleResource',
        ];

        return $classMap[$type] ?? 'Filament\\Resources\\Resource';
    }

    /**
     * Manageページのクラス名を取得
     */
    protected function getManagePageClassName(string $type): string
    {
        $classMap = [
            'user' => 'ManageUsers',
            'group' => 'ManageGroups',
            'role' => 'ManageRoles',
        ];

        return $classMap[$type] ?? 'ManageRecords';
    }

    /**
     * リソースクラスの内容を生成
     */
    protected function generateResourceContent(string $resourceName, string $resourceNamespace, string $baseClass, string $modelName, string $modelNamespace, string $type): string
    {
        $baseClassName = class_basename($baseClass);
        $pageClassName = $this->getManagePageClassName($type);
        $managePageClass = "\\{$resourceNamespace}\\{$resourceName}\\Pages\\{$pageClassName}";

        return "<?php

namespace {$resourceNamespace};

use {$baseClass} as {$baseClassName};
use {$modelNamespace}\\{$modelName};

class {$resourceName} extends {$baseClassName}
{
    protected static ?string \$model = {$modelName}::class;

    public static function getPages(): array
    {
        return [
            'index' => {$managePageClass}::route('/'),
        ];
    }
}
";
    }

    /**
     * Manageページクラスの内容を生成
     */
    protected function generateManagePageContent(string $resourceName, string $resourceNamespace, string $modelName, string $type): string
    {
        $pageNamespace = "{$resourceNamespace}\\{$resourceName}\\Pages";
        $resourceClass = "{$resourceNamespace}\\{$resourceName}";
        $pageClassName = $this->getManagePageClassName($type);
        $createActionClass = $this->getCreateActionClass($type);

        return "<?php

namespace {$pageNamespace};

use {$resourceClass};
use {$createActionClass};
use Filament\\Resources\\Pages\\ManageRecords as BaseManageRecords;

class {$pageClassName} extends BaseManageRecords
{
    protected static string \$resource = {$resourceName}::class;

    protected function getHeaderActions(): array
    {
        return [
            " . class_basename($createActionClass) . "::make(),
        ];
    }
}
";
    }

    /**
     * CreateActionクラス名を取得
     */
    protected function getCreateActionClass(string $type): string
    {
        $classMap = [
            'user' => 'Green\\AuthCore\\Filament\\Actions\\CreateUserAction',
            'group' => 'Green\\AuthCore\\Filament\\Actions\\CreateGroupAction',
            'role' => 'Green\\AuthCore\\Filament\\Actions\\CreateRoleAction',
        ];

        return $classMap[$type] ?? 'Filament\\Actions\\CreateAction';
    }
}
