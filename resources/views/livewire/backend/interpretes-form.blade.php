<form wire:submit.prevent="save" class="space-y-6">
  <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-3">
    <div>
      <label for="interprete" value="{{ __('Intérprete') }}" />
      <input id="interprete" type="text" class="block w-full mt-1" wire:model.defer="interprete" autofocus />
      <input-error for="interprete" class="mt-2" />
    </div>
    <div>
      <label for="slug" value="{{ __('Slug') }}" />
      <input id="slug" type="text" class="block w-full mt-1" wire:model.defer="slug" />
      <input-error for="slug" class="mt-2" />
    </div>
    <div>
      <label for="foto" value="{{ __('Foto') }}" />
      <input type="file" class="block w-full mt-1" wire:model="foto">
      <input-error for="foto" class="mt-2" />
      @if ($foto)
        <img src="{{ $foto->temporaryUrl() }}" class="mt-2 rounded-lg shadow-md">
      @endif
    </div>
  </div>



  <div class="mt-4">
    <label for="biografia" value="{{ __('Biografía') }}" />
    <textarea id="biografia" class="block w-full mt-1" wire:model.defer="biografia"></textarea>
    <input-error for="biografia" class="mt-2" />
  </div>

  <div class="grid grid-cols-1 gap-6 mt-4 md:grid-cols-3">
    <div>
      <label for="visitas" value="{{ __('Visitas') }}" />
      <input id="visitas" type="number" class="block w-full mt-1" wire:model.defer="visitas" />
      <input-error for="visitas" class="mt-2" />
    </div>
    <div>
      <label for="publicar" value="{{ __('Publicar') }}" />
      <input id="publicar" type="datetime-local" class="block w-full mt-1" wire:model.defer="publicar" />
      <input-error for="publicar" class="mt-2" />
    </div>
    <div>
      <label for="estado" value="{{ __('Estado') }}" />
      <select id="estado" class="block w-full mt-1" wire:model.defer="estado">
        <option value="0">Borrador</option>
        <option value="1">Publicado</option>
      </select>
      <input-error for="estado" class="mt-2" />
    </div>

  </div>

  {{-- <input type="hidden" wire:model.defer="user_id"> --}}
  <div class="flex items-center justify-end mt-4">

    <a href="/admin/interpretes" class="bg-gray-300 hover:bg-gray-600 font-bold py-2 px-4">Volver al
      listado</a>

    <button class="ml-4">
      {{ __('Guardar') }}
    </button>
  </div>
</form>
