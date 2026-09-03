@extends('adminlte::page')

@section('title', 'Peñas')

@section('content_header')
  <h1>Gestión de Peñas</h1>
@stop

@section('content')
  <div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
      <h3 class="card-title">Directorio evergreen</h3>
      <div class="card-tools"><a class="btn btn-success btn-sm" href="{{ route('backend.penia-profiles.create') }}"><i class="fas fa-plus mr-1"></i> Crear Peña</a></div>
    </div>
    <div class="card-body">
      <form class="row mb-3" method="GET" action="{{ route('backend.penia-profiles.index') }}">
        <div class="col-md-4 mb-2"><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre"></div>
        <div class="col-md-2 mb-2"><select class="form-control" name="status"><option value="">Estado</option>@foreach(['draft', 'approved', 'published', 'archived'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
        <div class="col-md-2 mb-2"><select class="form-control" name="province_id"><option value="">Provincia</option>@foreach($provincias as $province)<option value="{{ $province->id }}" @selected((int) request('province_id') === $province->id)>{{ $province->nombre }}</option>@endforeach</select></div>
        <div class="col-md-2 mb-2"><select class="form-control" name="venue_type"><option value="">Tipo</option>@foreach(['penia' => 'Peña', 'centro_cultural' => 'Centro cultural', 'gastronomico_cultural' => 'Gastronómico-cultural', 'otro' => 'Otro'] as $value => $label)<option value="{{ $value }}" @selected(request('venue_type') === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-2 mb-2"><select class="form-control" name="verification"><option value="">Verificación</option>@foreach(['pending', 'verified', 'outdated'] as $status)<option value="{{ $status }}" @selected(request('verification') === $status)>{{ $status }}</option>@endforeach</select></div>
        <div class="col-md-4 mb-2"><select class="form-control" name="quality"><option value="">Faltantes de calidad</option><option value="missing_verification" @selected(request('quality') === 'missing_verification')>Verificación incompleta</option><option value="missing_contact" @selected(request('quality') === 'missing_contact')>Sin contacto</option><option value="missing_sources" @selected(request('quality') === 'missing_sources')>Sin fuentes</option></select></div>
        <div class="col-md-8 mb-2 text-md-right"><button class="btn btn-primary">Filtrar</button><a class="btn btn-default" href="{{ route('backend.penia-profiles.index') }}">Limpiar</a></div>
      </form>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="thead-light"><tr><th>Peña</th><th>Territorio</th><th>Verificación</th><th>Estado</th><th>Eventos</th><th class="text-right">Acciones</th></tr></thead>
          <tbody>
            @forelse($profiles as $profile)
              <tr>
                <td>{{ $profile->title }}</td>
                <td>{{ $profile->provincia?->nombre ?? '-' }}{{ $profile->city ? ' · '.$profile->city : '' }}</td>
                <td>{{ $profile->verification_status }}<br><small>{{ optional($profile->last_verified_at)->format('d/m/Y') ?? '-' }}</small></td>
                <td>{{ $profile->editorial_status }}</td>
                <td>{{ $profile->events_count }}</td>
                <td class="text-right" style="white-space: nowrap;">
                  @can('view', $profile)<a class="btn btn-info btn-sm" href="{{ route('backend.penia-profiles.preview', $profile) }}" title="Vista previa"><i class="fas fa-eye"></i></a>@endcan
                  @can('update', $profile)
                    <a class="btn btn-warning btn-sm" href="{{ route('backend.penia-profiles.edit', $profile) }}" title="Editar"><i class="fas fa-edit"></i></a>
                    @if($profile->editorial_status === 'published')
                      <form class="d-inline" method="POST" action="{{ route('backend.penia-profiles.unpublish', $profile) }}">@csrf<button class="btn btn-secondary btn-sm" title="Despublicar"><i class="fas fa-eye-slash"></i></button></form>
                    @elseif($profile->editorial_status !== 'archived')
                      <form class="d-inline" method="POST" action="{{ route('backend.penia-profiles.publish', $profile) }}">@csrf<button class="btn btn-success btn-sm" title="Publicar"><i class="fas fa-check"></i></button></form>
                    @endif
                  @endcan
                  @can('delete', $profile)<form class="d-inline" method="POST" action="{{ route('backend.penia-profiles.destroy', $profile) }}">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" title="Archivar" onclick="return confirm('¿Archivar esta Peña?')"><i class="fas fa-archive"></i></button></form>@endcan
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted">No se encontraron Peñas para los filtros seleccionados.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
      <div class="pt-3">{{ $profiles->links() }}</div>
    </div>
  </div>
@stop
