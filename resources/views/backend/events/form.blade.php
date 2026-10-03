<div class="row">
  <!-- Título -->
  <div class="col-md-8">
    <div class="form-group">
      <label for="title">Título del Evento <span class="text-danger">*</span></label>
      <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $event->title ?? '') }}"
        required placeholder="Ej: Festival Nacional del Malambo">
      @error('title')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <!-- Artistas -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="interprete_ids">Artistas participantes</label>
      @php
        $selectedInterpreteIds = old('interprete_ids', $event->interpretes->pluck('id')->all());
      @endphp
      <select name="interprete_ids[]" id="interprete_ids" class="form-control select2" multiple data-placeholder="Seleccionar artistas">
        @foreach ($interpretes as $interprete)
          <option value="{{ $interprete->id }}"
            {{ in_array($interprete->id, $selectedInterpreteIds) ? 'selected' : '' }}>
            {{ $interprete->interprete }}
          </option>
        @endforeach
      </select>
      <small class="form-text text-muted">Podés asociar uno o varios artistas. La lista reemplaza la nómina actual al guardar.</small>
      @error('interprete_ids')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  <!-- Fecha y hora (start_at) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="start_at">Fecha y hora de Inicio <span class="text-danger">*</span></label>
      <input type="datetime-local" name="start_at" id="start_at" class="form-control" required
        value="{{ old('start_at', isset($event) && $event->start_at ? \Carbon\Carbon::parse($event->start_at)->format('Y-m-d\TH:i') : '') }}">
      @error('start_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <!-- Fecha y hora de fin (end_at) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="end_at">Fecha y hora de fin</label>
      <input type="datetime-local" name="end_at" id="end_at" class="form-control"
        value="{{ old('end_at', isset($event) && $event->end_at ? \Carbon\Carbon::parse($event->end_at)->format('Y-m-d\TH:i') : '') }}">
      <small class="form-text text-muted">Opcional. Para eventos de varios días o con horario de cierre.</small>
      @error('end_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>
</div>

<div class="row">
  @if(Auth::user()->canPublish())
  <!-- Publicar el (published_at) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="published_at">Fecha de Publicación:</label>
      <input type="datetime-local" name="published_at" id="published_at" class="form-control"
        value="{{ old('published_at', isset($event) && $event->published_at ? \Carbon\Carbon::parse($event->published_at)->format('Y-m-d\TH:i') : '') }}">
      <small class="form-text text-muted">Dejar vacío para publicar ahora.</small>
      @error('published_at')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <!-- Estado -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="estado" class="form-label">Estado Editorial <span class="text-danger">*</span></label>
      @php
        $defaultEstado = isset($event) && $event->id
          ? ($event->editorial_status === 'published' ? 1 : 0)
          : 1;
        $selectedEstado = old('estado', $defaultEstado);
      @endphp
      <select name="estado" class="form-control" id="estado" required>
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
    <div class="col-md-8">
        <div class="alert alert-info mt-4">
            <i class="fas fa-info-circle mr-2"></i> Tu contenido será revisado por un moderador antes de ser publicado.
        </div>
    </div>
  @endif
</div>

<div class="row">
  <!-- Lugar (city) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="city">Ciudad / Localidad</label>
      <input type="text" name="city" id="city" class="form-control"
        value="{{ old('city', $event->city ?? '') }}" placeholder="Ej: Cosquín">
      @error('city')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <!-- Dirección (address) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="address">Dirección / Predio</label>
      <input type="text" name="address" id="address" class="form-control"
        value="{{ old('address', $event->address ?? '') }}" placeholder="Ej: Plaza Próspero Molina">
      @error('address')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <!-- Provincia (province_id) -->
  <div class="col-md-4">
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
</div>

<div class="row">
  <!-- Link de entradas (ticket_url) -->
  <div class="col-md-6">
    <div class="form-group">
      <label for="ticket_url">URL de Entradas / Web</label>
      <input type="url" name="ticket_url" id="ticket_url" class="form-control"
        value="{{ old('ticket_url', $event->ticket_url ?? '') }}" placeholder="https://...">
      @error('ticket_url')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <!-- Precio (price_text) -->
  <div class="col-md-4">
    <div class="form-group">
      <label for="price_text">Información de Precio</label>
      <input type="text" name="price_text" id="price_text" class="form-control"
        value="{{ old('price_text', $event->price_text ?? '') }}" placeholder="Ej: $5000 o 'A la gorra'">
      @error('price_text')
        <small class="text-danger">{{ $message }}</small>
      @enderror
    </div>
  </div>

  <!-- Entrada libre (is_free) -->
  <div class="col-md-2 d-flex align-items-end pb-3">
    <div class="custom-control custom-checkbox">
      <input type="checkbox" name="is_free" class="custom-control-input" id="is_free" value="1"
        {{ old('is_free', $event->is_free ?? false) ? 'checked' : '' }}>
      <label class="custom-control-label" for="is_free">Es gratuito</label>
    </div>
  </div>
</div>

<!-- Relaciones editoriales (pivots event_festival, event_knowledge_article, penia_profile_event) -->
@php
  $relationSelects = [
    ['name' => 'festival_ids', 'label' => 'Festivales', 'placeholder' => 'Seleccionar festivales', 'options' => $festivalOptions ?? collect(), 'selected' => $event->relationLoaded('festivales') ? $event->festivales->pluck('id')->all() : []],
    ['name' => 'knowledge_article_ids', 'label' => 'Artículos de enciclopedia', 'placeholder' => 'Seleccionar artículos', 'options' => $knowledgeArticleOptions ?? collect(), 'selected' => $event->relationLoaded('knowledgeArticles') ? $event->knowledgeArticles->pluck('id')->all() : []],
    ['name' => 'penia_profile_ids', 'label' => 'Peñas', 'placeholder' => 'Seleccionar peñas', 'options' => $peniaOptions ?? collect(), 'selected' => $event->relationLoaded('peniaProfiles') ? $event->peniaProfiles->pluck('id')->all() : []],
  ];
@endphp
<div class="row">
  @foreach ($relationSelects as $relationSelect)
    @php $selectedIds = array_map('intval', array_filter((array) old($relationSelect['name'], $relationSelect['selected']))); @endphp
    <div class="col-md-4">
      <div class="form-group">
        <label for="{{ $relationSelect['name'] }}">{{ $relationSelect['label'] }}</label>
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
</div>
<small class="form-text text-muted mb-3">En la ficha pública solo se muestran los vínculos publicados. Las peñas se muestran si el directorio está habilitado.</small>

<!-- Resumen (excerpt) -->
<div class="form-group">
  <label for="excerpt">Resumen</label>
  <textarea name="excerpt" id="excerpt" class="form-control" rows="2" maxlength="500" placeholder="Una o dos frases que resuman el evento.">{{ old('excerpt', $event->excerpt ?? '') }}</textarea>
  @error('excerpt')
    <small class="text-danger">{{ $message }}</small>
  @enderror
</div>

<!-- Detalle (body) -->
<div class="form-group">
  <label for="body">Detalles del Evento <span class="text-danger">*</span></label>
  <textarea name="body" id="body" class="form-control summernote" rows="6" placeholder="Descripción completa del evento..." data-summernote-profile="editorial-body">{{ old('body', $event->body ?? '') }}</textarea>
  @error('body')
    <small class="text-danger">{{ $message }}</small>
  @enderror
</div>

@if(isset($event) && $event->id)
  <input type="hidden" name="slug" value="{{ old('slug', $event->slug ?? '') }}">
@endif
