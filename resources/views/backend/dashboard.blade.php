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

        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{ $contadores['noticias'] }}</h3>

              <p>Noticias</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('backend.noticias.index') }}" class="small-box-footer">Más info <i
                class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->

        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{ $contadores['interpretes'] }}</h3>

              <p>Interpretes</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{ route('backend.interpretes.index') }}" class="small-box-footer">Más info <i
                class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->

        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>{{ $contadores['usuarios'] }}</h3>

              <p>Usuarios</p>
            </div>
            <div class="icon">
              <i class="ion ion-person-add"></i>
            </div>
            <a href="{{ route('users.index') }}" class="small-box-footer">Más info <i
                class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->

        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-danger">
            <div class="inner">
              <h3>{{ $contadores['canciones'] }}</h3>

              <p>Canciones</p>
            </div>
            <div class="icon">
              <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('backend.canciones.index') }}" class="small-box-footer">Más info <i
                class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->

      </div>
      <!-- /.row -->


      <!-- Small boxes (Stat box) -->
      <div class="row">

        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{ $contadores['discos'] }}</h3>

              <p>Discos</p>
            </div>
            <div class="icon">
              <i class="ion ion-stats-bars"></i>
            </div>
            <a href="{{ route('backend.discos.index') }}" class="small-box-footer">Más info <i
                class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->

        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>{{ $contadores['festivales'] }}</h3>

              <p>Festivales</p>
            </div>
            <div class="icon">
              <i class="ion ion-person-add"></i>
            </div>
            <a href="{{ route('backend.festivales.index') }}" class="small-box-footer">Más info <i
                class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->

        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-danger">
            <div class="inner">
              <h3>{{ $contadores['comidas'] }}</h3>

              <p>Comidas</p>
            </div>
            <div class="icon">
              <i class="ion ion-pie-graph"></i>
            </div>
            <a href="{{ route('backend.comidas.index') }}" class="small-box-footer">Más info <i
                class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <!-- ./col -->

        <div class="col-lg-3 col-6">
          <!-- small box -->
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{ $contadores['shows'] }}</h3>

              <p>Shows</p>
            </div>
            <div class="icon">
              <i class="ion ion-bag"></i>
            </div>
            <a href="{{ route('backend.shows.index') }}" class="small-box-footer">Más info <i
                class="fas fa-arrow-circle-right"></i></a>
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
