<?php
namespace App\Services\Command;

use App\Component\ColorComponent as Color;
use function \App\Functions\get_config;

class HelpService extends ACommandService
{
    private $help;
    private $echo = [];

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

    private function _param_basic(string $filter): string
    {

        foreach ($cmds as $cmd => $class)
        {
            $cmd = Color::text($cmd,Color::LIGHT_GREEN);
            $class = Color::text($class, Color::LIGHT_WHITE);
            $echo[] = "$cmd:\n  $class";
        }
    }

    public function run()
    {
        $param = $this->_get_request(2);
        $filter = $this->_get_param("f");

        $this->echo[] = Color::text("\t\tHELP MENU",Color::LIGHT_GREEN);
        switch ($param)
        {
            case "":
                echo $this->_param_basic($filter);
            break;
            case "all":
                echo $this->_param_all($filter);
            break;
            case "projects":
                echo $this->_param_projects($filter);
                break;
            default:
                echo Color::text("param not found",Color::LIGHT_YELLOW);
        }






    }
}