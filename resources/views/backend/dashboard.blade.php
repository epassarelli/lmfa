@extends('adminlte::page')

@section('metaTitle', 'Dashboard')

@section('content_header')
  <h1>Dashboard</h1>
@stop

@section('content')

  <section class="content">
    <div class="container-fluid">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-lg-12">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $contadores['contributions'] }}</h3>
                    <p>Publicaciones Pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <a href="{{ route('backend.contributions.index') }}" class="small-box-footer">
                    Revisar publicaciones <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
      </div>
      <div class="row">

        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{ $contadores['news'] ?? 0 }}</h3>
              <p>Noticias / Actualidad</p>
            </div>
            <div class="icon"><i class="fas fa-newspaper"></i></div>
            <a href="{{ route('backend.news.index') }}" class="small-box-footer">Ver todas <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{ $contadores['interpretes'] }}</h3>
              <p>Intérpretes</p>
            </div>
            <div class="icon"><i class="fas fa-microphone"></i></div>
            <a href="{{ route('backend.interpretes.index') }}" class="small-box-footer">Ver todos <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>{{ $contadores['usuarios'] }}</h3>
              <p>Usuarios</p>
            </div>
            <div class="icon"><i class="fas fa-users-cog"></i></div>
            <a href="{{ route('users.index') }}" class="small-box-footer">Administrar <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3>{{ $contadores['canciones'] }}</h3>
              <p>Canciones</p>
            </div>
            <div class="icon"><i class="fas fa-music"></i></div>
            <a href="{{ route('backend.canciones.index') }}" class="small-box-footer">Ver todas <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-teal">
            <div class="inner">
              <h3>{{ $contadores['discos'] }}</h3>
              <p>Discos / Álbumes</p>
            </div>
            <div class="icon"><i class="fas fa-compact-disc"></i></div>
            <a href="{{ route('backend.discos.index') }}" class="small-box-footer">Ver todos <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-orange">
            <div class="inner">
              <h3>{{ $contadores['festivales'] }}</h3>
              <p>Festivales</p>
            </div>
            <div class="icon"><i class="fas fa-map-marked-alt text-white"></i></div>
            <a href="{{ route('backend.festivales.index') }}" class="small-box-footer text-white">Ver todos <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-maroon">
            <div class="inner">
              <h3>{{ $contadores['comidas'] }}</h3>
              <p>Comidas Típicas</p>
            </div>
            <div class="icon"><i class="fas fa-utensils"></i></div>
            <a href="{{ route('backend.comidas.index') }}" class="small-box-footer">Ver todas <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-primary">
            <div class="inner">
              <h3>{{ $contadores['events'] }}</h3>
              <p>Eventos / Cartelera</p>
            </div>
            <div class="icon"><i class="fas fa-calendar-check"></i></div>
            <a href="{{ route('backend.events.index') }}" class="small-box-footer">Ver cartelera <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->

      </div>
      <!-- /.row -->

    </div><!-- /.container-fluid -->
  </section>
  {{-- <div class="container">
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
  </div> --}}

@endsection
