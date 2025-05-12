@extends('adminlte::page')

@section('title', 'Editar Noticia')

@section('content_header')
  <h1>Editar Noticia</h1>
@endsection

@section('content')
  <form action="{{ route('backend.noticias.update', $noticia->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @php($action = 'edit')

    <div class="card">
      <div class="card-body">
        @include('backend.noticias.form')
      </div>
      <div class="card-footer">
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar</button>
        <a href="{{ route('backend.noticias.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i>
          Cancelar</a>
      </div>
    </div>
  </form>
@endsection

@section('js')
  @include('backend.partials.scripts._ckeditor')
  @include('backend.partials.scripts._slug')
  @include('backend.partials.scripts._select2')
@endsection
