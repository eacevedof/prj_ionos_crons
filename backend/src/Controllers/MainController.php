<?php
namespace App\Controllers;

class MainController
{
    private function _crondbs()
    {
        $pathcron = "cron_dbbackup.php";
        echo "_crondbs(): $pathcron";
        if(is_file($pathcron)){
            include_once($pathcron);
            (new CronDbbackup())->execute();
        }
    }

    public function index()
    {
        $this->_crondbs();
    }
}