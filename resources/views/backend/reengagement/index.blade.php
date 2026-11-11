@extends('adminlte::page')

@section('title', 'Reactivación de usuarios')

@section('content_header')
    <h1>Reactivación de usuarios</h1>
@stop

@section('content')
<details class="callout callout-info mb-3">
    <summary class="font-weight-bold" style="cursor:pointer"><i class="fas fa-question-circle mr-1"></i> ¿Cómo funciona?</summary>
    <div class="mt-2 small">
        <ul class="pl-3 mb-0">
            <li>Se le escribe a los usuarios activos que no ingresan hace más de <strong>{{ $inactivityDays }} días</strong> y a los que nunca ingresaron (con cuenta de más de {{ $inactivityDays }} días).</li>
            <li>Nadie recibe más de un mail cada <strong>{{ $cooldownDays }} días</strong>; quienes se dieron de baja no reciben nada.</li>
            <li>El envío es manual: revisás la vista previa, te mandás una prueba y confirmás. Los mails salen en cola, de a poco.</li>
        </ul>
    </div>
</details>

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

@if($isDue && $total > 0)
    <div class="alert alert-warning" data-reengagement-due>
        <i class="fas fa-bell mr-1"></i>
        @if($lastBatchAt)
            Pasaron más de {{ $cooldownDays }} días desde el último envío ({{ $lastBatchAt->format('d/m/Y') }}): corresponde enviar una nueva tanda.
        @else
            Todavía no se envió ninguna tanda: hay {{ $total }} usuarios para reactivar.
        @endif
    </div>
@elseif($dueSince)
    <div class="alert alert-secondary mb-3">Última tanda: {{ $lastBatchAt->format('d/m/Y H:i') }}. La próxima corresponde a partir del {{ $dueSince->format('d/m/Y') }}.</div>
@endif

<div class="row">
    <div class="col-md-4"><div class="small-box bg-info"><div class="inner"><h3>{{ $total }}</h3><p>Destinatarios hoy</p></div><div class="icon"><i class="fas fa-users"></i></div></div></div>
    <div class="col-md-4"><div class="small-box bg-secondary"><div class="inner"><h3>{{ $total - $neverCount }}</h3><p>Inactivos (variante «te extrañamos»)</p></div><div class="icon"><i class="fas fa-user-clock"></i></div></div></div>
    <div class="col-md-4"><div class="small-box bg-secondary"><div class="inner"><h3>{{ $neverCount }}</h3><p>Nunca ingresaron (variante «tu cuenta te espera»)</p></div><div class="icon"><i class="fas fa-user-plus"></i></div></div></div>
</div>

<div class="card card-outline card-primary shadow-sm">
    <div class="card-header"><h3 class="card-title mb-0">Antes de enviar</h3></div>
    <div class="card-body">
        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem">
            <a href="{{ route('backend.reengagement.preview', ['variant' => 'lapsed']) }}" target="_blank" class="btn btn-outline-secondary btn-sm">Vista previa «te extrañamos»</a>
            <a href="{{ route('backend.reengagement.preview', ['variant' => 'never']) }}" target="_blank" class="btn btn-outline-secondary btn-sm">Vista previa «tu cuenta te espera»</a>
            <form method="POST" action="{{ route('backend.reengagement.test') }}" class="d-inline">@csrf
                <input type="hidden" name="variant" value="lapsed">
                <button class="btn btn-outline-primary btn-sm"><i class="fas fa-paper-plane mr-1"></i> Enviarme una prueba</button>
            </form>
        </div>
        <hr>
        <form method="POST" action="{{ route('backend.reengagement.send') }}" data-reengagement-send>
            @csrf
            <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" id="confirm" name="confirm" value="1" {{ $total === 0 ? 'disabled' : '' }}>
                <label class="custom-control-label" for="confirm">Confirmo el envío a <strong>{{ $total }}</strong> usuarios.</label>
            </div>
            <button type="submit" class="btn btn-primary" {{ $total === 0 ? 'disabled' : '' }}><i class="fas fa-paper-plane mr-1"></i> Enviar mail de reactivación</button>
        </form>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header"><h3 class="card-title mb-0">Destinatarios (primeros {{ $users->count() }} de {{ $total }})</h3></div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light"><tr><th>Usuario</th><th>Correo</th><th>Último ingreso</th><th>Variante</th></tr></thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->last_login_at?->format('d/m/Y') ?? 'Nunca' }}</td>
                    <td>{{ $user->variant === 'never' ? 'Tu cuenta te espera' : 'Te extrañamos' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted py-3">No hay destinatarios en este momento.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card card-outline card-secondary shadow-sm">
    <div class="card-header"><h3 class="card-title mb-0">Historial de tandas</h3></div>
    <div class="card-body p-0 table-responsive">
        <table class="table table-sm mb-0">
            <thead class="thead-light"><tr><th>Fecha</th><th>Total</th><th>Enviados</th><th>En cola</th><th>Fallidos</th><th>Omitidos</th></tr></thead>
            <tbody>
            @forelse($batches as $batch)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($batch->started_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $batch->total }}</td><td>{{ $batch->sent }}</td><td>{{ $batch->queued }}</td><td>{{ $batch->failed }}</td><td>{{ $batch->skipped }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-3">Todavía no se envió ninguna tanda.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
