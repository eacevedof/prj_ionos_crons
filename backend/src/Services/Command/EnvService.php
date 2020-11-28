<?php
namespace App\Services\Command;

class EnvService extends ACommandService
{
    public function run()
    {
        $this->logpr("START ENVSERVICE");
        print_r($_ENV);
        $this->logpr("END ENVSERVICE");
    }
}