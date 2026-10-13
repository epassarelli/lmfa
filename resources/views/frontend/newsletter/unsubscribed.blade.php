@extends('layouts.app')

@section('metaTitle', 'Baja del Newsletter')
@section('metaDescription', 'Cancelar la suscripción al newsletter semanal del folklore argentino.')
@section('metaRobots', 'noindex,nofollow')

@section('content')
  <div class="max-w-3xl mx-auto px-4 py-16 text-center mt-12 mb-12">
    <h1 class="text-3xl font-bold mb-6 text-gray-800 border-b-2 border-[#ff661f] inline-block pb-2">Newsletter</h1>

    <div class="bg-white p-8 rounded-lg shadow-sm border border-gray-100 mt-6">
      @if ($token)
        <p class="text-xl text-gray-700 leading-relaxed">¿Querés darte de baja del newsletter semanal?</p>
        <form method="POST" action="{{ route('newsletter.unsubscribe.perform', $token) }}" class="mt-8">
          <button type="submit" class="inline-block bg-[#ff661f] hover:bg-orange-600 text-white font-semibold py-3 px-8 rounded-lg transition-colors duration-200">
            Sí, darme de baja
          </button>
        </form>
      @else
        <div class="text-5xl mb-6">{{ $done ? '📬' : '⚠️' }}</div>
        <p class="text-xl text-gray-700 leading-relaxed">{{ $message }}</p>
      @endif

      <div class="mt-10">
        <a href="{{ route('home') }}" class="inline-block text-[#ff661f] font-semibold hover:underline">Volver al inicio</a>
      </div>
    </div>
  </div>
@endsection

@section('sidebar')
  <x-sidebar.social-links />
  <x-sidebar.advertisement />
  <x-sidebar.invite-to-publish />
@endsection
