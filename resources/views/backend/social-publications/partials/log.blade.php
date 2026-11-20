<form method="GET" class="form-row align-items-end mb-3">
    <input type="hidden" name="tab" value="log">
    <div class="col-6 col-md-2"><label class="small mb-0" for="f-provider">Red</label><select id="f-provider" name="provider" class="form-control form-control-sm"><option value="">Todas</option>@foreach($providers as $p => $name)<option value="{{ $p }}" @selected(request('provider')===$p)>{{ $name }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><label class="small mb-0" for="f-kind">Tipo</label><select id="f-kind" name="content_kind" class="form-control form-control-sm"><option value="">Todos</option>@foreach($kinds as $k => $name)<option value="{{ $k }}" @selected(request('content_kind')===$k)>{{ $name }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><label class="small mb-0" for="f-status">Estado</label><select id="f-status" name="status" class="form-control form-control-sm"><option value="">Todos</option>@foreach(['success','failed','uncertain','processing','reserved','pending','cancelled'] as $state)<option value="{{ $state }}" @selected(request('status')===$state)>{{ $labels[$state] }}</option>@endforeach</select></div>
    <div class="col-6 col-md-2"><label class="small mb-0" for="f-from">Desde</label><input id="f-from" type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm"></div>
    <div class="col-6 col-md-2"><label class="small mb-0" for="f-to">Hasta</label><input id="f-to" type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm"></div>
    <div class="col-6 col-md-2"><button class="btn btn-secondary btn-sm btn-block"><i class="fas fa-filter"></i> Filtrar</button></div>
</form>

<div class="table-responsive"><table class="table table-sm table-hover">
<thead><tr><th>Fecha</th><th>Red</th><th>Contenido</th><th>Estado</th><th>Intentos</th><th>Post</th><th></th></tr></thead><tbody>
@forelse($targets as $target)
    @php $last = $target->attempts->last(); $date = $target->published_at ?? $target->updated_at; @endphp
    <tr>
        <td class="text-nowrap">{{ $date?->timezone($tz)->format('d/m/Y H:i') }}</td>
        <td class="text-nowrap"><i class="{{ $icons[$target->provider] ?? '' }}"></i> {{ $providers[$target->provider] ?? $target->provider }}</td>
        <td><span class="badge badge-light">{{ $kinds[$target->ledger_content_kind] ?? $target->ledger_content_kind }} #{{ $target->ledger_content_id }}</span>
            @if($url = $target->payload_snapshot_json['url'] ?? null)<a href="{{ $url }}" target="_blank" rel="noopener">{{ $target->request?->content?->title ?? 'Contenido retirado' }}</a>@else{{ $target->request?->content?->title ?? 'Contenido retirado' }}@endif</td>
        <td><span class="badge badge-{{ $badges[$target->status] ?? 'secondary' }}">{{ $labels[$target->status] ?? $target->status }}</span>
            @if($target->last_error_code)<br><small class="text-muted">{{ $target->last_error_code }}</small>@endif</td>
        <td>{{ $target->attempts_count }}@if($last)<br><small class="text-muted">{{ $last->executor === 'portal' ? 'Laravel' : $last->executor }}</small>@endif</td>
        <td>@if($target->external_url)<a href="{{ $target->external_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline-primary"><i class="fas fa-external-link-alt"></i> Ver</a>@endif</td>
        <td class="text-right"><button type="button" class="btn btn-xs btn-outline-secondary" data-toggle="collapse" data-target="#detail-{{ $target->id }}" aria-expanded="false">Detalle</button></td>
    </tr>
    <tr class="collapse" id="detail-{{ $target->id }}"><td colspan="7" class="bg-light">
        <div class="row"><div class="col-lg-7">
            <strong class="small">Intentos</strong>
            <ul class="small mb-2">@forelse($target->attempts as $attempt)
                <li>#{{ $attempt->attempt_number }} · {{ $labels[$attempt->status] ?? $attempt->status }} · {{ $attempt->executor === 'portal' ? 'Laravel' : $attempt->executor }}
                    · {{ ($attempt->dispatch_started_at ?? $attempt->created_at)?->timezone($tz)->format('d/m H:i:s') }}@if($attempt->error_code) · {{ $attempt->error_code }}@endif
                    @if($meta = $attempt->response_payload_json['meta_message'] ?? null)<br><span class="text-muted">Meta (HTTP {{ $attempt->response_payload_json['http_status'] ?? '?' }}): {{ $meta }}</span>@endif</li>
            @empty<li>Sin intentos.</li>@endforelse</ul>
            <small class="text-muted">Plantilla {{ $target->template_id }} · hash {{ substr($target->content_hash ?? '', 0, 12) }}@if($target->external_post_id) · ID {{ $target->external_post_id }}@endif</small>
        </div><div class="col-lg-5">
        @if(in_array($target->status, ['failed', 'uncertain']))
            <form method="POST" action="{{ route('social-publications.resolve', $target->id) }}">@csrf
                <select name="action" class="form-control form-control-sm mb-1" aria-label="Acción">@if($target->status === 'failed')<option value="retry">Reintentar (fallo seguro)</option>@else<option value="published">Verifiqué que se publicó</option><option value="not_published">Verifiqué que NO se publicó</option>@endif</select>
                @if($target->status === 'uncertain')
                    <input name="external_post_id" class="form-control form-control-sm mb-1" aria-label="ID remoto" placeholder="{{ $target->provider === 'instagram' ? 'ID de media de Instagram' : 'ID del post (página_post)' }} si se publicó">
                    @if($target->provider === 'instagram')<input name="external_url" class="form-control form-control-sm mb-1" aria-label="Permalink" placeholder="https://www.instagram.com/p/…/">@endif
                @endif
                <textarea name="reason" required minlength="10" maxlength="500" rows="2" class="form-control form-control-sm mb-1" aria-label="Motivo y evidencia" placeholder="Motivo y evidencia de la verificación"></textarea>
                <label class="small"><input type="checkbox" name="confirmed" value="1" required> Confirmo la verificación</label>
                <button class="btn btn-secondary btn-sm">Registrar</button>
            </form>
        @endif
        </div></div>
    </td></tr>
@empty
    <tr><td colspan="7" class="text-muted">Sin publicaciones para estos filtros.</td></tr>
@endforelse
</tbody></table></div>
{{ $targets->links() }}

<h5 class="mt-4">Últimas corridas</h5>
<div class="table-responsive"><table class="table table-sm">
<thead><tr><th>Fecha</th><th>Origen</th><th>Red</th><th>Contenido</th><th>Resultado</th></tr></thead><tbody>
@forelse($runs as $run)
    @php $r = $run->new_values_json ?? []; @endphp
    <tr><td class="text-nowrap">{{ $run->created_at?->timezone($tz)->format('d/m/Y H:i') }}</td>
        <td>{{ ['schedule' => 'Programado', 'manual' => 'Manual', 'console' => 'Consola'][$r['trigger'] ?? ''] ?? '-' }}</td>
        <td><i class="{{ $icons[$r['provider'] ?? ''] ?? '' }}"></i> {{ $kinds[$r['kind'] ?? ''] ?? '' }}</td>
        <td>@if(!empty($r['content_id']))#{{ $r['content_id'] }} {{ \Illuminate\Support\Str::limit($r['title'] ?? '', 60) }}@else — @endif</td>
        <td><span class="badge badge-{{ $badges[$r['status'] ?? ''] ?? 'secondary' }}">{{ $labels[$r['status'] ?? ''] ?? ($r['status'] ?? '') }}</span> <small>{{ $r['message'] ?? '' }}</small></td></tr>
@empty
    <tr><td colspan="5" class="text-muted">Todavía no hubo corridas del ejecutor Laravel.</td></tr>
@endforelse
</tbody></table></div>
