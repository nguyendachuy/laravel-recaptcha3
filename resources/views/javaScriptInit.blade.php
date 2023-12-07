@php
    $captcha = new \NguyenHuy\Recaptcha\CaptchaV3();
@endphp

<script src="{!! $captcha->api() !!}"></script>

<script>
    {!! $captcha->initJs($action) !!}
</script>
@stack('captchaScript')