@extends('adminlte::page')

@section('metaTitle', 'Solicitud de vínculo')

@section('content_header')
  <h1>Solicitud de vínculo #{{ $claim->id }}</h1>
@stop

@php
  $statusLabels = \App\Models\EntityClaimRequest::statusLabels();
  $relationships = config('entity_management.claims.relationships');
  $config = $claim->entityConfig();
@endphp

@section('content')
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
    <div class="alert alert-danger">
      <ul class="mb-0">
        @foreach ($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="row">
    <div class="col-lg-7">
      <div class="card card-outline card-primary shadow-sm">
        <div class="card-header"><h3 class="card-title">Detalle</h3></div>
        <div class="card-body">
          <dl class="row mb-0">
            <dt class="col-sm-4">Estado</dt>
            <dd class="col-sm-8">{{ $statusLabels[$claim->status] ?? $claim->status }}</dd>

            <dt class="col-sm-4">Usuario</dt>
            <dd class="col-sm-8">
              {{ $claim->user?->name ?? 'Usuario eliminado' }}<br>
              <span class="text-muted">{{ $claim->user?->email }}</span>
              @if ($claim->user?->phone) <br><span class="text-muted">Tel. cuenta: {{ $claim->user->phone }}</span> @endif
            </dd>

            <dt class="col-sm-4">Ficha pedida</dt>
            <dd class="col-sm-8">
              <span class="badge badge-light">{{ $config['label'] ?? 'Ficha' }}</span>
              @if ($claim->entityName() && $config)
                <a href="{{ route($config['edit_route'], $claim->entity_id) }}" target="_blank" rel="noopener">{{ $claim->entityName() }}</a>
              @elseif ($claim->isCreation())
                <span class="badge badge-info">Alta de ficha</span> <strong>{{ $claim->proposed_name }}</strong>
                @if (! empty($claim->proposed_data['mes_id']))
                  <div class="small text-muted">Mes propuesto (id): {{ $claim->proposed_data['mes_id'] }}</div>
                @endif
              @else
                <span class="text-muted">Ficha #{{ $claim->entity_id }} (no disponible)</span>
              @endif
            </dd>

            @if ($claim->evidence_url)
              <dt class="col-sm-4">Link de respaldo</dt>
              <dd class="col-sm-8"><a href="{{ $claim->evidence_url }}" target="_blank" rel="noopener nofollow">{{ $claim->evidence_url }}</a></dd>
            @endif

            <dt class="col-sm-4">Relación declarada</dt>
            <dd class="col-sm-8">{{ $relationships[$claim->relationship] ?? $claim->relationship }}</dd>

            <dt class="col-sm-4">Teléfono</dt>
            <dd class="col-sm-8">{{ $claim->contact_phone ?: '-' }}</dd>

            <dt class="col-sm-4">Mensaje</dt>
            <dd class="col-sm-8" style="white-space: pre-line;">{{ $claim->message }}</dd>

            <dt class="col-sm-4">Enviada</dt>
            <dd class="col-sm-8">{{ $claim->created_at->format('d/m/Y H:i') }}</dd>

            @if (! $claim->isPending())
              <dt class="col-sm-4">Resuelta</dt>
              <dd class="col-sm-8">
                {{ optional($claim->reviewed_at)->format('d/m/Y H:i') }}
                @if ($claim->reviewer) por {{ $claim->reviewer->name }} @endif
                @if ($claim->review_notes) <div class="mt-1"><strong>Nota:</strong> {{ $claim->review_notes }}</div> @endif
                @if ($claim->organization)
                  <div class="mt-1">
                    Organización: <a href="{{ route('backend.organizations.edit', $claim->organization) }}">{{ $claim->organization->name }}</a>
                  </div>
                @endif
              </dd>
            @endif
          </dl>
        </div>
      </div>

      <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header"><h3 class="card-title">Organizaciones actuales de la ficha</h3></div>
        <div class="card-body">
          @forelse ($organizations as $organization)
            <div>
              <a href="{{ route('backend.organizations.edit', $organization) }}">{{ $organization->name }}</a>
              <span class="badge badge-{{ $organization->status === 'active' ? 'success' : 'secondary' }}">{{ $organization->status === 'active' ? 'Activa' : 'Inactiva' }}</span>
              @if ($organization->pivot->is_primary) <span class="badge badge-primary">Principal</span> @endif
            </div>
          @empty
            <p class="text-muted mb-0">La ficha no tiene organización: al aprobar se crea una y el usuario queda como miembro.</p>
          @endforelse
        </div>
      </div>
    </div>

    @if ($claim->isPending())
      <div class="col-lg-5">
        <div class="card card-outline card-success shadow-sm">
          <div class="card-header"><h3 class="card-title">Aprobar</h3></div>
          <form action="{{ route('backend.claims.approve', $claim) }}" method="POST">
            @csrf
            <div class="card-body">
              <div class="form-group">
                <label for="role">Rol del usuario</label>
                <select name="role" id="role" class="form-control">
                  @foreach (config('entity_management.member_roles') as $value => $label)
                    <option value="{{ $value }}" @selected($defaultRole === $value)>{{ $label }}</option>
                  @endforeach
                </select>
                <small class="text-muted">Sugerido según si la ficha ya tiene un responsable activo.</small>
              </div>
              @if ($claim->isCreation() && ! $claim->entity_id)
                <div class="form-group">
                  <label for="existing_entity_id">¿Es un duplicado? Vincular a una ficha existente</label>
                  <select name="existing_entity_id" id="existing_entity_id" class="form-control">
                    <option value="">No: crear la ficha nueva (sin publicar)</option>
                    @foreach ($similar as $candidate)
                      <option value="{{ $candidate['id'] }}">{{ $candidate['name'] }} (#{{ $candidate['id'] }})</option>
                    @endforeach
                  </select>
                  <small class="text-muted">
                    @if ($similar->isEmpty()) No se encontraron fichas con nombre parecido. @else Fichas con nombre parecido; si es una de ellas el usuario queda vinculado a esa. @endif
                    La ficha nueva se crea solo con el nombre, sin publicar; el usuario la completa y queda en revisión.
                  </small>
                </div>
              @endif
              <div class="form-group mb-0">
                <label for="approve_notes">Nota (opcional, la ve el usuario)</label>
                <textarea name="review_notes" id="approve_notes" rows="2" class="form-control" maxlength="1000"></textarea>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-success"><i class="fas fa-check mr-1"></i> Aprobar</button>
            </div>
          </form>
        </div>

        <div class="card card-outline card-danger shadow-sm">
          <div class="card-header"><h3 class="card-title">Rechazar</h3></div>
          <form action="{{ route('backend.claims.reject', $claim) }}" method="POST">
            @csrf
            <div class="card-body">
              <div class="form-group mb-0">
                <label for="reject_notes">Motivo (lo ve el usuario)</label>
                <textarea name="review_notes" id="reject_notes" rows="3" class="form-control" minlength="3" maxlength="1000" required></textarea>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-danger"><i class="fas fa-times mr-1"></i> Rechazar</button>
            </div>
          </form>
        </div>
      </div>
    @endif
  </div>

  <a href="{{ route('backend.claims.index') }}" class="btn btn-outline-secondary">Volver al listado</a>
@stop
