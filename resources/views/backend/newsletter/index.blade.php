@extends('adminlte::page')

@section('title', 'Suscriptores del Newsletter')

@section('content_header')
    <h1>Usuarios Suscritos al Newsletter</h1>
@stop

@section('content')
<div class="card card-outline card-primary shadow-sm">
    <div class="card-header">
        <h3 class="card-title">Listado de Suscriptores</h3>
    </div>
    <div class="card-body pb-0">
        <x-admin-listing-toolbar
            :action="route('backend.newsletter.index')"
            search-placeholder="Buscar por email o nombre"
            :sort-options="[
                'created_at' => 'Alta',
                'email' => 'Email',
                'status' => 'Estado',
                'unsubscribed_at' => 'Baja',
            ]"
        >
            <div class="col-md-2">
                <label for="status" class="small text-muted mb-1">Estado</label>
                <select id="status" name="status" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>Desuscriptos</option>
                </select>
            </div>
        </x-admin-listing-toolbar>
    </div>
    <div class="card-body p-0 table-responsive">
        @if(session('success'))
            <div class="alert alert-success m-2">{{ session('success') }}</div>
        @endif
        
        <table class="table table-hover mb-0">
            <thead class="thead-light">
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
    <div class="card-footer bg-white">
        {{ $subscribers->appends(request()->query())->links() }}
    </div>
</div>
@stop
