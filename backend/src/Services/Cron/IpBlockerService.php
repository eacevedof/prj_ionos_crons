<?php
/**
 * Actualizado: 27/11/2020
 * Replica un dump en bds ReadOnly
 */
namespace App\Services\Cron;

use App\Factories\Db;

final class IpBlockerService extends ACronService
{
    private function _add_to_blacklist()
    {
        $today = date("Y-m-d");
        $sql = "
        INSERT INTO app_ip_blacklist(remote_ip,reason,is_blocked)
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

    public function run(): void
    {
        $this->logpr("START IPBLOCKER Blacklist");
        $this->_add_to_blacklist();
        $this->logpr("END IPBLOCKER Blacklist");
    }

}//class IpBlockerService
