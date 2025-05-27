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
  <div class="default-news-area">

    {{-- <div class="container-hero">
            <div class="container-fluid">
                <div class="container-title-hero">
                    <div class="content-icon-EH-hero">
                        <img src="images/icono-EH.svg" class="icon-EH-hero">
                    </div>
                    <h1 class="title-hero">Ubicación</h1>
                </div>
            </div>
            <div class="img-hero">
                <div class="img-overlay-contacto"></div>
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3274.7017215509363!2d-58.40766033952137!3d-34.83859134914625!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x95bcd491284448c7%3A0xb78807c351c8daaf!2sPlacaSur%20S.A.!5e0!3m2!1ses!2sar!4v1737387616197!5m2!1ses!2sar"
                    width="100%" height="480" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div> --}}
    <section class="default-news-area">
      <div class="container">

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
      </div>
    </section>

    {{-- Por el momento queda comentado, hasya definir si se ponen los datos de la empresa o no --}}

    {{-- Datos de contacto - opcion con los datos más el formulario --}}
    {{-- <div class="container">
            <div class="row">
                <div class="col-lg-4">
                    <div class="content-colum-contact-izq"> --}}
    {{-- Dirección --}}
    {{-- <p class="mb-3" title="Dirección">
                            <span><i class="fas fa-map-marker-alt text-danger"></i></span>
                            {{ $contact->address }}
                        </p> --}}
    {{-- Email --}}
    {{-- <p class="mb-3" title="Email">
                            <a href="mailto:{{ $contact->email }}">
                                <span><i class="fas fa-envelope text-danger"></i></span>
                                {{ $contact->email }}
                            </a>
                        </p> --}}
    {{-- Teléfono --}}
    {{-- <p class="mb-5" title="Teléfono">
                            <span><i class="fas fa-phone text-danger"></i></span>
                            {{ $contact->phone }}
                        </p> --}}
    {{-- Redes - sociales --}}
    {{-- <p> --}}
    {{-- whatsapp --}}
    {{-- <a href="{{ 'https://wa.me/' . $contact->whatsapp }}" target="_blank" title="Whatsapp"><i
                                    class="fab fa-whatsapp bg-danger rounded-2 text-white p-2 w-8 text-center"></i></a> --}}
    {{-- Facebook --}}
    {{-- <a href="{{ $contact->facebook }}" target="_blank" title="Facebook"><i
                                    class="fab fa-facebook-f bg-danger rounded-2 text-white p-2 w-8 text-center"></i></a> --}}
    {{-- Instagram --}}
    {{-- <a href="{{ $contact->instagram }}" title="Instagram" target="_blank">
                                <i class="fab fa-instagram bg-danger rounded-2 text-white p-2 w-8 text-center"></i>
                            </a> --}}
    {{-- Youtube --}}
    {{-- <a href="{{ $contact->youtube }}" title="Youtube" target="_blank"><i
                                    class="fab fa-youtube bg-danger rounded-2 text-white p-2 w-8 text-center"></i></a>
                        </p> --}}
    {{-- </div> --}}
    {{-- </div> --}}
    {{-- <div class="col-lg-8">
                    <div class="content-colum-contact-der">
                        <form action="{{ route('contacto.store') }}" method="POST" class="row">
                            @csrf

                            <input type="text" name="name" value="{{ old('name') }}"
                                class="form-control contact-content @error('name') is-invalid my-1 @enderror"
                                placeholder="Nombre:" aria-label="Nombre:">
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
                            <select
                                class="form-select contact-content @error('company')
                          is-invalid my-1
                        @enderror"
                                aria-label="Default select example" name="company">
                                <option value="">Empresa:</option>
                                <option value="Distribuidor" @selected(old('company') == 'Distribuidor')>Distribuidor</option>
                                <option value="carpintero" @selected(old('company') == 'Carpintero')>Carpintero</option>
                                <option value="Fabricante" @selected(old('company') == 'Fabricante')>Fabricante</option>
                                <option value="Arquitecto" @selected(old('company') == 'Arquitecto')>Arquitecto</option>
                                <option value="Consumidor final" @selected(old('company') == 'Consumidor final')>Consumidor final
                                </option>
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

                        </form>
                    </div>
                </div> --}}
  </div>
  </div>
  <div class="mt-2 mb-4">
    @error('g-recaptcha-response')
      <p class="text-danger">{{ $message }}</p>
    @enderror
  </div>
  </div>
@endsection
