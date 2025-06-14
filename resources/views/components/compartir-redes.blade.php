<div>
  <!-- The best way to take care of the future is to take care of the present moment. - Thich Nhat Hanh -->
  @props(['titulo' => '', 'url' => request()->url()])

  <div class="mt-8">
    <h3 class="text-lg font-semibold mb-2 text-gray-800  border-b-2 border-[#ff661f]">Compartir en:</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <!-- Facebook -->
      <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($url) }}" target="_blank"
        class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
          <path
            d="M22 12a10 10 0 10-11.6 9.9v-7H8v-2.9h2.4V9.5c0-2.4 1.4-3.8 3.6-3.8 1 0 2 .1 2 .1v2.2H15c-1.2 0-1.5.7-1.5 1.4v1.8H17l-.4 2.9h-2.1v7A10 10 0 0022 12z" />
        </svg>
        Facebook
      </a>

      <!-- X (Twitter) -->
      <a href="https://twitter.com/intent/tweet?url={{ urlencode($url) }}&text={{ urlencode($titulo) }}" target="_blank"
        class="flex items-center gap-2 px-4 py-2 bg-black text-white rounded hover:bg-gray-800 transition">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
          <path
            d="M22.46 6.01c-.8.36-1.65.61-2.54.72a4.48 4.48 0 001.97-2.48 8.95 8.95 0 01-2.83 1.08A4.48 4.48 0 0016.07 4a4.48 4.48 0 00-4.48 4.48c0 .35.04.7.11 1.03-3.73-.19-7.03-1.97-9.24-4.68a4.44 4.44 0 00-.61 2.25c0 1.56.8 2.93 2.02 3.74a4.47 4.47 0 01-2.03-.56v.06a4.49 4.49 0 003.6 4.4 4.5 4.5 0 01-2.02.08 4.49 4.49 0 004.19 3.12A9 9 0 012 19.54a12.7 12.7 0 006.88 2.01c8.26 0 12.77-6.84 12.77-12.77 0-.2 0-.39-.01-.59a9.12 9.12 0 002.25-2.33z" />
        </svg>
        X (Twitter)
      </a>

      <!-- WhatsApp -->
      <a href="https://api.whatsapp.com/send?text={{ urlencode($titulo . ' ' . $url) }}" target="_blank"
        class="flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 transition">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
          <path
            d="M17.47 14.37c-.27-.13-1.58-.78-1.83-.87s-.43-.13-.61.14-.7.87-.86 1.04-.31.2-.58.07a7.21 7.21 0 01-2.12-1.31 7.88 7.88 0 01-1.44-1.78c-.15-.26 0-.4.11-.53.11-.1.26-.27.4-.4.13-.13.17-.23.26-.38s.04-.28 0-.4c-.06-.13-.6-1.47-.82-2.02-.21-.5-.43-.43-.61-.44h-.52c-.17 0-.45.06-.68.3s-.9.88-.9 2.14 1.04 2.48 1.19 2.65a8.44 8.44 0 003.26 2.57c.46.2.83.3 1.11.38.47.15.9.13 1.23.08.38-.05 1.18-.48 1.35-.94.17-.45.17-.83.13-.9zM12 2A10 10 0 002.05 16.58L2 22l5.56-1.45A10 10 0 1012 2z" />
        </svg>
        WhatsApp
      </a>

      <!-- Email -->
      <a href="mailto:?subject={{ urlencode($titulo) }}&body={{ urlencode($url) }}"
        class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition">
        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
          <path
            d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 2v.01L12 13 4 6.01V6h16zM4 18V8.34l7.38 6.73a1 1 0 001.24 0L20 8.34V18H4z" />
        </svg>
        Email
      </a>
    </div>
  </div>

</div>
