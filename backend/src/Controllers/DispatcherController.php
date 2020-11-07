<?php
namespace App\Controllers;

final class DispatcherController extends MainController
{
    public function __invoke()
    {
        try
        {
            $service = $this->get_param("service");
            if(!$service) throw new \Exception("Missing service name");

            $class = $this->servicemapper[$service] ?? "";
            if(!$this->_is_service($class)) throw new \Exception("Class \"$class\" not found");

            (new $class())->run();
        }
        catch (\Exception $e)
        {
            $this->logerr($this->argv,"ERROR dispatchcontroller.invoke argv");
            $this->logerr($e->getMessage(),"ERROR dispatchcontroller.invoke message");
        }
    }//__invoke
}
