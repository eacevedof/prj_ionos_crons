<?php
/**
 * Actualizado: 07/11/2020
 * crontab -l
 * 15 3 * * *  /usr/bin/php7.4  $HOME/mi_common/crons/cron_dbbackup.php
 */
namespace App\Services\Cron;

final class DbbackupService extends AbstractService implements ICronable
{
    private function _check_intime()
    {
        $now = date("YmdHis");

        $today = date("Ymd");
        $min = "{$today}030000";
        $max = "{$today}040000";
        if($now<$min || $now>$max) die("Out of time");
        return [
            "min"=>$min, "max"=>$max
        ];
    }

    public function run()
    {
        $this->logpr("START","dbbackupservice.run");
        $r = $this->_check_intime();
        $min = $r["min"];

        $results = [];
        $output = [];

        foreach($this->projects as $alias => $arproject)
        {
            if(!$arproject) continue;

            list($dblocal, $server, $port, $database, $user, $password) = array_values($arproject);

            $dbfile = "~/backup_bd/cron_{$dblocal}_{$min}.sql";
            if(is_file($dbfile)) die("backup already done");

            $command = "/usr/bin/mysqldump --no-tablespaces --host={$server} --user={$user} --password={$password} {$database} > {$dbfile}";
            //echo "$command \n";
            exec($command, $output, $result);
            //$result = shell_exec($command);
            $results[] = "$alias resultado: $result"; // 0:ok, 1:error
        }//forach

        $this->log($results,"dbbackupservice.run.results");
        $this->logpr("END","dbbackupservice.run");
    }

}//class CronDbbackup
