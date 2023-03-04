<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">





            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Gestión de interpretes') }}
            </h2>

            @if (session()->has('message'))
                <div class="bg-green-500 text-white p-3 mb-3">{{ session('message') }}</div>
            @endif

            <div>
                {{ auth()->user() }}
                {{ auth()->user()->name }}
            </div>

            @if ($modal)
                @include('livewire.backend.interpretes-form')
            @else
                <div class="grid grid-cols-1 sm:grid-cols-3">
                    <div></div>
                    <div class="py-3">
                        <x-jet-input type="text" placeholder="Texto a buscar" wire:model="search" class="w-full" />
                    </div>
                    <div class="flex justify-end">
                        <button wire:click="create()"
                            class="bg-green-500 hover:bg-green-600 text-white font-bold rounded-md py-2 px-4 my-3">+
                            Nuevo interprete</button>
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
                            <th class="cursor-pointer px-4 py-2" wire:click="order('interprete')">Interprete
                                {{-- -- Ordenar -- --}}
                                @if ($sort == 'interprete')
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
                        @foreach ($interpretes as $interprete)
                            <tr>
                                <td class="border px-4 py-2">{{ $interprete->id }}</td>
                                <td class="border px-4 py-2">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <img class="h-10 w-10 rounded-full"
                                            src="{{ asset('storage/interpretes/' . $interprete->foto) }}"
                                            alt="">
                                    </div>
                                </td>
                                <td class="border px-4 py-2">{{ $interprete->interprete }}</td>

                                <td class="border px-4 py-2">{{ date('Y-m-d', strtotime($interprete->publicar)) }}</td>
                                <td class="border px-4 py-2 text-center">
                                    {{-- <livewire:toggle-button :model="$interprete" field="estado"
                                        key="{{ $interprete->id }}" /> --}}
                                    {{-- @livewire(
                                        'toggle-button',
                                        [
                                            'model' => $interprete,
                                            'field' => 'estado',
                                        ],
                                        key($interprete->id)
                                    ) --}}

                                </td>
                                <td class="border px-4 py-2 text-center">
                                    <button wire:click="edit({{ $interprete->id }})"
                                        class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4">Editar</button>
                                    <button wire:click="$emit('alertDelete',{{ $interprete->id }})"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4">Borrar</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="py-3">
                    {{ $interpretes->links() }}
                </div>

            @endif





        </div>
    </div>
</div>
