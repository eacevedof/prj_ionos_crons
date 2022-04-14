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

    private function _get_requests_by_bots(): array
    {
        $sql = "
        SELECT bots.*, app_ip.country, IF(bl.id IS NULL,'','blocked') is_blocked, bl.reason
        FROM
        (
            SELECT user_agent, MIN(insert_date) first_visit, MAX(insert_date) last_visit, MAX(remote_ip) remote_ip
            FROM `app_ip_request`
            WHERE 1 
            AND insert_date LIKE '{$this->yesterday}%'
            AND (
                user_agent LIKE '%bot%' OR user_agent LIKE '%crawl%' OR user_agent LIKE '%ALittle%'
                OR user_agent LIKE '%spider%' OR user_agent LIKE '%Go-http-client%' 
                OR user_agent LIKE '%facebookexternalhit%' OR user_agent LIKE '%evc-batch%'
            )
            GROUP BY user_agent
        ) bots
        LEFT JOIN app_ip
        ON app_ip.remote_ip = bots.remote_ip
        LEFT JOIN app_ip_blacklist bl
        ON bots.remote_ip = bl.remote_ip
        ORDER BY first_visit, last_visit
        ";
        return $this->db->query($sql);
    }

    private function _get_anonymous_requests(): array
    {
        $sql = "
        SELECT bots.*, app_ip.country, IF(bl.id IS NULL,'','blocked') is_blocked, bl.insert_date block_date, bl.reason
        FROM
        (
            SELECT remote_ip, MIN(insert_date) first_visit, MAX(insert_date) last_visit, MAX(CONCAT(`domain`,request_uri)) request_uri
            FROM `app_ip_request`
            WHERE 1 
            AND insert_date LIKE '{$this->yesterday}%'
            AND TRIM(user_agent)=''
            GROUP BY remote_ip
        ) bots
        LEFT JOIN app_ip
        ON app_ip.remote_ip = bots.remote_ip
        LEFT JOIN app_ip_blacklist bl
        ON bots.remote_ip = bl.remote_ip
        ORDER BY first_visit, last_visit
        ";
        return $this->db->query($sql);
    }

    private function _get_max_requests_by_no_bots(): array
    {
        $sql = "
        SELECT bots.*, app_ip.country, IF(bl.id IS NULL,'','blocked') is_blocked, bl.insert_date block_date, bl.reason
        FROM
        (
            SELECT remote_ip, COUNT(id) num_visits, MAX(CONCAT(`domain`, request_uri)) request_uri, MAX(user_agent) user_agent
            FROM `app_ip_request`
            WHERE 1 
            AND insert_date LIKE '{$this->yesterday}%'
            AND (
                TRIM(user_agent)!='' AND user_agent NOT LIKE '%bot%' AND user_agent NOT LIKE '%crawl%' AND user_agent NOT LIKE '%ALittle%'
                AND user_agent NOT LIKE '%spider%' AND user_agent NOT LIKE '%Go-http-client%' AND user_agent NOT LIKE '%facebookexternalhit%'
                AND user_agent NOT LIKE '%evc-batch%'
            )
            GROUP BY remote_ip
            ORDER BY COUNT(id) DESC
        ) bots
        LEFT JOIN app_ip
        ON app_ip.remote_ip = bots.remote_ip
        LEFT JOIN app_ip_blacklist bl
        ON bots.remote_ip = bl.remote_ip
        ORDER BY user_agent ASC
        ";

        return $this->db->query($sql);
    }

    private function _get_most_visited_urls_by_no_bots():array
    {
        $sql = "
        SELECT CONCAT(`domain`, request_uri) request_uri, COUNT(id) num_visits
        FROM `app_ip_request`
        WHERE 1 
        AND insert_date LIKE '{$this->yesterday}%'
        AND (
            TRIM(user_agent)!='' AND user_agent NOT LIKE '%bot%' AND user_agent NOT LIKE '%crawl%' AND user_agent NOT LIKE '%ALittle%'
            AND user_agent NOT LIKE '%spider%' AND user_agent NOT LIKE '%Go-http-client%' AND user_agent NOT LIKE '%facebookexternalhit%'
            AND user_agent NOT LIKE '%evc-batch%'
        )
        GROUP BY CONCAT(`domain`, request_uri)
        ORDER BY `domain` ASC, 2 DESC, 1 ASC
        ";
        return $this->db->query($sql);
    }

    private function _get_num_visits_by_country_no_bots(): array
    {
        $sql = "
        SELECT app_ip.country, SUM(num_visits) num_visits
        FROM
        (
            SELECT remote_ip, COUNT(id) num_visits
            FROM `app_ip_request`
            WHERE 1 
            AND insert_date LIKE '{$this->yesterday}%'
            AND (
                TRIM(user_agent)!='' AND user_agent NOT LIKE '%bot%' AND user_agent NOT LIKE '%crawl%' AND user_agent NOT LIKE '%ALittle%'
                AND user_agent NOT LIKE '%spider%' AND user_agent NOT LIKE '%Go-http-client%' AND user_agent NOT LIKE '%facebookexternalhit%'
                AND user_agent NOT LIKE '%evc-batch%'
            )
            GROUP BY remote_ip
        ) visits
        LEFT JOIN app_ip
        ON app_ip.remote_ip = visits.remote_ip
        GROUP BY country
        ORDER BY num_visits DESC, country
        ";
        return $this->db->query($sql);
    }

    private function _get_user_agents(): array
    {
        $sql = "
        SELECT insert_date, user_agent, CONCAT(`domain`,request_uri) url 
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