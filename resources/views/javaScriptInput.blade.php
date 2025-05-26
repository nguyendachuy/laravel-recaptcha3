@php
    $captcha = new \NguyenHuy\Recaptcha\CaptchaV3();
    $attributes = isset($attributes) ? $attributes : [];
    $action = isset($action) ? $action : null;
    $display = $captcha->display($name, $attributes, $action);
@endphp
@if (!is_null($display))
    {!! $display['input'] !!}

    @push('captchaScript')
    <script> {!! $display['script'] !!} </script>
    @endpush
@endif