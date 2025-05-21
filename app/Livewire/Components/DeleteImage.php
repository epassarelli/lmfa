<?php

namespace App\Livewire\Components;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class DeleteImage extends Component
{

    public string $field;
    public Product $product;

    public function mount($field, $product)
    {
        $this->field = $field;
        $this->product = $product;
    }
    public function render()
    {
        return view('livewire.components.delete-image');
    }

    #[On('delete')]
    public function delete($field)
    {
        // dd($this->product->{$this->field});
        if (Storage::exists('products/' . $this->product->{$field})) {
            Storage::delete('products/' . $this->product->{$field});
        }
        $this->product->update([
            $field => null
        ]);

        toast('La imágen se eliminó correctamente', 'success');
        $this->redirect(route('admin.products.edit', $this->product));
    }
}
