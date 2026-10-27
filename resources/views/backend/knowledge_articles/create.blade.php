@extends('adminlte::page')

@section('title', 'Nuevo artículo de enciclopedia')

@section('content_header')
  <x-backend.form-header mode="create">Nuevo artículo de enciclopedia</x-backend.form-header>
@stop

@section('content')
  <div class="row">
    <div class="col-12">
      <form action="{{ route('backend.knowledge-articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="card card-outline card-success">
          <div class="card-header">
            <h3 class="card-title">Enciclopedia del folklore argentino</h3>
          </div>
          <div class="card-body">
            @include('backend.knowledge_articles.form')
          </div>
          <div class="card-footer">
            <x-backend.form-actions :back="route('backend.knowledge-articles.index')" />
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
