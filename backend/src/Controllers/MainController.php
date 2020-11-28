<?php
namespace App\Controllers;
use App\Component\ConsoleComponent as Console;
use function App\Functions\get_config;
use App\Traits\LogTrait;


abstract class MainController
{
    use LogTrait;

    protected $argv;
    protected $request = [];
    protected $servicemapper = [];

    public function __construct()
    {
        $this->argv = $_REQUEST;
        $this->request = (new Console($this->argv))->get_request();
        $this->servicemapper = get_config("services");
    }

    protected function get_param($key) {return $this->request[$key] ?? null;}

    protected function _is_service($class)
    {
        if(!$class) return false;
        //con true busca en el includepath
        return class_exists($class,true);
    }
}
