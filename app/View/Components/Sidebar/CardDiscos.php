<?php

namespace App\View\Components\Sidebar;

use Illuminate\View\Component;

class CardDiscos extends Component
{
    public $discos;

    public function __construct($discos)
    {
        $this->discos = $discos;
    }

    public function render()
    {
        return view('components.sidebar.card-discos');
    }
}
