<div class="border rounded p-3 mb-3" data-slot-row>
  <input type="hidden" name="slots[{{ $i }}][id]" value="{{ $slot['id'] ?? '' }}">
  <div class="row">
    <div class="col-md-3 form-group"><label>Día</label><select class="form-control" name="slots[{{ $i }}][weekday]"><option value="">Seleccionar</option>@foreach(['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'] as $day => $label)<option value="{{ $day }}" @selected((string) ($slot['weekday'] ?? '') === (string) $day)>{{ $label }}</option>@endforeach</select></div>
    <div class="col-md-2 form-group"><label>Desde</label><input type="time" class="form-control" name="slots[{{ $i }}][starts_at]" value="{{ isset($slot['starts_at']) ? substr($slot['starts_at'], 0, 5) : '' }}"></div>
    <div class="col-md-2 form-group"><label>Hasta</label><input type="time" class="form-control" name="slots[{{ $i }}][ends_at]" value="{{ isset($slot['ends_at']) ? substr($slot['ends_at'], 0, 5) : '' }}"></div>
    <div class="col-md-3 form-group"><label>Zona horaria</label><input class="form-control" name="slots[{{ $i }}][timezone]" value="{{ $slot['timezone'] ?? 'America/Argentina/Buenos_Aires' }}"></div>
    <div class="col-md-2 form-group d-flex align-items-end justify-content-between"><div><input type="hidden" name="slots[{{ $i }}][is_active]" value="0"><label><input type="checkbox" name="slots[{{ $i }}][is_active]" value="1" @checked((bool) ($slot['is_active'] ?? true))> Activa</label></div><button class="btn btn-outline-danger btn-sm" type="button" data-remove-slot>Quitar</button></div>
  </div>
</div>
