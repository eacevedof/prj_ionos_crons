<?php
namespace App\Services\Cron;

use App\Traits\LogTrait;

abstract class AbstractService implements ICronable
{
    use LogTrait;

    protected $projects;

    public function __construct()
    {
        $this->projects = include_once(PATH_CONFIG.DS."projects.php");
    }

    protected function _get_env($key){ return getenv($key);}
}