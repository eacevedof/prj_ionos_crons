<?php
namespace App\Services\Command;

use App\Component\ColorComponent as Color;
use function \App\Functions\get_config;

class HelpService extends ACommandService
{
    private function _get_projects()
    {
        $r = array_keys($this->projects);
        sort($r);
        return $r;
    }

    public function run()
    {
        $cmds = $this->services;

        $echo[] = "";
        $echo[] = Color::text("\t\tHELP MENU","green");
        foreach ($cmds as $cmd => $class)
        {
            $echo[] = Color::text("$cmd:\n\t$class","yellow");
        }

        $echo[] = "\n\nprojects:";
        $prjs = $this->_get_projects();
        foreach ($prjs as $prj)
            $echo[] = "\t$prj";
        $echo[] = "\n";
        echo implode("\n",$echo);
    }
}