<?php
namespace App\Controllers;

abstract class MainController
{
    protected function get_param($ipos) {return $_REQUEST[$ipos] ?? null;}

    protected function load_service(){
        /*
        spl_autoload_register(function ($nombre_clase) {
            include_once $nombre_clase . '.php';
        });
        */
    }
}
