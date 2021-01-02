<?php
namespace App\Services\Command;

use App\Component\ColorComponent as Color;
use function \App\Functions\get_config;

class HelpService extends ACommandService
{
    private $help;

    //help o h  param 2: all, projects
    public function __construct()
    {
        parent::__construct();
        $this->help = get_config("help");
    }

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
        $echo[] = Color::text("\t\tHELP MENU",Color::LIGHT_GREEN);
        foreach ($cmds as $cmd => $class)
        {
            $cmd = Color::text($cmd,Color::LIGHT_GREEN);
            $class = Color::text($class, Color::LIGHT_WHITE);
            $echo[] = "\n$cmd:\n  $class";
        }

        $echo[] = "\n\nprojects:";
        $prjs = $this->_get_projects();
        foreach ($prjs as $prj)
            $echo[] = "\t$prj";
        $echo[] = "\n";
        echo implode("\n",$echo);
    }
}