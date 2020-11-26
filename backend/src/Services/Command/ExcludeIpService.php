<?php
namespace App\Services\Command;

class ExcludeIpService extends AbstractService
{
    private $ip;

    public function __construct()
    {
        $this->ip = $this->_get_ip();
    }

    private function _exists_ip()
    {

    }

    private function _save_ip()
    {

    }

    private function _get_ip()
    {
        $ip = $this->_get_param(2);
        $ip = trim($ip);
        $ip = str_replace(",",".",$ip);
        return $ip;
    }

    private function _exceptions()
    {
        if(!filter_var($this->ip, FILTER_VALIDATE_IP))
            throw new \Exception("\nWrong ip value\n");
    }

    private function _pr()
    {
        echo "IP saved $this->ip}";
    }

    public function run()
    {
        $this->_exceptions();
        if(!$this->_exists_ip()){
            $this->_save_ip();
            $this->_pr();
        }
    }

}