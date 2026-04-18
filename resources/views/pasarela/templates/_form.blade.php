{{-- Shared form fields for create/edit --}}

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Proveedor <span class="text-danger">*</span></label>
            <select name="provider" class="form-control @error('provider') is-invalid @enderror" required>
                <option value="">Seleccionar...</option>
                @foreach($providers as $p)
                    <option value="{{ $p }}"
                        {{ old('provider', $template?->provider) === $p ? 'selected' : '' }}>
                        {{ ucfirst($p) }}
                    </option>
                @endforeach
            </select>
            @error('provider')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Tipo de contenido</label>
            <select name="content_type" class="form-control @error('content_type') is-invalid @enderror">
                <option value="">Global (todos los tipos)</option>
                @foreach($contentTypes as $ct)
                    <option value="{{ $ct }}"
                        {{ old('content_type', $template?->content_type) === $ct ? 'selected' : '' }}>
                        {{ class_basename($ct) }}
                    </option>
                @endforeach
            </select>
            @error('content_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label>Nombre de variante <span class="text-danger">*</span></label>
            <input type="text" name="variant_name"
                   class="form-control @error('variant_name') is-invalid @enderror"
                   value="{{ old('variant_name', $template?->variant_name) }}"
                   placeholder="ej: facebook_default"
                   required>
            @error('variant_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label>Texto del template <span class="text-danger">*</span></label>
    <small class="text-muted d-block mb-1">
        Tokens disponibles: <code>{title}</code> <code>{subtitle}</code>
        <code>{excerpt}</code> <code>{url}</code> <code>{date}</code>
        <code>{city}</code> <code>{venue}</code>
    </small>
    <textarea name="template_text" rows="6"
              class="form-control @error('template_text') is-invalid @enderror"
              required>{{ old('template_text', $template?->template_text) }}</textarea>
    @error('template_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="form-group">
    <div class="custom-control custom-switch">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1"
               {{ old('is_active', $template?->is_active ?? true) ? 'checked' : '' }}>
        <label class="custom-control-label" for="is_active">Template activo</label>
    </div>
</div>

<div class="form-group">
    <button type="button" class="btn btn-outline-secondary btn-sm" id="previewBtn">
        Vista previa con datos de ejemplo
    </button>
    <div id="previewOutput" class="mt-2 p-3 bg-light border rounded" style="display:none; white-space:pre-wrap;"></div>
</div>

@push('js')
<script>
document.getElementById('previewBtn').addEventListener('click', function() {
    const text = document.querySelector('textarea[name="template_text"]').value;
    fetch('{{ route('pasarela.templates.preview') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ template_text: text })
    })
    .then(r => r.json())
    .then(data => {
        const out = document.getElementById('previewOutput');
        out.textContent = data.preview;
        out.style.display = 'block';
    });
});
</script>
@endpush
