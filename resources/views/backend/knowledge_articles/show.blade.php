@extends('adminlte::page')

@section('title', 'Detalle del artículo')

@section('content_header')
  <h1><i class="fas fa-book-open mr-2"></i>{{ $article->title }}</h1>
@stop

@section('content')
  <div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="card-title">Detalle editorial</h3>
      <div>
        <a href="{{ route('backend.knowledge-articles.edit', $article) }}" class="btn btn-warning btn-sm">
          <i class="fas fa-edit mr-1"></i> Editar
        </a>
        <a href="{{ route('backend.knowledge-articles.preview', $article) }}" target="_blank" class="btn btn-info btn-sm">
          <i class="fas fa-external-link-alt mr-1"></i> Abrir en sitio
        </a>
      </div>
    </div>
    <div class="card-body">
      <dl class="row">
        <dt class="col-sm-3">Familia</dt>
        <dd class="col-sm-9">{{ $article->category?->name ?? '—' }}</dd>
        <dt class="col-sm-3">Slug</dt>
        <dd class="col-sm-9">{{ $article->slug }}</dd>
        <dt class="col-sm-3">Estado</dt>
        <dd class="col-sm-9">{{ $article->editorial_status }}</dd>
        <dt class="col-sm-3">Publicación</dt>
        <dd class="col-sm-9">{{ $article->published_at?->format('d/m/Y H:i') ?? '—' }}</dd>
        <dt class="col-sm-3">Última verificación</dt>
        <dd class="col-sm-9">{{ $article->last_verified_at?->format('d/m/Y H:i') ?? '—' }}</dd>
        <dt class="col-sm-3">SEO title</dt>
        <dd class="col-sm-9">{{ $article->seo_title ?: '—' }}</dd>
        <dt class="col-sm-3">Meta description</dt>
        <dd class="col-sm-9">{{ $article->meta_description ?: '—' }}</dd>
      </dl>

      @if ($article->excerpt)
        <div class="mb-3">
          <h4 class="h6 text-uppercase text-muted">Bajada</h4>
          <p class="mb-0">{{ $article->excerpt }}</p>
        </div>
      @endif

      <div class="mb-3">
        <h4 class="h6 text-uppercase text-muted">Cuerpo</h4>
        <div>{!! $article->body !!}</div>
      </div>
    </div>
  </div>
@stop
