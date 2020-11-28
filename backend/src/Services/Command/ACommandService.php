<?php
namespace App\Services\Command;
use function App\Functions\get_config;
use App\Traits\LogTrait;

abstract class ACommandService implements ICommand
{
    use LogTrait;

    protected $projects;
    protected $services;

    public function __construct()
    {
        $this->projects = get_config("projects");
        $this->services = get_config("services");
    }

    protected function _get_param($key){return $_REQUEST[$key] ?? "";}

    protected function _get_env($key){ return getenv($key);}
}