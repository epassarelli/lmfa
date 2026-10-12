@extends('layouts.app')

@section('metaTitle', 'Contacto | Mi Folklore Argentino')
@section('metaDescription', 'Escribinos a Mi Folklore Argentino: consultas, sugerencias, correcciones de información, propuestas para publicitar o colaborar con material.')
@section('canonical', \App\Support\CanonicalUrl::normalize(route('contacto')))

@section('content')
  <section class="mb-4 mt-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 md:p-8">
    <header class="mb-6 border-b border-gray-200 pb-5">
      <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#ff661f]">Escribinos</p>
      <h1 class="mt-2 text-3xl font-bold text-gray-900">Contacto</h1>
      <p class="mt-2 max-w-2xl text-base leading-7 text-gray-600">
        Contanos tu consulta, sugerencia o propuesta y te respondemos por email.
        Si querés corregir información de un artista, festival o evento, indicá el enlace de la página en el mensaje.
      </p>
    </header>

    @if (session('contact_success'))
      <div role="status" class="mb-6 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
        {{ session('contact_success') }}
      </div>
    @endif

    @if ($errors->any())
      <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <p class="font-semibold">Revisá estos datos para poder enviar el mensaje:</p>
        <ul class="mt-1 list-disc pl-5">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form action="{{ route('contacto.store') }}" method="POST" class="grid gap-5 md:grid-cols-2" novalidate>
      @csrf
      <input type="hidden" name="form_token" value="{{ $formToken }}">

      {{-- Campo trampa: las personas no lo ven; los bots suelen completarlo. --}}
      <div class="hidden" aria-hidden="true">
        <label for="website">No completar este campo</label>
        <input type="text" name="website" id="website" value="" tabindex="-1" autocomplete="off">
      </div>

      <div class="space-y-5">
        <div>
          <label for="name" class="mb-1 block text-sm font-semibold text-gray-800">Nombre y apellido</label>
          <input type="text" name="name" id="name" value="{{ old('name', $prefillName) }}" maxlength="150" required autocomplete="name"
            @class(['w-full rounded-lg border px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#ff661f]', 'border-red-400' => $errors->has('name'), 'border-gray-300' => ! $errors->has('name')])
            @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
          @error('name') <p id="name-error" class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="email" class="mb-1 block text-sm font-semibold text-gray-800">Email</label>
          <input type="email" name="email" id="email" value="{{ old('email', $prefillEmail) }}" maxlength="255" required autocomplete="email"
            @class(['w-full rounded-lg border px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#ff661f]', 'border-red-400' => $errors->has('email'), 'border-gray-300' => ! $errors->has('email')])
            @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
          @error('email') <p id="email-error" class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="issue" class="mb-1 block text-sm font-semibold text-gray-800">Asunto</label>
          <select name="issue" id="issue" required
            @class(['w-full rounded-lg border bg-white px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#ff661f]', 'border-red-400' => $errors->has('issue'), 'border-gray-300' => ! $errors->has('issue')])
            @if ($errors->has('issue')) aria-invalid="true" aria-describedby="issue-error" @endif>
            <option value="">Elegí un asunto</option>
            @foreach ($subjects as $subject)
              <option value="{{ $subject }}" @selected(old('issue') === $subject)>{{ $subject }}</option>
            @endforeach
          </select>
          @error('issue') <p id="issue-error" class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>
      </div>

      <div class="space-y-5">
        <div>
          <label for="message" class="mb-1 block text-sm font-semibold text-gray-800">Mensaje</label>
          <textarea name="message" id="message" rows="8" required minlength="{{ config('contact.message_min') }}" maxlength="{{ config('contact.message_max') }}"
            @class(['w-full rounded-lg border px-3 py-2 text-gray-900 focus:outline-none focus:ring-2 focus:ring-[#ff661f]', 'border-red-400' => $errors->has('message'), 'border-gray-300' => ! $errors->has('message')])
            @if ($errors->has('message')) aria-invalid="true" aria-describedby="message-error" @endif>{{ old('message') }}</textarea>
          @error('message') <p id="message-error" class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <div>
          <label class="flex items-start gap-2 text-sm text-gray-700">
            <input type="checkbox" name="consent" value="1" required @checked(old('consent')) class="mt-1 h-4 w-4 rounded border-gray-300 text-[#ff661f] focus:ring-[#ff661f]">
            <span>Acepto la <a href="{{ route('legal.privacy') }}" class="font-semibold text-[#ff661f] hover:underline" target="_blank" rel="noopener">política de privacidad</a> y que usen estos datos para responderme.</span>
          </label>
          @error('consent') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="inline-flex items-center rounded-lg bg-[#ff661f] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-[#ff661f] focus:ring-offset-2">
          Enviar mensaje
        </button>
      </div>
    </form>
  </section>
@endsection

@section('sidebar')
  <x-sidebar.newsletter-form />
  <x-sidebar.donate />
  <x-sidebar.social-links />
  <x-sidebar.advertisement />
  <x-sidebar.invite-to-publish />
@endsection
