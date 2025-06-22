<?php

namespace App\View\Components\Sidebar;

use Illuminate\View\Component;
use Illuminate\View\View;

class TopNews extends Component
{
    public $noticias;

    public function __construct($noticias)
    {
        $this->noticias = $noticias;
    }

    public function render(): View
    {
        return view('components.sidebar.top-news');
    }
}
