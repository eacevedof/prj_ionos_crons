<?php
namespace App\Controllers;

final class DispatcherController extends MainController
{
    private function _is_service($class) {return class_exists($class,true);}

    public function __invoke()
    {
        try
        {
            $service = $this->get_param("service");
            if(!$service) throw new \Exception("Missing service name");

            $service = $this->get_parsed_ns($service);
            $class = "App\\Services\\$service";
            if(!$this->_is_service($class)) throw new \Exception("Class $class not found");

            (new $class())->run();
        }
        catch (\Exception $e)
        {
            $this->logerr($this->argv,"ERROR dispatchcontroller.invoke argv");
            $this->logerr($e->getMessage(),"ERROR dispatchcontroller.invoke message");
        }
    }//__invoke
}
