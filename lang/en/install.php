<?php

return [
    'title' => 'Green Auth Installation Wizard',
    
    'steps' => [
        'basic' => 'Step 1: Basic Configuration',
        'models' => 'Step 2: Model Configuration',
        'auth' => 'Step 3: Authentication Configuration',
        'database' => 'Step 4: Database Configuration',
        'filament' => 'Step 5: Filament Configuration',
    ],
    
    'prompts' => [
        // Basic Configuration
        'guard_name' => 'Guard name',
        'model_namespace' => 'Model namespace',
        'use_soft_deletes' => 'Use soft deletes?',
        
        // Model Configuration
        'user_model' => 'User model name (enter "n" to disable)',
        'group_model' => 'Group model name (enter "n" to disable groups)',
        'role_model' => 'Role model name (enter "n" to disable roles)',
        'login_log_model' => 'Login log model name (enter "n" to disable login logging)',
        'extend_user' => 'Extend existing User model?',
        
        // Feature Configuration
        'feature_config' => 'Feature Configuration:',
        'enable_groups' => 'Enable group functionality?',
        'enable_roles' => 'Enable role functionality?',
        'enable_permissions' => 'Enable permission functionality?',
        'enable_password_expiration' => 'Enable password expiration?',
        'enable_account_suspension' => 'Enable account suspension?',
        'enable_avatar' => 'Enable user avatar?',
        'enable_username' => 'Enable username field?',
        'enable_login_logging' => 'Enable login logging?',
        'enable_group_roles' => 'Allow roles to be assigned to groups?',
        
        // Authentication Configuration
        'login_with_email' => 'Allow login with email?',
        'login_with_username' => 'Allow login with username?',
        'multiple_groups' => 'Allow users to belong to multiple groups?',
        'multiple_roles' => 'Allow users to have multiple roles?',
        'configure_password_rules' => 'Configure password rules?',
        
        // Password Rules
        'password_rules_config' => 'Password Rules Configuration:',
        'password_min_length' => 'Minimum password length',
        'password_require_uppercase' => 'Require uppercase letters?',
        'password_require_lowercase' => 'Require lowercase letters?',
        'password_require_numbers' => 'Require numbers?',
        'password_require_symbols' => 'Require symbols?',
        'password_uncompromised' => 'Check against compromised passwords?',
        'password_expires_days' => 'Password expiration days',
        'password_warning_days' => 'Warning days before expiration',
        
        // Database Configuration
        'customize_table_names' => 'Customize table names?',
        'table_users' => 'Users table name',
        'table_groups' => 'Groups table name',
        'table_roles' => 'Roles table name',
        'table_user_groups' => 'User-groups pivot table name',
        'table_user_roles' => 'User-roles pivot table name',
        'table_group_roles' => 'Group-roles pivot table name',
        'table_login_logs' => 'Login logs table name',
        
        // Filament Configuration
        'generate_resources' => 'Generate Filament resources?',
        'resource_namespace' => 'Filament resource namespace',
        'use_custom_login' => 'Use custom login page?',
        'allow_password_change' => 'Allow users to change their passwords?',
        
        // Confirmation
        'confirm_proceed' => 'Proceed with installation?',
    ],
    
    'summary' => [
        'title' => 'Configuration Summary:',
        'features' => 'Features:',
        'password_rules' => 'Password Rules:',
    ],
    
    'messages' => [
        'generating_files' => 'Generating files...',
        'publishing_config' => 'Publishing configuration...',
        'config_published' => 'Configuration published',
        'lang_published' => 'Language files published',
        'generating_models' => 'Generating models...',
        'model_created' => ':model model created',
        'generating_migrations' => 'Generating migrations...',
        'migration_created' => ':migration migration created',
        'generating_resources' => 'Generating Filament resources...',
        'resource_created' => ':resource and ManageRecords page created',
        'installation_complete' => 'Green Auth installation completed successfully!',
        'installation_cancelled' => 'Installation cancelled.',
        'default_password_rules' => 'Default Password Rules:',
    ],
    
    'next_steps' => [
        'title' => 'Next steps:',
        'update_auth_config' => 'Update your config/auth.php to use the new models',
        'run_migrations' => 'Run php artisan migrate to create the database tables',
        'register_resources' => 'Register your Filament resources in your panel provider',
        'update_login_page' => 'Update your Filament panel to use the custom login page',
    ],
    
    'settings' => [
        'guard' => 'Guard',
        'namespace' => 'Namespace',
        'user_model' => 'User Model',
        'group_model' => 'Group Model',
        'role_model' => 'Role Model',
        'login_log_model' => 'Login Log Model',
        'login_with_email' => 'Login with Email',
        'login_with_username' => 'Login with Username',
        'use_soft_deletes' => 'Use Soft Deletes',
        'generate_resources' => 'Generate Resources',
    ],
    
    'features' => [
        'groups' => 'Groups',
        'roles' => 'Roles',
        'permissions' => 'Permissions',
        'password_expiration' => 'Password Expiration',
        'account_suspension' => 'Account Suspension',
        'user_avatar' => 'User Avatar',
        'username_field' => 'Username Field',
        'login_logging' => 'Login Logging',
        'group_role_assignment' => 'Group-Role Assignment',
    ],
    
    'password_rules_labels' => [
        'min_length' => 'Min Length',
        'require_uppercase' => 'Require Uppercase',
        'require_lowercase' => 'Require Lowercase',
        'require_numbers' => 'Require Numbers',
        'require_symbols' => 'Require Symbols',
        'check_compromised' => 'Check Compromised',
        'expires_days' => 'Expires Days',
        'warning_days' => 'Warning Days',
    ],
    
    'values' => [
        'yes' => 'Yes',
        'no' => 'No',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
    ],
    
    'labels' => [
        'rule' => 'Rule',
        'value' => 'Value',
        'min_length' => 'Minimum Length',
        'require_uppercase' => 'Require Uppercase',
        'require_lowercase' => 'Require Lowercase',
        'require_numbers' => 'Require Numbers',
        'require_symbols' => 'Require Symbols',
        'check_compromised' => 'Check Compromised',
        'expiration_rule' => 'Expiration Rule',
        'expires_days' => 'Expires Days',
        'warning_days' => 'Warning Days',
        'yes' => 'Yes',
        'no' => 'No',
    ],
];