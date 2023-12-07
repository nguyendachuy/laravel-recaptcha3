<?php

namespace NguyenHuy\Recaptcha;

use Illuminate\Support\Facades\Facade;

class Recaptcha extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-recaptcha';
    }
}
