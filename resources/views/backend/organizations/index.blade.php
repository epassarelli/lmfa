@extends('adminlte::page')

@section('metaTitle', 'Organizaciones')

@section('content_header')
  <h1>Organizaciones</h1>
@stop

@section('content')
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
      <h3 class="card-title">Listado de organizaciones</h3>
      <div class="card-tools">
        <a href="{{ route('backend.organizations.create') }}" class="btn btn-success btn-sm">
          <i class="fas fa-plus mr-1"></i> Nueva organización
        </a>
      </div>
    </div>
    <div class="card-body">
      <x-admin-listing-toolbar
        :action="route('backend.organizations.index')"
        search-placeholder="Buscar por nombre, slug o email"
        :sort-options="[
          'id' => 'Alta',
          'name' => 'Nombre',
          'type' => 'Tipo',
          'status' => 'Estado',
          'entity_links_count' => 'Fichas vinculadas',
          'active_members_count' => 'Miembros activos',
        ]"
      >
        <div class="col-md-2">
          <label for="type" class="small text-muted mb-1">Tipo</label>
          <select id="type" name="type" class="form-control">
            <option value="">Todos</option>
            @foreach (config('entity_management.organization_types') as $value => $label)
              <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2">
          <label for="status" class="small text-muted mb-1">Estado</label>
          <select id="status" name="status" class="form-control">
            <option value="">Todos</option>
            <option value="active" @selected(request('status') === 'active')>Activa</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactiva</option>
          </select>
        </div>
        <div class="col-md-2">
          <div class="custom-control custom-checkbox mt-4">
            <input type="checkbox" class="custom-control-input" id="verified" name="verified" value="1" @checked(request('verified') === '1')>
            <label class="custom-control-label" for="verified">Verificadas</label>
          </div>
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="contactable" name="contactable" value="1" @checked(request('contactable') === '1')>
            <label class="custom-control-label" for="contactable">Contactables</label>
          </div>
        </div>
      </x-admin-listing-toolbar>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th>Nombre</th>
              <th>Tipo</th>
              <th>Email</th>
              <th>Estado</th>
              <th class="text-center">Verificada</th>
              <th class="text-center">Contactable</th>
              <th class="text-center">Fichas</th>
              <th class="text-center">Miembros activos</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($organizations as $organization)
              @php
                $contactable = $organization->status === 'active'
                  && $organization->contact_consent_at
                  && ! $organization->unsubscribed_at;
              @endphp
              <tr>
                <td>
                  <a href="{{ route('backend.organizations.edit', $organization) }}">{{ $organization->name }}</a>
                  <div class="small text-muted"><code>{{ $organization->slug }}</code></div>
                </td>
                <td>{{ config('entity_management.organization_types.'.$organization->type, $organization->type) }}</td>
                <td>{{ $organization->email ?: '-' }}</td>
                <td>
                  <span class="badge badge-{{ $organization->status === 'active' ? 'success' : 'secondary' }}">
                    {{ $organization->status === 'active' ? 'Activa' : 'Inactiva' }}
                  </span>
                </td>
                <td class="text-center">{!! $organization->is_verified ? '<i class="fas fa-check-circle text-success"></i>' : '<span class="text-muted">-</span>' !!}</td>
                <td class="text-center">{!! $contactable ? '<i class="fas fa-envelope text-primary"></i>' : '<span class="text-muted">-</span>' !!}</td>
                <td class="text-center">{{ $organization->entity_links_count }}</td>
                <td class="text-center">{{ $organization->active_members_count }}</td>
                <td class="text-right" style="white-space: nowrap;">
                  <a href="{{ route('backend.organizations.edit', $organization) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i>
                  </a>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="9" class="text-center text-muted py-4">No hay organizaciones para los filtros elegidos.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="card-footer bg-white px-0 pb-0 pt-3">
        {{ $organizations->links() }}
      </div>
    </div>
  </div>
@stop
