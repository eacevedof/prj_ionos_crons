<?php
namespace App\Services\Command;

use function \App\Functions\get_config;

class HelpService extends ACommandService
{
    private function _get_projects()
    {
        $r = array_keys($this->projects);
        sort($r);
        return $r;
    }

    private function _get_commands()
    {
        $params = get_config("services");
        return $params;
    }

    public function run()
    {
        $cmds = $this->_get_commands();
        $echo[] = "";
        foreach ($cmds as $cmd => $class){
            $echo[] = "$cmd:\n\t$class";
        }
        $echo[] = "\n\nprojects:";
        $prjs = $this->_get_projects();
        foreach ($prjs as $prj)
            $echo[] = "\t$prj";
        $echo[] = "\n";
        echo implode("\n",$echo);
    }
}