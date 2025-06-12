<?php

return [
    'title' => 'Green Auth インストールウィザード',
    
    'steps' => [
        'basic' => 'ステップ 1: 基本設定',
        'models' => 'ステップ 2: モデル設定',
        'auth' => 'ステップ 3: 認証設定',
        'database' => 'ステップ 4: データベース設定',
        'filament' => 'ステップ 5: Filament設定',
    ],
    
    'prompts' => [
        // 基本設定
        'guard_name' => 'ガード名',
        'model_namespace' => 'モデルの名前空間',
        'use_soft_deletes' => '論理削除を使用しますか？',
        
        // モデル設定
        'user_model' => 'ユーザーモデル名（"n"を入力すると無効）',
        'group_model' => 'グループモデル名（"n"を入力するとグループ機能を無効化）',
        'role_model' => 'ロールモデル名（"n"を入力するとロール機能を無効化）',
        'login_log_model' => 'ログインログモデル名（"n"を入力するとログイン履歴機能を無効化）',
        'extend_user' => '既存のUserモデルを拡張しますか？',
        
        // 機能設定
        'feature_config' => '機能設定:',
        'enable_groups' => 'グループ機能を有効にしますか？',
        'enable_roles' => 'ロール機能を有効にしますか？',
        'enable_permissions' => '権限機能を有効にしますか？',
        'enable_password_expiration' => 'パスワード有効期限を有効にしますか？',
        'enable_account_suspension' => 'アカウント停止機能を有効にしますか？',
        'enable_avatar' => 'ユーザーアバターを有効にしますか？',
        'enable_username' => 'ユーザー名フィールドを有効にしますか？',
        'enable_login_logging' => 'ログイン履歴を有効にしますか？',
        'enable_group_roles' => 'グループにロールを割り当て可能にしますか？',
        
        // 認証設定
        'login_with_email' => 'メールアドレスでのログインを許可しますか？',
        'login_with_username' => 'ユーザー名でのログインを許可しますか？',
        'multiple_groups' => 'ユーザーが複数のグループに所属できるようにしますか？',
        'multiple_roles' => 'ユーザーが複数のロールを持てるようにしますか？',
        'configure_password_rules' => 'パスワードルールを設定しますか？',
        
        // パスワードルール
        'password_rules_config' => 'パスワードルール設定:',
        'password_min_length' => '最小パスワード長',
        'password_require_uppercase' => '大文字を必須にしますか？',
        'password_require_lowercase' => '小文字を必須にしますか？',
        'password_require_numbers' => '数字を必須にしますか？',
        'password_require_symbols' => '記号を必須にしますか？',
        'password_uncompromised' => '漏洩したパスワードをチェックしますか？',
        'password_expires_days' => 'パスワード有効期限（日数）',
        'password_warning_days' => '有効期限前の警告日数',
        
        // データベース設定
        'customize_table_names' => 'テーブル名をカスタマイズしますか？',
        'table_users' => 'ユーザーテーブル名',
        'table_groups' => 'グループテーブル名',
        'table_roles' => 'ロールテーブル名',
        'table_user_groups' => 'ユーザー・グループ中間テーブル名',
        'table_user_roles' => 'ユーザー・ロール中間テーブル名',
        'table_group_roles' => 'グループ・ロール中間テーブル名',
        'table_login_logs' => 'ログイン履歴テーブル名',
        
        // Filament設定
        'generate_resources' => 'Filamentリソースを生成しますか？',
        'resource_namespace' => 'Filamentリソースの名前空間',
        'use_custom_login' => 'カスタムログインページを使用しますか？',
        'allow_password_change' => 'ユーザーがパスワードを変更できるようにしますか？',
        
        // 確認
        'confirm_proceed' => 'インストールを実行しますか？',
    ],
    
    'summary' => [
        'title' => '設定内容:',
        'features' => '機能:',
        'password_rules' => 'パスワードルール:',
    ],
    
    'messages' => [
        'generating_files' => 'ファイルを生成しています...',
        'publishing_config' => '設定を公開しています...',
        'config_published' => '設定が公開されました',
        'generating_models' => 'モデルを生成しています...',
        'model_created' => ':model モデルが作成されました',
        'generating_migrations' => 'マイグレーションを生成しています...',
        'migration_created' => ':migration マイグレーションが作成されました',
        'generating_resources' => 'Filamentリソースを生成しています...',
        'resource_created' => ':resource と ManageRecords ページが作成されました',
        'installation_complete' => 'Green Auth のインストールが正常に完了しました！',
        'installation_cancelled' => 'インストールがキャンセルされました。',
        'default_password_rules' => 'デフォルトのパスワードルール:',
    ],
    
    'next_steps' => [
        'title' => '次のステップ:',
        'update_auth_config' => 'config/auth.php を更新して新しいモデルを使用するように設定してください',
        'run_migrations' => 'php artisan migrate を実行してデータベーステーブルを作成してください',
        'register_resources' => 'Filamentリソースをパネルプロバイダーに登録してください',
        'update_login_page' => 'Filamentパネルをカスタムログインページを使用するように更新してください',
    ],
    
    'settings' => [
        'guard' => 'ガード',
        'namespace' => '名前空間',
        'user_model' => 'ユーザーモデル',
        'group_model' => 'グループモデル',
        'role_model' => 'ロールモデル',
        'login_log_model' => 'ログインログモデル',
        'login_with_email' => 'メールログイン',
        'login_with_username' => 'ユーザー名ログイン',
        'use_soft_deletes' => '論理削除',
        'generate_resources' => 'リソース生成',
    ],
    
    'features' => [
        'groups' => 'グループ',
        'roles' => 'ロール',
        'permissions' => '権限',
        'password_expiration' => 'パスワード有効期限',
        'account_suspension' => 'アカウント停止',
        'user_avatar' => 'ユーザーアバター',
        'username_field' => 'ユーザー名フィールド',
        'login_logging' => 'ログイン履歴',
        'group_role_assignment' => 'グループ・ロール割り当て',
    ],
    
    'password_rules_labels' => [
        'min_length' => '最小長',
        'require_uppercase' => '大文字必須',
        'require_lowercase' => '小文字必須',
        'require_numbers' => '数字必須',
        'require_symbols' => '記号必須',
        'check_compromised' => '漏洩チェック',
        'expires_days' => '有効期限日数',
        'warning_days' => '警告日数',
    ],
    
    'values' => [
        'yes' => 'はい',
        'no' => 'いいえ',
        'enabled' => '有効',
        'disabled' => '無効',
    ],
    
    'labels' => [
        'rule' => 'ルール',
        'value' => '値',
        'min_length' => '最小文字数',
        'require_uppercase' => '大文字必須',
        'require_lowercase' => '小文字必須',
        'require_numbers' => '数字必須',
        'require_symbols' => '記号必須',
        'check_compromised' => '漏洩チェック',
        'expiration_rule' => '有効期限ルール',
        'expires_days' => '有効期限日数',
        'warning_days' => '警告日数',
        'yes' => 'はい',
        'no' => 'いいえ',
    ],
];