@extends('adminlte::page')

@section('title', 'Nueva Noticia')

@section('content_header')
    <h1><i class="fas fa-plus-circle mr-2"></i>Crear Nueva Noticia</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-11 mx-auto">
        <form action="{{ route('backend.news.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">Cuerpo de la Noticia</h3>
                </div>
                <div class="card-body">
                    @include('backend.news.form')
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Publicar Noticia
                    </button>
                    <a href="{{ route('backend.news.index') }}" class="btn btn-default float-right">
                        Cancelar
                    </a>
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
