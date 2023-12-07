# Google reCAPTCHA v3 in Laravel
## This library provides support for Google reCAPTCHA v3 in Laravel. This library makes it easy to add reCAPTCHA to your Laravel application to protect against spam and bots.

[![Latest Stable Version](https://poser.pugx.org/nguyendachuy/laravel-recaptcha3/v)](//packagist.org/packages/nguyendachuy/laravel-recaptcha3) [![Total Downloads](https://poser.pugx.org/nguyendachuy/laravel-recaptcha3/downloads)](//packagist.org/packages/nguyendachuy/laravel-recaptcha3) [![Latest Unstable Version](https://poser.pugx.org/nguyendachuy/laravel-recaptcha3/v/unstable)](//packagist.org/packages/nguyendachuy/laravel-recaptcha3) [![License](https://poser.pugx.org/nguyendachuy/laravel-recaptcha3/license)](//packagist.org/packages/nguyendachuy/laravel-recaptcha3)

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

## Blade directive

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

## Please feel free to contact me if you find any bug or create an issue for that!.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
