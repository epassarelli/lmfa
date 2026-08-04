@extends('adminlte::page')

@section('title', 'Editar artículo de enciclopedia')

@section('content_header')
  <h1><i class="fas fa-edit mr-2"></i>Editar artículo: {{ $article->title }}</h1>
@stop

@section('content')
  <div class="row">
    <div class="col-md-11 mx-auto">
      <form action="{{ route('backend.knowledge-articles.update', $article) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="card card-outline card-warning">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Actualizar entrada de la Enciclopedia</h3>
            <div>
              <a href="{{ route('backend.knowledge-articles.preview', $article) }}" class="btn btn-info btn-sm" target="_blank">
                <i class="fas fa-external-link-alt mr-1"></i> Vista previa
              </a>
            </div>
          </div>
          <div class="card-body">
            @include('backend.knowledge_articles.form')
          </div>
          <div class="card-footer d-flex justify-content-between">
            <div>
              @if ($article->editorial_status === 'published')
                <form action="{{ route('backend.knowledge-articles.unpublish', $article) }}" method="POST" class="d-inline-block">
                  @csrf
                  <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-eye-slash mr-1"></i> Despublicar
                  </button>
                </form>
              @else
                <form action="{{ route('backend.knowledge-articles.publish', $article) }}" method="POST" class="d-inline-block">
                  @csrf
                  <button type="submit" class="btn btn-success">
                    <i class="fas fa-check mr-1"></i> Publicar
                  </button>
                </form>
              @endif
            </div>
            <div>
              <a href="{{ route('backend.knowledge-articles.index') }}" class="btn btn-default mr-2">Regresar</a>
              <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Actualizar artículo
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
@stop

@section('js')
  @include('backend.partials.scripts._ckeditor')
  @include('backend.partials.scripts._slug')
  @include('backend.partials.scripts._select2')
  <script>
    $(document).ready(function () {
      $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass('selected').html(fileName);
      });
    });
  </script>
@stop
