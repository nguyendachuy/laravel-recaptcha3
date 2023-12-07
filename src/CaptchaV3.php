<?php

namespace NguyenHuy\Recaptcha;

use GuzzleHttp\Client;
use Illuminate\Support\Arr;

class CaptchaV3
{
    /**
     * @var string
     */
    protected $secret;

    /**
     * @var string
     */
    protected $siteKey;

    /**
     * @var string
     */
    protected $origin;

    /**
     * @var bool
     */
    protected $rendered = false;

    public function __construct()
    {
        $this->secret = config('recaptcha.secret');
        $this->siteKey = config('recaptcha.sitekey');
        $this->origin = 'https://www.google.com/recaptcha';
    }

    /**
     * Verify the given token and return the score.
     * Returns false if token is invalid.
     * Returns the score if the token is valid.
     *
     * @param string $token
     * @param string $clientIp
     * @param array $parameters
     * @return bool|mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify($token, $clientIp, $parameters = [])
    {
        $client = new Client;

        $response = $client->request('POST', $this->origin . '/api/siteverify', [
            'form_params' => [
                'secret'   => $this->secret,
                'response' => $token,
                'remoteip' => $clientIp,
            ],
        ]);

        $body = json_decode($response->getBody(), true);

        if (!isset($body['success']) || $body['success'] !== true) {
            return false;
        }

        $action = $parameters[0];
        $minScore = isset($parameters[1]) ? (float)$parameters[1] : 0.5;

        if ($action && (!isset($body['action']) || $action != $body['action'])) {
            return false;
        }

        $score = isset($body['score']) ? $body['score'] : false;

        return $score && $score >= $minScore;
    }

    /**
     * @param string[] $attributes
     * @param array $options
     * @return string
     */
    public function display($name = 'g-recaptcha-response')
    {
        if (!$this->siteKey) {
            return null;
        }

        $fieldId = uniqid($name . '-', false);

        $input = '<input type="hidden" name="' . $name . '" id="' . $fieldId . '">';

        $script = "grecaptcha.ready(function() { refreshRecaptcha('" . $fieldId . "'); });";

        return [
            'input' => $input,
            'script' => $script
        ];
    }

    public function initJs($action = 'form')
    {
        $script = "
                var refreshRecaptcha = function (fieldId, action) {
                   if (!action) {
                       action = '" . $action . "';
                   }

                   var field = document.getElementById(fieldId);

                   if (field) {
                      grecaptcha.execute('" . $this->siteKey . "', {action: action}).then(function(token) {
                         field.value = token;
                      });
                   }
               };";

        return $script;
    }
    public function api()
    {
        return $this->origin . '/api.js?render=' . $this->siteKey . '&hl=' . app()->getLocale();
    }
}
