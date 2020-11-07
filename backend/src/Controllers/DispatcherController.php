<?php
namespace App\Controllers;

class DispatcherController
{

    public function __invoke()
    {
        print_r($_REQUEST);
    }
}
