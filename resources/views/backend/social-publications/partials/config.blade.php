<div class="row">
<div class="col-xl-7">
<form method="POST" action="{{ route('social-publications.settings') }}" class="card">@csrf
    <div class="card-header"><strong>Ejecución y horarios</strong></div>
    <div class="card-body">
        <div class="form-row">
            <div class="col-md-6 mb-2"><label class="small mb-1">Ejecutor activo <x-backend.help-tip text="Pasá a Laravel recién después de desactivar el workflow de n8n, para que no publiquen los dos." /></label>
                <div class="custom-control custom-radio"><input class="custom-control-input" type="radio" id="ex-n8n" name="executor" value="n8n" @checked($settings['executor']==='n8n')><label class="custom-control-label" for="ex-n8n">n8n (Docker local)</label></div>
                <div class="custom-control custom-radio"><input class="custom-control-input" type="radio" id="ex-laravel" name="executor" value="laravel" @checked($settings['executor']==='laravel')><label class="custom-control-label" for="ex-laravel">Laravel (servidor)</label></div>
            </div>
            <div class="col-md-6 mb-2"><label class="small mb-1">Pausa general</label>
                <div class="custom-control custom-switch"><input type="hidden" name="paused" value="0"><input class="custom-control-input" type="checkbox" id="paused" name="paused" value="1" @checked($settings['paused'])><label class="custom-control-label" for="paused">Pausar todas las publicaciones</label></div>
                <label class="small mb-1 mt-2" for="alert-email">Email de avisos <x-backend.help-tip text="Recibe un aviso si una publicación falla o queda incierta, y si el token deja de ser válido." /></label>
                <input id="alert-email" type="email" name="alert_email" value="{{ old('alert_email', $settings['alert_email']) }}" class="form-control form-control-sm" placeholder="nombre@dominio.com">
            </div>
        </div>
        <table class="table table-sm mt-2 mb-0"><thead><tr><th>Tipo</th>@foreach($providers as $p => $name)<th><i class="{{ $icons[$p] }}"></i> {{ $name }}</th>@endforeach</tr></thead><tbody>
        @foreach($kinds as $k => $kname)<tr><td class="align-middle">{{ $kname }}</td>
            @foreach($providers as $p => $name)@php $slot = $settings['schedules'][$k][$p]; $last = $settings['last_runs'][$k.'.'.$p] ?? null; @endphp
            <td><div class="d-flex align-items-center" style="gap:.5rem">
                <input type="hidden" name="schedules[{{ $k }}][{{ $p }}][enabled]" value="0">
                <div class="custom-control custom-switch"><input class="custom-control-input" type="checkbox" id="s-{{ $k }}-{{ $p }}" name="schedules[{{ $k }}][{{ $p }}][enabled]" value="1" @checked($slot['enabled'])><label class="custom-control-label" for="s-{{ $k }}-{{ $p }}"><span class="sr-only">Activar {{ $kname }} en {{ $name }}</span></label></div>
                <input type="time" name="schedules[{{ $k }}][{{ $p }}][time]" value="{{ $slot['time'] }}" class="form-control form-control-sm" style="max-width:7.5rem" aria-label="Horario {{ $kname }} {{ $name }}">
            </div>@if($last)<small class="text-muted">Última corrida: {{ \Carbon\Carbon::parse($last)->format('d/m') }}</small>@endif</td>
            @endforeach</tr>
        @endforeach
        </tbody></table>
        <p class="small text-muted mt-2 mb-0">Una publicación por tipo y red en cada horario. Si coinciden, Facebook sale primero: Instagram solo publica lo que ya salió en Facebook.</p>
    </div>
    <div class="card-footer"><button class="btn btn-primary"><i class="fas fa-save"></i> Guardar</button></div>
</form>
</div>

