# Google reCAPTCHA v3 in Laravel

This library provides support for Google reCAPTCHA v3 in Laravel. This library makes it easy to add reCAPTCHA to your Laravel application to protect against spam and bots.

[![Latest Stable Version](https://poser.pugx.org/nguyendachuy/laravel-recaptcha3/v)](//packagist.org/packages/nguyendachuy/laravel-recaptcha3) [![Total Downloads](https://poser.pugx.org/nguyendachuy/laravel-recaptcha3/downloads)](//packagist.org/packages/nguyendachuy/laravel-recaptcha3) [![Latest Unstable Version](https://poser.pugx.org/nguyendachuy/laravel-recaptcha3/v/unstable)](//packagist.org/packages/nguyendachuy/laravel-recaptcha3) [![License](https://poser.pugx.org/nguyendachuy/laravel-recaptcha3/license)](//packagist.org/packages/nguyendachuy/laravel-recaptcha3)

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Blade Directives](#blade-directive)
  - [Form Integration](#form-integration)
  - [Validation](#validation)
  - [Using the Facade](#using-the-facade)
- [Advanced Features](#advanced-features)
  - [Auto Token Refresh](#auto-token-refresh)
  - [Error Handling](#error-handling)
  - [Custom Attributes](#custom-attributes)
  - [Action-specific Validation](#action-specific-validation)
- [Changelog](#changelog)
- [Credits](#credits)
- [License](#license)

## Installation

You can install the package via composer:

```bash
composer require nguyendachuy/laravel-recaptcha3
```

You can publish config file with:

```bash
php artisan vendor:publish --tag="recaptcha-config"
```

## This is the contents of the published config file:
```php
return [
    /*
    |--------------------------------------------------------------------------
    | The reCAPTCHA site key provided by Google
    |--------------------------------------------------------------------------
    |
    | Here you can set the sitekey
    */

    'sitekey' => env('GOOGLE_CAPTCHA_SITEKEY', null),

    /*
    |--------------------------------------------------------------------------
    | The reCAPTCHA secret key provided by Google
    |--------------------------------------------------------------------------
    |
    | Here you can set the secet
    */

    'secret' => env('GOOGLE_CAPTCHA_SECRET', null)
];
```
## References

Google reCAPTCHA documentation: https://developers.google.com/recaptcha/docs/v3

## Usage

### Using the Facade

You can also use the `Recaptcha` facade directly in your controllers or other classes:

```php
use NguyenHuy\Recaptcha\Facades\Recaptcha;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Verify the token
        $token = $request->input('g-recaptcha-response');
        $ip = $request->ip();
        $action = 'contact_form';
        $minScore = 0.5;
        
        $result = Recaptcha::verify($token, $ip, [$action, $minScore]);
        
        if (!$result) {
            return back()->withErrors(['captcha' => 'reCAPTCHA verification failed']);
        }
        
        // Process form submission
        // ...
    }
}
```

### Blade directive

This directive imports the recaptcha JavaScript library and configures it with your site key.
```php
<body>
    {{-- your app --}}

    {{-- Default action is "form" --}}
    @recaptchaJs

    {{-- or custom action --}}
    @recaptchaJs('form')
</body>
```

Use on the form

```php
<form>
    {{-- your input --}}

    {{-- Default name is "g-recaptcha-response" --}}
    @recaptchaInput

    {{-- or custom name --}}
    @recaptchaInput('custom-name')
    
    {{-- with custom attributes and action --}}
    @php
        $attributes = ['data-form' => 'contact'];
        $action = 'contact_form';
        $name = 'g-recaptcha-response';
    @endphp
    @recaptchaInput($name)
</form>
```

Use on the validator

```php
$request->validate([
    'g-recaptcha-response' => 'captcha'
]);
```

Optimizing Views

```php
php artisan view:clear
```

## Credits

- [NguyenDacHuy](https://github.com/nguyendachuy)
- [All Contributors](../../contributors)

## Advanced Features

### Auto Token Refresh
reCAPTCHA tokens expire after 2 minutes. This library now automatically refreshes tokens every 90 seconds to ensure your forms always have valid tokens.

### Error Handling
Improved error handling with detailed logging when verification fails, making it easier to debug issues.

### Custom Attributes
You can now add custom HTML attributes to the reCAPTCHA input field:

```php
$attributes = ['data-form' => 'contact', 'class' => 'recaptcha-input'];
$captcha = new \NguyenHuy\Recaptcha\CaptchaV3();
$display = $captcha->display('g-recaptcha-response', $attributes);
```

### Action-specific Validation
You can specify different actions for different forms and validate them accordingly:

```php
// In your form
@php
    $action = 'contact_form';
@endphp
@recaptchaInput('g-recaptcha-response')

// In your controller
$request->validate([
    'g-recaptcha-response' => 'captcha:contact_form,0.7'
]);
```

## Changelog

### v1.1.0 (2025-05-26)

- Added auto token refresh to prevent token expiration
- Improved error handling with detailed logging
- Added support for custom HTML attributes
- Fixed Facade binding issue
- Added support for action-specific validation
- Enhanced security with proper input sanitization

### v1.0.0 (2023)

- Initial release

## Support

Please feel free to contact me if you find any bug or create an issue for that!.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
