@extends('adminlte::page')

@section('metaTitle', 'Editar organización')

@section('content_header')
  <x-backend.form-header mode="edit">Editar organización: {{ $organization->name }}
    @if ($organization->status !== 'active')
      <span class="badge badge-secondary">Inactiva</span>
    @endif
  </x-backend.form-header>
@stop

@section('content')
  @php
    $entityConfigByClass = collect(config('entity_management.entities'))->keyBy('model');
  @endphp

  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <form action="{{ route('backend.organizations.update', $organization) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card card-outline card-primary shadow-sm">
      <div class="card-header"><h3 class="card-title">Datos de la organización</h3></div>
      <div class="card-body">
        @include('backend.organizations._form')
      </div>
      <div class="card-footer">
        <x-backend.form-actions :back="route('backend.organizations.index')" />
      </div>
    </div>
  </form>

  <div class="row">
    {{-- Fichas vinculadas --}}
    <div class="col-lg-6">
      <div class="card card-outline card-info shadow-sm">
        <div class="card-header"><h3 class="card-title">Fichas vinculadas ({{ $organization->entityLinks->count() }})</h3></div>
        <div class="card-body">
          <p class="text-muted small">Los miembros activos <strong>owner</strong> o <strong>editor</strong> pueden editar estas fichas.</p>

          <div class="table-responsive mb-3">
            <table class="table table-sm mb-0">
              <tbody>
                @forelse ($organization->entityLinks as $link)
                  @php
                    $cfg = $entityConfigByClass->get($link->entity_type);
                    $name = $cfg && $link->entity ? $link->entity->{$cfg['name_column']} : null;
                  @endphp
                  <tr>
                    <td>
                      <span class="badge badge-light">{{ $cfg['label'] ?? class_basename($link->entity_type) }}</span>
                      @if ($name && $cfg)
                        <a href="{{ route($cfg['edit_route'], $link->entity_id) }}">{{ $name }}</a>
                      @else
                        <span class="text-muted">Ficha #{{ $link->entity_id }} (no disponible)</span>
                      @endif
                      @if ($link->is_primary)
                        <span class="badge badge-primary">Principal</span>
                      @endif
                    </td>
                    <td class="text-right" style="white-space: nowrap;">
                      @unless ($link->is_primary)
                        <form action="{{ route('backend.organizations.entities.primary', [$organization, $link]) }}" method="POST" class="d-inline">
                          @csrf @method('PATCH')
                          <button class="btn btn-outline-primary btn-xs" title="Marcar como organización principal de la ficha">Principal</button>
                        </form>
                      @endunless
                      <form action="{{ route('backend.organizations.entities.destroy', [$organization, $link]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Quitar este vínculo?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-xs"><i class="fas fa-unlink"></i></button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td class="text-muted">Todavía no hay fichas vinculadas.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <form action="{{ route('backend.organizations.entities.store', $organization) }}" method="POST">
            @csrf
            <div class="form-row">
              <div class="form-group col-md-4">
                <label for="entity_type">Tipo de ficha</label>
                <select name="entity_type" id="entity_type" class="form-control">
                  @foreach (config('entity_management.entities') as $key => $cfg)
                    <option value="{{ $key }}">{{ $cfg['label'] }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-8">
                <label for="entity_id">Buscar ficha</label>
                <select name="entity_id" id="entity_id" class="form-control" required></select>
              </div>
            </div>
            <div class="custom-control custom-checkbox mb-2">
              <input type="checkbox" class="custom-control-input" id="entity_primary" name="is_primary" value="1">
              <label class="custom-control-label" for="entity_primary">Es la organización principal de la ficha</label>
            </div>
            <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-link mr-1"></i> Vincular ficha</button>
          </form>
        </div>
      </div>
    </div>

    {{-- Miembros --}}
    <div class="col-lg-6">
      <div class="card card-outline card-success shadow-sm">
        <div class="card-header"><h3 class="card-title">Miembros ({{ $organization->members->count() }})</h3></div>
        <div class="card-body">
          <p class="text-muted small">Una solicitud <strong>pendiente</strong> no otorga permisos hasta que la pases a <strong>activo</strong>.</p>

          <div class="table-responsive mb-3">
            <table class="table table-sm mb-0">
              <tbody>
                @forelse ($organization->members as $member)
                  <tr>
                    <td>
                      {{ $member->user?->name ?? 'Usuario eliminado' }}
                      <div class="small text-muted">{{ $member->user?->email }}</div>
                    </td>
                    <td class="text-right" style="white-space: nowrap;">
                      <form action="{{ route('backend.organizations.members.update', [$organization, $member]) }}" method="POST" class="form-inline justify-content-end">
                        @csrf @method('PATCH')
                        <select name="role" class="form-control form-control-sm mr-1">
                          @foreach (config('entity_management.member_roles') as $value => $label)
                            <option value="{{ $value }}" @selected($member->role === $value)>{{ $label }}</option>
                          @endforeach
                        </select>
                        <select name="status" class="form-control form-control-sm mr-1">
                          @foreach (config('entity_management.member_statuses') as $value => $label)
                            <option value="{{ $value }}" @selected($member->status === $value)>{{ $label }}</option>
                          @endforeach
                        </select>
                        <button class="btn btn-outline-success btn-sm mr-1" title="Guardar"><i class="fas fa-check"></i></button>
                      </form>
                      <form action="{{ route('backend.organizations.members.destroy', [$organization, $member]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Quitar a este miembro?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm"><i class="fas fa-user-minus"></i></button>
                      </form>
                    </td>
                  </tr>
                @empty
                  <tr><td class="text-muted">Todavía no hay miembros.</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>

          <form action="{{ route('backend.organizations.members.store', $organization) }}" method="POST">
            @csrf
            <div class="form-group">
              <label for="user_id">Buscar usuario (nombre o email)</label>
              <select name="user_id" id="user_id" class="form-control" required></select>
            </div>
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="member_role">Rol</label>
                <select name="role" id="member_role" class="form-control">
                  @foreach (config('entity_management.member_roles') as $value => $label)
                    <option value="{{ $value }}" @selected($value === 'editor')>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-6">
                <label for="member_status">Estado</label>
                <select name="status" id="member_status" class="form-control">
                  @foreach (config('entity_management.member_statuses') as $value => $label)
                    <option value="{{ $value }}" @selected($value === 'active')>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-user-plus mr-1"></i> Agregar miembro</button>
          </form>
        </div>
      </div>
    </div>
  </div>
@stop

@section('js')
  @include('backend.partials.scripts._select2')
  <script>
    window.addEventListener('load', function () {
      const remote = function (selector, url, extra) {
        $(selector).select2({
          theme: 'classic',
          width: '100%',
          placeholder: 'Escribí al menos 2 letras',
          minimumInputLength: 2,
          language: {
            inputTooShort: function () { return 'Escribí al menos 2 letras'; },
            noResults: function () { return 'Sin resultados'; },
            searching: function () { return 'Buscando...'; }
          },
          ajax: {
            url: url,
            dataType: 'json',
            delay: 250,
            data: function (params) { return Object.assign({ q: params.term }, extra ? extra() : {}); }
          }
        });
      };

      remote('#entity_id', @json(route('backend.organization-lookups.entities')), function () {
        return { type: $('#entity_type').val() };
      });
      remote('#user_id', @json(route('backend.organization-lookups.users')));

      $('#entity_type').on('change', function () { $('#entity_id').val(null).trigger('change'); });
    });
  </script>
@stop
