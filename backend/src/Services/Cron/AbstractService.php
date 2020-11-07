<?php
namespace App\Services\Cron;

use App\Traits\LogTrait;

abstract class AbstractService
{
    use LogTrait;

    protected $projects;

    public function __construct()
    {
        $this->projects = include_once(PATH_SRC_CONFIG.DS."projects.php");
    }

    protected function logjson($mxvar, $title="")
    {
        $json = json_encode($mxvar);
        $this->log($json,$title);
    }
}