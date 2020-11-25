<?php
namespace App\Services\Command;

use App\Traits\LogTrait;

abstract class AbstractService implements ICommand
{
    use LogTrait;

    protected $projects;

    public function __construct()
    {
        $this->projects = include_once(PATH_CONFIG.DS."projects.php");
    }

    protected function _get_request($key){
        return $_REQUEST[$key] ?? "";
    }

    protected function _get_env($key){ return getenv($key);}
}