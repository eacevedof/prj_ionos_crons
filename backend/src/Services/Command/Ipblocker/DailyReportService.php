<?php
namespace App\Services\Command\Ipblocker;

use App\Services\Command\ACommandService;
use App\Factories\Db as db;

final class DailyReportService extends ACommandService
{
    private $db;
    private string $yesterday;

    public function __construct()
    {
        $this->db = db::get("ipblocker-ro");
        $this->yesterday = date("Y-m-d", strtotime("-1 days"));
    }

    private function _get_bots(): array
    {
        $sql = "
        SELECT bots.*, app_ip.country, IF(bl.id IS NULL,'','blocked'), bl.reason
        FROM
        (
            SELECT user_agent, MIN(insert_date) first_visit, MAX(insert_date) last_visit, MAX(remote_ip) remote_ip
            FROM `app_ip_request`
            WHERE 1 
            AND insert_date LIKE '{$this->yesterday}%'
            AND (user_agent LIKE '%bot%')
            GROUP BY user_agent
            -- order by user_agent desc
        ) bots
        LEFT JOIN app_ip
        ON app_ip.remote_ip = bots.remote_ip
        LEFT JOIN app_ip_blacklist bl
        ON bots.remote_ip = bl.remote_ip
        ORDER BY first_visit, last_visit
        ";
        return $this->db->query($sql);
    }


    private function _get_user_agents(): array
    {
        $sql = "
        SELECT insert_date, user_agent, CONCAT(domain,request_uri) url 
        FROM app_ip_request
        WHERE 1
        AND remote_ip='$this->ip'
        AND COALESCE(TRIM(user_agent),'') != '' 
        ORDER BY insert_date DESC
        ";

        return $this->db->query($sql);
    }

    public function run()
    {
        $this->logpr("START DAILYREPORT");

        $this->logpr("END DAILYREPORT");
    }
}