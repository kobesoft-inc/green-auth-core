<?php

return [
    'login' => [
        'rate_limit_exceeded' => 'Rate limit exceeded',
        'rate_limit_message' => 'Please try again in :seconds seconds',
        'account_suspended' => 'Account suspended',
        'account_suspended_message' => 'This account is suspended and cannot log in',
        'invalid_credentials' => 'Invalid credentials',
        'title' => 'Login',
        'heading' => 'Sign in to your account',
        'submit' => 'Sign in',
        'remember_me' => 'Remember me',
        'fields' => [
            'password' => 'Password',
            'username_or_email' => 'Username or Email',
            'username' => 'Username',
            'email' => 'Email',
            'placeholders' => [
                'username_or_email' => 'Enter username or email',
                'username' => 'Enter username',
                'email' => 'Enter email address',
            ],
        ],
    ],
    'change_password' => [
        'title' => 'Change Password',
        'heading' => 'Change Password',
        'subheading' => 'Please set a new password for security',
        'submit' => 'Change Password',
        'success' => 'Password Changed',
        'success_message' => 'Password has been changed successfully',
        'error' => 'Error',
        'user_not_found' => 'User not found',
        'current_password_incorrect' => 'Current password is incorrect',
        'fields' => [
            'current_password' => 'Current Password',
            'new_password' => 'New Password',
            'password_confirmation' => 'Confirm Password',
        ],
    ],
    'password_expired' => [
        'title' => 'Password Expired',
        'heading' => 'Password Update Required',
        'subheading' => 'Your password has expired. Please set a new password to continue',
        'submit' => 'Update Password',
        'success' => 'Password Updated',
        'success_message' => 'Password has been updated successfully. Please login again',
    ],
];