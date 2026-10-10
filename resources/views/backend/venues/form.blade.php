@extends('adminlte::page')
@section('title', $venue->exists ? 'Editar lugar' : 'Nuevo lugar')
@section('content_header')<h1>{{ $venue->exists ? 'Editar lugar' : 'Nuevo lugar' }}</h1>@stop
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ $venue->exists ? route('backend.venues.update', $venue) : route('backend.venues.store') }}" class="card">
@csrf @if($venue->exists) @method('PUT') @endif
<div class="card-body">
  <p class="text-muted">Las correcciones de ubicación se reflejan en los eventos vinculados. Para cambiar la sede de una jornada, editá esa jornada. Los lugares inactivos conservan sus vínculos.</p>
  <div class="row">
  @foreach(['name' => 'Nombre *', 'address' => 'Dirección', 'city' => 'Localidad *'] as $field => $label)
  <div class="col-md-4 form-group"><label for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}" name="{{ $field }}" class="form-control" value="{{ old($field, $venue->$field) }}" maxlength="255" @required(in_array($field, ['name', 'city']))></div>
  @endforeach
  <div class="col-md-4 form-group"><label for="province_id">Provincia *</label><select id="province_id" name="province_id" class="form-control select2" required><option value="">Seleccionar</option>@foreach($provincias as $provincia)<option value="{{ $provincia->id }}" @selected(old('province_id', $venue->province_id) == $provincia->id)>{{ $provincia->nombre }}</option>@endforeach</select></div>
  @foreach(['latitude' => 'Latitud', 'longitude' => 'Longitud'] as $field => $label)<div class="col-md-4 form-group"><label for="{{ $field }}">{{ $label }}</label><input type="number" step="any" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $venue->$field) }}" class="form-control" min="{{ $field === 'latitude' ? -90 : -180 }}" max="{{ $field === 'latitude' ? 90 : 180 }}"></div>@endforeach
  @foreach(['phone' => 'Teléfono público', 'website' => 'Sitio web'] as $field => $label)<div class="col-md-4 form-group"><label for="{{ $field }}">{{ $label }}</label><input type="{{ $field === 'website' ? 'url' : 'text' }}" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $venue->$field) }}" class="form-control"></div>@endforeach
  <div class="col-md-4 form-group"><label for="status">Estado</label><select id="status" name="status" class="form-control">@foreach(['active' => 'Activo', 'pending' => 'Pendiente de revisión', 'inactive' => 'Inactivo'] as $value => $label)<option value="{{ $value }}" @selected(old('status', $venue->status ?? 'active') === $value)>{{ $label }}</option>@endforeach</select></div>
  </div>
  <div class="form-group"><label for="description">Descripción</label><textarea id="description" name="description" class="form-control" rows="3">{{ old('description', $venue->description) }}</textarea></div>
</div><div class="card-footer"><button class="btn btn-primary">Guardar lugar</button><a href="{{ route('backend.venues.index') }}" class="btn btn-default">Volver</a></div>
</form>
@stop
@section('js') @include('backend.partials.scripts._select2') @stop
