<?php
namespace App\Controllers;

class DispatcherController extends MainController
{
    public function __invoke()
    {
        print_r($this->get_param(1));
    }
}
