<?php
namespace App\Traits;
use App\Component\LogComponent As L;

trait LogTrait
{
    private function _get_pathlog(){return realpath(__DIR__."/../../logs");}

    protected function log($mxVar, $title=null, $prefix="")
    {
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("trace", $pathlogs, $prefix);
        $oLog->save($mxVar,$title, $prefix);
    }

    protected function logd($mxVar, $title="", $prefix="")
    {
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("debug",$pathlogs);
        $oLog->save($mxVar, $title, $prefix);
    }

    protected function logerr($mxVar,$title="", $prefix="")
    {
        $this->_pr($mxVar,$title);
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("error",$pathlogs);
        $oLog->save($mxVar, $title, $prefix);
    }

    private function _pr($mxvar, $title="")
    {
        $now = date("Ymd H:i:s");
        echo "\n$now\n";
        if($title) echo "\n$title:\n";
        print_r($mxvar);
        echo "\n";
    }

    protected function logpr($mxvar, $title="", $prefix="")
    {
        $this->_pr($mxvar,$title);
        $this->log($mxvar,$title, $prefix);
    }
}