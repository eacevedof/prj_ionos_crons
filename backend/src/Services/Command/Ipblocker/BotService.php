<?php
namespace App\Services\Command\Ipblocker;

use App\Services\Command\ACommandService;
use App\Factories\Db as db;
use phpDocumentor\Reflection\Utils;
use function PHPUnit\Framework\stringEndsWith;

class BotService extends ACommandService
{
    private $db;
    private $ip;
    private const CONTEXT = "ipblocker";
    private const CONTEXT_RO = "ipblocker-ro";

    public function __construct()
    {
        $this->_load_db();
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

    private function _get_all()
    {
        $sql = "
        SELECT DISTINCT r.remote_ip, 
        i.country, i.whois,
        r.user_agent
        FROM app_ip_request r
        INNER  JOIN app_ip i
        ON r.remote_ip = i.remote_ip
        WHERE 1
        AND r.user_agent LIKE '%bot%'
        ORDER BY r.insert_date DESC
        ";

        return $this->db->query($sql);
    }

    private function _get_top_10()
    {
        $sql = "
        SELECT DISTINCT r.remote_ip, 
        i.country, i.whois,
        r.user_agent
        FROM app_ip_request r
        INNER  JOIN app_ip i
        ON r.remote_ip = i.remote_ip
        WHERE 1
        AND r.user_agent LIKE '%bot%'
        ORDER BY r.insert_date DESC
        ";

        return $this->db->query($sql);
    }

    private function _get_result()
    {
        $param = $this->_get_request(2);
        $param = strtolower(trim($param));
        if(!$param) return [];

        switch ($param)
        {
            case "all": return $this->_get_all();
            case "top": return $this->_get_top_10();
            default: return [];
        }
    }

    public function run()
    {
        $this->logpr("START BOT ".self::CONTEXT);
        print_r($this->_get_result());
        $this->logpr("END BOT");
    }
}