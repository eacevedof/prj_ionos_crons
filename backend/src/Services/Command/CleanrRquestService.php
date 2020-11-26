<?php
namespace App\Services\Command;

use App\Factories\Db as db;

class CleanRequest extends AbstractService
{
    private $ip;

    /**
     * @var \App\Component\QueryComponent
     */
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->ip = $this->_get_ip();
        $this->db = db::get("ipblocker");
    }


    private function _get_ip()
    {
        $ip = $this->_get_param(2);
        if(!$ip) return "-1";
        $ip = trim($ip);
        $ip = str_replace(",",".",$ip);
        return $ip;
    }

    private function _pr()
    {
        echo "\nIP {$this->ip} in context ipblocker\n";
    }

    private function _delete_fromip()
    {
        $sql = "DELETE FROM app_ip_request WHERE 1 AND remote_ip='{$this->ip}'";
        $this->db->exec($sql);
        $r = $this->db->exec($sql);
        $this->logpr($r,"_delete_fromip");
    }

    private function _delete_apple_icons()
    {
        $sql = "
        DELETE t.* 
        FROM `app_ip_request` t 
        WHERE 1
        AND t.id IN 
        ( 
            SELECT id 
            FROM 
            (
                SELECT id 
                FROM `app_ip_request`
                WHERE 1 
                AND request_uri LIKE '%apple%icon%'
            ) as ids
        )        
        ";
        $r = $this->db->exec($sql);
        $this->logpr($r,"_delete_apple_icons");
    }

    public function run()
    {
        $this->_pr();
        $this->_delete_apple_icons();
        $this->_delete_fromip();
    }

}