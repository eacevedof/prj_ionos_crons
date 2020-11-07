<?php
namespace App\Traits;
use App\Component\LogComponent As L;

trait LogTrait
{

    private function _get_pathlog(){return realpath(__DIR__."/../../logs");}

    protected function log($mxVar,$sTitle=NULL)
    {
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("sql",$pathlogs);
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
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("error",$pathlogs);
        $oLog->save($mxVar,$sTitle);
    }
}