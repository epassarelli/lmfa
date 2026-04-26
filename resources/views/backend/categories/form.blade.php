@csrf
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="name">Nombre de Categoría <span class="text-danger">*</span></label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="slug">Slug</label>
            <input type="text" name="slug" id="slug" class="form-control" value="{{ old('slug', $category->slug ?? '') }}" placeholder="autogenerado si se deja vacío">
            @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
        </div>
    </div>
</div>
