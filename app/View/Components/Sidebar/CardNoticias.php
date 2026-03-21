<?php

namespace App\View\Components\Sidebar;

use Illuminate\View\Component;

class CardNoticias extends Component
{
    public function render()
    {
        return view('components.sidebar.card-noticias', [
            'noticias' => $this->noticias ?? []
        ]);
    }
}
