<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * @param  array<int, string>  $vite
     */
    public function __construct(
        public string $title = '',
        public string $active = '',
        public string $crumbs = '',
        public array $vite = [],
    ) {}

    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
