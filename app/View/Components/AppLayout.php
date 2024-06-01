<?php

namespace App\View\Components;

use Illuminate\View\Component;
<<<<<<< HEAD
=======
use Illuminate\View\View;
>>>>>>> dev

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
<<<<<<< HEAD
     *
     * @return \Illuminate\View\View
     */
    public function render()
=======
     */
    public function render(): View
>>>>>>> dev
    {
        return view('layouts.app');
    }
}
