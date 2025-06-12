<?php

return [
    /**
     * ガード別設定
     * 各ガードごとにモデルクラス、テーブル名、その他の設定を定義
     */
    'guards' => [
        'web' => [
            /**
             * モデルクラス
             */
            'models' => [
                'user' => 'App\\Models\\User',
                'group' => 'App\\Models\\Group',
                'role' => 'App\\Models\\Role',
                'login_log' => 'App\\Models\\LoginLog',
            ],

            /**
             * ピボットテーブル名
             */
            'tables' => [
                'user_groups' => 'user_groups',
                'user_roles' => 'user_roles',
                'group_roles' => 'group_roles',
                'login_logs' => 'login_logs',
            ],

            /**
             * 認証設定
             */
            'auth' => [
                'login_with_email' => true,      // メールアドレスでのログインを許可
                'login_with_username' => false,  // ユーザー名でのログインを許可
            ],

            /**
             * ユーザー権限設定
             */
            'user_permissions' => [
                'multiple_groups' => true,   // ユーザーが複数グループに所属可能
                'multiple_roles' => true,    // ユーザーが複数ロールを持てる
            ],

            /**
             * アバター設定（このガード固有）
             */
            'avatar' => [
                'column' => 'avatar',           // アバターカラム名
                'disk' => 'public',             // アバター保存ディスク
                'directory' => 'avatars',       // アバター保存ディレクトリ
            ],

            /**
             * パスワード設定（このガード固有）
             */
            'password' => [
                'length' => 12,                 // パスワード長
                'uppercase' => true,            // 大文字を含む
                'lowercase' => true,            // 小文字を含む
                'numbers' => true,              // 数字を含む
                'symbols' => true,              // 記号を含む
                'exclude_similar' => true,      // 似た文字を除外
            ],

            /**
             * パスワード有効期限設定（このガード固有）
             */
            'password_expiration' => [
                'enabled' => true,              // パスワード有効期限を有効にする
                'days' => 90,                   // パスワード有効期限（日数）
            ],

            /**
             * ユーザーメニュー設定（このガード固有）
             */
            'user_menu' => [
                'allow_password_change' => true, // ユーザーがパスワードを変更できる
            ],
        ],
    ],
];