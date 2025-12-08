<?php

return [
    'name' => 'Name',
    'email' => 'Email Address',
    'username' => 'Username',
    'groups' => 'Groups',
    'roles' => 'Roles',
    'status' => 'Status',
    'last_login' => 'Last Login',
    'created_at' => 'Created At',
    'updated_at' => 'Updated At',
    'auto_generate_password' => 'Auto Generate Password',
    'send_email_notification' => 'Send Email Notification',
    'require_password_change' => 'Require Password Change on Next Login',
    'email_or_username_required' => 'Either email address or username is required.',

    // Actions
    'actions' => [
        'create_user' => 'Create User',
        'operations' => 'Operations',
        'suspend' => 'Suspend',
        'unsuspend' => 'Unsuspend',
        'modals' => [
            'suspend_user' => [
                'heading' => 'Suspend User',
                'description' => 'Are you sure you want to suspend this user?',
                'submit' => 'Suspend',
            ],
            'unsuspend_user' => [
                'heading' => 'Unsuspend User',
                'description' => 'Are you sure you want to unsuspend this user?',
                'submit' => 'Unsuspend',
            ],
        ],
    ],

    // Filters
    'filters' => [
        'groups' => 'Groups',
        'roles' => 'Roles',
        'suspended' => 'Suspended',
        'email_verified' => 'Email Verified',
    ],

    // Validation
    'validation' => [
        'email_or_username_required' => 'Either email address or username is required.',
    ],
];
