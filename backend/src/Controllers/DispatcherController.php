<?php
namespace App\Controllers;
use App\Component\ColorComponent as Color;

final class DispatcherController extends MainController
{
    public function __invoke()
    {
        $this->log("dipsatcher.invoke");
        try
        {
            $service = $this->get_param("service");
            if(!$service) $service = $this->get_param(1);
            if(!$service) $service = "help";

            $class = $this->services[$service] ?? "";
            if(!$this->_is_service($class)) throw new \Exception("Class \"$class\" not found for service or command $service");

            (new $class())->run();
        }
        catch (\Exception $e)
        {
            //$this->logpr(PATH_ROOT,"PATH_ROOT");
            $title = Color::text("EXCEPTION dispatcher.argv (trace)",Color::LIGHT_RED);
            $this->logerr($this->argv, $title);
            $title = Color::text("EXCEPTION dispatcher",Color::LIGHT_RED);
            $this->logerr($e->getMessage(), $title);
        }
    }//__invoke
}
