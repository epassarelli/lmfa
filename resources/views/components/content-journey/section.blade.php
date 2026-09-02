@props(['title'])

<section class="bg-white rounded-xl shadow-sm p-6 mb-6">
  <h2 class="text-xl font-semibold text-slate-900 mb-4">{{ $title }}</h2>
  {{ $slot }}
</section>
