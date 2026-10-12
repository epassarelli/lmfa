@extends('adminlte::page')

@section('metaTitle', 'Mensajes de contacto')

@section('content_header')
  <h1>Mensajes de contacto</h1>
@stop

@php
  $badges = ['new' => 'primary', 'read' => 'secondary', 'answered' => 'success', 'spam' => 'danger'];
  $labels = \App\Models\ContactMessage::statuses();
@endphp

@section('content')
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card shadow-sm">
    <div class="card-header">
      <ul class="nav nav-pills">
        @foreach (['new' => 'Nuevos', 'read' => 'Leídos', 'answered' => 'Respondidos', 'spam' => 'Spam', 'all' => 'Todos'] as $key => $label)
          <li class="nav-item">
            <a class="nav-link {{ $status === $key ? 'active' : '' }}" href="{{ route('backend.contact-messages.index', array_filter(['status' => $key, 'q' => $search])) }}">
              {{ $label }}
              @if ($key !== 'all')
                <span class="badge badge-light ml-1">{{ $counts[$key] ?? 0 }}</span>
              @else
                <span class="badge badge-light ml-1">{{ $counts->sum() }}</span>
              @endif
            </a>
          </li>
        @endforeach
      </ul>
    </div>
    <div class="card-body">
      <form method="GET" class="form-inline mb-3">
        <input type="hidden" name="status" value="{{ $status }}">
        <input type="search" name="q" value="{{ $search }}" class="form-control mr-2" placeholder="Buscar por nombre, email, asunto o texto" style="min-width: 320px">
        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i> Buscar</button>
      </form>

      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr><th>Fecha</th><th>De</th><th>Asunto</th><th>Mensaje</th><th>Estado</th></tr>
          </thead>
          <tbody>
            @forelse ($messages as $item)
              <tr>
                <td class="text-nowrap">{{ optional($item->fecha_envio)->format('d/m/Y H:i') }}</td>
                <td>{{ $item->nombre }}<div class="small text-muted">{{ $item->email }}</div></td>
                <td>{{ $item->asunto }}</td>
                <td><a href="{{ route('backend.contact-messages.show', $item) }}" class="{{ $item->status === 'new' ? 'font-weight-bold' : '' }}">{{ \Illuminate\Support\Str::limit($item->mensaje, 90) }}</a></td>
                <td><span class="badge badge-{{ $badges[$item->status] ?? 'secondary' }}">{{ $labels[$item->status] ?? $item->status }}</span></td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">No hay mensajes en esta vista.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    @if ($messages->hasPages())
      <div class="card-footer">{{ $messages->links() }}</div>
    @endif
  </div>
@stop
