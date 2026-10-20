<x-backend.form-help section="event" />
@if($event->program_id)
<div class="form-group">
  <input type="hidden" name="program_id" value="{{ $event->program_id }}">
  <p class="mt-2"><a href="{{ route('backend.event-programs.edit', $event->program_id) }}">Editar datos comunes y ver jornadas</a></p>
  <fieldset class="border rounded p-2 mb-3"><legend class="h6">Volver a heredar datos del evento</legend><p class="small text-muted">Marcá los campos que querés reemplazar por los del evento al guardar. Los campos distintos se conservan como excepciones de esta jornada.</p>
    @foreach(['body' => 'Detalle', 'excerpt' => 'Resumen', 'venue_id' => 'Lugar', 'ticket_url' => 'Entradas', 'price_text' => 'Precio', 'is_free' => 'Gratuidad'] as $field => $label)<label class="mr-3"><input type="checkbox" name="inherit_fields[]" value="{{ $field }}" @checked(in_array($field, old('inherit_fields', [])))> {{ $label }}</label>@endforeach
  </fieldset>
</div>
@endif
@php
  $selectedInterpreteIds = old('interprete_ids', $event->interpretes->pluck('id')->all());
  $ownIds = $ownInterpreteIds ?? [];
  $selectedVenue = old('venue_id', $event->venue_id);
  $relationSelects = [
    ['name' => 'festival_ids', 'label' => 'Festivales', 'placeholder' => 'Seleccionar festivales', 'options' => $festivalOptions ?? collect(), 'selected' => $event->relationLoaded('festivales') ? $event->festivales->pluck('id')->all() : []],
    ['name' => 'knowledge_article_ids', 'label' => 'Artículos de enciclopedia', 'placeholder' => 'Seleccionar artículos', 'options' => $knowledgeArticleOptions ?? collect(), 'selected' => $event->relationLoaded('knowledgeArticles') ? $event->knowledgeArticles->pluck('id')->all() : []],
    ['name' => 'penia_profile_ids', 'label' => 'Peñas', 'placeholder' => 'Seleccionar peñas', 'options' => $peniaOptions ?? collect(), 'selected' => $event->relationLoaded('peniaProfiles') ? $event->peniaProfiles->pluck('id')->all() : []],
  ];
  $artistsHelp = 'Podés asociar uno o varios artistas. La lista reemplaza la nómina actual al guardar.'
    .(! empty($restrictedByFichas) ? ' Incluí al menos un artista o festival que administrás; los demás son invitados.' : '');
