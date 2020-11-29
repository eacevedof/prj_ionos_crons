<?php
namespace App\Services\Cron;
use App\Component\ConsoleComponent as Console;
use function App\Functions\get_config;
use App\Traits\LogTrait;

abstract class ACronService implements ICronable
{
    use LogTrait;

    protected $projects;
    protected $services;
    protected $argv;

    public function __construct()
    {
        $this->projects = get_config("projects");
        $this->services = get_config("services");
        $this->argv = (new Console($_REQUEST))->get_request();
    }

    protected function _get_param($key){return $this->argv[$key] ?? null;}

    protected function _get_env($key){ return getenv($key);}
}