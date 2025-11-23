@extends('layouts.app')

{{-- @push('head')

    <script src={{ 'https://www.google.com/recaptcha/api.js?render=' . config('services.recaptcha.site_key') }}></script>

    <script>
        document.addEventListener('submit', (e) => {
            e.preventDefault();
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {
                    action: 'submit'
                }).then(function(token) {
                    let form = e.target;
                    let input = document.createElement('input');
                    input.type = "hidden";
                    input.name = "g-recaptcha-response";
                    input.value = token;

                    form.appendChild(input);
                    form.submit();
                });
            });
        });
    </script>
@endpush --}}

@section('content')

    <section class="bg-white p-2 rounded shadow-sm mt-4 mb-4">
      

        <div class="row">
          <div class="most-popular-news">
            <h2 class="section-title">
              Contacto
            </h2>
          </div>
        </div>

        <form action="{{ route('contacto.store') }}" method="POST" class="row">
          @csrf
          <div class="col-lg-6">
            <div class="content-colum-contact-izq">
              <input type="text" name="name" value="{{ old('name') }}"
                class="form-control contact-content @error('name') is-invalid my-1 @enderror" placeholder="Nombre:"
                aria-label="Nombre:">
              <div class="my-2">
                @error('name')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <input type="text" name="lastName" value="{{ old('lastName') }}"
                class="form-control contact-content @error('lastName')
                          is-invalid my-1
                        @enderror"
                placeholder="Apellido:" aria-label="Apellido:">
              <div class="my-2">
                @error('lastName')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <select
                class="form-select contact-content @error('issue')
                          is-invalid my-1
                        @enderror"
                name="issue" aria-label="Default select example">
                <option value="">Asunto:</option>
                <option value="Consulta" @selected(old('issue') == 'Consulta')>Consulta</option>
                <option value="Presupuesto" @selected(old('issue') == 'Presupuesto')>Presupuesto</option>
                <option value="Reclamo" @selected(old('issue') == 'Reclamo')>Reclamo</option>
                <option value="Otros" @selected(old('issue') == 'Otros')>Otros</option>
              </select>
              <div class="my-2">
                @error('issue')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <input type="text" name="email" value="{{ old('email') }}"
                class="form-control contact-content @error('email')
                          is-invalid my-1
                        @enderror"
                placeholder="Correo electrónico:" aria-label="Correo electrónico:" id="inputEmail4">
              <div class="my-2">
                @error('email')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <input type="text" name="phone" value="{{ old('phone') }}"
                class="form-control contact-content @error('phone')
                          is-invalid my-1
                        @enderror"
                placeholder="Teléfono de contacto:" aria-label="Teléfono de contacto:">
              <div class="my-2">
                @error('phone')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="content-colum-contact-der">
              <select
                class="form-select contact-content @error('company')
                          is-invalid my-1
                        @enderror"
                aria-label="Default select example" name="company">
                <option value="">Empresa:</option>
                <option value="Distribuidor" @selected(old('company') == 'Distribuidor')>Distribuidor</option>
                <option value="Carpintero" @selected(old('company') == 'Carpintero')>Carpintero</option>
                <option value="Fabricante" @selected(old('company') == 'Fabricante')>Fabricante</option>
                <option value="Arquitecto" @selected(old('company') == 'Arquitecto')>Arquitecto</option>
                <option value="Consumidor final" @selected(old('company') == 'Consumidor final')>Consumidor final</option>
              </select>
              <div class="my-2">
                @error('company')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <textarea
                class="form-control contact-content @error('message')
                          is-invalid my-1
                        @enderror"
                name="message" id="Mensaje" rows="8" placeholder="Mensaje:" aria-label="Mensaje:">{{ old('message') }}</textarea>
              <div class="my-2">
                @error('message')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
              <button type="submit" class="btn btn-primary btn-contact">Enviar</button>
            </div>
          </div>
        </form>
      
    </section>

@endsection

@section('sidebar')
  {{-- <x-sidebar.newsletter-form /> --}}
  <x-sidebar.donate />
  <x-sidebar.social-links />
  {{-- 
      <x-sidebar.top-news :noticias="$noticiasMasLeidas" />
      <x-sidebar.upcoming-shows :eventos="$eventosSidebar" />
      <x-sidebar.artist-of-the-month :artista="$artistaDelMes" /> --}}
  <x-sidebar.advertisement />
  <x-sidebar.invite-to-publish />
@endsection
