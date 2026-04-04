<div>
  <div class="bg-white p-4 rounded-2xl shadow-sm mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2 border-b pb-1 border-gray-200">Suscribite al Newsletter</h3>
    
    @if(session('newsletter_success'))
      <div class="bg-green-100 text-green-800 p-2 rounded text-sm mb-3">
        {{ session('newsletter_success') }}
      </div>
    @elseif(session('newsletter_info'))
      <div class="bg-blue-100 text-blue-800 p-2 rounded text-sm mb-3">
        {{ session('newsletter_info') }}
      </div>
    @else
      <p class="text-sm text-gray-600 mb-3">Recibí las novedades del folklore argentino directamente en tu correo.</p>
    @endif

    <form method="POST" action="{{ route('newsletter.subscribe') }}" class="space-y-2">
      @csrf
      <input type="hidden" name="source" value="sidebar">
      <input type="email" name="email" placeholder="Tu correo electrónico" required
        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#ff661f]">
      @error('email')
        <span class="text-red-500 text-xs">{{ $message }}</span>
      @enderror
      <button type="submit"
        class="bg-[#ff661f] hover:bg-orange-600 text-white text-sm py-2 px-4 rounded-lg w-full transition-colors duration-200">
        Suscribirme
      </button>
    </form>
  </div>
</div>
