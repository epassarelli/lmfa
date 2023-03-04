<div>

    {{-- Nothing in the world is as soft and yielding as water. --}}
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Gestión de gacetillas de prensa') }}
        </h2>
    </x-slot> --}}



    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Gestión de gacetillas de prensa') }}
                </h2>

                @if ($modal)

                    @include('livewire.backend.noticias-form')
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-3">
                        <div></div>
                        <div class="py-3">
                            <x-jet-input type="text" placeholder="Texto a buscar" wire:model="search" class="w-full" />
                        </div>
                        <div class="flex justify-end">
                            <button wire:click="create()"
                                class="bg-green-500 hover:bg-green-600 text-white font-bold rounded-md py-2 px-4 my-3">+
                                Nueva gacetilla</button>
                        </div>
                    </div>




                    <table class="table-auto w-full">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700">
                                <th class="cursor-pointer px-4 py-2" wire:click="order('id')">Id
                                    {{-- -- Ordenar -- --}}
                                    @if ($sort == 'id')
                                        @if ($order == 'asc')
                                            <i class="fas fa-sort-alpha-up-alt float-right mt-1"></i>
                                        @else
                                            <i class="fas fa-sort-alpha-down-alt float-right mt-1"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort float-right mt-1"></i>
                                    @endif
                                </th>
                                <th class="cursor-pointer px-4 py-2">Imagen</th>
                                <th class="cursor-pointer px-4 py-2" wire:click="order('titulo')">Titulo
                                    {{-- -- Ordenar -- --}}
                                    @if ($sort == 'titulo')
                                        @if ($order == 'asc')
                                            <i class="fas fa-sort-alpha-up-alt float-right mt-1"></i>
                                        @else
                                            <i class="fas fa-sort-alpha-down-alt float-right mt-1"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort float-right mt-1"></i>
                                    @endif
                                </th>

                                <th class="cursor-pointer px-4 py-2" wire:click="order('publicar')">Publicar
                                    {{-- -- Ordenar -- --}}
                                    @if ($sort == 'publicar')
                                        @if ($order == 'asc')
                                            <i class="fas fa-sort-alpha-up-alt float-right mt-1"></i>
                                        @else
                                            <i class="fas fa-sort-alpha-down-alt float-right mt-1"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort float-right mt-1"></i>
                                    @endif
                                </th>

                                <th class="cursor-pointer px-4 py-2"">Interpretes</th>
                                <th class="cursor-pointer px-4 py-2"">Usuario</th>
                                <th class="cursor-pointer px-4 py-2" wire:click="order('estado')">Estado
                                    {{-- -- Ordenar -- --}}
                                    @if ($sort == 'estado')
                                        @if ($order == 'asc')
                                            <i class="fas fa-sort-alpha-up-alt float-right mt-1"></i>
                                        @else
                                            <i class="fas fa-sort-alpha-down-alt float-right mt-1"></i>
                                        @endif
                                    @else
                                        <i class="fas fa-sort float-right mt-1"></i>
                                    @endif
                                </th>
                                <th class="px-4 py-2">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($noticias as $noticia)
                                <tr>
                                    <td class="border px-4 py-2">{{ $noticia->id }}</td>
                                    <td class="border px-4 py-2">
                                        {{-- <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full"
                                                src="{{ asset('storage/noticias/' . $noticia->foto) }}" alt="">
                                        </div> --}}
                                        {{ $noticia->id }}
                                    </td>
                                    <td class="border px-4 py-2">{{ $noticia->titulo }}</td>

                                    <td class="border px-4 py-2">{{ $noticia->publicar }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @foreach ($noticia->interpretes as $interprete)
                                            <span
                                                class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700 mr-2 mb-2">{{ $interprete->interprete }}</span>
                                        @endforeach
                                    </td>
                                    <td class="border px-4 py-2">{{ $noticia->user->name }}</td>
                                    <td class="border px-4 py-2 text-center">
                                        <livewire:toggle-button :model="$noticia" field="estado"
                                            key="{{ $noticia->id }}" />
                                    </td>
                                    <td class="border px-4 py-2 text-center">
                                        <button wire:click="edit({{ $noticia->id }})"
                                            class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4">Editar</button>
                                        <button wire:click="$emit('alertDelete',{{ $noticia->id }})"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4">Borrar</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{ $noticias->links() }}



                @endif

            </div>
        </div>
    </div>





    @push('scripts')
        <script src="{{ asset('vendor/ckeditor5/ckeditor.js') }}"></script>
        <script>
            ClassicEditor.create(document.querySelector('#noticia'))
                .then(editor => {
                    editor.model.document.on('change:data', () => {
                        Livewire.emit('noticiaUpdated', editor.getData());
                    });
                })
                .catch(error => {
                    console.error(error);
                });
        </script>
    @endpush


</div>
