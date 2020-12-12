<?php
namespace App\Services\Command\Ipblocker;

use App\Services\Command\ACommandService;
use App\Factories\Db as db;

class BotService extends ACommandService
{
    private $db;
    private $ip;
    private const CONTEXT = "ipblocker";
    private const CONTEXT_RO = "ipblocker-ro";

    public function __construct()
    {
        $this->_load_db()
            ->_load_ip();
    }

    private function _load_db()
    {
        $ro = $this->_get_request(3);
        if($ro)
            $this->db = db::get(self::CONTEXT_RO);
        else
            $this->db = db::get(self::CONTEXT);
        return $this;
    }
    
    private function _load_ip()
    {
        $ip = $this->_get_request(2);
        if(!$ip) return "-1";
        $ip = trim($ip);
        $ip = str_replace(",",".",$ip);
        $this->ip = $ip;
    }

    private function _get_bots()
    {
        $sql = "
        SELECT insert_date, remote_ip, user_agent, CONCAT(domain,request_uri) url 
        FROM app_ip_request
        WHERE 1
        AND user_agent LIKE '%bot%'
        ORDER BY insert_date DESC
        ";

        return $this->db->query($sql);
    }

    public function run()
    {
        $this->logpr("START USERAGENT ".self::CONTEXT);
        print_r($this->_get_bots());
        $this->logpr("END USERAGENT");
    }
}