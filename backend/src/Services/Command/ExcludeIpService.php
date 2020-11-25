<?php
namespace App\Services\Command;

class ExcludeIpService extends AbstractService
{
    public function run()
    {
        $ip = $this->_get_param(2);
        if(!filter_var($ip, FILTER_VALIDATE_IP))
            throw new \Exception("Wrong ip value");

    }


}