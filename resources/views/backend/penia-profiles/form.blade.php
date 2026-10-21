@php
  $selectedEvents = old('event_ids', $profile->events->pluck('id')->all());
  $sourceUrls = old('source_urls', $profile->source_urls ?? []);
  $sourceUrls = is_array($sourceUrls) ? implode(PHP_EOL, $sourceUrls) : $sourceUrls;
@endphp

@if ($errors->any())
  <div class="alert alert-danger">Revisá los campos marcados antes de guardar.</div>
@endif

<div class="row">
  {{-- Fila 1 (grilla de 12): Nombre 4 · Slug 3 · Tipo 3 · Capacidad 2 --}}
  <div class="col-md-12 col-xl-4 form-group">
    <label for="title">Nombre <span class="text-danger">*</span></label>
    <input id="title" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $profile->title) }}" onkeyup="autocompleteSlug(this, '#slug')" required>
    @error('title') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
  <div class="col-md-6 col-xl-3 form-group">
    <label for="slug">Slug</label>
    <x-backend.help-tip text="Opcional. Se completa sola desde el nombre. Es parte del enlace público." />
    <input id="slug" class="form-control @error('slug') is-invalid @enderror" name="slug" value="{{ old('slug', $profile->slug) }}">
    @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
  <div class="col-md-3 col-xl-3 form-group">
    <label for="venue_type">Tipo</label>
    <select id="venue_type" class="form-control" name="venue_type">@foreach(['penia' => 'Peña', 'centro_cultural' => 'Centro cultural', 'gastronomico_cultural' => 'Gastronómico-cultural', 'otro' => 'Otro'] as $value => $label)<option value="{{ $value }}" @selected(old('venue_type', $profile->venue_type) === $value)>{{ $label }}</option>@endforeach</select>
  </div>
  <div class="col-md-3 col-xl-2 form-group">
    <label for="capacity">Capacidad</label>
    <input id="capacity" type="number" min="1" class="form-control" name="capacity" value="{{ old('capacity', $profile->capacity) }}">
  </div>

  {{-- Fila 2: Provincia 3 · Localidad 3 · Ciudad 3 · Dirección 3 --}}
  <div class="col-md-6 col-xl-3 form-group">
    <label for="province_id">Provincia <span class="text-danger">*</span></label>
    <select id="province_id" class="form-control @error('province_id') is-invalid @enderror" name="province_id" required><option value="">Seleccionar</option>@foreach($provincias as $province)<option value="{{ $province->id }}" @selected((int) old('province_id', $profile->province_id) === $province->id)>{{ $province->nombre }}</option>@endforeach</select>
    @error('province_id') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
  <div class="col-md-6 col-xl-3 form-group">
    <label for="locality_id">Localidad</label>
    <select id="locality_id" class="form-control select2" name="locality_id"><option value="">Sin localidad</option>@foreach($localities as $locality)<option value="{{ $locality->id }}" @selected((int) old('locality_id', $profile->locality_id) === $locality->id)>{{ $locality->name }}</option>@endforeach</select>
  </div>
  <div class="col-md-6 col-xl-3 form-group">
    <label for="city">Ciudad</label>
    <input id="city" class="form-control" name="city" value="{{ old('city', $profile->city) }}">
  </div>
  <div class="col-md-6 col-xl-3 form-group">
    <label for="address">Dirección</label>
    <input id="address" class="form-control" name="address" value="{{ old('address', $profile->address) }}">
  </div>

  {{-- Fila 3: Latitud 2 · Longitud 2 · Teléfono 2 · Email 3 · Sitio web 3 --}}
  <div class="col-md-6 col-xl-2 form-group">
    <label for="latitude">Latitud</label>
    <input id="latitude" type="number" step="0.00000001" min="-90" max="90" class="form-control" name="latitude" value="{{ old('latitude', $profile->latitude) }}">
  </div>
  <div class="col-md-6 col-xl-2 form-group">
    <label for="longitude">Longitud</label>
    <input id="longitude" type="number" step="0.00000001" min="-180" max="180" class="form-control" name="longitude" value="{{ old('longitude', $profile->longitude) }}">
  </div>
  <div class="col-md-4 col-xl-2 form-group">
    <label for="phone">Teléfono</label>
    <input id="phone" class="form-control" name="phone" value="{{ old('phone', $profile->phone) }}">
  </div>
  <div class="col-md-4 col-xl-3 form-group">
    <label for="email">Email</label>
    <input id="email" type="email" class="form-control" name="email" value="{{ old('email', $profile->email) }}">
  </div>
  <div class="col-md-4 col-xl-3 form-group">
    <label for="website">Sitio web</label>
    <input id="website" type="url" class="form-control" name="website" value="{{ old('website', $profile->website) }}">
  </div>

  {{-- Fila 4: Reservas 4 · Accesibilidad 4 · Eventos relacionados 4 --}}
  <div class="col-md-6 col-xl-4 form-group">
    <label for="reservation_url">Reservas</label>
    <input id="reservation_url" type="url" class="form-control" name="reservation_url" value="{{ old('reservation_url', $profile->reservation_url) }}">
  </div>
  <div class="col-md-6 col-xl-4 form-group">
    <label for="accessibility_notes">Accesibilidad</label>
    <input id="accessibility_notes" class="form-control" name="accessibility_notes" value="{{ old('accessibility_notes', $profile->accessibility_notes) }}">
  </div>
  <div class="col-md-12 col-xl-4 form-group">
    <label for="event_ids">Eventos relacionados</label>
    <select id="event_ids" class="form-control select2" name="event_ids[]" multiple data-placeholder="Seleccionar eventos">@foreach($events as $event)<option value="{{ $event->id }}" @selected(in_array($event->id, $selectedEvents))>{{ $event->title }}</option>@endforeach</select>
  </div>

  {{-- Fila 5: Bajada 4 · Programación habitual 4 · Ingreso y reservas 4 --}}
  <div class="col-md-12 col-xl-4 form-group">
    <label for="excerpt">Bajada</label>
    <textarea id="excerpt" class="form-control" rows="3" name="excerpt">{{ old('excerpt', $profile->excerpt) }}</textarea>
  </div>
  <div class="col-md-6 col-xl-4 form-group">
    <label for="regular_events_summary">Programación habitual</label>
    <textarea id="regular_events_summary" class="form-control" rows="3" name="regular_events_summary">{{ old('regular_events_summary', $profile->regular_events_summary) }}</textarea>
  </div>
  <div class="col-md-6 col-xl-4 form-group">
    <label for="admission_notes">Ingreso y reservas</label>
    <textarea id="admission_notes" class="form-control" rows="3" name="admission_notes">{{ old('admission_notes', $profile->admission_notes) }}</textarea>
  </div>

  {{-- Fila 6: Contenido 8 · Fuentes 4 --}}
  <div class="col-md-12 col-xl-8 form-group">
    <label for="editor">Contenido <span class="text-danger">*</span></label>
    <textarea id="editor" class="form-control @error('body') is-invalid @enderror" name="body" data-ckeditor-profile="editorial-body" required>{{ old('body', $profile->body) }}</textarea>
    @error('body') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
  <div class="col-md-12 col-xl-4 form-group">
    <label for="source_urls">Fuentes <span class="text-danger">*</span></label>
    <x-backend.help-tip text="Una URL por línea. Se necesita al menos una fuente." />
    <textarea id="source_urls" class="form-control @error('source_urls') is-invalid @enderror" rows="8" name="source_urls" required>{{ $sourceUrls }}</textarea>
    @error('source_urls') <small class="text-danger">{{ $message }}</small> @enderror
  </div>

  {{-- Fila 7: gestión editorial (solo equipo editorial): Estado 2 · Verificación 2 · Fecha de validación 3 · Responsable 3 · Método 2 --}}
  @if($canManageEditorialState)
    <div class="col-md-6 col-xl-2 form-group"><label for="editorial_status">Estado</label><select id="editorial_status" class="form-control" name="editorial_status">@foreach(['draft', 'approved', 'published', 'archived'] as $status)<option value="{{ $status }}" @selected(old('editorial_status', $profile->editorial_status) === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-6 col-xl-2 form-group"><label for="verification_status">Verificación</label><select id="verification_status" class="form-control" name="verification_status">@foreach(['pending', 'verified', 'outdated'] as $status)<option value="{{ $status }}" @selected(old('verification_status', $profile->verification_status) === $status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-6 col-xl-3 form-group"><label for="last_verified_at">Fecha de validación</label><input id="last_verified_at" type="datetime-local" class="form-control" name="last_verified_at" value="{{ old('last_verified_at', optional($profile->last_verified_at)->format('Y-m-d\\TH:i')) }}"></div>
    <div class="col-md-6 col-xl-3 form-group"><label for="verified_by_user_id">Responsable editorial</label><select id="verified_by_user_id" class="form-control select2" name="verified_by_user_id"><option value="">Seleccionar</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('verified_by_user_id', $profile->verified_by_user_id) === $user->id)>{{ $user->name }}</option>@endforeach</select></div>
    <div class="col-md-12 col-xl-2 form-group"><label for="verification_method">Método</label><select id="verification_method" class="form-control" name="verification_method"><option value="">Seleccionar</option>@foreach(['official_source' => 'Fuente oficial', 'direct_confirmation' => 'Confirmación directa', 'editorial_visit' => 'Visita editorial'] as $value => $label)<option value="{{ $value }}" @selected(old('verification_method', $profile->verification_method) === $value)>{{ $label }}</option>@endforeach</select></div>
  @else
    <div class="col-md-12"><div class="alert alert-info py-2">Tu aporte se guardará como borrador pendiente. La verificación y publicación corresponden al equipo editorial.</div></div>
  @endif

  <x-backend.seo-box noun="esta peña">
    <div class="col-md-6 form-group"><label for="seo_title">Título en Google</label><input id="seo_title" class="form-control" name="seo_title" value="{{ old('seo_title', $profile->seo_title) }}"></div>
    <div class="col-md-6 form-group"><label for="meta_description">Resumen en Google</label><input id="meta_description" class="form-control" name="meta_description" value="{{ old('meta_description', $profile->meta_description) }}"></div>
  </x-backend.seo-box>
</div>
