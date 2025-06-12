<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }

        .content {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .credentials {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            font-size: 14px;
            color: #6c757d;
        }

        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
<div class="header">
    <h1>{{ config('app.name') }}</h1>
</div>

<div class="content">
    <h2>{{ $message_ }}</h2>

    <p>{{ $user->name ? __('password-notification.greeting', ['name' => $user->name]) : __('password-notification.guest_greeting') }}</p>

    <p>{{ __('password-notification.message', ['message' => $message_]) }}</p>

    <div class="credentials">
        <strong>{{ __('password-notification.credentials_title') }}</strong><br>
        <strong>{{ __('password-notification.url_label') }}</strong> <a href="{{ $loginUrl }}">{{ $loginUrl }}</a><br>
        <strong>{{ __('password-notification.username_label') }}</strong> {{ $username }}<br>
        <strong>{{ __('password-notification.password_label') }}</strong> <code>{{ $password }}</code>
    </div>

    <a href="{{ $loginUrl }}" class="button">{{ __('password-notification.login_button') }}</a>

    <div class="footer">
        <p>
            <strong>{{ __('password-notification.important') }}</strong> {{ __('password-notification.security_notice') }}
        </p>
        <p>{{ __('password-notification.disclaimer') }}</p>
    </div>
</div>
</body>
</html>