@props(['key', 'title', 'color' => 'warning', 'count', 'columns' => [], 'open' => false])

{{-- Tarjeta contraible del Centro de Moderación. Contraída por defecto; "open" la deja expandida (ej. tras publicar). --}}
<div class="col-md-12" id="mod-{{ $key }}">
    <div class="card card-{{ $color }} card-outline {{ $open ? '' : 'collapsed-card' }}">
        <div class="card-header">
            <h3 class="card-title">{{ $title }} ({{ $count }})</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Contraer / expandir">
                    <i class="fas {{ $open ? 'fa-minus' : 'fa-plus' }}"></i>
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm">
                <thead>
                    <tr>
                        @foreach($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                        <th style="width: 150px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    </div>
</div>
