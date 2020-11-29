<?php
namespace App\Controllers;
use App\Component\ConsoleComponent as Console;
use function App\Functions\get_config;
use App\Traits\LogTrait;


abstract class MainController
{
    use LogTrait;

    protected $argv;
    protected $services = [];

    public function __construct()
    {
        $this->argv =( new Console($_REQUEST))->get_request();
        $this->services = get_config("services");
    }

    protected function get_param($key) {return $this->argv[$key] ?? null;}

    protected function _is_service($class)
    {
        if(!$class) return false;
        //con true busca en el includepath
        return class_exists($class,true);
    }
}
