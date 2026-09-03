@php
  $selectedEvents = old('event_ids', $profile->events->pluck('id')->all());
  $sourceUrls = old('source_urls', $profile->source_urls ?? []);
  $sourceUrls = is_array($sourceUrls) ? implode(PHP_EOL, $sourceUrls) : $sourceUrls;
@endphp

@if ($errors->any())
  <div class="alert alert-danger">Revisá los campos marcados antes de guardar.</div>
@endif

<div class="row">
  <div class="col-md-8 form-group">
    <label>Nombre</label>
    <input class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title', $profile->title) }}" onkeyup="autocompleteSlug(this, '#slug')" required>
    @error('title') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
  <div class="col-md-4 form-group">
    <label>Slug</label>
    <input id="slug" class="form-control @error('slug') is-invalid @enderror" name="slug" value="{{ old('slug', $profile->slug) }}">
    @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
  <div class="col-md-12 form-group"><label>Bajada</label><textarea class="form-control" name="excerpt">{{ old('excerpt', $profile->excerpt) }}</textarea></div>
  <div class="col-md-12 form-group">
    <label>Contenido</label>
    <textarea id="editor" class="form-control @error('body') is-invalid @enderror" name="body" data-ckeditor-profile="editorial-body" required>{{ old('body', $profile->body) }}</textarea>
    @error('body') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
  <div class="col-md-4 form-group">
    <label>Provincia</label>
    <select class="form-control @error('province_id') is-invalid @enderror" name="province_id" required><option value="">Seleccionar</option>@foreach($provincias as $province)<option value="{{ $province->id }}" @selected((int) old('province_id', $profile->province_id) === $province->id)>{{ $province->nombre }}</option>@endforeach</select>
    @error('province_id') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
  <div class="col-md-4 form-group"><label>Localidad</label><select class="form-control select2" name="locality_id"><option value="">Sin localidad</option>@foreach($localities as $locality)<option value="{{ $locality->id }}" @selected((int) old('locality_id', $profile->locality_id) === $locality->id)>{{ $locality->name }}</option>@endforeach</select></div>
  <div class="col-md-4 form-group"><label>Tipo</label><select class="form-control" name="venue_type">@foreach(['penia' => 'Peña', 'centro_cultural' => 'Centro cultural', 'gastronomico_cultural' => 'Gastronómico-cultural', 'otro' => 'Otro'] as $value => $label)<option value="{{ $value }}" @selected(old('venue_type', $profile->venue_type) === $value)>{{ $label }}</option>@endforeach</select></div>
  <div class="col-md-4 form-group"><label>Ciudad</label><input class="form-control" name="city" value="{{ old('city', $profile->city) }}"></div>
  <div class="col-md-4 form-group"><label>Dirección</label><input class="form-control" name="address" value="{{ old('address', $profile->address) }}"></div>
  <div class="col-md-4 form-group"><label>Capacidad</label><input type="number" min="1" class="form-control" name="capacity" value="{{ old('capacity', $profile->capacity) }}"></div>
  <div class="col-md-4 form-group"><label>Teléfono</label><input class="form-control" name="phone" value="{{ old('phone', $profile->phone) }}"></div>
  <div class="col-md-4 form-group"><label>Email</label><input type="email" class="form-control" name="email" value="{{ old('email', $profile->email) }}"></div>
  <div class="col-md-4 form-group"><label>Sitio web</label><input type="url" class="form-control" name="website" value="{{ old('website', $profile->website) }}"></div>
  <div class="col-md-6 form-group"><label>Reservas</label><input type="url" class="form-control" name="reservation_url" value="{{ old('reservation_url', $profile->reservation_url) }}"></div>
  <div class="col-md-6 form-group"><label>Accesibilidad</label><input class="form-control" name="accessibility_notes" value="{{ old('accessibility_notes', $profile->accessibility_notes) }}"></div>
  <div class="col-md-6 form-group"><label>Programación habitual</label><textarea class="form-control" name="regular_events_summary">{{ old('regular_events_summary', $profile->regular_events_summary) }}</textarea></div>
  <div class="col-md-6 form-group"><label>Ingreso y reservas</label><textarea class="form-control" name="admission_notes">{{ old('admission_notes', $profile->admission_notes) }}</textarea></div>
  <div class="col-md-12 form-group">
    <label>Fuentes (una URL por línea)</label>
    <textarea class="form-control @error('source_urls') is-invalid @enderror" name="source_urls" required>{{ $sourceUrls }}</textarea>
    @error('source_urls') <small class="text-danger">{{ $message }}</small> @enderror
  </div>
  <div class="col-md-4 form-group"><label>Estado</label><select class="form-control" name="editorial_status">@foreach(['draft', 'approved', 'published', 'archived'] as $status)<option value="{{ $status }}" @selected(old('editorial_status', $profile->editorial_status) === $status)>{{ $status }}</option>@endforeach</select></div>
  <div class="col-md-4 form-group"><label>Verificación</label><select class="form-control" name="verification_status">@foreach(['pending', 'verified', 'outdated'] as $status)<option value="{{ $status }}" @selected(old('verification_status', $profile->verification_status) === $status)>{{ $status }}</option>@endforeach</select></div>
  <div class="col-md-4 form-group"><label>Fecha de validación</label><input type="datetime-local" class="form-control" name="last_verified_at" value="{{ old('last_verified_at', optional($profile->last_verified_at)->format('Y-m-d\\TH:i')) }}"></div>
  <div class="col-md-6 form-group"><label>Responsable editorial</label><select class="form-control select2" name="verified_by_user_id"><option value="">Seleccionar</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected((int) old('verified_by_user_id', $profile->verified_by_user_id) === $user->id)>{{ $user->name }}</option>@endforeach</select></div>
  <div class="col-md-6 form-group"><label>Método</label><select class="form-control" name="verification_method"><option value="">Seleccionar</option>@foreach(['official_source' => 'Fuente oficial', 'direct_confirmation' => 'Confirmación directa', 'editorial_visit' => 'Visita editorial'] as $value => $label)<option value="{{ $value }}" @selected(old('verification_method', $profile->verification_method) === $value)>{{ $label }}</option>@endforeach</select></div>
  <div class="col-md-6 form-group"><label>SEO title</label><input class="form-control" name="seo_title" value="{{ old('seo_title', $profile->seo_title) }}"></div>
  <div class="col-md-6 form-group"><label>Meta descripción</label><input class="form-control" name="meta_description" value="{{ old('meta_description', $profile->meta_description) }}"></div>
  <div class="col-md-12 form-group"><label>Eventos relacionados</label><select class="form-control select2" name="event_ids[]" multiple>@foreach($events as $event)<option value="{{ $event->id }}" @selected(in_array($event->id, $selectedEvents))>{{ $event->title }}</option>@endforeach</select></div>
</div>
