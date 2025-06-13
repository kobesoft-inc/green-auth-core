# {{ config('app.name') }}

## {{ $message_ }}

{{ $user->name ? __('password-notification.greeting', ['name' => $user->name]) : __('password-notification.guest_greeting') }}

{{ __('password-notification.message', ['message' => $message_]) }}

### {{ __('password-notification.credentials_title') }}

@if($loginUrl)
**{{ __('password-notification.url_label') }}** {{ $loginUrl }}
@endif
**{{ __('password-notification.username_label') }}** {{ $username }}  
**{{ __('password-notification.password_label') }}** `{{ $password }}`

@if($loginUrl)
[{{ __('password-notification.login_button') }}]({{ $loginUrl }})
@endif

---

**{{ __('password-notification.important') }}** {{ __('password-notification.security_notice') }}

{{ __('password-notification.disclaimer') }}