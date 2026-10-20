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
@include('backend.events._venue_new')
