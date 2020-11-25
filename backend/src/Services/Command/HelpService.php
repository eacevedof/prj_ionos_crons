<?php
namespace App\Services\Command;

class HelpService extends AbstractService
{
    private function get_commands()
    {
        $params = include(PATH_CONFIG.DS."services.php");
        $cmds = array_filter($params, function($item){
            return strstr($item,"\\Command\\");
        });
        return $cmds;
    }

    public function run()
    {
        $cmds = $this->get_commands();
        $echo[] = "";
        foreach ($cmds as $cmd => $class){
            $echo[] = "$cmd\n:\t$class";
        }
        $echo[] = "";
        echo implode("\n",$echo);
    }
}