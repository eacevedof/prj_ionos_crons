<?php
namespace App\Controllers;

class MainController
{
    private const FOLDER_CRONS = "";

    private function _get_cronpath($filename){
        return self::FOLDER_CRONS."/".$filename;
    }

    private function _crondbs()
    {
        $cronfile = "cron_dbbackup.php";
        $pathcron = $this->_get_cronpath($cronfile);
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