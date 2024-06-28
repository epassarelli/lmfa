@extends('adminlte::page')

@section('metaTitle', 'Dashboard')

@section('content_header')
  <h1>Dashboard</h1>
@stop

@section('content')
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">Dashboard</div>

          <div class="card-body">
            <h1>Bienvenid@, {{ $user->name }}!</h1>
            <p>Usted tiene los siguientes roles:</p>
            <ul>
              @foreach ($roles as $role)
                <li>{{ $role }}</li>
              @endforeach
            </ul>
            <p>Y los siguientes permisos:</p>
            <ul>
              @foreach ($permissions as $permission)
                <li>{{ $permission }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
