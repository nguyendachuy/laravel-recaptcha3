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
        if (empty($token)) {
            if (function_exists('log_message') || method_exists('Log', 'warning')) {
                $logger = function_exists('log_message') ? 'log_message' : 'Log::warning';
                $logger('reCAPTCHA verification failed: Empty token provided');
            }
            return false;
        }

        try {
            $client = new Client;

            $response = $client->request('POST', $this->origin . '/api/siteverify', [
                'form_params' => [
                    'secret'   => $this->secret,
                    'response' => $token,
                    'remoteip' => $clientIp,
                ],
                'timeout' => 5,
            ]);

            $body = json_decode($response->getBody(), true);

            if (!isset($body['success']) || $body['success'] !== true) {
                $errorCodes = isset($body['error-codes']) ? implode(', ', $body['error-codes']) : 'unknown error';
                if (function_exists('log_message') || method_exists('Log', 'warning')) {
                    $logger = function_exists('log_message') ? 'log_message' : 'Log::warning';
                    $logger("reCAPTCHA verification failed: {$errorCodes}");
                }
                return false;
            }

            $action = isset($parameters[0]) ? $parameters[0] : null;
            $minScore = isset($parameters[1]) ? (float)$parameters[1] : 0.5;

            if ($action && (!isset($body['action']) || $action != $body['action'])) {
                if (function_exists('log_message') || method_exists('Log', 'warning')) {
                    $logger = function_exists('log_message') ? 'log_message' : 'Log::warning';
                    $logger("reCAPTCHA action verification failed: Expected '{$action}', got '" . ($body['action'] ?? 'none') . "'");
                }
                return false;
            }

            $score = isset($body['score']) ? $body['score'] : false;
            $result = $score && $score >= $minScore;

            if (!$result && function_exists('log_message') || method_exists('Log', 'warning')) {
                $logger = function_exists('log_message') ? 'log_message' : 'Log::warning';
                $logger("reCAPTCHA score too low: {$score} (minimum: {$minScore})");
            }

            return $result;
        } catch (\Exception $e) {
            if (function_exists('log_message') || method_exists('Log', 'error')) {
                $logger = function_exists('log_message') ? 'log_message' : 'Log::error';
                $logger('reCAPTCHA verification error: ' . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Generate the reCAPTCHA HTML input and script
     *
     * @param string $name Input field name
     * @param array $attributes Additional HTML attributes for the input field
     * @param string $action Custom action name for this specific input
     * @return array|null Array with input and script elements or null if site key is not set
     */
    public function display($name = 'g-recaptcha-response', $attributes = [], $action = null)
    {
        if (!$this->siteKey) {
            return null;
        }

        $fieldId = uniqid($name . '-', false);
        
        // Build HTML attributes string
        $htmlAttributes = '';
        foreach ($attributes as $key => $value) {
            $htmlAttributes .= ' ' . $key . '="' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '"';
        }

        $input = '<input type="hidden" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '" id="' . $fieldId . '"' . $htmlAttributes . '>';

        $actionParam = $action ? "'" . addslashes($action) . "'" : 'null';
        $script = "grecaptcha.ready(function() { refreshRecaptcha('" . $fieldId . "', " . $actionParam . "); });";

        return [
            'input' => $input,
            'script' => $script,
            'field_id' => $fieldId
        ];
    }

    /**
     * Generate the JavaScript needed to initialize reCAPTCHA
     *
     * @param string $defaultAction Default action to use when no specific action is provided
     * @param bool $addErrorHandling Whether to add error handling to the reCAPTCHA execution
     * @return string JavaScript code for reCAPTCHA initialization
     */
    public function initJs($defaultAction = 'form', $addErrorHandling = true)
    {
        $errorHandling = $addErrorHandling ? "
                   .catch(function(error) {
                      console.error('reCAPTCHA error:', error);
                      // Optionally, you can add custom error handling here
                      // For example: document.dispatchEvent(new CustomEvent('recaptchaError', {detail: error}));
                   })" : "";

        $script = "
                var refreshRecaptcha = function (fieldId, action) {
                   if (!action) {
                       action = '" . addslashes($defaultAction) . "';
                   }

                   var field = document.getElementById(fieldId);

                   if (field) {
                      grecaptcha.execute('" . $this->siteKey . "', {action: action})
                      .then(function(token) {
                         field.value = token;
                         // Optionally, you can dispatch an event when token is set
                         // document.dispatchEvent(new CustomEvent('recaptchaTokenSet', {detail: {fieldId: fieldId}}));
                      })" . $errorHandling . ";
                   } else {
                      console.warn('reCAPTCHA field not found:', fieldId);
                   }
               };
               
               // Auto-refresh tokens every 90 seconds (before the 2-minute expiration)
               var recaptchaIntervals = {};
               var setupRecaptchaInterval = function(fieldId, action) {
                   if (recaptchaIntervals[fieldId]) {
                       clearInterval(recaptchaIntervals[fieldId]);
                   }
                   // Refresh every 90 seconds
                   recaptchaIntervals[fieldId] = setInterval(function() {
                       refreshRecaptcha(fieldId, action);
                   }, 90000);
               };
               
               // Override the refreshRecaptcha function to also setup intervals
               var originalRefreshRecaptcha = refreshRecaptcha;
               refreshRecaptcha = function(fieldId, action) {
                   originalRefreshRecaptcha(fieldId, action);
                   setupRecaptchaInterval(fieldId, action);
               };";

        return $script;
    }
    public function api()
    {
        return $this->origin . '/api.js?render=' . $this->siteKey . '&hl=' . app()->getLocale();
    }
}
