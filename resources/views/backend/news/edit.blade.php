@extends('adminlte::page')

@section('title', 'Editar Noticia')

@section('content_header')
    <h1><i class="fas fa-edit mr-2"></i>Editar Noticia: {{ $news->title }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-11 mx-auto">
        <form action="{{ route('backend.news.update', $news) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">Actualizar Contenido</h3>
                </div>
                <div class="card-body">
                    @include('backend.news.form')
                </div>
                <div class="card-footer text-right">
                    <a href="{{ route('backend.news.index') }}" class="btn btn-default mr-2">
                        Regresar
                    </a>
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fas fa-save mr-1"></i> Actualizar Noticia
                    </button>
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
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });
            // $('.select2').select2({ theme: 'bootstrap4' }); // Ya manejado por el partial _select2
        });
    </script>
@stop
