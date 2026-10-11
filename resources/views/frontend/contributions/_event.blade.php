<div><label for="event-title" class="block font-medium mb-1">Nombre del evento o edición *</label><input id="event-title" name="payload[title]" value="{{ old('payload.title', $original?->title) }}" class="w-full border rounded p-2" required maxlength="255"></div>
<div><label for="editor" class="block font-medium mb-1">Detalle *</label><textarea id="editor" name="payload[body]" rows="8" class="w-full border rounded p-2" data-ckeditor-profile="editorial-body">{{ old('payload.body', $original?->body) }}</textarea></div>
<div><label for="event-venue" class="block font-medium mb-1">Sala / Lugar</label><select id="event-venue" name="payload[venue_id]" class="w-full border rounded p-2"><option value="">Sin lugar del catálogo</option>@foreach($venues as $venue)<option value="{{ $venue->id }}" @selected(old('payload.venue_id', $original?->venue_id) == $venue->id)>{{ $venue->name }} — {{ $venue->city }}</option>@endforeach</select></div>
<details class="border rounded p-3"><summary>Proponer un lugar nuevo</summary><p class="text-sm my-2">Si no existe en el catálogo, completá nombre, localidad y provincia. Se revisará junto con tu propuesta.</p>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
@foreach(['name' => 'Nombre', 'address' => 'Dirección', 'city' => 'Localidad'] as $field => $label)<label>{{ $label }}<input name="payload[new_venue][{{ $field }}]" value="{{ old('payload.new_venue.'.$field) }}" class="w-full border rounded p-2"></label>@endforeach
<label>Provincia<select name="payload[new_venue][province_id]" class="w-full border rounded p-2"><option value="">Seleccionar</option>@foreach($provincias as $province)<option value="{{ $province->id }}" @selected(old('payload.new_venue.province_id') == $province->id)>{{ $province->nombre }}</option>@endforeach</select></label>
</div></details>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><label>Entradas / web<input type="url" name="payload[ticket_url]" value="{{ old('payload.ticket_url', $original?->ticket_url) }}" class="w-full border rounded p-2"></label><label>Precio<input name="payload[price_text]" value="{{ old('payload.price_text', $original?->price_text) }}" class="w-full border rounded p-2" maxlength="100"></label></div>
<input type="hidden" name="payload[is_free]" value="0"><label><input type="checkbox" name="payload[is_free]" value="1" @checked(old('payload.is_free', $original?->is_free))> Entrada gratuita</label>
@if(!$original)<label class="block"><input type="checkbox" id="multiple-sessions" @checked(old('payload.sessions'))> Tiene varias jornadas con programación propia</label>@endif
<div id="single-session" class="space-y-3">
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><label>Inicio *<input type="datetime-local" name="payload[start_at]" value="{{ old('payload.start_at', $original?->start_at?->format('Y-m-d\TH:i')) }}" class="w-full border rounded p-2" required></label><label>Fin (opcional)<input type="datetime-local" name="payload[end_at]" value="{{ old('payload.end_at', $original?->end_at?->format('Y-m-d\TH:i')) }}" class="w-full border rounded p-2"></label></div>
@include('frontend.contributions._artists', ['prefix' => 'payload[interprete_ids]', 'selectedArtistIds' => old('payload.interprete_ids', $original?->interpretes->pluck('id')->all() ?? [])])
</div>
@if(!$original)
<div id="multiple-session-fields" class="space-y-3" hidden>
<p class="text-sm">Cada jornada tendrá su ficha y fecha. Podés proponer hasta 60 jornadas; no se generan días intermedios.</p>
<div id="contribution-sessions">@foreach(old('payload.sessions', [['title' => '', 'start_at' => '']]) as $index => $session) @include('frontend.contributions._session') @endforeach</div>
<button type="button" id="add-contribution-session" class="border border-orange-500 rounded px-4 py-2 text-orange-700">Agregar jornada</button>
<template id="contribution-session-template">@include('frontend.contributions._session', ['index' => '__INDEX__', 'session' => []])</template>
</div>
@endif
<datalist id="event-artist-options">@foreach($interpretes as $artist)<option value="{{ $artist->interprete }}" data-id="{{ $artist->id }}"></option>@endforeach</datalist>
