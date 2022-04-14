<?php
namespace App\Services\Command\Ipblocker;

use App\Component\Email\EmailComponent;
use App\Services\Command\ACommandService;
use App\Factories\Db as db;
use function App\Functions\get_config;

final class DailyReportService extends ACommandService
{
    private $db;
    private string $yesterday;

    public function __construct()
    {
        $this->db = db::get("ipblocker-test");
        $this->yesterday = date("Y-m-d", strtotime("-1 days"));
        if($value = $this->_get_request(2)) $this->yesterday = (string)$value;
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

    private function _get_blocked_ips_and_num_visits_of_no_bots(): array
    {
        $sql = "
        SELECT nobots.*, app_ip.country, IF(bl.id IS NULL,'','blocked') is_blocked, 
        bl.insert_date block_date, bl.reason
        FROM
        (
            SELECT remote_ip, COUNT(id) num_visits
            FROM `app_ip_request`
            WHERE 1 
            AND insert_date LIKE '{$this->yesterday}%'
            AND remote_ip IN (SELECT remote_ip FROM app_ip_blacklist)
            AND (
                TRIM(user_agent)!='' AND user_agent NOT LIKE '%bot%' AND user_agent NOT LIKE '%crawl%' AND user_agent NOT LIKE '%ALittle%'
                AND user_agent NOT LIKE '%spider%' AND user_agent NOT LIKE '%Go-http-client%' AND user_agent NOT LIKE '%facebookexternalhit%'
                AND user_agent NOT LIKE '%evc-batch%'
            )
            GROUP BY remote_ip
        ) nobots
        LEFT JOIN app_ip
        ON app_ip.remote_ip = nobots.remote_ip
        LEFT JOIN app_ip_blacklist bl
        ON nobots.remote_ip = bl.remote_ip
        ORDER BY bl.insert_date DESC, num_visits DESC, country
        ";
        return $this->db->query($sql);
    }

    private function _get_html(array $data, string $h3): string
    {
        $titles = array_keys($data[0] ?? []);
        if(!$titles) return "<h3>$h3</h3>";
        $html = [
            "<hr/>",
            "<br/>",
            "<h3>$h3</h3>",
            "<table>"
        ];
        $tmp = [];
        foreach ($titles as $title) {
            $tmp[] = "<th>$title</th>";
        }
        $tmp = implode("", $tmp);
        $html[] = "<tr>$tmp</tr>";

        foreach ($data as $row) {
            $tmp = [];
            foreach ($titles as $field) {
                $value = $row[$field];
                $value = htmlentities($value);
                $tmp[] = "<td>{$value}</td>";
            }
            $tmp = implode("", $tmp);
            $html[] = "<tr>$tmp</tr>";
        }
        $html[] = "</table>";
        return implode("\n", $html);
    }

    private function _send(string $content): void
    {
        $this->logpr("email._send");
        $emails = get_config("emails");
        $config = $emails["configs"][0];

        $r = EmailComponent::get($config)
            ->set_from($config["email"])
            ->add_to($emails["contacts"][0])  //gmail
            ->set_subject("Daily report of $this->yesterday")
            ->set_content($content)
            ->send()
            ->get_errors()
        ;
        $this->logpr($r, "error on send?");
    }

    public function run()
    {
        $this->logpr("START DAILYREPORT");
        $html = [];

        $data = $this->_get_anonymous_requests();
        $html[] = $this->_get_html($data, "Anonymous requests");

        $data = $this->_get_blocked_ips_and_num_visits_of_no_bots();
        $html[] = $this->_get_html($data, "Blocked ips and num visits of no bots");

        $data = $this->_get_max_requests_by_no_bots();
        $html[] = $this->_get_html($data, "Max requests by no bots");

        $data = $this->_get_most_visited_urls_by_no_bots();
        $html[] = $this->_get_html($data, "Most visited urls by no bots");

        $data = $this->_get_requests_by_bots();
        $html[] = $this->_get_html($data, "Requests made by bots");

        $data = $this->_get_num_visits_by_country_no_bots();
        $html[] = $this->_get_html($data, "Num of visits by country no bots");

        $html = implode("\n", $html);
        $this->_send($html);
        $this->logpr("END DAILYREPORT");
    }
}