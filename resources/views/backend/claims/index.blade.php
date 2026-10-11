@extends('adminlte::page')

@section('metaTitle', 'Solicitudes de vínculo')

@section('content_header')
  <h1>Solicitudes de vínculo
    @if ($pendingCount > 0)
      <span class="badge badge-warning">{{ $pendingCount }} pendientes</span>
    @endif
  </h1>
@stop

@php
  $statusBadges = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'];
  $statusLabels = \App\Models\EntityClaimRequest::statusLabels();
  $relationships = config('entity_management.claims.relationships');
@endphp

@section('content')
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card card-outline card-primary shadow-sm">
    <div class="card-body">
      <form method="GET" action="{{ route('backend.claims.index') }}" class="form-inline mb-3">
        <label for="status" class="mr-2 small text-muted">Estado</label>
        <select id="status" name="status" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
          <option value="all" @selected($status === 'all')>Todas</option>
          @foreach ($statusLabels as $value => $label)
            <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
          @endforeach
        </select>
      </form>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="thead-light">
            <tr>
              <th>Fecha</th>
              <th>Usuario</th>
              <th>Ficha</th>
              <th>Relación</th>
              <th>Estado</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($requests as $claim)
              <tr>
                <td>{{ $claim->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ $claim->user?->name ?? 'Usuario eliminado' }}<div class="small text-muted">{{ $claim->user?->email }}</div></td>
                <td>
                  <span class="badge badge-light">{{ $claim->entityConfig()['label'] ?? 'Ficha' }}</span>
                  {{ $claim->entityName() ?? 'Ficha #'.$claim->entity_id.' (no disponible)' }}
                </td>
                <td>{{ $relationships[$claim->relationship] ?? $claim->relationship }}</td>
                <td><span class="badge badge-{{ $statusBadges[$claim->status] ?? 'secondary' }}">{{ $statusLabels[$claim->status] ?? $claim->status }}</span></td>
                <td class="text-right">
                  <a href="{{ route('backend.claims.show', $claim) }}" class="btn btn-sm {{ $claim->isPending() ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $claim->isPending() ? 'Revisar' : 'Ver' }}
                  </a>
                </td>
              </tr>
            @empty
              <tr><td colspan="6" class="text-center text-muted py-4">No hay solicitudes para este filtro.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="card-footer bg-white px-0 pb-0 pt-3">{{ $requests->links() }}</div>
    </div>
  </div>
@stop
