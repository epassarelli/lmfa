<div>
    @if (session()->has('message'))
        <div class="bg-green-500 text-white p-3 mb-3">{{ session('message') }}</div>
    @endif

    <form wire:submit.prevent="save" class="space-y-6">
        <div>
            <label for="titulo" class="block font-medium text-gray-700">Título</label>
            <input wire:model="titulo" type="text" id="titulo" name="titulo"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            @error('titulo')
                <p class="text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="noticia" class="block font-medium text-gray-700">Noticia</label>
            <textarea wire:model="noticia" id="noticia" name="noticia"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            @error('noticia')
                <p class="text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="foto" class="block font-medium text-gray-700">Foto</label>
            <input wire:model="foto" type="file" id="foto" name="foto" accept="image/*" class="mt-1">
            @if ($foto and $accion !== 'edit')
                <img src="{{ $foto->temporaryUrl() }}" class="mt-2">
            @endif
            @error('foto')
                <p class="text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="interpretes" class="block font-medium text-gray-700">Intérpretes</label>
            <select wire:model="interpretes" id="interpretes" name="interpretes[]" multiple
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                @foreach ($todos_interpretes as $interprete)
                    <option value="{{ $interprete->id }}">{{ $interprete->interprete }}</option>
                @endforeach
            </select>
            @error('interpretes')
                <p class="text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Guardar
            </button>
        </div>
    </form>
</div>

Pero en la consola me salen varios errores como el siguiente
Livewire: Multiple root elements detected.

No encuentro el error, lo podes identificar?
