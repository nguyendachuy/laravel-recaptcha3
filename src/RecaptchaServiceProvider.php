<?php

namespace NguyenHuy\Recaptcha;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class RecaptchaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/recaptcha.php' => config_path('recaptcha.php'),
            ], 'recaptcha-config');
        }
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'recaptcha');

        Blade::directive('recaptchaJs', function ($action) {
            $action = empty($action) ? '"form"' : $action;
            return "<?php echo view('recaptcha::javaScriptInit', [
                        'action' => $action
                      ]); ?>";
        });
        Blade::directive('recaptchaInput', function ($name) {
            $name = empty($name) ? '"g-recaptcha-response"' : $name;
            return "<?php echo view('recaptcha::javaScriptInput',['name' => {$name}]); ?>";
        });
        $this->bootValidator();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/recaptcha.php', 'recaptcha');

        AliasLoader::getInstance()->alias('Recaptcha', Recaptcha::class);

    }

    /**
     * Create captcha validator rule
     */
    public function bootValidator()
    {
        $app = $this->app;

        /**
         * @var Validator $validator
         */
        $validator = $app['validator'];
        $validator->extend('captcha', function ($attribute, $value, $parameters) use ($app) {
            /**
             * @var CaptchaV3 $captcha
             */
            $captcha = new CaptchaV3();
            /**
             * @var Request $request
             */
            $request = $app['request'];

            if (empty($parameters)) {
                $parameters = ['form', '0.6'];
            }

            return $captcha->verify($value, $request->getClientIp(), $parameters);
        });

        $validator->replacer('captcha', function ($message) {
            return $message === 'validation.captcha' ? 'Failed to validate the captcha.' : $message;
        });
    }
}
