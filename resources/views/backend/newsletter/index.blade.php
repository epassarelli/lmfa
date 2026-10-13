@extends('adminlte::page')

@section('title', 'Suscriptores del Newsletter')

@section('content_header')
    <h1>Suscriptores del Newsletter</h1>
    <a href="{{ route('backend.newsletter.editions.index') }}" class="btn btn-sm btn-outline-primary mt-2"><i class="far fa-paper-plane mr-1"></i> Ir a las ediciones</a>
@stop

@section('content')
<details class="callout callout-info mb-3" data-newsletter-help>
    <summary class="font-weight-bold" style="cursor:pointer"><i class="fas fa-question-circle mr-1"></i> ¿Cómo funcionan las suscripciones?</summary>
    <div class="mt-2 small">
        <ul class="pl-3 mb-0">
            <li><strong>Doble confirmación:</strong> quien se suscribe recibe un correo con un botón. Solo después de confirmar recibe el newsletter.</li>
            <li><strong>Sin confirmar:</strong> son las altas que todavía no confirmaron y los suscriptores anteriores a este sistema. <em>No reciben nada</em> hasta que confirmen.</li>
            <li><strong>Pedir confirmación:</strong> reenvía el correo a una persona. Usalo solo con direcciones que parezcan reales; no escribas a las que sean spam, porque dañan la reputación del envío.</li>
            <li><strong>Eliminar:</strong> borra el registro (para spam o pedidos de borrado). Una baja conserva el registro para no volver a escribirle.</li>
            <li>Quien se dio de baja solo vuelve a estar activo si lo confirma desde su correo.</li>
            <li><strong>Rebotadas:</strong> direcciones que no existen. Se marcan solas cuando el servidor las rechaza al enviar, o a mano con el botón <i class="fas fa-ban"></i>. No se les vuelve a escribir.</li>
        </ul>
    </div>
</details>

<div class="row">
    <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-success"><i class="fas fa-check"></i></span><div class="info-box-content"><span class="info-box-text">Reciben el newsletter</span><span class="info-box-number">{{ $counts['deliverable'] }}</span></div></div></div>
    <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span><div class="info-box-content"><span class="info-box-text">Sin confirmar</span><span class="info-box-number">{{ $counts['unconfirmed'] }}</span></div></div></div>
    <div class="col-md-4"><div class="info-box"><span class="info-box-icon bg-secondary"><i class="fas fa-user-times"></i></span><div class="info-box-content"><span class="info-box-text">Dados de baja · Rebotados</span><span class="info-box-number">{{ $counts['unsubscribed'] }} · {{ $counts['bounced'] }}</span></div></div></div>
</div>

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
                'confirmed_at' => 'Confirmación',
                'unsubscribed_at' => 'Baja',
            ]"
        >
            <div class="col-md-3">
                <label for="status" class="small text-muted mb-1">Estado</label>
                <select id="status" name="status" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="unconfirmed" {{ request('status') == 'unconfirmed' ? 'selected' : '' }}>Sin confirmar</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="unsubscribed" {{ request('status') == 'unsubscribed' ? 'selected' : '' }}>Dados de baja</option>
                    <option value="bounced" {{ request('status') == 'bounced' ? 'selected' : '' }}>Rebotados</option>
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
                    <th>Origen</th>
                    <th>Alta</th>
                    <th>Confirmó</th>
                    <th>Baja</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subscribers as $sub)
                <tr>
                    <td>{{ $sub->email }}</td>
                    <td>
                        @if($sub->isDeliverable())
                            <span class="badge badge-success">Activo</span>
                        @elseif($sub->isUnconfirmed())
                            <span class="badge badge-warning">Sin confirmar</span>
                        @elseif($sub->status === 'bounced')
                            <span class="badge badge-danger">Rebotado</span>
                        @else
                            <span class="badge badge-secondary">Baja</span>
                        @endif
                    </td>
                    <td>{{ $sub->source ?? '-' }}</td>
                    <td>{{ $sub->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $sub->confirmed_at ? $sub->confirmed_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $sub->unsubscribed_at ? $sub->unsubscribed_at->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-nowrap">
                        @if(! $sub->isDeliverable() && $sub->status !== 'bounced')
                            <form method="POST" action="{{ route('backend.newsletter.confirmation', $sub) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-info" title="Pedir confirmación por correo"><i class="fas fa-fw fa-envelope"></i></button>
                            </form>
                        @endif
                        @if(in_array($sub->status, ['active', 'pending'], true))
                            <form method="POST" action="{{ route('backend.newsletter.toggle', $sub) }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning" title="Dar de baja"><i class="fas fa-fw fa-user-times"></i></button>
                            </form>
                        @endif
                        @if($sub->status !== 'bounced')
                            <form method="POST" action="{{ route('backend.newsletter.bounced', $sub) }}" class="d-inline" onsubmit="return confirm('¿Marcar como rebotada? No se le va a volver a escribir.');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-secondary" title="Marcar como rebotada (la dirección no existe)"><i class="fas fa-fw fa-ban"></i></button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('backend.newsletter.destroy', $sub) }}" class="d-inline" onsubmit="return confirm('¿Eliminar este suscriptor? No se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar (spam o pedido de borrado)"><i class="fas fa-fw fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center">No hay suscriptores aún.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">
        {{ $subscribers->appends(request()->query())->links() }}
    </div>
</div>
@stop
