<?php
namespace App\Controllers;

abstract class MainController
{
    protected $console;

    public function __construct()
    {
        $this->console = $_REQUEST;
    }

    protected function get_param($ipos) {return $this->console[$ipos] ?? null;}

    protected function load_service(){
        /*
        spl_autoload_register(function ($nombre_clase) {
            include_once $nombre_clase . '.php';
        });
        */
    }
}
