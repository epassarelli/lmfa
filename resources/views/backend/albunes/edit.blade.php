@extends('adminlte::page')

@section('title', 'Editar Álbum')

@section('content_header')
  <span>Editar Album</span>
@stop

@section('content')

  <form action="{{ route('backend.discos.update', $album) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="card">

      <div class="card-body">
        @include('backend.albunes.form')



        <div class="card-footer">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Guardar
          </button>
          <button type="reset" class="btn btn-secondary">
            <i class="fas fa-undo"></i> Reset
          </button>
          <a href="{{ url()->previous() }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al listado
          </a>
        </div>
  </form>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js" crossorigin="anonymous"
    referrerpolicy="no-referrer"></script>
  <script>
    document.getElementById('add-cancion').addEventListener('click', function() {
      var selector = document.getElementById('canciones-selector');
      var selectedId = selector.value;
      var selectedText = selector.options[selector.selectedIndex].text;

      // Obtener el último orden de la lista
      var cancionesList = document.getElementById('canciones-list');
      var lastOrder = 0;
      if (cancionesList.children.length > 0) {
        lastOrder = parseInt(cancionesList.lastElementChild.querySelector('input[name="ordenes[]"]').value);
      }

      var li = document.createElement('li');
      li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
      li.style.cursor = 'grab';
      li.innerHTML = `
        <div class="d-flex align-items-center">
            <input type="hidden" name="canciones[]" value="${selectedId}">
            <input type="number" class="form-control me-2" name="ordenes[]" value="${lastOrder + 1}" min="1" style="width: 60px;">
            ${selectedText}
        </div>
        <button type="button" class="btn btn-danger btn-sm remove-cancion">Quitar</button>
    `;

      cancionesList.appendChild(li);

      // Remover opción del selector
      selector.remove(selector.selectedIndex);
    });

    document.addEventListener('click', function(e) {
      if (e.target && e.target.classList.contains('remove-cancion')) {
        // Agregar la canción de nuevo al selector
        var li = e.target.parentNode;
        var cancionId = li.querySelector('input[name="canciones[]"]').value;
        var cancionTitulo = li.querySelector('div').textContent.trim();

        var option = document.createElement('option');
        option.value = cancionId;
        option.textContent = cancionTitulo;
        document.getElementById('canciones-selector').appendChild(option);

        li.remove();
      }
    });

    // Inicializar Sortable
    var sortable = Sortable.create(document.getElementById('canciones-list'), {
      handle: '.list-group-item',
      animation: 150,
      onEnd: function(evt) {
        var items = evt.to.children;
        for (var i = 0; i < items.length; i++) {
          items[i].querySelector('input[name="ordenes[]"]').value = i + 1;
        }
      }
    });

    // Validación del formulario
    document.querySelector('form').addEventListener('submit', function(e) {
      var ordenes = Array.from(document.querySelectorAll('input[name="ordenes[]"]')).map(input => input.value);
      var ordenesSet = new Set(ordenes);

      if (ordenes.length !== ordenesSet.size) {
        e.preventDefault();
        alert('No puede haber canciones con el mismo número de orden.');
      }
    });
  </script>
@endsection
