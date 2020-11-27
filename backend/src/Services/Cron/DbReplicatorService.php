<?php
/**
 * Actualizado: 27/11/2020
 * Replica un dump en bds ReadOnly
 */
namespace App\Services\Cron;

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

        $this->_load_pathdumps()
            ->_load_config()
            ->_load_dumps();
    }

    private function _load_pathdumps()
    {
        $home = $this->_get_env("HOME");
        self::$PATH_DUMPSDS = "$home/backup_bd/";
        return $this;
    }

    private function _load_config()
    {
        $this->config = [
            "ipblocker" => ["ipblocker-ro"],
        ];
        return $this;
    }

    private function _load_dumps()
    {
        $dumps = scandir(self::$PATH_DUMPSDS);
        arsort($dumps);
        $this->dumps = $dumps;
        return $this;
    }

    private function _check_intime()
    {
        $now = date("YmdHis");

        $today = date("Ymd");
        $min = "{$today}030000";
        $max = "{$today}040000";

        if($now<$min || $now>$max)
            die("Out of time");
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
        $r = file_put_contents($path, $content);
        $this->logpr($content,"content");
        $this->logpr($r, "file_put_contents.r");
        sleep(1);
    }

    public function run()
    {
        $this->logpr("START","dbreplicator");
        //$this->_check_intime();

        $results = [];
        $output = [];

        foreach ($this->config as $ctxfrom => $arto)
        {
            $this->logpr($ctxfrom,"ctxfrom");
            $arproject = $this->projects[$ctxfrom] ?? "";
            $this->logpr($arproject,"project from");
            if(!$arproject) continue;

            $dblocal = $arproject["dblocal"];
            $this->logpr($dblocal,"dblocal");
            $prefix = "cron_{$dblocal}_";
            $filename = $this->_get_lastdump($prefix);
            $this->logpr($filename,"temp filename");
            if(!$filename) continue;

            foreach ($arto as $ctxto)
            {
                $arproject = $this->projects[$ctxto] ?? "";
                $this->logpr($arproject,"project to");
                if(!$arproject) continue;

                list($dblocal, $server, $port, $database, $user, $password) = array_values($arproject);
                $this->_create_tmpdump($filename);
                $this->logpr($this->tmpdump,"tmpdump");
                if(!is_file($this->tmpdump)) continue;
                
                $command = "/usr/bin/mysql --host={$server} --user={$user} --password={$password} {$database} < $this->tmpdump";
                exec($command, $output, $result);

                $results[] = "$ctxto resultado: $result"; // 0:ok, 1:error
            }//foreach arto

        }//foreach this->config
        
        $this->log($results,"dbreplicator.run.results");
        $this->logpr("END","dbreplicator");
    }

}//class DbReplicatorService
