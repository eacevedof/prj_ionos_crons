<?php
namespace App\Services\Command;

class HelpService extends AbstractService
{
    private function _get_projects()
    {
        parent::_construct();
        $r = array_keys($this->projects);
        sort($r);
        return $r;
    }

    private function _get_commands()
    {
        $params = include(PATH_CONFIG.DS."services.php");
        /*
        $cmds = array_filter($params, function($item){
            return strstr($item,"\\Command\\");
        });
        */
        return $params;
    }

    public function run()
    {
        $cmds = $this->_get_commands();
        $echo[] = "";
        foreach ($cmds as $cmd => $class){
            $echo[] = "$cmd:\n\t$class";
        }
        $echo[] = "\n\nprojects:\n";
        $prjs = $this->_get_projects();
        foreach ($prjs as $prj)
            $echo[] = "$prj";
        $echo[] = "\n";
        echo implode("\n",$echo);
    }
}