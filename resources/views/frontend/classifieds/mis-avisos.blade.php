@extends('layouts.app')
@section('title', 'Mis Avisos | Clasificados del Folklore')

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row justify-between items-center mb-8 border-b-2 border-gray-100 pb-4">
        <h1 class="text-3xl font-extrabold text-[#8B4513] mb-4 sm:mb-0">Mis Avisos</h1>
        <a href="{{ route('classifieds.create') }}" class="bg-[#ff661f] hover:bg-orange-600 text-white font-bold py-2 px-6 rounded-md shadow-sm transition flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg>
            Nuevo Aviso
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-8 rounded shadow-sm" role="alert">
            <div class="flex">
                <div class="py-1"><svg class="fill-current h-6 w-6 text-green-500 mr-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z"/></svg></div>
                <div><p>{{ session('success') }}</p></div>
            </div>
        </div>
    @endif

    @if($avisos->isEmpty())
        <div class="text-center py-20 bg-white rounded-xl shadow-sm border border-gray-100">
            <div class="text-6xl mb-4">📭</div>
            <p class="text-xl text-gray-500 mb-6 font-medium">Todavía no publicaste ningún aviso.</p>
            <a href="{{ route('classifieds.create') }}" class="bg-[#ff661f] hover:bg-orange-600 text-white font-bold py-2.5 px-6 rounded inline-block shadow-sm transition">Publicar mi primer aviso</a>
        </div>
    @else
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Aviso</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Categoría</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Estado</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Vence</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">Acciones</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($avisos as $aviso)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                    <div class="font-medium text-gray-900">{{ $aviso->title }}</div>
                                    @if($aviso->moderator_comment)
                                        <div class="text-red-600 text-xs mt-1 font-medium bg-red-50 inline-block px-2 py-0.5 rounded border border-red-100">Motivo: {{ $aviso->moderator_comment }}</div>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 text-gray-700 font-medium">
                                        {{ $aviso->category->icon ?? '' }} {{ $aviso->category->name }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    @switch($aviso->estado)
                                        @case('activo')
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">✅ Activo</span> @break
                                        @case('pendiente')
                                            <span class="inline-flex items-center rounded-md bg-yellow-50 px-2 py-1 text-xs font-medium text-yellow-800 ring-1 ring-inset ring-yellow-600/20">⏳ En revisión</span> @break
                                        @case('rechazado')
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">❌ Rechazado</span> @break
                                        @case('vencido')
                                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">⌛ Vencido</span> @break
                                    @endswitch
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ $aviso->expiration_date ? $aviso->expiration_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    @if($aviso->estado === 'activo')
                                        <a href="{{ route('classifieds.show', $aviso->slug) }}" class="text-blue-600 hover:text-blue-900 border border-blue-200 hover:bg-blue-50 px-3 py-1.5 rounded transition inline-block mr-2" target="_blank">Ver</a>
                                    @endif
                                    
                                    @if(in_array($aviso->estado, ['rechazado', 'vencido']))
                                        <form action="{{ route('classifieds.renovar', $aviso) }}" method="POST" class="inline-block">
                                            @csrf
                                            <button type="submit" class="text-[#ff661f] hover:text-white border border-[#ff661f] hover:bg-[#ff661f] px-3 py-1.5 rounded transition">Renovar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="mt-6">
            {{ $avisos->links() }}
        </div>
    @endif
</div>
@endsection