<div class="col-xl-5">
<div class="card"><div class="card-header"><strong>Token de Meta</strong> <x-backend.help-tip text="Se guarda cifrado en el servidor (storage/app/private), nunca se muestra ni se versiona. Usá un token de Página de larga duración." /></div>
<div class="card-body">
    @foreach($providers as $p => $name)@php $t = $tokens[$p]; @endphp
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div class="small"><i class="{{ $icons[$p] }}"></i> <strong>{{ $name }}</strong>:
            @if(!$t['configured'])<span class="text-danger">sin cargar</span>
            @elseif($t['inherited'])<span class="text-muted">usa el token de Facebook</span>
            @else terminado en …{{ $t['last4'] }}, guardado {{ \Carbon\Carbon::parse($t['saved_at'])->timezone($tz)->format('d/m/Y H:i') }}
                @if($t['checked_at'])<br>Verificado {{ \Carbon\Carbon::parse($t['checked_at'])->timezone($tz)->format('d/m H:i') }}:
                    @if($t['check_ok'])<span class="text-success">OK ({{ $t['check_name'] }})</span>@else<span class="text-danger">{{ $t['check_error'] }}</span>@endif
                @endif
            @endif</div>
        @if($t['configured'] && !$t['inherited'])
        <form method="POST" action="{{ route('social-publications.token.forget', $p) }}" onsubmit="return confirm('¿Eliminar el token de {{ $name }}?')">@csrf @method('DELETE')<button class="btn btn-xs btn-outline-danger" title="Eliminar token"><i class="fas fa-trash"></i></button></form>
        @endif
    </div>
    @endforeach
    <form method="POST" action="{{ route('social-publications.token.check') }}" class="mb-3">@csrf<button class="btn btn-sm btn-outline-secondary"><i class="fas fa-check-circle"></i> Verificar tokens</button></form>
    <form method="POST" action="{{ route('social-publications.token.store') }}" autocomplete="off">@csrf
        <div class="form-row">
            <div class="col-4"><label class="small mb-0" for="t-provider">Red</label><select id="t-provider" name="provider" class="form-control form-control-sm"><option value="facebook">Facebook</option><option value="instagram">Instagram</option></select></div>
            <div class="col-8"><label class="small mb-0" for="t-token">Token nuevo</label><input id="t-token" type="password" name="token" required minlength="40" autocomplete="new-password" class="form-control form-control-sm"></div>
        </div>
        <button class="btn btn-sm btn-primary mt-2"><i class="fas fa-key"></i> Guardar y verificar</button>
        <p class="small text-muted mt-2 mb-0">Si Instagram no tiene token propio, se usa el de Facebook.</p>
    </form>
</div></div>

<details class="card card-body small">
    <summary>Habilitación manual (modo anterior)</summary>
    <p class="mt-2 text-muted">Solo para contenidos fuera de la cola automática (por ejemplo, anteriores al umbral). No publica nada.</p>
    <form method="POST" action="{{ route('social-publications.eligibility') }}">@csrf
        <div class="form-row">
            <div class="col-4"><label for="kind" class="mb-0">Tipo</label><select id="kind" name="content_kind" class="form-control form-control-sm">@unless(config('social_publication.news_automatic'))<option value="news">Noticia</option>@endunless<option value="events">Evento</option></select></div>
            <div class="col-4"><label for="content-id" class="mb-0">ID</label><input id="content-id" type="number" name="content_id" min="1" required class="form-control form-control-sm"></div>
            <div class="col-4"><label for="enabled" class="mb-0">Acción</label><select id="enabled" name="enabled" class="form-control form-control-sm"><option value="1">Habilitar</option><option value="0">Retirar</option></select></div>
        </div>
        <label class="mt-2"><input type="checkbox" name="portal_only_override" value="1"> Autorizo publicar aunque indique «solo portal».</label><br>
        <button class="btn btn-sm btn-secondary">Aplicar</button>
    </form>
</details>
</div>
</div>
@section('js')@include('backend.partials.scripts._tooltips')@stop
