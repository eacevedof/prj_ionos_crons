<?php
namespace App\Services\Command\Ipblocker;

use App\Factories\Db as db;

class UseragentService extends ACommandService
{
    private $db;
    private $ip;

    public function __construct()
    {
        $this->db = db::get("ipblocker");
        $this->ip = $this->_get_ip();
    }

    private function _get_ip()
    {
        $ip = $this->_get_request(2);
        if(!$ip) return "-1";
        $ip = trim($ip);
        $ip = str_replace(",",".",$ip);
        return $ip;
    }

    private function _get_user_agents()
    {
        $sql = "
        SELECT insert_date, remote_ip, user_agent, CONCAT(domain,request_uri) url 
        FROM app_ip_request
        WHERE 1
        AND remote_ip='$this->ip'
        ORDER BY insert_date DESC
        ";

        return $this->db->query($sql);
    }

    public function run()
    {
        $this->logpr("START USERAGENT");
        print_r($this->_get_user_agents());
        $this->logpr("END USERAGENT");
    }
}