<?php

namespace Green\Auth\Console\Commands\Concerns;

trait CollectsConfiguration
{
    /**
     * モデル名を収集し、'n'の場合はnullを返す
     */
    protected function askForModel(string $prompt, string $default): ?string
    {
        $result = $this->ask(__($prompt), $default);
        return strtolower($result) === 'n' ? null : $result;
    }

    /**
     * 基本設定を収集
     */
    protected function collectBasicConfiguration(): void
    {
        $this->info(__('green-auth::install.steps.basic'));
        $this->newLine();

        $this->config['guard'] = $this->ask(__('green-auth::install.prompts.guard_name'), 'web');
        $this->config['namespace'] = $this->ask(__('green-auth::install.prompts.model_namespace'), 'App\\Models');
        $this->config['use_soft_deletes'] = $this->confirm(__('green-auth::install.prompts.use_soft_deletes'), true);
    }

    /**
     * モデル設定を収集
     */
    protected function collectModelConfiguration(): void
    {
        $this->newLine();
        $this->info(__('green-auth::install.steps.models'));
        $this->newLine();

        // Userモデル名を先に取得
        $userModel = $this->ask(__('green-auth::install.prompts.user_model'), 'User');

        // Userモデル名からGroup/Roleのデフォルト値を生成
        $defaultGroup = $this->generateDefaultModelName($userModel, 'Group');
        $defaultRole = $this->generateDefaultModelName($userModel, 'Role');

        // 各モデル名を収集し、'n'入力で機能を無効化
        $this->config['models'] = [
            'user' => $userModel,
            'group' => $this->askForModel('green-auth::install.prompts.group_model', $defaultGroup),
            'role' => $this->askForModel('green-auth::install.prompts.role_model', $defaultRole),
            'login_log' => $this->askForModel('green-auth::install.prompts.login_log_model', 'LoginLog'),
        ];

        // 'n'が入力された場合、対応する機能を無効化
        $this->config['features'] = [
            'groups' => $this->config['models']['group'] !== null,
            'roles' => $this->config['models']['role'] !== null,
            'login_logging' => $this->config['models']['login_log'] !== null,
        ];

        // 機能選択
        $this->collectFeatureConfiguration();
    }

    /**
     * 機能設定を収集
     */
    protected function collectFeatureConfiguration(): void
    {
        $this->newLine();
        $this->info(__('green-auth::install.prompts.feature_config'));
        $this->newLine();

        // モデル入力で設定された値を保持しつつ、追加の機能を設定
        // permissionsはロールが有効な場合のみ問い合わせ
        if ($this->config['features']['roles']) {
            $this->config['features']['permissions'] = $this->confirm(__('green-auth::install.prompts.enable_permissions'), true);
        } else {
            $this->config['features']['permissions'] = false;
        }

        $this->config['features']['password_expiration'] = $this->confirm(__('green-auth::install.prompts.enable_password_expiration'), true);
        $this->config['features']['account_suspension'] = $this->confirm(__('green-auth::install.prompts.enable_account_suspension'), true);
        $this->config['features']['avatar'] = $this->confirm(__('green-auth::install.prompts.enable_avatar'), true);
        $this->config['features']['username'] = $this->confirm(__('green-auth::install.prompts.enable_username'), true);

        // グループとロールの関係
        if ($this->config['features']['groups'] && $this->config['features']['roles']) {
            $this->config['features']['group_roles'] = $this->confirm(__('green-auth::install.prompts.enable_group_roles'), true);
        } else {
            $this->config['features']['group_roles'] = false;
        }
    }

