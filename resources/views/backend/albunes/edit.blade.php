@extends('adminlte::page')

@section('title', 'Editar Álbum')

@section('content_header')
  <x-backend.form-header mode="edit">Editar Álbum: {{ $album->album }}</x-backend.form-header>
@stop

@section('css')
  @include('backend.partials.styles._compact-form')
@endsection

@section('content')

  <form action="{{ route('backend.discos.update', $album) }}" method="POST" enctype="multipart/form-data" class="form-compact">
    @csrf
    @method('PUT')

    <div class="card">

      <div class="card-body">
        @include('backend.albunes.form')
      </div>

      <div class="card-footer">
        <x-backend.form-actions :back="route('backend.discos.index')" />
      </div>
    </div>
  </form>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js" crossorigin="anonymous"
    referrerpolicy="no-referrer"></script>
  {{-- <script>
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
  </script> --}}

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



    document.getElementById('create-cancion').addEventListener('click', function() {
      var cancionNombre = document.getElementById('new-cancion').value.trim();

      if (cancionNombre === '') {
        alert('Por favor, ingresa un nombre de canción.');
        return;
      }

      fetch("{{ route('backend.canciones.store-ajax') }}", {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            cancion: cancionNombre,
            interprete_id: {{ $album->interprete_id }}
          })
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            var selector = document.getElementById('canciones-selector');

            // Crear la nueva opción
            var newOption = document.createElement('option');
            newOption.value = data.cancion.id;
            newOption.textContent = data.cancion.cancion;

            // Agregar la nueva opción al selector
            selector.appendChild(newOption);

            // Establecer el valor del selector en la nueva canción
            selector.value = data.cancion.id;

            // Limpia el campo de texto de la nueva canción
            document.getElementById('new-cancion').value = '';

            // Seleccionar automáticamente la nueva canción y agregarla a la lista
            document.getElementById('add-cancion').click();
          } else {
            alert(data.message);
          }
        })
        .catch(error => console.error('Error:', error));
    });



    document.addEventListener('click', function(e) {
      if (e.target && e.target.classList.contains('remove-cancion')) {
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

@section('js')
  @include('backend.partials.scripts._select2')
  @include('backend.partials.scripts._tooltips')
  <script>
    $(document).on('change', '.custom-file-input', function () {
      $(this).next('.custom-file-label').addClass('selected').text($(this).val().split('\\').pop());
    });
  </script>
@endsection
