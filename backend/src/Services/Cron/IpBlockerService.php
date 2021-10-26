<?php
/**
 * Actualizado: 26/10/2021
 * Busca peticiones sospechosas, recupera las ips y las vuelca en blacklist
 */
namespace App\Services\Cron;

use App\Factories\Db;

final class IpBlockerService extends ACronService
{
    private $now;

    private function _add_to_blacklist(): void
    {
        $this->now = date("Y-m-d H:i:00");
        sleep(1);
        $sql = "
        INSERT INTO app_ip_blacklist(remote_ip, reason, is_blocked)
        SELECT DISTINCT remote_ip,'cron - mlicious request',1
        FROM app_ip_request
        WHERE 1
        AND insert_date > CURDATE()
        AND domain IN ('eduardoaf.com','doblerr.es','theframework.es')
        AND (
            request_uri LIKE '%wp_admin%' OR request_uri LIKE '%.env%' OR request_uri LIKE '%.php%'
            OR request_uri LIKE '%wlwmanifest.xml%'
        )
        AND remote_ip NOT IN(
            SELECT DISTINCT remote_ip FROM app_ip_blacklist
        )
        ORDER BY remote_ip DESC
        ";
        db::get("ipblocker")->exec($sql);
    }

    private function get_added_ips(): array
    {
        $sql = "
        SELECT remote_ip 
        FROM app_ip_blacklist
        WHERE 1 
        AND insert_date > '$this->now'
        AND reason LIKE 'cron%'
        ";
        $data = db::get("ipblocker")->query($sql);
        return $data;
    }

    public function run(): void
    {
        $this->logpr("START IPBLOCKER Blacklist");
        $this->_add_to_blacklist();
        $added = $this->get_added_ips();
        $this->logpr($added,"Added IPS");
        $this->logpr("END IPBLOCKER Blacklist");
    }

}//class IpBlockerService
