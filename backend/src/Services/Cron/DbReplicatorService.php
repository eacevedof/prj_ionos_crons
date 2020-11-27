<?php
/**
 * Actualizado: 27/11/2020
 * Replica un dump en bds ReadOnly
 */
namespace App\Services\Cron;

use App\Factories\Db as db;

final class DbReplicatorService extends AbstractService
{
    /**
     * @var string
     */
    private static $PATH_DUMPSDS;
    private $config;
    private $dumps;
    private $tmpdump;
    
    public function __construct()
    {
        parent::__construct();
        $home = $this->_get_env("HOME");
        self::$PATH_DUMPSDS = "$home/backup_bd/";
        $this->_load_config();
    }

    private function _load_config()
    {
        $this->config = [
            "ipblocker" => ["ipblocker-ro"],
        ];
    }

    private function _load_dumps()
    {
        $dumps = scandir(self::$PATH_DUMPSDS);
        arsort($dumps);
        $this->dumps = $dumps;
    }

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

    private function _get_lastdump($prefix)
    {
        foreach ($this->dumps as $file)
        {
            if(strpos($file,$prefix)===0)
                return $file;
        }
        return "";
    }

    private function _create_tmpdump($file)
    {
        $path = self::$PATH_DUMPSDS.$file;
        $content = file_get_contents($path);
        $arcontent = explode("\n",$content);
        array_splice($arcontent,-11);
        array_splice($arcontent,17);
        $content = implode("\n",$arcontent);
        $this->tmpdump = "tmp_".uniqid().".sql";
        $this->tmpdump = self::$PATH_DUMPSDS.$this->tmpdump;
        file_put_contents($path,$content);
        //obtener el ultimo backup de un contexto
        //recuperar su contenido
        //limpiar 16 lineas por arriba
        //limpiar 10 por abajo
        //guardar un tmp-uniqid.sql para restore
        //ejecutar
        // ssh.cmd(f"mysql --host={dbserver} --user={dbuser} --password={dbpassword} {dbname} < $HOME/{pathremote}/db/temp.sql")
    }

    public function run()
    {
        $this->logpr("START","dbbackupservice.run");
        $r = $this->_check_intime();
        $min = $r["min"];

        $results = [];
        $output = [];

        foreach ($this->config as $ctxfrom => $arto)
        {
            foreach ($arto as $ctxto)
            {
                $arproject = $this->projects[$ctxfrom] ?? "";
                if(!$arproject) continue;

                $dblocal = $arproject["dblocal"];
                $prefix = "cron_{$dblocal}_";
                $filename = $this->_get_lastdump($prefix);
                if(!$filename) continue;

                $arproject = $this->projects[$ctxto] ?? "";
                if(!$arproject) continue;
                list($dblocal, $server, $port, $database, $user, $password) = array_values($arproject);
                $this->_create_tmpdump($filename);
                if(!is_file($this->tmpdump)) continue;
                
                $command = "mysql --host={$server} --user={$user} --password={$password} {$dblocal} < $this->tmpdump"
                exec($command, $output, $result);
            }
        }


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
