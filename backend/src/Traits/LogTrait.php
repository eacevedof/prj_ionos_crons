<?php
namespace App\Traits;
use App\Component\LogComponent As L;

trait LogTrait
{
    protected function log($mxVar,$sTitle=NULL)
    {
        $pathlogs = realpath(__DIR__."/../../logs");
        $oLog = new L("sql",$pathlogs);
        $oLog->save($mxVar,$sTitle);
    }

    protected function logd($mxVar,$sTitle=NULL)
    {
        $pathlogs = realpath(__DIR__."/../../logs");
        $oLog = new L("debug",$pathlogs);
        $oLog->save($mxVar,$sTitle);
    }

}