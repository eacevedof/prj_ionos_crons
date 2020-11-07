<?php
namespace App\Controllers;

use App\Component\ConsoleComponent as Console;
use App\Traits\LogTrait;

abstract class MainController
{
    use LogTrait;

    protected $argv;
    protected $request = [];

    public function __construct()
    {
        $this->argv = $_REQUEST;
        $this->request = (new Console($this->argv))->get_request();
    }

    protected function get_param($key) {return $this->request[$key] ?? null;}

    protected function get_parsed_ns($dotns)
    {
        $changed = str_replace(".","\\",$dotns);
        return $changed;
    }

    protected function _is_service($class) {return class_exists($class,true);}
}
