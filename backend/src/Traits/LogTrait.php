<?php
namespace App\Traits;
use App\Component\LogComponent As L;

trait LogTrait
{
    private function _get_pathlog(){return realpath(__DIR__."/../../logs");}

    protected function log($mxVar, string $title="", string $prefix=""): void
    {
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("trace", $pathlogs, $prefix);
        $oLog->save($mxVar,$title);
    }

    protected function logd($mxVar, string $title="", string $prefix=""): void
    {
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("debug", $pathlogs, $prefix);
        $oLog->save($mxVar, $title);
    }

    protected function logerr($mxVar, string $title="", string $prefix=""): void
    {
        $this->_pr($mxVar, $title);
        $pathlogs = $this->_get_pathlog();
        $oLog = new L("error", $pathlogs, $prefix);
        $oLog->save($mxVar, $title);
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
        $this->log($mxvar, $title, $prefix);
    }
}