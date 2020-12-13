<?php
namespace App\Services\Command\Ipblocker;

use App\Services\Command\ACommandService;
use App\Factories\Db as db;

class BotService extends ACommandService
{
    private $db;
    private $ctx;
    private const CONTEXT = "ipblocker";
    private const CONTEXT_RO = "ipblocker-ro";

    public function __construct()
    {
        $this->_load_db();
    }

    private function _load_db()
    {
        $ro = $this->_get_request(3);
        if($ro) {
            $this->db = db::get(self::CONTEXT_RO);
            $this->ctx = self::CONTEXT_RO;
        }
        else {
            $this->db = db::get(self::CONTEXT);
            $this->ctx = self::CONTEXT;
        }
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

    private function _get_top_15()
    {
        $sql = "
        SELECT met.user_agent, met.remote_ip, i.country, met.m_reqs, met.m_lastdate
        FROM app_ip i
        INNER JOIN 
        (
            SELECT r.user_agent, r.remote_ip, COUNT(r.id) m_reqs, MAX(r.insert_date) m_lastdate
            FROM app_ip_request r
            WHERE 1
            AND r.user_agent LIKE '%bot%'
            GROUP BY r.user_agent, r.remote_ip
            -- ORDER BY n.reqs DESC
        ) met
        ON i.remote_ip = met.remote_ip
        ORDER BY met.m_reqs DESC, met.m_lastdate DESC
        LIMIT 15
        ";

        return $this->db->query($sql);
    }

    private function _get_names()
    {
        $sql = "
        SELECT r.user_agent, MAX(r.insert_date) m_lastdate
        FROM app_ip_request r
        WHERE 1
        AND r.user_agent LIKE '%bot%'
        GROUP BY r.user_agent, m_lastdate DESC 
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
            case "top": return $this->_get_top_15();
            case "names": return $this->_get_names();
            default: return [];
        }
    }

    public function run()
    {
        $this->logpr("START BOT CONTEXT:{$this->ctx}");
        print_r($this->_get_result());
        $this->logpr("END BOT");
    }
}