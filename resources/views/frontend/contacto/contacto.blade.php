@extends('layouts.app')



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
              <select
                class="form-select contact-content @error('issue')
                          is-invalid my-1
                        @enderror"
                name="issue" aria-label="Default select example">
                <option value="">Asunto:</option>
                <option value="Consulta General" @selected(old('issue') == 'Consulta General')>Consulta General</option>
                <option value="Sugerencia" @selected(old('issue') == 'Sugerencia')>Sugerencia</option>
                <option value="Reclamo" @selected(old('issue') == 'Reclamo')>Reclamo</option>
                <option value="Quiero publicitar en el sitio" @selected(old('issue') == 'Quiero publicitar en el sitio')>Quiero publicitar en el sitio</option>
                <option value="Me gustaría colaborar con material" @selected(old('issue') == 'Me gustaría colaborar con material')>Me gustaría colaborar con material</option>
                <option value="Otros" @selected(old('issue') == 'Otros')>Otros</option>
              </select>
              <div class="my-2">
                @error('issue')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>
            </div>
          </div>
          <div class="col-lg-6">
            <div class="content-colum-contact-der">
              <textarea
                class="form-control contact-content @error('message')
                          is-invalid my-1
                        @enderror"
                name="message" id="Mensaje" rows="5" placeholder="Mensaje:" aria-label="Mensaje:">{{ old('message') }}</textarea>
              <div class="my-2">
                @error('message')
                  <span class="text-danger">{{ $message }}</span>
                @enderror
              </div>

              <div class="mb-3">
                  <label for="captcha" class="form-label">Para verificar que eres humano, ¿cuánto es 3 + 4?</label>
                  <input type="number" name="captcha" class="form-control contact-content @error('captcha') is-invalid @enderror" placeholder="Respuesta" aria-label="Captcha">
                  @error('captcha')
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
