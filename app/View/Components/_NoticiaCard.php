<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NoticiaCard extends Component
{
    public $noticia, $class;

    /**
     * Create a new component instance.
     */
    public function __construct($noticia, $class = '')
    {
        $this->noticia = $noticia;
        $this->class = $class;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.noticia-card');
    }
}
