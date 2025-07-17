<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Database\Eloquent\Model;

class ToggleButton extends Component
{
    public Model $model;
    public string $field;
    // public bool $active;

    // public function mount()
    // {
    //     $this->active = (bool) $this->model->getAttribute($this->field);
    // }

    public function toggle()
    {
        $this->model->{$this->field} = !$this->model->{$this->field};
        $this->model->save();
    }

    // public function updatedActive($value)
    // {
    //     $this->model->setAttribute($this->field, $value);
    //     $this->model->save();
    // }

    public function render()
    {
        return view('livewire.toggle-button');
    }
}
