<div>
  <!-- Very little is needed to make a happy life. - Marcus Aurelius -->
  <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2 border-b pb-1 border-gray-200">Suscribite al Newsletter</h3>
    <p class="text-sm text-gray-600 mb-3">Recibí las novedades del folklore argentino directamente en tu correo.</p>
    <form method="POST" action="{{ route('newsletter.subscribe') }}" class="space-y-2">
      @csrf
      <input type="email" name="email" placeholder="Tu correo electrónico"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ff661f]">
      <button type="submit"
        class="bg-[#ff661f] hover:bg-orange-600 text-white text-sm py-2 px-4 rounded-lg w-full transition-colors duration-200">
        Suscribirme
      </button>
    </form>
  </div>

</div>