    /**
     * 認証設定を収集
     */
    protected function collectAuthConfiguration(): void
    {
        $this->newLine();
        $this->info(__('green-auth::install.steps.auth'));
        $this->newLine();

        $this->config['auth'] = [
            'login_with_email' => $this->confirm(__('green-auth::install.prompts.login_with_email'), true),
            'login_with_username' => $this->config['features']['username']
                ? $this->confirm(__('green-auth::install.prompts.login_with_username'), false)
                : false,
        ];

        // グループ/ロールが有効な場合のみ問い合わせ
        if ($this->config['features']['groups']) {
            $this->config['user_permissions']['multiple_groups'] = $this->confirm(__('green-auth::install.prompts.multiple_groups'), true);
        } else {
            $this->config['user_permissions']['multiple_groups'] = false;
        }

        if ($this->config['features']['roles']) {
            $this->config['user_permissions']['multiple_roles'] = $this->confirm(__('green-auth::install.prompts.multiple_roles'), true);
        } else {
            $this->config['user_permissions']['multiple_roles'] = false;
        }

        // パスワードルールの表示と設定確認
        $this->displayPasswordRules();
        if ($this->confirm(__('green-auth::install.prompts.configure_password_rules'), false)) {
            $this->collectPasswordRules();
        } else {
            // デフォルトのパスワードルールを使用
            $this->config['password'] = $this->getDefaultPasswordRules();
        }
    }

    /**
     * パスワードルール設定を収集
     */
    protected function collectPasswordRules(): void
    {
        $defaults = $this->getDefaultPasswordRules();

        $this->newLine();
        $this->info(__('green-auth::install.prompts.password_rules_config'));
        $this->newLine();

        $this->config['password'] = [
            'min_length' => (int) $this->ask(__('green-auth::install.prompts.password_min_length'), (string) $defaults['min_length']),
            'require_uppercase' => $this->confirm(__('green-auth::install.prompts.password_require_uppercase'), $defaults['require_uppercase']),
            'require_lowercase' => $this->confirm(__('green-auth::install.prompts.password_require_lowercase'), $defaults['require_lowercase']),
            'require_numbers' => $this->confirm(__('green-auth::install.prompts.password_require_numbers'), $defaults['require_numbers']),
            'require_symbols' => $this->confirm(__('green-auth::install.prompts.password_require_symbols'), $defaults['require_symbols']),
            'uncompromised' => $this->confirm(__('green-auth::install.prompts.password_uncompromised'), $defaults['uncompromised']),
        ];

        if ($this->config['features']['password_expiration']) {
            $this->config['password']['expires_days'] = (int) $this->ask(__('green-auth::install.prompts.password_expires_days'), (string) $defaults['expires_days']);
            $this->config['password']['warning_days'] = (int) $this->ask(__('green-auth::install.prompts.password_warning_days'), (string) $defaults['warning_days']);
        }
    }

    /**
     * データベース設定を収集
     */
    protected function collectDatabaseConfiguration(): void
    {
        $this->newLine();
        $this->info(__('green-auth::install.steps.database'));
        $this->newLine();

        // テーブル名をカスタマイズするか聞く
        $customizeTables = $this->confirm(__('green-auth::install.prompts.customize_table_names'), false);

        if ($customizeTables) {
            $this->config['tables'] = [
                'users' => $this->ask(__('green-auth::install.prompts.table_users'), 'users'),
            ];

            // 機能が有効な場合のみテーブル名を収集
            if ($this->config['features']['groups']) {
                $this->config['tables']['groups'] = $this->ask(__('green-auth::install.prompts.table_groups'), 'groups');
                $this->config['tables']['user_groups'] = $this->ask(__('green-auth::install.prompts.table_user_groups'), 'user_groups');
            }

            if ($this->config['features']['roles']) {
                $this->config['tables']['roles'] = $this->ask(__('green-auth::install.prompts.table_roles'), 'roles');
                $this->config['tables']['user_roles'] = $this->ask(__('green-auth::install.prompts.table_user_roles'), 'user_roles');
            }

            if ($this->config['features']['groups'] && $this->config['features']['roles']) {
                $this->config['tables']['group_roles'] = $this->ask(__('green-auth::install.prompts.table_group_roles'), 'group_roles');
            }

            if ($this->config['features']['login_logging']) {
                $this->config['tables']['login_logs'] = $this->ask(__('green-auth::install.prompts.table_login_logs'), 'login_logs');
            }
        } else {
            // デフォルトのテーブル名を使用
            $this->config['tables'] = [
                'users' => 'users',
            ];

            if ($this->config['features']['groups']) {
                $this->config['tables']['groups'] = 'groups';
                $this->config['tables']['user_groups'] = 'user_groups';
            }

            if ($this->config['features']['roles']) {
                $this->config['tables']['roles'] = 'roles';
                $this->config['tables']['user_roles'] = 'user_roles';
            }

            if ($this->config['features']['groups'] && $this->config['features']['roles']) {
                $this->config['tables']['group_roles'] = 'group_roles';
            }

            if ($this->config['features']['login_logging']) {
                $this->config['tables']['login_logs'] = 'login_logs';
            }
        }

        $this->config['use_laravel_nestedset'] = true;
    }

