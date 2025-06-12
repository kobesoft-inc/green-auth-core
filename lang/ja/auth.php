<?php

return [
    'login' => [
        'rate_limit_exceeded' => 'レート制限を超えました',
        'rate_limit_message' => ':seconds秒後にもう一度お試しください',
        'account_suspended' => 'アカウントが停止されています',
        'account_suspended_message' => 'このアカウントは停止されているためログインできません',
        'invalid_credentials' => '認証情報が正しくありません',
        'title' => 'ログイン',
        'heading' => 'アカウントにログイン',
        'submit' => 'ログイン',
        'remember_me' => 'ログイン状態を保持',
        'fields' => [
            'password' => 'パスワード',
            'username_or_email' => 'ユーザー名またはメールアドレス',
            'username' => 'ユーザー名',
            'email' => 'メールアドレス',
            'placeholders' => [
                'username_or_email' => 'ユーザー名またはメールアドレスを入力',
                'username' => 'ユーザー名を入力',
                'email' => 'メールアドレスを入力',
            ],
        ],
    ],
    'change_password' => [
        'title' => 'パスワード変更',
        'heading' => 'パスワードを変更',
        'subheading' => '新しいパスワードを設定してください',
        'submit' => 'パスワードを変更',
        'success' => 'パスワード変更完了',
        'success_message' => 'パスワードが正常に変更されました',
        'error' => 'エラー',
        'user_not_found' => 'ユーザーが見つかりません',
        'current_password_incorrect' => '現在のパスワードが正しくありません',
        'fields' => [
            'current_password' => '現在のパスワード',
            'new_password' => '新しいパスワード',
            'password_confirmation' => 'パスワード確認',
        ],
    ],
    'password_expired' => [
        'title' => 'パスワード期限切れ',
        'heading' => 'パスワードの更新が必要です',
        'subheading' => 'パスワードの有効期限が切れています。新しいパスワードを設定してください',
        'submit' => 'パスワードを更新',
        'success' => 'パスワード更新完了',
        'success_message' => 'パスワードが正常に更新されました。再度ログインしてください',
    ],
];