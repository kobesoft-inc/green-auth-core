<?php

return [
    'forms' => [
        'labels' => [
            'name' => '名前',
            'email' => 'メールアドレス',
            'username' => 'ユーザー名',
            'groups' => 'グループ',
            'roles' => 'ロール',
            'password' => 'パスワード',
            'auto_generate_password' => 'パスワードを自動生成する',
            'send_email_notification' => 'メールでパスワードを通知する',
            'require_password_change' => '次回ログイン時に再設定',
        ],
        'validation' => [
            'email_or_username_required' => 'メールアドレスまたはユーザー名のどちらかは必須です。',
            'email_required_for_notification' => 'メールでパスワードを通知するにはメールアドレスが必要です。',
        ],
    ],
    'actions' => [
        'operations' => '操作',
        'suspend' => '停止',
        'unsuspend' => '停止解除',
        'suspend_user' => '停止',
        'unsuspend_user' => '停止解除',
        'create_user' => '新しいユーザーを作成',
        'password_change' => 'パスワード変更',
        'password_reset' => 'パスワード変更',
        'modals' => [
            'suspend_user' => [
                'heading' => 'ユーザーを停止しますか？',
                'description' => 'このユーザーは停止され、ログインできなくなります。',
                'submit' => '停止する',
            ],
            'unsuspend_user' => [
                'heading' => 'ユーザーの停止を解除しますか？',
                'description' => 'このユーザーの停止を解除し、再度ログインできるようになります。',
                'submit' => '停止を解除する',
            ],
        ],
    ],
    'notifications' => [
        'user_suspended' => 'ユーザーを停止しました',
        'user_suspended_message' => ':name を停止しました。',
        'user_unsuspended' => 'ユーザーの停止を解除しました',
        'user_unsuspended_message' => ':name の停止を解除しました。',
        'password_reset_complete' => 'パスワードリセット完了',
        'password_sent_by_email' => '新しいパスワードをメールで送信しました。',
        'new_password_display' => '新しいパスワード: :password',
    ],
    'tables' => [
        'columns' => [
            'name' => '名前',
            'email' => 'メールアドレス',
            'username' => 'ユーザー名',
            'groups' => 'グループ',
            'roles' => 'ロール',
            'status' => 'ステータス',
            'last_login' => '最終ログイン',
            'created_at' => '作成日',
            'updated_at' => '更新日',
            'suspended_at' => '停止日',
        ],
        'filters' => [
            'groups' => 'グループ',
            'roles' => 'ロール',
            'suspended' => '停止状態',
            'email_verified' => 'メール認証',
        ],
    ],
    'models' => [
        'user' => 'ユーザー',
        'group' => 'グループ',
        'role' => 'ロール',
        'admin_user' => '管理者',
        'admin_group' => '管理グループ',
        'admin_role' => '管理ロール',
    ],
    'emails' => [
        'account_created' => [
            'subject' => 'アカウント作成のお知らせ',
            'message' => '新しいアカウントが作成されました。',
        ],
        'password_reset' => [
            'subject' => 'パスワードリセットのお知らせ',
            'message' => 'パスワードがリセットされました。',
        ],
    ],
];
