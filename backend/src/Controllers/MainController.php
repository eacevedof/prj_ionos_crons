<?php
namespace App\Controllers;

use App\Components\ConsoleComponent as Console;

abstract class MainController
{
    protected $argv;
    protected $request = [];

    public function __construct()
    {
        $this->argv = $_REQUEST;
        $this->request = (new Console($this->argv))->get_request();
    }

    private function _load_request()
    {
        foreach ($this->argv as $i => $param) {

        }
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
