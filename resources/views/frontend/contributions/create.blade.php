@extends('layouts.app')

@section('metaTitle', 'Colaborar - Mi Folklore Argentino')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <div class="mb-8">
        <a href="{{ route('contributions.index') }}" class="text-orange-600 hover:text-orange-700 font-medium">← Volver al panel</a>
        <h1 class="text-3xl font-bold text-gray-800 mt-2">
            {{ $original ? 'Sugerir Edición' : 'Cargar Nuevo Contenido' }}: {{ ucfirst($type) }}
        </h1>
    </div>

    <form action="{{ route('contributions.store') }}" method="POST" class="bg-white rounded-lg shadow-lg p-8 space-y-6">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        @if($original)
            <input type="hidden" name="id" value="{{ $original->id }}">
        @endif

        @if($type == 'interprete')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Artista / Conjunto</label>
                <input type="text" name="payload[interprete]" value="{{ $original?->interprete }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Biografía</label>
                <textarea id="editor" name="payload[biografia]" rows="10" class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ $original?->biografia }}</textarea>
            </div>
        @elseif($type == 'noticia')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Título de la Noticia / Evento</label>
                <input type="text" name="payload[titulo]" value="{{ $original?->titulo }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cuerpo de la noticia</label>
                <textarea id="editor" name="payload[noticia]" rows="10" class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ $original?->noticia }}</textarea>
            </div>
        @elseif($type == 'cancion')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la Canción</label>
                <input type="text" name="payload[cancion]" value="{{ $original?->cancion }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Letra</label>
                <textarea id="editor" name="payload[letra]" rows="12" class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ $original?->letra }}</textarea>
            </div>
        @elseif($type == 'festival')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Festival</label>
                <input type="text" name="payload[titulo]" value="{{ $original?->titulo }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Detalle del Festival</label>
                <textarea id="editor" name="payload[detalle]" rows="10" class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ $original?->detalle }}</textarea>
            </div>
        @elseif($type == 'show')
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Show / Evento</label>
                <input type="text" name="payload[titulo]" value="{{ $original?->titulo }}" class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Detalles del Show</label>
                <textarea id="editor" name="payload[detalle]" rows="10" class="w-full border-gray-300 rounded-md shadow-sm focus:border-orange-500 focus:ring-orange-500">{{ $original?->detalle }}</textarea>
            </div>
        @endif

        <div class="bg-blue-50 p-4 rounded-md text-sm text-blue-700 italic border border-blue-100">
            <strong>Nota:</strong> Tu contribución será revisada por un moderador antes de hacerse pública. Ganarás puntos de colaborador una vez aprobada.
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="bg-orange-600 text-white px-8 py-3 rounded-md font-bold hover:bg-orange-700 transition-colors shadow-lg">
                Enviar Colaboración
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('editor')) {
            CKEDITOR.replace('editor');
        }
    });
</script>
@endsection
