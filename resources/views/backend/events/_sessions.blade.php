<div class="card"><div class="card-header"><h2 class="card-title">Jornadas del evento</h2></div><div class="card-body">
  <p>Cargá al menos dos jornadas. Cada una tiene sus artistas y horarios; una noche puede terminar al día siguiente.</p>
  <div id="sessions">@foreach(old('sessions', [['title' => '', 'start_at' => ''], ['title' => '', 'start_at' => '']]) as $index => $session) @include('backend.event-programs._session') @endforeach</div>
  <button type="button" id="add-session" class="btn btn-outline-primary">Agregar jornada</button>
  <template id="session-template">@include('backend.event-programs._session', ['index' => '__INDEX__', 'session' => []])</template>
</div></div>
