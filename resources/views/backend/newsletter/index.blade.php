@extends('adminlte::page')

@section('title', 'Suscriptores del Newsletter')

@section('content_header')
    <h1>Usuarios Suscritos al Newsletter</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header bg-white border-bottom">
        <form method="GET" action="{{ route('backend.newsletter.index') }}" class="form-inline">
            <div class="input-group input-group-sm mr-2" style="width: 250px;">
                <input type="text" name="search" class="form-control" placeholder="Buscar email o nombre..." value="{{ request('search') }}">
            </div>
            <select name="status" class="form-control form-control-sm mr-2">
                <option value="">Todos los estados</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>Desuscriptos</option>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filtrar</button>
        </form>
    </div>
    <div class="card-body p-0 table-responsive">
        @if(session('success'))
            <div class="alert alert-success m-2">{{ session('success') }}</div>
        @endif
        
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Alta</th>
                    <th>Baja</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscribers as $sub)
                <tr>
                    <td>{{ $sub->email }}</td>
                    <td>
                        @if($sub->status == 'active')
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-secondary">Desuscripto</span>
                        @endif
                    </td>
                    <td>{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $sub->unsubscribed_at ? $sub->unsubscribed_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('backend.newsletter.toggle', $sub) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-{{ $sub->status == 'active' ? 'warning' : 'info' }}" title="Cambiar Estado">
                                <i class="fas fa-fw {{ $sub->status == 'active' ? 'fa-user-times' : 'fa-user-check' }}"></i> 
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center">No hay suscriptores aún.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $subscribers->appends(request()->query())->links() }}
    </div>
</div>
@stop
