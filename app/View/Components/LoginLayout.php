<?php

namespace App\View\Components;

use Illuminate\View\Component;

class LoginLayout extends Component
{
    public $title;
    public $bodyClass;

    public function __construct($title = 'Login', $bodyClass = 'login-page')
    {
        $this->title = $title;
        $this->bodyClass = $bodyClass;
    }

    public function render()
    {
        return view('layouts.login-layout');
    }
}
