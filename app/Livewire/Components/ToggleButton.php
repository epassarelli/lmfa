<?php

namespace App\Livewire\Components;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

class ToggleButton extends Component
{
  public Model $model;
  public string $field;
  public bool $active;

  public function mount()
  {
    $this->active = (bool)$this->model->getAttribute($this->field);
  }

  public function render()
  {
    return view('livewire.toggle-button');
  }

  public function updating($field, $value)
  {
    $this->model->setAttribute($this->field, $value);
    $this->model->save();

    if ($this->model instanceof ProductCategory) {
      if (!$value) {
        $this->model->inactiveWithRelations();
        $this->redirect(route('admin.productCategory.index'), navigate: true);
      } else {
        $this->model->activeWithRelations();
        $this->redirect(route('admin.productCategory.index'), navigate: true);
      };
    }
  }
}
