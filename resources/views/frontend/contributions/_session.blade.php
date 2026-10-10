<fieldset class="border rounded p-3 mb-3" data-index="{{ $index }}"><legend>Jornada</legend>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
<label>Título *<input name="payload[sessions][{{ $index }}][title]" value="{{ $session['title'] ?? '' }}" class="w-full border rounded p-2" required></label>
<label>Inicio *<input type="datetime-local" name="payload[sessions][{{ $index }}][start_at]" value="{{ $session['start_at'] ?? '' }}" class="w-full border rounded p-2" required></label>
<label>Fin (opcional)<input type="datetime-local" name="payload[sessions][{{ $index }}][end_at]" value="{{ $session['end_at'] ?? '' }}" class="w-full border rounded p-2"></label>
</div>
@include('frontend.contributions._artists', ['prefix' => 'payload[sessions]['.$index.'][interprete_ids]', 'selectedArtistIds' => $session['interprete_ids'] ?? []])
<button type="button" class="remove-contribution-session text-red-700 mt-2">Quitar jornada</button>
</fieldset>
