@once
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const generateSlug = (texto) => {
        let slug = texto.toLowerCase();
        slug = slug.normalize('NFD').replace(/[\u0300-\u036f]/g, ''); // elimina tildes
        slug = slug.replace(/ñ/g, 'n');
        slug = slug.replace(/[^a-z0-9\s-]/g, ''); // elimina caracteres especiales
        slug = slug.trim().replace(/\s+/g, '-'); // reemplaza espacios por guiones
        return slug;
      }

      // Uso flexible: autocompleteSlug(this, '#slug')
      window.autocompleteSlug = function(sourceElement, targetSelector = '#slug') {
        const slugInput = document.querySelector(targetSelector);
        if (slugInput && sourceElement) {
          slugInput.value = generateSlug(sourceElement.value);
        }
      }
    });
  </script>
@endonce
