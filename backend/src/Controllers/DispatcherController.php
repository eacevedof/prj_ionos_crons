<?php
namespace App\Controllers;

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
            $this->logerr($this->argv,"EXCEPTION dispatcher.argv (trace)");
            $this->logerr($e->getMessage(),"EXCEPTION dispatcher");
        }
    }//__invoke
}
