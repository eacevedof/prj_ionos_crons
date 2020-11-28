<?php
namespace App\Traits;
use App\Component\LogComponent As L;

trait LogTrait
{

    private function _get_pathlog(){return realpath(__DIR__."/../../logs");}

    protected function log($mxVar,$sTitle=NULL)
    {
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("trace",$pathlogs);
        $oLog->save($mxVar,$sTitle);
    }

    protected function logd($mxVar,$sTitle=NULL)
    {
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("debug",$pathlogs);
        $oLog->save($mxVar,$sTitle);
    }

    protected function logerr($mxVar,$sTitle=NULL)
    {
        $this->_pr($mxVar,$sTitle);
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("error",$pathlogs);
        $oLog->save($mxVar,$sTitle);
    }

    private function _pr($mxvar,$title="")
    {
        $now = date("Ymd H:i:s");
        echo "\n$now\n";
        if($title) echo "\n$title:\n";
        print_r($mxvar);
        echo "\n";
    }

    protected function logpr($mxvar,$title="")
    {
        $this->_pr($mxvar,$title);
        $this->log($mxvar,$title);
    }
}