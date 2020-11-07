<?php
namespace App\Controllers;

class MainController
{
    protected function get_param($ipos) {return $_REQUEST[$ipos] ?? null;}

}
