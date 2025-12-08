<?php

return [
    'forms' => [
        'labels' => [
            'name' => 'Name',
            'email' => 'Email',
            'username' => 'Username',
            'groups' => 'Groups',
            'roles' => 'Roles',
            'password' => 'Password',
            'auto_generate_password' => 'Auto-generate password',
            'send_email_notification' => 'Send password by email',
            'require_password_change' => 'Require password change on next login',
        ],
        'validation' => [
            'email_or_username_required' => 'Either email or username is required.',
            'email_required_for_notification' => 'Email address is required to send password notification.',
        ],
    ],
    'actions' => [
        'operations' => 'Operations',
        'suspend' => 'Suspend',
        'unsuspend' => 'Unsuspend',
        'suspend_user' => 'Suspend User',
        'unsuspend_user' => 'Unsuspend User',
        'create_user' => 'Create New User',
        'password_change' => 'Change Password',
        'password_reset' => 'Change Password',
        'modals' => [
            'suspend_user' => [
                'heading' => 'Suspend this user?',
                'description' => 'This user will be suspended and unable to log in.',
                'submit' => 'Suspend',
            ],
            'unsuspend_user' => [
                'heading' => 'Unsuspend this user?',
                'description' => 'This user will be unsuspended and able to log in again.',
                'submit' => 'Unsuspend',
            ],
        ],
    ],
    'notifications' => [
        'user_suspended' => 'User suspended',
        'user_suspended_message' => ':name has been suspended.',
        'user_unsuspended' => 'User unsuspended',
        'user_unsuspended_message' => ':name has been unsuspended.',
        'password_reset_complete' => 'Password Reset Complete',
        'password_sent_by_email' => 'New password has been sent by email.',
        'new_password_display' => 'New password: :password',
    ],
    'tables' => [
        'columns' => [
            'name' => 'Name',
            'email' => 'Email',
            'username' => 'Username',
            'groups' => 'Groups',
            'roles' => 'Roles',
            'status' => 'Status',
            'last_login' => 'Last Login',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'suspended_at' => 'Suspended At',
        ],
        'filters' => [
            'groups' => 'Groups',
            'roles' => 'Roles',
            'suspended' => 'Suspended',
            'email_verified' => 'Email Verified',
        ],
    ],
    'models' => [
        'user' => 'User',
        'group' => 'Group',
        'role' => 'Role',
        'admin_user' => 'Administrator',
        'admin_group' => 'Admin Group',
        'admin_role' => 'Admin Role',
    ],
    'emails' => [
        'account_created' => [
            'subject' => 'Account Created',
            'message' => 'A new account has been created for you.',
        ],
        'password_reset' => [
            'subject' => 'Password Reset',
            'message' => 'Your password has been reset.',
        ],
    ],
];
