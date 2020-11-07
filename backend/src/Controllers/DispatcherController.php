<?php
namespace App\Controllers;

class DispatcherController extends MainController
{
    public function __invoke()
    {
        $service = $this->get_param("service");
        if(!$service) throw new \Exception("Missing service name");

        $service = $this->get_parsed_ns($service);
        $class = "App\\Services\\$service";
        try {

        }
        catch (\Exception $e){

        }

    }
}
