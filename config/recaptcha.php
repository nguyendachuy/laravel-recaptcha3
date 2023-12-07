<?php

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
