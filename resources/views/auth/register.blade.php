@extends('adminlte::auth.register')

@section('auth_footer')
    @parent

    <p class="text-center small mt-2 mb-0"><a href="{{ route('publish.guide') }}">¿Cómo funciona publicar en el portal?</a></p>

    @php
        $googleLoginEnabled = config('services.social_login.google_enabled') ?? config('services.social_login.enabled');
        $facebookLoginEnabled = config('services.social_login.facebook_enabled') ?? config('services.social_login.enabled');
    @endphp
    @if ($googleLoginEnabled || $facebookLoginEnabled)
        <hr class="my-3">

        @if ($googleLoginEnabled)
            <a href="{{ route('auth.google') }}" class="btn btn-block btn-outline-danger mb-2">
                <i class="fab fa-google mr-2"></i>Registrarme con Google
            </a>
        @endif

        @if ($facebookLoginEnabled)
            <a href="{{ route('auth.facebook') }}" class="btn btn-block btn-outline-primary">
                <i class="fab fa-facebook-f mr-2"></i>Registrarme con Facebook
            </a>
        @endif
    @endif
@stop
