<?php
/**
 * Actualizado: 07/11/2020
 *
 * se lanza a las 03:15 todos los días
 * 15 3 * * *  /usr/bin/php7.4
 */
namespace App\Services\Cron;

final class DbBackupService extends ACronService
{
    private const LOG_PREFIX = "dbbackup";
    private $exclude;
    
    private function _check_intime()
    {
        $now = date("YmdHis");

        $today = date("Ymd");
        $min = "{$today}030000";
        $max = "{$today}040000";
        if(!$this->_get_param("force"))
            if($now<$min || $now>$max) die("Out of time");

        return [
            "min"=>$min, "max"=>$max
        ];
    }

    public function _load_exclude()
    {
        $this->exclude = [
            "ipblocker-ro",
            "ipblocker-test",
            "elchalanwp-test"
        ];
    }

    public function run()
    {
        $this->logpr("START","dbbackupservice.run", self::LOG_PREFIX);
        $this->_load_exclude();
        $r = $this->_check_intime();
        $min = $r["min"];

        $results = [];
        $output = [];

        foreach($this->projects as $context => $arproject)
        {
            if(in_array($context, $this->exclude)) continue;
            if(!$arproject) continue;

            list($dblocal, $server, $port, $database, $user, $password) = array_values($arproject);

            $dbfile = "~/backup_bd/cron_{$dblocal}_{$min}.sql";
            if(is_file($dbfile)) {
                $this->logpr("is_file $dbfile","backup already done");
                die("backup already done");
            }

            $command = "/usr/bin/mysqldump --no-tablespaces --host={$server} --user={$user} --password={$password} {$database} > {$dbfile}";
            //echo "$command \n";
            exec($command, $output, $result);
            //$result = shell_exec($command);
            $results[] = "$context resultado: $result"; // 0:ok, 1:error

        }//foreach this->projects

        $this->logpr($results,"dbbackupservice.run.results", self::LOG_PREFIX);
        $this->logpr("END","dbbackupservice.run", self::LOG_PREFIX);
    }

}//class CronDbbackup
