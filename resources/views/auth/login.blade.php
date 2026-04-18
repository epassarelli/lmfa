@extends('adminlte::auth.login')

@section('auth_footer')
    @parent

    <hr class="my-3">

    <a href="{{ route('auth.google') }}" class="btn btn-block btn-outline-danger mb-2">
        <i class="fab fa-google mr-2"></i>Ingresar con Google
    </a>

    <a href="{{ route('auth.facebook') }}" class="btn btn-block btn-outline-primary">
        <i class="fab fa-facebook-f mr-2"></i>Ingresar con Facebook
    </a>
@stop
