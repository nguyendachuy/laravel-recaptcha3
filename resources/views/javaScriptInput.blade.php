@php
    $captcha = new \NguyenHuy\Recaptcha\CaptchaV3();
    $display = $captcha->display($name);
@endphp
@if (!is_null($display))
    {!! $display['input'] !!}

    @push('captchaScript')
    <script> {!! $display['script'] !!} </script>
    @endpush
@endif