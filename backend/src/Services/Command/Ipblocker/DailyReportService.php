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
        SELECT user_agent, MIN(insert_date) first_visit, MAX(insert_date) last_visit
        FROM `app_ip_request`
        WHERE 1 
        AND insert_date LIKE '2021-12-27%'
        AND (user_agent LIKE '%bot%' OR TRIM(user_agent)='')
        GROUP BY user_agent
        ORDER BY user_agent DESC
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