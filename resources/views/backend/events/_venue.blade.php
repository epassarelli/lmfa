@php $selectedVenue = old('venue_id', $locationEntity->venue_id); @endphp
<div class="form-group">
  <label for="venue_id">Sala / Lugar</label>
  <select id="venue_id" name="venue_id" class="form-control select2" data-placeholder="Buscar un lugar">
    <option value="">Sin lugar del catálogo</option>
    @foreach($venues as $venue)<option value="{{ $venue->id }}" @selected($selectedVenue == $venue->id) data-city="{{ $venue->city }}" data-address="{{ $venue->address }}" data-province="{{ $venue->province_id }}">{{ $venue->name }} — {{ $venue->city }} / {{ $venue->provincia?->nombre }}{{ $venue->status !== 'active' ? ' ('.$venue->status.')' : '' }}</option>@endforeach
    @if($locationEntity->venue_id && !$venues->contains('id', $locationEntity->venue_id))
      <option value="{{ $locationEntity->venue_id }}" selected>{{ $locationEntity->venue?->name }} (lugar ya vinculado)</option>
    @endif
  </select>
  <small class="form-text text-muted">La ubicación se toma del catálogo. @if(auth()->user()->hasRole('administrador'))<a href="{{ route('backend.venues.index') }}" target="_blank" rel="noopener">Administrar Salas / Lugares</a>@endif</small>
  @error('venue_id')<small class="text-danger">{{ $message }}</small>@enderror
</div>
<details class="border rounded p-3 mb-3" @if(old('new_venue.name') || $errors->has('new_venue.*')) open @endif>
  <summary>{{ auth()->user()->hasRole('administrador') ? 'Crear un lugar nuevo al guardar' : 'Proponer un lugar nuevo para revisión' }}</summary>
  <p class="text-muted mt-2">Completá estos datos solo si el lugar no existe en el catálogo. El nuevo lugar reemplaza la selección anterior.</p>
  <div class="row">
  @foreach(['name' => 'Nombre', 'address' => 'Dirección', 'city' => 'Localidad'] as $field => $label)<div class="col-md-4 form-group"><label for="new_venue_{{ $field }}">{{ $label }}</label><input id="new_venue_{{ $field }}" name="new_venue[{{ $field }}]" value="{{ old('new_venue.'.$field) }}" class="form-control" maxlength="255"></div>@endforeach
  <div class="col-md-4 form-group"><label for="new_venue_province">Provincia</label><select id="new_venue_province" name="new_venue[province_id]" class="form-control select2"><option value="">Seleccionar</option>@foreach($provincias as $provincia)<option value="{{ $provincia->id }}" @selected(old('new_venue.province_id') == $provincia->id)>{{ $provincia->nombre }}</option>@endforeach</select></div>
  @foreach(['latitude' => 'Latitud', 'longitude' => 'Longitud'] as $field => $label)<div class="col-md-4 form-group"><label for="new_venue_{{ $field }}">{{ $label }} (opcional)</label><input type="number" step="any" id="new_venue_{{ $field }}" name="new_venue[{{ $field }}]" value="{{ old('new_venue.'.$field) }}" class="form-control"></div>@endforeach
  </div>
  @foreach($errors->get('new_venue.*') as $messages) @foreach($messages as $message)<p class="text-danger">{{ $message }}</p>@endforeach @endforeach
</details>
