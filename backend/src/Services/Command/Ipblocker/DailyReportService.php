<?php
namespace App\Services\Command\Ipblocker;

use App\Services\Command\ACommandService;
use App\Factories\Db as db;
use function App\Functions\get_config;
use App\Component\Html\ArrayToTable;
use App\Component\Email\EmailComponent;

final class DailyReportService extends ACommandService
{
    private const LOG_PREFIX = "dailyreport";
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
        SELECT DISTINCT bots.*, app_ip.country, bl.reason, bl.insert_date block_date
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
        SELECT bots.*, app_ip.country, app_ip.`whois`, IF(bl.id IS NULL,'','yes') blocked, bl.insert_date block_date, bl.reason
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
        SELECT bots.*, app_ip.country, IF(bl.id IS NULL,'','yes') blocked, bl.insert_date block_date, bl.reason
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
        ) bots
        LEFT JOIN app_ip
        ON app_ip.remote_ip = bots.remote_ip
        LEFT JOIN app_ip_blacklist bl
        ON bots.remote_ip = bl.remote_ip
        ORDER BY num_visits DESC
        LIMIT 25
        ";

        return $this->db->query($sql);
    }

    private function _get_most_visited_urls_by_no_bots_and_non_blocked():array
    {
        $sql = "
        SELECT DISTINCT `domain`
        FROM app_ip_request
        WHERE 1 
        AND insert_date LIKE '{$this->yesterday}%'
        ORDER BY 1 ASC
        ";
        $domains = $this->db->query($sql);
        $domains = array_column($domains,"domain");

        $result = [];
        foreach ($domains as $domain)
        {
            $sql = "
            SELECT CONCAT(`domain`, request_uri) request_uri, COUNT(id) num_visits
            FROM `app_ip_request`
            WHERE 1 
            AND insert_date LIKE '{$this->yesterday}%'
            AND `domain`='$domain'  
            AND (
                TRIM(user_agent)!='' AND user_agent NOT LIKE '%bot%' AND user_agent NOT LIKE '%crawl%' AND user_agent NOT LIKE '%ALittle%'
                AND user_agent NOT LIKE '%spider%' AND user_agent NOT LIKE '%Go-http-client%' AND user_agent NOT LIKE '%facebookexternalhit%'
                AND user_agent NOT LIKE '%evc-batch%'
            )
            AND remote_ip NOT IN (SELECT remote_ip FROM app_ip_blacklist WHERE 1 AND is_blocked=1)
            GROUP BY CONCAT(`domain`, request_uri)
            ORDER BY `domain` ASC, num_visits DESC, request_uri ASC
            ";
            $r = $this->db->query($sql);
            if($r) $result[$domain] = $r;
        }
        return $result;
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
        SELECT DISTINCT nobots.*, app_ip.country, IF(bl.id IS NULL,'','yes') blocked, 
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
        ORDER BY num_visits DESC,  bl.insert_date DESC, country
        ";
        return $this->db->query($sql);
    }

    private function _get_eduardoaf_root_requests(): array
    {
        $sql = "
        SELECT app_ip.country, app_ip.whois, 
        IF(bl.id IS NULL,'','yes') blocked,
        eduardoaf.*
        FROM
        (
            SELECT remote_ip, user_agent, insert_date, `post`, `get`, files
            FROM `app_ip_request`
            WHERE 1 
            AND insert_date LIKE '{$this->yesterday}%'
            AND `domain`='eduardoaf.com'
            AND request_uri='/' 
            AND (
                TRIM(user_agent)!='' AND user_agent NOT LIKE '%bot%' AND user_agent NOT LIKE '%crawl%' AND user_agent NOT LIKE '%ALittle%'
                AND user_agent NOT LIKE '%spider%' AND user_agent NOT LIKE '%Go-http-client%' AND user_agent NOT LIKE '%facebookexternalhit%'
                AND user_agent NOT LIKE '%evc-batch%'
            )
        ) eduardoaf
        LEFT JOIN app_ip
        ON eduardoaf.remote_ip = app_ip.remote_ip
        LEFT JOIN app_ip_blacklist bl
        ON eduardoaf.remote_ip = bl.remote_ip
        ORDER BY country ASC, remote_ip
        ";
        return $this->db->query($sql);
    }

    private function _get_new_blocked_ips(): array
    {
        $sql = "
        SELECT bl.remote_ip, ip.country, ip.`whois`, bl.reason, bl.insert_date block_date
        FROM app_ip_blacklist bl
        LEFT JOIN app_ip ip
        ON bl.remote_ip = ip.remote_ip
        WHERE 1
        AND is_blocked=1
        AND bl.insert_date LIKE '{$this->yesterday}%'
        ORDER BY bl.insert_date DESC
        ";
        return $this->db->query($sql);
    }

    private function _get_num_visits_by_all(): array
    {
        $sql = "
        SELECT ip.remote_ip, ip.country, ip.`whois`, remotes.num_visits, bl.insert_date block_date, bl.reason, user_agent
        FROM
        (
            SELECT remote_ip, COUNT(id) num_visits, MAX(user_agent) user_agent
            FROM `app_ip_request`
            WHERE 1 
            AND insert_date LIKE '{$this->yesterday}%'
            GROUP BY remote_ip
        ) remotes
        LEFT JOIN app_ip ip
        ON remotes.remote_ip = ip.remote_ip
        LEFT JOIN app_ip_blacklist bl 
        ON remotes.remote_ip = bl. remote_ip
        ORDER BY num_visits DESC, country ASC
        ";
        return $this->db->query($sql);
    }

    private function _get_html(array $data, string $h3, string $footer=""): string
    {
        return (new ArrayToTable($data, $h3, $footer))();
    }

    private function _send(string $content): void
    {
        $this->logpr("email._send", "", self::LOG_PREFIX);
        $emails = get_config("emails");
        $config = $emails["configs"][0];

        $r = EmailComponent::get($config)
            ->set_from($config["email"])
            ->add_to($emails["contacts"][0])  //gmail
            ->set_subject("Daily security report on $this->yesterday")
            ->set_content($content)
            ->send()
            ->get_errors()
        ;
        $this->logpr($r, "error on send?", "", self::LOG_PREFIX);
    }

    public function run()
    {
        $this->logpr("START DAILYREPORT {$this->yesterday}", "", self::LOG_PREFIX);
        $html = [];

        $data = $this->_get_new_blocked_ips();
        $html[] = $this->_get_html($data, "New blocked");

        $data = $this->_get_most_visited_urls_by_no_bots_and_non_blocked();
        $domains = array_keys($data);
        foreach ($domains as $domain)
        {
            $bydomain = $data[$domain];
            $sum = array_column($bydomain, "num_visits");
            $sum = array_sum($sum);
            $html[] = $this->_get_html(
                $bydomain,
                "Most visited urls by no bots and no blocked in $domain",
                "total visits: $sum"
            );
        }

        $data = $this->_get_eduardoaf_root_requests();
        $html[] = $this->_get_html($data, "eduardoaf root by no bots");

        $data = $this->_get_blocked_ips_and_num_visits_of_no_bots();
        $html[] = $this->_get_html($data, "Blocked ips and num visits of no bots");

        $data = $this->_get_max_requests_by_no_bots();
        $html[] = $this->_get_html($data, "Max requests by no bots");

        $data = $this->_get_requests_by_bots();
        $html[] = $this->_get_html($data, "Requests made by bots");

        $data = $this->_get_num_visits_by_country_no_bots();
        $html[] = $this->_get_html($data, "Num of visits by country no bots");

        $data = $this->_get_anonymous_requests();
        $html[] = $this->_get_html($data, "Anonymous requests");

        $data = $this->_get_num_visits_by_all();
        $sum = array_column($data, "num_visits");
        $sum = array_sum($sum);
        $html[] = $this->_get_html(
            $data,
            "All visits",
            "total visits: $sum"
        );

        $html = implode("\n", $html);
        $this->_send($html);
        $this->logpr("END DAILYREPORT", "", self::LOG_PREFIX);
    }
}