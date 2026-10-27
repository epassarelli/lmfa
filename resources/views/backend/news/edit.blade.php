@extends('adminlte::page')

@section('title', 'Editar Noticia')

@section('content_header')
    <x-backend.form-header mode="edit">Editar Noticia: {{ $news->title }}</x-backend.form-header>
@stop

@section('css')
    @include('backend.partials.styles._compact-form')
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <form action="{{ route('backend.news.update', $news) }}" method="POST" enctype="multipart/form-data" class="form-compact">
            @csrf
            @method('PUT')

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">Actualizar Contenido</h3>
                </div>
                <div class="card-body">
                    @include('backend.news.form')
                </div>
                <div class="card-footer">
        <x-backend.form-actions :back="route('backend.news.index')" />
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
    @include('backend.partials.scripts._tooltips')
    <script>
        $(document).ready(function () {
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
            // $('.select2').select2({ theme: 'bootstrap4' }); // Ya manejado por el partial _select2
        });
    </script>
@stop
