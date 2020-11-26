<?php
namespace App\Services\Command;

use App\Factories\Db as db;

class ExcludeIpService extends AbstractService
{
    private $ip;

    public function __construct()
    {
        parent::__construct();
        $this->ip = $this->_get_ip();
    }

    private function _is_table($context)
    {
        return db::get($context)->is_table("app_ip_untracked");
    }

    private function _exists_ip($context)
    {
        $sql = "SELECT id FROM app_ip_untracked WHERE remote_ip='{$this->id}'";
        return db::get($context)->query($sql,0,0);
    }

    private function _save_ip($context)
    {
        $sql = "INSERT INTO app_ip_untracked (remote_ip) VALUES('{$this->ip}')";
        db::get($context)->exec($sql);
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
        $contexts = array_keys($this->projects);

        foreach ($contexts as $context) {
            if(in_array($context,["upload","tools"]))
                continue;

            if (!$this->_is_table($context))
                continue;

            if (!$this->_exists_ip($context)) {
                $this->_save_ip($context);
                $this->_pr($context);
            }
        }
    }

}