    /**
     * Filament設定を収集
     */
    protected function collectFilamentConfiguration(): void
    {
        $this->newLine();
        $this->info(__('green-auth::install.steps.filament'));
        $this->newLine();

        $this->config['generate_resources'] = $this->confirm(__('green-auth::install.prompts.generate_resources'), true);

        if ($this->config['generate_resources']) {
            $this->config['resource_namespace'] = $this->ask(__('green-auth::install.prompts.resource_namespace'), 'App\\Filament\\Resources');
        }

        // ユーザーメニュー設定
        $this->config['user_menu']['allow_password_change'] = $this->confirm(__('green-auth::install.prompts.allow_password_change'), true);
    }

    /**
     * パスワードルールを表示
     */
    protected function displayPasswordRules(): void
    {
        $defaults = $this->getDefaultPasswordRules();

        $this->newLine();
        $this->info(__('green-auth::install.messages.default_password_rules'));
        $this->table([__('green-auth::install.labels.rule'), __('green-auth::install.labels.value')], [
            [__('green-auth::install.labels.min_length'), $defaults['min_length']],
            [__('green-auth::install.labels.require_uppercase'), $defaults['require_uppercase'] ? __('green-auth::install.labels.yes') : __('green-auth::install.labels.no')],
            [__('green-auth::install.labels.require_lowercase'), $defaults['require_lowercase'] ? __('green-auth::install.labels.yes') : __('green-auth::install.labels.no')],
            [__('green-auth::install.labels.require_numbers'), $defaults['require_numbers'] ? __('green-auth::install.labels.yes') : __('green-auth::install.labels.no')],
            [__('green-auth::install.labels.require_symbols'), $defaults['require_symbols'] ? __('green-auth::install.labels.yes') : __('green-auth::install.labels.no')],
            [__('green-auth::install.labels.check_compromised'), $defaults['uncompromised'] ? __('green-auth::install.labels.yes') : __('green-auth::install.labels.no')],
        ]);

        if (isset($this->config['features']['password_expiration']) && $this->config['features']['password_expiration']) {
            $this->table([__('green-auth::install.labels.expiration_rule'), __('green-auth::install.labels.value')], [
                [__('green-auth::install.labels.expires_days'), $defaults['expires_days']],
                [__('green-auth::install.labels.warning_days'), $defaults['warning_days']],
            ]);
        }
    }

    /**
     * デフォルトのパスワードルールを取得
     */
    protected function getDefaultPasswordRules(): array
    {
        $defaults = [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => false,
            'uncompromised' => true,
        ];

        if ($this->config['features']['password_expiration']) {
            $defaults['expires_days'] = 90;
            $defaults['warning_days'] = 7;
        }

        return $defaults;
    }

    /**
     * Userモデル名から他のモデルのデフォルト名を生成
     */
    protected function generateDefaultModelName(string $userModel, string $baseModel): string
    {
        // Userという単語が含まれている場合は置き換える
        if (str_contains($userModel, 'User')) {
            return str_replace('User', $baseModel, $userModel);
        }

        // Userという単語がない場合は標準のモデル名を返す
        return $baseModel;
    }
}
