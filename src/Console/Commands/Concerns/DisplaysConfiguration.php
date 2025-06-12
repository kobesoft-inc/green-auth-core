<?php

namespace Green\AuthCore\Console\Commands\Concerns;

trait DisplaysConfiguration
{
    /**
     * 設定確認
     */
    protected function confirmConfiguration(): bool
    {
        $this->newLine();
        $this->info(__('green-auth::install.summary.title'));
        
        $this->displayBasicSettings();
        $this->displayFeatureSettings();
        $this->displayPasswordSettings();

        return $this->confirm(__('green-auth::install.prompts.confirm_proceed'), true);
    }

    /**
     * 基本設定の表示
     */
    protected function displayBasicSettings(): void
    {
        $basicSettings = [
            [__('green-auth::install.settings.guard'), $this->config['guard']],
            [__('green-auth::install.settings.namespace'), $this->config['namespace']],
            [__('green-auth::install.settings.user_model'), $this->config['models']['user'] ?? 'N/A'],
            [__('green-auth::install.settings.group_model'), $this->config['models']['group'] ?? 'N/A'],
            [__('green-auth::install.settings.role_model'), $this->config['models']['role'] ?? 'N/A'],
            [__('green-auth::install.settings.login_log_model'), $this->config['models']['login_log'] ?? 'N/A'],
            [__('green-auth::install.settings.login_with_email'), $this->config['auth']['login_with_email'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.settings.login_with_username'), $this->config['auth']['login_with_username'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.settings.use_soft_deletes'), $this->config['use_soft_deletes'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.settings.generate_resources'), $this->config['generate_resources'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
        ];

        $this->table(['Setting', 'Value'], $basicSettings);
    }

    /**
     * 機能設定の表示
     */
    protected function displayFeatureSettings(): void
    {
        $featureSettings = [
            [__('green-auth::install.features.groups'), $this->config['features']['groups'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.features.roles'), $this->config['features']['roles'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.features.permissions'), $this->config['features']['permissions'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.features.password_expiration'), $this->config['features']['password_expiration'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.features.account_suspension'), $this->config['features']['account_suspension'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.features.user_avatar'), $this->config['features']['avatar'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.features.username_field'), $this->config['features']['username'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.features.login_logging'), $this->config['features']['login_logging'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.features.group_role_assignment'), $this->config['features']['group_roles'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
        ];

        $this->newLine();
        $this->info(__('green-auth::install.summary.features'));
        $this->table(['Feature', 'Enabled'], $featureSettings);
    }

    /**
     * パスワード設定の表示
     */
    protected function displayPasswordSettings(): void
    {
        if (!isset($this->config['password'])) {
            return;
        }

        $this->newLine();
        $this->info(__('green-auth::install.summary.password_rules'));
        
        $passwordSettings = [
            [__('green-auth::install.password_rules_labels.min_length'), $this->config['password']['min_length']],
            [__('green-auth::install.password_rules_labels.require_uppercase'), $this->config['password']['require_uppercase'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.password_rules_labels.require_lowercase'), $this->config['password']['require_lowercase'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.password_rules_labels.require_numbers'), $this->config['password']['require_numbers'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.password_rules_labels.require_symbols'), $this->config['password']['require_symbols'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
            [__('green-auth::install.password_rules_labels.check_compromised'), $this->config['password']['uncompromised'] ? __('green-auth::install.values.yes') : __('green-auth::install.values.no')],
        ];
        
        if ($this->config['features']['password_expiration']) {
            $passwordSettings[] = [__('green-auth::install.password_rules_labels.expires_days'), $this->config['password']['expires_days']];
            $passwordSettings[] = [__('green-auth::install.password_rules_labels.warning_days'), $this->config['password']['warning_days']];
        }
        
        $this->table(['Password Rule', 'Value'], $passwordSettings);
    }

    /**
     * 次のステップを表示
     */
    protected function displayNextSteps(): void
    {
        $this->newLine();
        $this->info(__('green-auth::install.next_steps.title'));
        $this->line('1. ' . __('green-auth::install.next_steps.update_auth_config'));
        $this->line('2. ' . __('green-auth::install.next_steps.run_migrations'));
        $this->line('3. ' . __('green-auth::install.next_steps.register_resources'));
        
        if (isset($this->config['use_custom_login']) && $this->config['use_custom_login']) {
            $this->line('4. ' . __('green-auth::install.next_steps.update_login_page'));
        }
    }
}