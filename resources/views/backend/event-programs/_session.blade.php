<div class="border rounded p-3 mb-3 session-row" data-index="{{ $index }}">
  <div class="d-flex justify-content-between"><h3 class="h6">Jornada</h3><button type="button" class="btn btn-sm btn-outline-danger remove-session">Quitar jornada</button></div>
  <div class="row">
    <div class="col-md-4 form-group"><label>Título de la jornada *</label><input aria-label="Título de la jornada" name="sessions[{{ $index }}][title]" value="{{ $session['title'] ?? '' }}" class="form-control" required maxlength="255" placeholder="Ej.: Noche del viernes"></div>
    <div class="col-md-4 form-group"><label>Inicio *</label><input aria-label="Inicio de la jornada" type="datetime-local" name="sessions[{{ $index }}][start_at]" value="{{ $session['start_at'] ?? '' }}" class="form-control" required></div>
    <div class="col-md-4 form-group"><label>Fin (opcional)</label><input aria-label="Fin de la jornada" type="datetime-local" name="sessions[{{ $index }}][end_at]" value="{{ $session['end_at'] ?? '' }}" class="form-control"></div>
  </div>
  <div class="form-group"><label>Artistas de esta jornada</label><select aria-label="Artistas de la jornada" name="sessions[{{ $index }}][interprete_ids][]" class="form-control session-artists" multiple>@foreach($interpretes as $artist)<option value="{{ $artist->id }}" @selected(in_array($artist->id, $session['interprete_ids'] ?? []))>{{ $artist->interprete }}</option>@endforeach</select></div>
  <details><summary>Entradas o lugar distintos para esta jornada</summary><p class="small text-muted">Dejá vacío para usar los datos comunes. Luego podés editar también el detalle, precio y gratuidad desde la ficha de la jornada.</p><div class="row mt-2">
    <div class="col-md-6 form-group"><label>URL de entradas propia</label><input aria-label="Entradas de la jornada" type="url" name="sessions[{{ $index }}][ticket_url]" value="{{ $session['ticket_url'] ?? '' }}" class="form-control"></div>
    <div class="col-md-6 form-group"><label>Lugar propio</label><select aria-label="Lugar de la jornada" name="sessions[{{ $index }}][venue_id]" class="form-control session-venue"><option value="">Usar lugar común</option>@foreach($venues as $venue)<option value="{{ $venue->id }}" @selected(($session['venue_id'] ?? '') == $venue->id)>{{ $venue->name }} — {{ $venue->city }}</option>@endforeach</select></div>
  </div></details>
</div>
