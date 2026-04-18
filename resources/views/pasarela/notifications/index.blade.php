@extends('adminlte::page')

@section('title', 'Notificaciones')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            Mis Notificaciones
            @if($unreadCount > 0)
                <span class="badge badge-danger ml-2">{{ $unreadCount }} sin leer</span>
            @endif
        </h1>
        @if($unreadCount > 0)
            <form method="POST" action="{{ route('pasarela.notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-check-double"></i> Marcar todas como leídas
                </button>
            </form>
        @endif
    </div>
@endsection

@section('content')
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    </div>
@endif

<div class="card">
    <div class="card-body p-0">
        @forelse($notifications as $notification)
            <div class="d-flex align-items-start p-3 border-bottom {{ $notification->is_read ? '' : 'bg-light' }}">
                <div class="mr-3">
                    @if(!$notification->is_read)
                        <span class="badge badge-primary p-2">&bull;</span>
                    @else
                        <span class="badge badge-secondary p-2">&bull;</span>
                    @endif
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $notification->title }}</strong>
                        <small class="text-muted">{{ $notification->created_at?->diffForHumans() }}</small>
                    </div>
                    <p class="mb-1 text-muted">{{ $notification->body }}</p>
                    <div class="d-flex gap-2">
                        @if($notification->action_url)
                            <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary">
                                Ver detalle
                            </a>
                        @endif
                        @if(!$notification->is_read)
                            <form method="POST"
                                  action="{{ route('pasarela.notifications.mark-read', $notification) }}"
                                  class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                    Marcar leída
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-5">
                <i class="fas fa-bell-slash fa-2x mb-3"></i>
                <p>No tenés notificaciones</p>
            </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
        <div class="card-footer">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection
