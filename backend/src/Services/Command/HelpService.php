<?php
namespace App\Services\Command;

use App\Component\ColorComponent as Color;
use App\Component\ArrayqueryComponent as ArrayQuery;
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

    private function _in_filter($cmd, $arinfo, $filter):bool
    {
        if(!$filter) return true;
        if(strstr($cmd,$filter)) return true;
        $json = json_encode(array_values($arinfo));
        if(strstr($json,$filter)) return true;
        return false;
    }

    private function _get_basic(string $filter): array
    {
        $basic = [];
        foreach ($this->help as $cmd => $arinfo)
        {
            if($this->_in_filter($cmd, $arinfo, $filter))
            {
                $basic[$cmd] = $arinfo;
            }
        }
        return $basic;
    }

    private function _get_echo($glue="\n")
    {
        return implode($glue,$this->echo);
    }

    private function _param_basic(string $filter): string
    {
        $cmds = $this->_get_basic($filter);
        foreach ($cmds as $cmd => $arinfo)
        {
            $cmd = Color::text($cmd,Color::LIGHT_GREEN);
            $description = $arinfo["description"] ?? "";
            $description = Color::text($description, Color::LIGHT_WHITE);
            $this->echo[] = "$cmd:\n  $description";
        }

        return $this->_get_echo();
    }

    private function _param_all(string $filter): string
    {}

    private function _param_projects(string $filter): string
    {}

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