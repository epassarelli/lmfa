@extends('layouts.app')
@section('title', $classified->title . ' | Clasificados del Folklore')
@section('meta_description', Str::limit($classified->description, 160))

@section('content')
<div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        {{-- Aviso principal --}}
        <div class="lg:col-span-8">
            <nav class="flex text-sm text-gray-500 mb-6" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('home') }}" class="hover:text-gray-900 transition-colors">Inicio</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="mx-2">/</span>
                            <a href="{{ route('classifieds.index') }}" class="hover:text-gray-900 transition-colors">Clasificados</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <span class="mx-2">/</span>
                            <a href="{{ route('classifieds.index', ['categoria' => $classified->category->slug]) }}" class="hover:text-gray-900 transition-colors">{{ $classified->category->name }}</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <span class="mx-2">/</span>
                            <span class="text-gray-800 font-medium truncate max-w-[200px] md:max-w-xs">{{ $classified->title }}</span>
                        </div>
                    </li>
                </ol>
            </nav>

            @if($classified->is_featured)
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-2 text-sm font-semibold inline-flex items-center rounded mb-4">
                    <span class="mr-2">⭐</span> Aviso Destacado
                </div>
            @endif

            <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-3 leading-tight">{{ $classified->title }}</h1>
            <div class="flex flex-wrap items-center text-sm text-gray-500 gap-y-2 mb-6">
                <span class="flex items-center mr-4">📂 <span class="ml-1">{{ $classified->category->name }}</span></span>
                @if($classified->location)
                    <span class="flex items-center mr-4">📍 <span class="ml-1">{{ $classified->location }}</span></span>
                @endif
                <span class="flex items-center">🕐 <span class="ml-1">{{ $classified->created_at->diffForHumans() }}</span></span>
            </div>

            {{-- Galería --}}
            @if($classified->images->isNotEmpty())
                <div x-data="{ activeSlide: 0 }" class="mb-8 relative rounded-xl overflow-hidden shadow-md bg-black">
                    <div class="relative h-64 sm:h-80 md:h-[450px]">
                        @foreach($classified->images as $i => $img)
                            <div x-show="activeSlide === {{ $i }}" class="absolute inset-0 transition-opacity duration-500" {{ $i !== 0 ? 'style="display: none;"' : '' }}>
                                <x-optimized-image :image="$img" variant="detail" class="w-full h-full object-contain" />
                            </div>
                        @endforeach
                    </div>
                    
                    @if($classified->images->count() > 1)
                        <button @click="activeSlide = activeSlide === 0 ? {{ $classified->images->count() - 1 }} : activeSlide - 1" class="absolute top-1/2 left-4 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white w-10 h-10 flex items-center justify-center rounded-full transition focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <button @click="activeSlide = activeSlide === {{ $classified->images->count() - 1 }} ? 0 : activeSlide + 1" class="absolute top-1/2 right-4 -translate-y-1/2 bg-black bg-opacity-50 hover:bg-opacity-75 text-white w-10 h-10 flex items-center justify-center rounded-full transition focus:outline-none">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                        
                        <div class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
                            @foreach($classified->images as $i => $img)
                                <button @click="activeSlide = {{ $i }}" :class="{'bg-white': activeSlide === {{ $i }}, 'bg-white bg-opacity-50': activeSlide !== {{ $i }}}" class="w-3 h-3 rounded-full transition focus:outline-none"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="bg-gray-100 rounded-xl flex items-center justify-center mb-8 shadow-inner" style="height:300px; font-size:6rem;">
                    {{ $classified->category->icon ?? '📦' }}
                </div>
            @endif

            {{-- Descripción --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 mb-8 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-xl font-bold text-gray-800">Descripción del Aviso</h2>
                </div>
                <div class="p-6">
                    <div class="prose max-w-none text-gray-700 whitespace-pre-wrap">{{ $classified->description }}</div>
                </div>
            </div>

            {{-- Tags --}}
            @if($classified->tags->isNotEmpty())
                <div class="mb-10 flex flex-wrap gap-2">
                    @foreach($classified->tags as $tag)
                        <span class="bg-gray-100 text-gray-600 border border-gray-200 px-3 py-1 rounded-full text-sm font-medium">#{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif

            {{-- Relacionados --}}
            @if($related->isNotEmpty())
                <div class="mt-12 border-t border-gray-200 pt-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">Otros avisos en {{ $classified->category->name }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach($related as $r)
                            <a href="{{ route('classifieds.show', $r->slug) }}" class="group bg-white rounded-lg shadow-sm hover:shadow-md border border-gray-100 overflow-hidden transition block">
                                <div class="h-28 bg-gray-100 relative overflow-hidden">
                                    @if($r->images->isNotEmpty())
                                        <x-optimized-image :image="$r->images->first()" variant="card" class="w-full h-full object-cover group-hover:scale-105 transition duration-500" />
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-4xl opacity-50 group-hover:scale-110 transition duration-500">
                                            {{ $r->category->icon ?? '📦' }}
                                        </div>
                                    @endif
                                </div>
                                <div class="p-3">
                                    <h4 class="text-sm font-semibold text-gray-800 line-clamp-2 group-hover:text-blue-600 transition">{{ $r->title }}</h4>
                                    @if($r->price)
                                        <p class="text-green-600 font-bold text-xs mt-1">$ {{ is_numeric($r->price) ? number_format((float)$r->price, 0, ',', '.') : $r->price }}</p>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar: datos de contacto --}}
        <div class="lg:col-span-4 mt-8 lg:mt-0">
            <div class="bg-white rounded-xl shadow-md border border-gray-100 sticky top-[100px] overflow-hidden">
                <div class="bg-[#ff661f] h-2 w-full"></div>
                <div class="p-6">
                    @if($classified->price)
                        <div class="mb-6 bg-green-50 text-green-700 px-4 py-3 rounded-lg border border-green-100">
                            <span class="text-sm block mb-1 font-medium text-green-600 uppercase tracking-wide">Precio Total</span>
                            <span class="text-3xl font-extrabold">$ {{ is_numeric($classified->price) ? number_format((float)$classified->price, 0, ',', '.') : $classified->price }}</span>
                        </div>
                    @else
                        <div class="mb-6 bg-gray-50 text-gray-700 px-4 py-3 rounded-lg border border-gray-100 text-center">
                            <span class="text-lg font-bold">Precio a consultar</span>
                        </div>
                    @endif

                    <h3 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 mb-4 flex items-center">
                        <span class="mr-2">📞</span> Contactar al vendedor
                    </h3>

                    @if($classified->contact_info)
                        <div class="mb-4 flex items-start">
                            <div class="text-gray-400 mt-0.5 mr-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <span class="block text-sm text-gray-500 font-medium">Email / Contacto</span>
                                @if(str_contains($classified->contact_info, '@'))
                                    <a href="mailto:{{ $classified->contact_info }}" class="text-blue-600 font-semibold hover:underline bg-blue-50 px-2 py-1 rounded inline-block mt-1">{{ $classified->contact_info }}</a>
                                @else
                                    <span class="text-gray-900 font-semibold text-lg">{{ $classified->contact_info }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if($classified->contact_whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $classified->contact_whatsapp) }}?text=Hola, vi tu aviso '{{ urlencode($classified->title) }}' en Mi Folklore Argentino"
                           class="w-full bg-[#25D366] hover:bg-[#128c7e] text-white font-bold py-3 px-4 rounded-lg shadow inline-flex items-center justify-center transition mt-2 mb-2" target="_blank">
                            <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            Enviar WhatsApp
                        </a>
                    @endif

                    <div class="mt-6 pt-4 border-t border-gray-100">
                        <div class="bg-gray-50 rounded p-4 text-sm text-gray-600">
                            <div class="flex justify-between mb-2">
                                <span class="font-medium">Publicado:</span>
                                <span>{{ $classified->created_at->format('d/m/Y') }}</span>
                            </div>
                            @if($classified->expiration_date)
                                <div class="flex justify-between mb-2">
                                    <span class="font-medium">Vencimiento:</span>
                                    <span>{{ $classified->expiration_date->format('d/m/Y') }}</span>
                                </div>
                            @endif
                            @if($classified->user)
                                <div class="flex justify-between">
                                    <span class="font-medium">Vendedor:</span>
                                    <span class="font-semibold">{{ $classified->user->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
