@extends('adminlte::page')

@section('title', 'Dashboard Publicador')

@section('content_header')
    <h1>Mi Dashboard de Publicaciones</h1>
@endsection

@section('content')

{{-- Tarjetas de resumen --}}
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-info elevation-1"><i class="fas fa-paper-plane"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total solicitudes</span>
                <span class="info-box-number">{{ $totalRequests }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Publicadas</span>
                <span class="info-box-number">{{ $requestsByStatus['done'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-clock"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pendientes</span>
                <span class="info-box-number">{{ $requestsByStatus['pending'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-times-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Con fallos</span>
                <span class="info-box-number">{{ $recentFailures->count() }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Estado por canal --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-share-alt mr-2"></i>Estado por canal</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr><th>Canal</th><th>Estado</th><th class="text-right">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse($targetsByProvider as $provider => $statuses)
                            @foreach($statuses as $row)
                                <tr>
                                    @if($loop->first)
                                        <td rowspan="{{ $statuses->count() }}">
                                            <span class="badge badge-info">{{ $provider }}</span>
                                        </td>
                                    @endif
                                    <td>
                                        @php
                                            $color = match($row->status) {
                                                'success' => 'success',
                                                'failed'  => 'danger',
                                                'pending' => 'warning',
                                                default   => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $color }}">{{ $row->status }}</span>
                                    </td>
                                    <td class="text-right">{{ $row->total }}</td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Sin publicaciones aún</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Próximos eventos --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar-alt mr-2"></i>Próximos eventos</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Evento</th><th>Fecha</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingEvents as $event)
                            <tr>
                                <td>{{ Str::limit($event->title, 35) }}</td>
                                <td>{{ $event->start_at?->format('d/m/Y') }}</td>
                                <td><span class="badge badge-info">{{ $event->editorial_status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Sin eventos próximos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Últimas solicitudes --}}
    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-history mr-2"></i>Últimas solicitudes</h3>
                <a href="{{ route('pasarela.publication-requests.index') }}" class="btn btn-sm btn-outline-primary">
                    Ver todas
                </a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Tipo</th><th>Modo</th><th>Estado</th><th>Fecha</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentRequests as $req)
                            <tr>
                                <td>{{ class_basename($req->content_type) }}</td>
                                <td><code>{{ $req->mode }}</code></td>
                                <td>
                                    @php
                                        $color = match($req->status) {
                                            'done'    => 'success',
                                            'pending' => 'warning',
                                            'failed'  => 'danger',
                                            default   => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $color }}">{{ $req->status }}</span>
                                </td>
                                <td>{{ $req->created_at?->format('d/m/Y H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Sin solicitudes</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Fallos recientes --}}
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2 text-danger"></i>Fallos recientes</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Canal</th><th>Error</th><th>Fecha</th></tr>
                    </thead>
                    <tbody>
                        @forelse($recentFailures as $attempt)
                            <tr>
                                <td>
                                    <span class="badge badge-info">{{ $attempt->target->provider ?? '—' }}</span>
                                </td>
                                <td title="{{ $attempt->error_message }}">
                                    {{ Str::limit($attempt->error_code ?? $attempt->error_message ?? '—', 25) }}
                                </td>
                                <td>{{ $attempt->created_at?->format('d/m H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Sin fallos</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
