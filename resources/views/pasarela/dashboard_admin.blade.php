@extends('adminlte::page')

@section('title', 'Dashboard Admin — Pasarela')

@section('content_header')
    <h1>Dashboard Administrativo — Pasarela de Contenidos</h1>
@endsection

@section('content')

{{-- KPIs principales --}}
<div class="row">
    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-hourglass-half"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Pendientes moderación</span>
                <span class="info-box-number">{{ $pendingModeration }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-success elevation-1"><i class="fas fa-check-double"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Publicaciones hoy</span>
                <span class="info-box-number">{{ $publishedToday }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-bug"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Fallos (24h)</span>
                <span class="info-box-number">{{ $failuresByProvider->sum() }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="info-box">
            <span class="info-box-icon bg-orange elevation-1"><i class="fas fa-key"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Tokens vencidos / por vencer</span>
                <span class="info-box-number">{{ $expiredTokens }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Estado de targets hoy --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Targets hoy</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Estado</th><th class="text-right">Cantidad</th></tr></thead>
                    <tbody>
                        @forelse($targetsToday as $status => $count)
                            <tr>
                                <td>
                                    @php
                                        $color = match($status) {
                                            'success'    => 'success',
                                            'failed'     => 'danger',
                                            'pending'    => 'warning',
                                            'processing' => 'info',
                                            default      => 'secondary',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $color }}">{{ $status }}</span>
                                </td>
                                <td class="text-right">{{ $count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">Sin actividad hoy</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Fallos por canal --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-times-circle mr-2 text-danger"></i>Fallos por canal (24h)</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Canal</th><th class="text-right">Fallos</th></tr></thead>
                    <tbody>
                        @forelse($failuresByProvider as $provider => $count)
                            <tr>
                                <td><span class="badge badge-info">{{ $provider }}</span></td>
                                <td class="text-right text-danger font-weight-bold">{{ $count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">Sin fallos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Top publicadores --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-users mr-2"></i>Top publicadores</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Usuario</th><th class="text-right">Solicitudes</th></tr></thead>
                    <tbody>
                        @forelse($topPublishers as $row)
                            <tr>
                                <td>{{ $row->requester?->name ?? 'Usuario #' . $row->requested_by }}</td>
                                <td class="text-right">{{ $row->total }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted">Sin datos</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Últimos fallos detallados --}}
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list-alt mr-2 text-danger"></i>Últimos fallos</h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Canal</th>
                    <th>Intento #</th>
                    <th>Código error</th>
                    <th>Mensaje</th>
                    <th>¿Reintentable?</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentFailures as $attempt)
                    <tr>
                        <td><span class="badge badge-info">{{ $attempt->target?->provider ?? '—' }}</span></td>
                        <td>{{ $attempt->attempt_number }}</td>
                        <td><code>{{ $attempt->error_code ?? '—' }}</code></td>
                        <td title="{{ $attempt->error_message }}">
                            {{ Str::limit($attempt->error_message ?? '—', 60) }}
                        </td>
                        <td>
                            @if($attempt->is_retryable)
                                <span class="badge badge-warning">Sí</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </td>
                        <td>{{ $attempt->created_at?->format('d/m H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">Sin fallos registrados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
