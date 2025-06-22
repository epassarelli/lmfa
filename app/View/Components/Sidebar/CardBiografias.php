<?php

namespace App\View\Components\Sidebar;

use Illuminate\View\Component;

class CardBiografias extends Component
{
    public $interpretes;

    public function __construct($interpretes)
    {
        $this->interpretes = $interpretes;
    }

    public function render()
    {
        return view('components.sidebar.card-biografias');
    }
}
