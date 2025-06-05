@extends('layouts.app')

@section('metaTitle', $metaTitle)
@section('metaDescription', $metaDescription)

@section('content')
  <section class="py-8 bg-white">
    <div class="container mx-auto px-4">
      <div class="flex flex-col lg:flex-row gap-8">

        {{-- Sección de contenido principal --}}
        <div class="w-full lg:w-2/3">
          {{-- Aquí irán los bloques de noticias en futuras vistas --}}
        </div>

        {{-- Aside --}}
        <div class="w-full lg:w-1/3">
          <aside class="space-y-10">

            {{-- Eventos destacados --}}
            <section>
              <h3 class="text-xl font-bold border-b pb-2 text-gray-800 mb-4">Eventos destacados</h3>
              {{-- Aquí podés incluir un listado de eventos destacados con tarjetas o links --}}
            </section>

            {{-- Redes sociales --}}
            <section>
              <h3 class="text-xl font-bold border-b pb-2 text-gray-800 mb-4">Seguinos en redes</h3>
              <ul class="space-y-3">
                <li>
                  <a href="#" class="flex items-center text-blue-600 hover:underline">
                    <i class="fab fa-facebook fa-lg mr-2"></i> 120.345 Fans
                  </a>
                </li>
                <li>
                  <a href="#" class="flex items-center text-blue-400 hover:underline">
                    <i class="fab fa-twitter fa-lg mr-2"></i> 25.321 Seguidores
                  </a>
                </li>
                <li>
                  <a href="#" class="flex items-center text-blue-700 hover:underline">
                    <i class="fab fa-linkedin fa-lg mr-2"></i> 7.519 Contactos
                  </a>
                </li>
                <li>
                  <a href="#" class="flex items-center text-red-600 hover:underline">
                    <i class="fab fa-youtube fa-lg mr-2"></i> 101.545 Suscriptores
                  </a>
                </li>
                <li>
                  <a href="#" class="flex items-center text-pink-500 hover:underline">
                    <i class="fab fa-instagram fa-lg mr-2"></i> 10.129 Seguidores
                  </a>
                </li>
                <li>
                  <a href="#" class="flex items-center text-gray-600 hover:underline">
                    <i class="fas fa-rss fa-lg mr-2"></i> 952 Suscriptores
                  </a>
                </li>
              </ul>
            </section>

            {{-- Newsletter --}}
            <section>
              <div class="bg-gray-100 p-4 rounded shadow">
                <h3 class="text-lg font-semibold mb-2">Suscribite al newsletter</h3>
                <p class="text-sm text-gray-600 mb-4">Recibí en tu correo las últimas novedades de folklore argentino.</p>

                <form method="POST" action="#" class="space-y-3">
                  <input type="email" name="email" placeholder="Ingresá tu email" required
                    class="w-full p-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-yellow-400">

                  <button type="submit"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 rounded">
                    Suscribirme
                  </button>
                </form>
              </div>
            </section>

          </aside>
        </div>
      </div>
    </div>
  </section>
@endsection
