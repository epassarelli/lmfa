@extends('adminlte::page')

@section('metaTitle', 'Mi panel')

@section('content_header')
  <h1>Hola, {{ \Illuminate\Support\Str::of($user->name)->before(' ') }}</h1>
@stop

@section('content')

  <section class="content">
    <div class="container-fluid" data-member-panel>

      <p class="text-muted">{{ $user->roleLabel() }} · Desde acá seguís tus fichas, tus solicitudes y lo que cargaste.</p>

      @if ($claims['to_review'] > 0)
        <div class="alert alert-warning" data-member="to-review">
          <i class="fas fa-user-check mr-1"></i>
          Tenés <strong>{{ $claims['to_review'] }}</strong> {{ $claims['to_review'] === 1 ? 'solicitud' : 'solicitudes' }} de otras personas para revisar.
          <a href="{{ route('backend.my-entities.index') }}" class="alert-link">Revisar</a>
        </div>
      @endif

      <div class="row">
        {{-- Mis fichas --}}
        <div class="col-lg-6">
          <div class="card card-outline card-success shadow-sm" data-member="entities">
            <div class="card-header">
              <h3 class="card-title">Mis fichas</h3>
              <div class="card-tools"><a href="{{ route('backend.my-entities.index') }}" class="btn btn-sm btn-outline-success">Ver todas</a></div>
            </div>
            <div class="card-body p-0">
              @if (count($entities))
                <ul class="list-group list-group-flush">
                  @foreach ($entities as $entity)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <a href="{{ $entity['edit_url'] }}">{{ $entity['name'] }}</a>
                      <span class="badge badge-light">{{ $entity['label'] }}</span>
                    </li>
                  @endforeach
                </ul>
              @else
                <div class="p-3">
                  <p class="mb-2">Todavía no administrás ninguna ficha.</p>
                  <p class="small text-muted mb-3">Pedí vincularte a un artista o festival que ya existe, o solicitá el alta de uno nuevo. Un administrador lo revisa.</p>
                  <a href="{{ route('backend.my-entities.index') }}" class="btn btn-success btn-sm">Solicitar una ficha</a>
                </div>
              @endif
            </div>
          </div>
        </div>

        {{-- Solicitudes --}}
        <div class="col-lg-6">
          <div class="card card-outline card-info shadow-sm" data-member="claims">
            <div class="card-header"><h3 class="card-title">Mis solicitudes</h3></div>
            <div class="card-body">
              <div class="row text-center">
                <div class="col-4"><div class="h3 mb-0">{{ $claims['pending'] }}</div><div class="small text-muted">En revisión</div></div>
                <div class="col-4"><div class="h3 mb-0">{{ $claims['approved'] }}</div><div class="small text-muted">Aprobadas</div></div>
                <div class="col-4"><div class="h3 mb-0">{{ $claims['rejected'] }}</div><div class="small text-muted">Rechazadas</div></div>
              </div>
              @if ($claims['pending'] > 0)
                <p class="small text-muted mt-3 mb-0">Te avisamos por email cuando se resuelvan.</p>
              @endif
            </div>
          </div>
        </div>
      </div>

      {{-- Mi contenido --}}
      @if (count($content))
        <div class="card card-outline card-secondary shadow-sm" data-member="content">
          <div class="card-header"><h3 class="card-title">Lo que cargaste</h3></div>
          <div class="card-body p-0">
            <table class="table table-sm mb-0">
              <thead><tr><th>Tipo</th><th class="text-right">Publicado</th><th class="text-right">En revisión</th></tr></thead>
              <tbody>
                @foreach ($content as $row)
                  <tr><td>{{ $row['label'] }}</td><td class="text-right">{{ $row['published'] }}</td><td class="text-right">{{ $row['review'] }}</td></tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @endif

      <p class="small text-muted"><a href="{{ route('backend.account.edit') }}">Mis datos</a> · cambiá tu nombre, contraseña y suscripción al newsletter.</p>

    </div>
  </section>

@endsection
