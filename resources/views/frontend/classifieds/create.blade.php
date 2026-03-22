@extends('layouts.app')
@section('title', 'Publicar Aviso Clasificado | Folklore Argentino')

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 border-b-2 border-[#ff661f] pb-4 inline-block">
        <h1 class="text-3xl font-extrabold text-[#8B4513] tracking-tight">Publicar mi Aviso</h1>
    </div>
    <p class="text-lg text-gray-600 mb-8 border-l-4 border-[#ff661f] pl-4">Tu aviso será revisado y publicado en menos de 24 horas. Es 100% gratis.</p>

    @if($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-r-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Hay errores en el formulario:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('classifieds.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        {{-- Datos del Aviso --}}
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <div class="px-4 py-6 sm:px-8 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-xl font-bold leading-7 text-gray-900 flex items-center">
                    <span class="bg-blue-100 text-blue-800 p-1.5 rounded-lg mr-3">📝</span> Datos del aviso
                </h2>
            </div>
            <div class="px-4 py-6 sm:p-8">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6 mb-2">
                    <div class="sm:col-span-6">
                        <label for="category_id" class="block text-sm font-semibold leading-6 text-gray-900">Categoría <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select name="category_id" id="category_id" required class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6 bg-white">
                                <option value="">Seleccioná una categoría...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->icon ?? '' }} {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="title" class="block text-sm font-semibold leading-6 text-gray-900">Título del aviso <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" name="title" id="title" required maxlength="255" value="{{ old('title') }}" placeholder="Ej: Vendo Bombo Legüero artesanal" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            @error('title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="description" class="block text-sm font-semibold leading-6 text-gray-900">Descripción <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <textarea id="description" name="description" rows="5" required placeholder="Describí tu aviso con todos los detalles relevantes..." class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">{{ old('description') }}</textarea>
                            @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="price" class="block text-sm font-semibold leading-6 text-gray-900">Precio</label>
                        <div class="mt-2 relative rounded-md shadow-sm">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <span class="text-gray-500 sm:text-sm">$</span>
                            </div>
                            <input type="number" name="price" id="price" value="{{ old('price') }}" placeholder="15000" class="block w-full rounded-md border-0 py-2.5 pl-7 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Dejar vacío para "A consultar"</p>
                        @error('price') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="sm:col-span-4">
                        <label for="location" class="block text-sm font-semibold leading-6 text-gray-900">Localidad / Provincia <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" name="location" id="location" required value="{{ old('location') }}" placeholder="Ej: Cosquín, Córdoba" class="block w-full rounded-md border-0 py-2.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            @error('location') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contacto --}}
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <div class="px-4 py-6 sm:px-8 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-xl font-bold leading-7 text-gray-900 flex items-center">
                    <span class="bg-green-100 text-green-800 p-1.5 rounded-lg mr-3">📞</span> Datos de contacto
                </h2>
            </div>
            <div class="px-4 py-6 sm:p-8">
                <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6 mb-2">
                    <div class="sm:col-span-6 border border-blue-100 bg-blue-50 rounded-lg pb-4 px-4 pt-1 mb-2">
                        <div class="mt-4">
                            <label for="contact_info" class="block text-sm font-semibold leading-6 text-gray-900">Email o teléfono principal <span class="text-red-500">*</span></label>
                            <div class="mt-2 mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" /><path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" /></svg>
                                </div>
                                <input type="text" name="contact_info" id="contact_info" required value="{{ old('contact_info') }}" placeholder="mimail@correo.com o teléfono" class="block w-full rounded-md border-0 py-2.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-blue-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                            </div>
                            @error('contact_info') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="sm:col-span-6 border border-green-200 bg-green-50 rounded-lg pb-4 px-4 pt-1">
                        <div class="mt-4">
                            <label for="contact_whatsapp" class="block text-sm font-semibold leading-6 text-green-900">WhatsApp (opcional pero recomendado)</label>
                            <div class="mt-2 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-green-600">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                                </div>
                                <input type="tel" name="contact_whatsapp" id="contact_whatsapp" value="{{ old('contact_whatsapp') }}" placeholder="Ej: 5491151234567" class="block w-full rounded-md border-0 py-2.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-green-400 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-green-600 sm:text-sm sm:leading-6">
                            </div>
                            <p class="mt-2 text-xs text-green-700 font-medium">✨ Los avisos con botón de WhatsApp directo se venden más rápido.</p>
                            @error('contact_whatsapp') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fotos --}}
        <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
            <div class="px-4 py-6 sm:px-8 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-xl font-bold leading-7 text-gray-900 flex items-center">
                    <span class="bg-purple-100 text-purple-800 p-1.5 rounded-lg mr-3">📸</span> Fotos (opcional)
                </h2>
                <p class="mt-1 text-sm leading-6 text-gray-500">Podés subir hasta 5 fotos. Una imagen vale más que mil palabras.</p>
            </div>
            <div class="px-4 py-6 sm:p-8">
                <div class="col-span-full">
                    <div class="mt-2 flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 hover:bg-gray-50 transition">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z" clip-rule="evenodd" />
                            </svg>
                            <div class="mt-4 flex flex-col items-center text-sm leading-6 text-gray-600">
                                <label for="images" class="relative cursor-pointer rounded-md bg-white font-semibold text-blue-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-blue-600 focus-within:ring-offset-2 hover:text-blue-500 mb-2 p-2 border border-blue-200">
                                    <span>Seleccionar archivos de fotos</span>
                                    <input id="images" name="images[]" type="file" class="sr-only" multiple accept="image/*">
                                </label>
                                <p class="pl-1">O arrastrá las fotos aquí</p>
                            </div>
                            <p class="text-xs leading-5 text-gray-600 mt-2">PNG, JPG, GIF hasta 5MB</p>
                        </div>
                    </div>
                    @error('images.*') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-x-6 mt-8">
            <a href="{{ route('classifieds.index') }}" class="text-sm font-semibold leading-6 text-gray-900 px-4 py-2 hover:bg-gray-100 rounded-md transition">Cancelar</a>
            <button type="submit" class="rounded-md bg-[#ff661f] px-8 py-3 text-sm font-bold text-white shadow-sm hover:bg-orange-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 transition-colors shadow-md transform hover:-translate-y-0.5">
                Enviar Aviso para Revisión
            </button>
        </div>

        <div class="mt-6 border-t border-gray-200 pt-6">
            <p class="text-center text-xs text-gray-500">
                Al publicar aceptás nuestros términos de uso. Tu aviso será activado por 30 días, y vas a poder renovarlo cuantas veces quieras gratis desde tu panel de usuario.
            </p>
        </div>
    </form>
</div>
@endsection
