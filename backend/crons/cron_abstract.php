<?php
abstract class AbstractCron
{
    protected $projects;

    public function __construct()
    {
        $this->projects = include_once("projects.php");
    }

    protected function log($mxvar,$title="")
    {
        $now = date("YmdHis");
        $logfile = "cronlog_${now}.log";

        $content = "\n";
        if($title) $content = "\n$title:\n";
        $content .= print_r($mxvar,1);
        file_put_contents($logfile, $content,FILE_APPEND);
    }

    protected function logjson($mxvar, $title="")
    {
        $json = json_encode($mxvar);
        $this->log($json,$title);
    }
}