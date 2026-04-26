@extends('layouts.app')

@section('metaTitle', 'Mis Colaboraciones - Mi Folklore Argentino')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Panel de Colaborador</h1>
        <div class="flex items-center gap-4">
            <span class="bg-orange-100 text-orange-800 px-4 py-2 rounded-full font-semibold">
                Rango: {{ Auth::user()->rank }} ({{ Auth::user()->points }} pts)
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
        <!-- Sidebar de Opciones -->
        <div class="md:col-span-1 space-y-4">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-semibold mb-4 border-b pb-2">Nueva Aportación</h2>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('backend.contributions.create', ['type' => 'interprete']) }}" class="flex items-center gap-2 p-2 hover:bg-orange-50 rounded transition-colors text-gray-700">
                        <span class="bg-orange-500 text-white p-1 rounded">👤</span> Biografía de Artista
                    </a>
                    <a href="{{ route('backend.contributions.create', ['type' => 'noticia']) }}" class="flex items-center gap-2 p-2 hover:bg-orange-50 rounded transition-colors text-gray-700">
                        <span class="bg-blue-500 text-white p-1 rounded">📰</span> Noticia / Evento
                    </a>
                    <a href="{{ route('backend.contributions.create', ['type' => 'cancion']) }}" class="flex items-center gap-2 p-2 hover:bg-orange-50 rounded transition-colors text-gray-700">
                        <span class="bg-green-500 text-white p-1 rounded">🎵</span> Letra de Canción
                    </a>
                    <a href="{{ route('backend.contributions.create', ['type' => 'festival']) }}" class="flex items-center gap-2 p-2 hover:bg-orange-50 rounded transition-colors text-gray-700">
                        <span class="bg-purple-500 text-white p-1 rounded">🎪</span> Festival / Fiesta
                    </a>
                </div>
            </div>
        </div>

        <!-- Lista de Colaboraciones -->
        <div class="md:col-span-3">
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contenido</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($contributions as $contribution)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $contribution->payload['nombre'] ?? ($contribution->payload['titulo'] ?? 'Desconocido') }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $contribution->contributable_id ? 'Sugerencia de edición' : 'Contenido nuevo' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ class_basename($contribution->contributable_type) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'approved' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            'auto-applied' => 'bg-blue-100 text-blue-800'
                                        ];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClasses[$contribution->status] }}">
                                        {{ ucfirst($contribution->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $contribution->created_at->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500 italic">
                                    Aún no has realizado ninguna colaboración. ¡Anímate a participar!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