@endphp
<div class="row">
  {{-- Fila 1 (grilla de 12): Título 4 · Inicio 2 · Fin 2 · Publicación 2 · Estado 2 --}}
  <div class="col-md-12 col-xl-4">
    <div class="form-group">
      <label for="title">Título del Evento <span class="text-danger">*</span></label>
      <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $event->title ?? '') }}"
        required placeholder="Ej: Festival Nacional del Malambo">
      @error('title')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-6 col-xl-2" data-single-event>
    <div class="form-group">
      <label for="start_at">Inicio <span class="text-danger">*</span></label>
      <input type="datetime-local" name="start_at" id="start_at" class="form-control" required
        value="{{ old('start_at', isset($event) && $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('Y-m-d\TH:i') : '') }}">
      @error('start_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-6 col-xl-2" data-single-event>
    <div class="form-group">
      <label for="end_at">Fin</label>
      <x-backend.help-tip text="Opcional. Una misma jornada puede terminar al día siguiente; para varias fechas usá jornadas." />
      <input type="datetime-local" name="end_at" id="end_at" class="form-control"
        value="{{ old('end_at', isset($event) && $event->end_at ? \Carbon\Carbon::parse($event->end_at)->format('Y-m-d\TH:i') : '') }}">
      @error('end_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  @if(Auth::user()->canPublish())
  <div class="col-md-6 col-xl-2">
    <div class="form-group">
      <label for="published_at">Publicación</label>
      <x-backend.help-tip text="Opcional. Dejar vacío para publicar ahora." />
      <input type="datetime-local" name="published_at" id="published_at" class="form-control"
        value="{{ old('published_at', isset($event) && $event->published_at ? \Carbon\Carbon::parse($event->published_at)->format('Y-m-d\TH:i') : '') }}">
      @error('published_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-6 col-xl-2">
    <div class="form-group">
      <label for="estado" class="form-label">Estado</label>
      @php
        $defaultEstado = isset($event) && $event->id
          ? ($event->editorial_status === 'published' ? 1 : 0)
          : 1;
        $selectedEstado = old('estado', $defaultEstado);
      @endphp
      <select name="estado" class="form-control" id="estado">
        <option value="1" {{ $selectedEstado == 1 ? 'selected' : '' }}>Publicado (Activo)</option>
        <option value="0" {{ $selectedEstado == 0 ? 'selected' : '' }}>Borrador (Inactivo)</option>
      </select>
      @error('estado')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
  @else
    <input type="hidden" name="estado" value="0">
    <div class="col-md-12 col-xl-4">
      <div class="alert alert-info py-2 mb-2">
        <i class="fas fa-info-circle mr-2"></i> Tu contenido será revisado por un moderador antes de ser publicado.
      </div>
    </div>
  @endif

  {{-- Fila 2: Sala 4 · Ciudad 2 · Dirección 3 · Provincia 3 --}}
  <div class="col-md-12 col-xl-4">
    <div class="form-group">
      <label for="venue_id">Sala / Lugar</label>
      <x-backend.help-tip text="La ubicación se toma del catálogo. Si el lugar no existe, podés crear o proponer uno nuevo más abajo." />
      @if(auth()->user()->hasRole('administrador'))
        <a href="{{ route('backend.venues.index') }}" target="_blank" rel="noopener" class="ml-1 small" title="Administrar Salas / Lugares"><i class="fas fa-external-link-alt"></i></a>
      @endif
      <select id="venue_id" name="venue_id" class="form-control select2" data-placeholder="Buscar un lugar">
        <option value="">Sin lugar del catálogo</option>
        @foreach($venues as $venue)<option value="{{ $venue->id }}" @selected($selectedVenue == $venue->id) data-city="{{ $venue->city }}" data-address="{{ $venue->address }}" data-province="{{ $venue->province_id }}">{{ $venue->name }} — {{ $venue->city }} / {{ $venue->provincia?->nombre }}{{ $venue->status !== 'active' ? ' ('.$venue->status.')' : '' }}</option>@endforeach
        @if($event->venue_id && !$venues->contains('id', $event->venue_id))
          <option value="{{ $event->venue_id }}" selected>{{ $event->venue?->name }} (lugar ya vinculado)</option>
        @endif
      </select>
      @error('venue_id')<small class="text-danger">{{ $message }}</small>@enderror
    </div>
  </div>

  <div class="col-md-4 col-xl-2" data-single-event>
    <div class="form-group">
      <label for="city">Ciudad / Localidad</label>
      <input type="text" name="city" id="city" class="form-control"
        value="{{ old('city', $event->city ?? '') }}" placeholder="Ej: Cosquín">
      @error('city')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4 col-xl-3" data-single-event>
    <div class="form-group">
      <label for="address">Dirección / Predio</label>
      <input type="text" name="address" id="address" class="form-control"
        value="{{ old('address', $event->address ?? '') }}" placeholder="Ej: Plaza Próspero Molina">
      @error('address')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4 col-xl-3" data-single-event>
    <div class="form-group">
      <label for="province_id">Provincia</label>
      <select name="province_id" id="province_id" class="form-control">
        <option value="">— Seleccionar —</option>
        @foreach ($provincias as $provincia)
          <option value="{{ $provincia->id }}"
            {{ old('province_id', $event->province_id ?? '') == $provincia->id ? 'selected' : '' }}>
            {{ $provincia->nombre }}
          </option>
        @endforeach
      </select>
      @error('province_id')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-12">
    @include('backend.events._venue_new')
  </div>

  {{-- Fila 3: Artistas 3 · Festivales 3 · Artículos 3 · Peñas 3 --}}
  <div class="col-md-6 col-xl-3" data-single-event>
    <div class="form-group">
      <label for="interprete_ids">Artistas participantes</label>
      <x-backend.help-tip :text="$artistsHelp" />
      <select name="interprete_ids[]" id="interprete_ids" class="form-control select2" multiple data-placeholder="Seleccionar artistas">
        @if (count($ownIds))
          <optgroup label="Mis artistas">
            @foreach ($interpretes->whereIn('id', $ownIds) as $interprete)
              <option value="{{ $interprete->id }}" {{ in_array($interprete->id, $selectedInterpreteIds) ? 'selected' : '' }}>{{ $interprete->interprete }}</option>
            @endforeach
          </optgroup>
          <optgroup label="Otros artistas (invitados)">
            @foreach ($interpretes->whereNotIn('id', $ownIds) as $interprete)
              <option value="{{ $interprete->id }}" {{ in_array($interprete->id, $selectedInterpreteIds) ? 'selected' : '' }}>{{ $interprete->interprete }}</option>
            @endforeach
          </optgroup>
        @else
          @foreach ($interpretes as $interprete)
            <option value="{{ $interprete->id }}"
              {{ in_array($interprete->id, $selectedInterpreteIds) ? 'selected' : '' }}>
              {{ $interprete->interprete }}
            </option>
          @endforeach
        @endif
      </select>
      <input type="hidden" name="interprete_ids[]" value="">
      @error('interprete_ids')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  @foreach ($relationSelects as $relationSelect)
    @php $selectedIds = array_map('intval', array_filter((array) old($relationSelect['name'], $relationSelect['selected']))); @endphp
    <div class="col-md-6 col-xl-3" data-single-event>
      <div class="form-group">
        <label for="{{ $relationSelect['name'] }}">{{ $relationSelect['label'] }}</label>
        <x-backend.help-tip text="En la ficha pública solo se muestran los vínculos publicados. Las peñas se muestran si el directorio está habilitado." />
        {{-- El hidden vacío permite desvincular todo: sin él, un select múltiple vacío no se envía. --}}
        <input type="hidden" name="{{ $relationSelect['name'] }}[]" value="">
        <select name="{{ $relationSelect['name'] }}[]" id="{{ $relationSelect['name'] }}" class="form-control select2" multiple data-placeholder="{{ $relationSelect['placeholder'] }}">
          @foreach ($relationSelect['options'] as $option)
            <option value="{{ $option->id }}" @selected(in_array((int) $option->id, $selectedIds, true))>{{ $option->title }}@if ($option->status !== 'published') ({{ $option->status }})@endif</option>
          @endforeach
        </select>
        @error($relationSelect['name'].'.*')
          <small class="text-danger">{{ $message }}</small>
        @enderror
      </div>
    </div>
  @endforeach

  {{-- Fila 4: Entradas 4 · Precio 3 · Gratuito 2 --}}
  <div class="col-md-6 col-xl-4">
    <div class="form-group">
      <label for="ticket_url">URL de Entradas / Web</label>
      <input type="url" name="ticket_url" id="ticket_url" class="form-control"
        value="{{ old('ticket_url', $event->ticket_url ?? '') }}" placeholder="https://...">
      @error('ticket_url')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-4 col-xl-3">
    <div class="form-group">
      <label for="price_text">Información de Precio</label>
      <input type="text" name="price_text" id="price_text" class="form-control"
        value="{{ old('price_text', $event->price_text ?? '') }}" placeholder="Ej: $5000 o 'A la gorra'">
      @error('price_text')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-2 col-xl-2 d-flex align-items-end pb-2">
    <input type="hidden" name="is_free" value="0">
    <div class="custom-control custom-checkbox mb-2">
      <input type="checkbox" name="is_free" class="custom-control-input" id="is_free" value="1"
        {{ old('is_free', $event->is_free ?? false) ? 'checked' : '' }}>
      <label class="custom-control-label" for="is_free">Es gratuito</label>
    </div>
  </div>

  {{-- Fila 5: Resumen 8 · Imagen destacada 4 --}}
  <div class="col-md-12 col-xl-8">
    <div class="form-group">
      <label for="excerpt">Resumen</label>
      <textarea name="excerpt" id="excerpt" class="form-control" rows="3" maxlength="500" placeholder="Una o dos frases que resuman el evento.">{{ old('excerpt', $event->excerpt ?? '') }}</textarea>
      @error('excerpt')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <div class="col-md-12 col-xl-4">
    <div class="form-group">
      @php $currentImage = isset($event->id) ? $event->images->first() : null; @endphp
      <label for="imagen_destacada">Imagen destacada</label>
      @if($currentImage)
        <x-backend.help-tip text="Si no elegís un archivo, se mantiene la imagen actual." />
      @endif
      <x-backend.image-hint profile="event" mode="tip" />
      <div class="d-flex align-items-start">
        @if($currentImage)
          <x-optimized-image :image="$currentImage" variant="card" class="img-thumbnail shadow-sm mr-2" style="max-height: 64px; width: auto;" />
        @endif
        <div class="custom-file flex-grow-1">
          <input type="file" name="imagen_destacada" class="custom-file-input" id="imagen_destacada" accept="image/*">
          <label class="custom-file-label" for="imagen_destacada">{{ $currentImage ? 'Elegir nuevo archivo...' : 'Elegir archivo...' }}</label>
        </div>
      </div>
      <x-backend.image-hint profile="event" mode="warnings" />
    </div>
  </div>

  {{-- Fila 6: Detalle 12 --}}
  <div class="col-12">
    <div class="form-group">
      <label for="editor">Detalles del Evento <span class="text-danger">*</span></label>
      <textarea name="body" id="editor" class="form-control" rows="6" placeholder="Descripción completa del evento..." data-ckeditor-profile="editorial-body">{{ old('body', $event->body ?? '') }}</textarea>
      @error('body')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
</div>

@if(isset($event) && $event->id)
  <input type="hidden" name="slug" value="{{ old('slug', $event->slug ?? '') }}">
@endif
