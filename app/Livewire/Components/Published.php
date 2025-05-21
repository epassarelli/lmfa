<?php

  namespace App\Livewire\Components;

  use Illuminate\Database\Eloquent\Model;
  use Livewire\Attributes\On;
  use Livewire\Component;

  class Published extends Component
  {
    public Model $model;
    public string $field;
    public bool $active;

    public function mount()
    {
      $this->active = (bool)$this->model->getAttribute($this->field);
    }

    #[On('change-active')]
    public function changeActive()
    {
      $this->active = (bool)$this->model->getAttribute($this->field);
    }

    public function render()
    {
      return view('livewire.components.published');
    }
  }
