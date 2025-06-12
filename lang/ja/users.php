<?php

return [
    'name' => '名前',
    'email' => 'メールアドレス',
    'username' => 'ユーザー名',
    'groups' => 'グループ',
    'roles' => 'ロール',
    'status' => 'ステータス',
    'last_login' => '最終ログイン',
    'created_at' => '作成日',
    'updated_at' => '更新日',
    'auto_generate_password' => 'パスワードを自動生成',
    'send_email_notification' => 'メール通知を送信',
    'require_password_change' => '次回ログイン時にパスワード変更を要求',
    'email_or_username_required' => 'メールアドレスまたはユーザー名のいずれかが必要です。',
    
    // Actions
    'actions' => [
        'create_user' => 'ユーザー作成',
        'operations' => '操作',
        'suspend' => '停止',
        'unsuspend' => '停止解除',
        'modals' => [
            'suspend_user' => [
                'heading' => 'ユーザーを停止',
                'description' => 'このユーザーを停止しますか？',
                'submit' => '停止',
            ],
            'unsuspend_user' => [
                'heading' => 'ユーザーの停止を解除',
                'description' => 'このユーザーの停止を解除しますか？',
                'submit' => '停止解除',
            ],
        ],
    ],
    
    // Filters
    'filters' => [
        'groups' => 'グループ',
        'roles' => 'ロール',
        'suspended' => '停止状態',
        'email_verified' => 'メール認証',
    ],
    
    // Validation
    'validation' => [
        'email_or_username_required' => 'メールアドレスまたはユーザー名のいずれかが必要です。',
    ],
];