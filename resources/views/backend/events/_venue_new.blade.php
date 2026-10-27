@once
  {{-- Fondo gris y borde lateral: deja claro, abierto o cerrado, que es otra entidad (un Lugar) y no parte del evento. --}}
  <style>
    .venue-new-box { background: #e9ecef; border-left: 4px solid #6c757d !important; }
    .venue-new-box > summary { cursor: pointer; font-weight: 600; }
  </style>
@endonce
<details class="venue-new-box border rounded p-3 mb-3" @if(old('new_venue.name') || $errors->has('new_venue.*')) open @endif>
  <summary><i class="fas fa-map-marker-alt mr-1"></i> {{ auth()->user()->hasRole('administrador') ? 'Crear un lugar nuevo al guardar' : 'Proponer un lugar nuevo para revisión' }}</summary>
  <p class="text-muted mt-2">Completá estos datos solo si el lugar no existe en el catálogo. El nuevo lugar reemplaza la selección anterior.</p>
  <div class="row">
  @foreach(['name' => 'Nombre', 'address' => 'Dirección', 'city' => 'Localidad'] as $field => $label)<div class="col-md-4 form-group"><label for="new_venue_{{ $field }}">{{ $label }}</label><input id="new_venue_{{ $field }}" name="new_venue[{{ $field }}]" value="{{ old('new_venue.'.$field) }}" class="form-control" maxlength="255"></div>@endforeach
  <div class="col-md-4 form-group"><label for="new_venue_province">Provincia</label><select id="new_venue_province" name="new_venue[province_id]" class="form-control select2"><option value="">Seleccionar</option>@foreach($provincias as $provincia)<option value="{{ $provincia->id }}" @selected(old('new_venue.province_id') == $provincia->id)>{{ $provincia->nombre }}</option>@endforeach</select></div>
  @foreach(['latitude' => 'Latitud', 'longitude' => 'Longitud'] as $field => $label)<div class="col-md-4 form-group"><label for="new_venue_{{ $field }}">{{ $label }} (opcional)</label><input type="number" step="any" id="new_venue_{{ $field }}" name="new_venue[{{ $field }}]" value="{{ old('new_venue.'.$field) }}" class="form-control"></div>@endforeach
  </div>
  @foreach($errors->get('new_venue.*') as $messages) @foreach($messages as $message)<p class="text-danger">{{ $message }}</p>@endforeach @endforeach
</details>
