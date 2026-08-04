@extends('adminlte::page')

@section('title', 'Enciclopedia')

@section('content_header')
  <h1><i class="fas fa-book-open mr-2"></i>Enciclopedia del folklore argentino</h1>
@stop

@section('content')
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">Artículos evergreen</h3>
      <div class="card-tools">
        @can('create', App\Models\KnowledgeArticle::class)
          <a href="{{ route('backend.knowledge-articles.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> Nuevo artículo
          </a>
        @endcan
      </div>
    </div>

    <div class="card-body">
      <form method="GET" class="mb-4">
        <div class="row">
          <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Buscar por título o slug" value="{{ request('search') }}">
          </div>
          <div class="col-md-2">
            <select name="knowledge_category_id" class="form-control">
              <option value="">Todas las familias</option>
              @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) request('knowledge_category_id') === $category->id)>
                  {{ $category->name }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <select name="editorial_status" class="form-control">
              <option value="">Todos los estados</option>
              @foreach (['draft' => 'Borrador', 'published' => 'Publicado', 'archived' => 'Archivado'] as $value => $label)
                <option value="{{ $value }}" @selected(request('editorial_status') === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div class="col-md-2">
            <input type="date" name="published_from" class="form-control" value="{{ request('published_from') }}">
          </div>
          <div class="col-md-2">
            <div class="d-flex">
              <input type="date" name="published_to" class="form-control mr-2" value="{{ request('published_to') }}">
              <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
          </div>
        </div>
      </form>

      <div class="table-responsive">
        <table class="table table-striped table-bordered">
          <thead>
            <tr>
              <th>Fecha</th>
              <th>Título</th>
              <th>Familia</th>
              <th>Estado</th>
              <th>Autor</th>
              <th class="text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($articles as $item)
              <tr>
                <td>{{ $item->published_at?->format('d-m-Y') ?? '—' }}</td>
                <td>
                  <strong>{{ $item->title }}</strong>
                  <div class="text-muted small">{{ $item->slug }}</div>
                </td>
                <td>{{ $item->category?->name ?? '—' }}</td>
                <td>
                  @if ($item->editorial_status === 'published')
                    <span class="badge badge-success">Publicado</span>
                  @elseif ($item->editorial_status === 'archived')
                    <span class="badge badge-secondary">Archivado</span>
                  @else
                    <span class="badge badge-warning">Borrador</span>
                  @endif
                </td>
                <td>{{ $item->author?->name ?? '—' }}</td>
                <td class="text-right" style="white-space: nowrap;">
                  <a href="{{ route('backend.knowledge-articles.show', $item) }}" class="btn btn-sm btn-primary" title="Ver">
                    <i class="fas fa-eye"></i>
                  </a>
                  @can('update', $item)
                    <a href="{{ route('backend.knowledge-articles.edit', $item) }}" class="btn btn-sm btn-warning" title="Editar">
                      <i class="fas fa-edit"></i>
                    </a>
                  @endcan
                  @can('delete', $item)
                    <form action="{{ route('backend.knowledge-articles.destroy', $item) }}" method="POST" class="d-inline-block">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Archivar artículo?')">
                        <i class="fas fa-archive"></i>
                      </button>
                    </form>
                  @endcan
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="text-center text-muted">Todavía no hay artículos de enciclopedia.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $articles->links() }}
      </div>
    </div>
  </div>
@stop
