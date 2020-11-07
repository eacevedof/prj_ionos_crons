<?php
//fecha: 07/11/2020
namespace App\Crons;

include_once("icronable.php");
include_once("cron_abstract.php");

final class CronDbbackup extends AbstractCron implements Icronable
{

    private function _start()
    {
        $now = date("Ymd H:i:s");
        $msg = "START crondbbackup.execute $now \n";
        echo $msg;
    }

    private function _end()
    {
        $now = date("Ymd H:i:s");
        $msg = "END crondbbackup.execute $now \n";
        echo $msg;
    }

    public function run()
    {
        //echo "<pre>";
        $this->_start();
        $now = date("YmdHis");
        //$hour = substr($now,0,10)
        $todday = date("Ymd");
        $min = "{$todday}030000";
        $max = "{$todday}040000";
        if($now<$min || $now>$max) die("Out of time");

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

        //print_r($output);
        print_r($results);

        if($results){
            $json = json_encode($results, 1);
            $logfile = "cronlog_${now}.log";
            file_put_contents($logfile, $json);
        }

        $this->_end();
    }

}//class CronDbbackup

(new CronDbbackup())->run();
