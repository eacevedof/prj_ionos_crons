<?php
/**
 * Actualizado: 27/11/2020
 * Replica un dump en bds ReadOnly
 */
namespace App\Services\Cron;

use App\Factories\Db;

final class DbReplicatorService extends ACronService
{
    /**
     * @var string
     */
    private static string $PATH_DUMPSDS;
    private const LOG_PREFIX = "replicator";
    private array $config;
    private array $dumps;
    private string $tmpdump;
    
    public function __construct()
    {
        parent::__construct();

        $this->_load_pathdumps()
            ->_load_config()
            ->_load_dumps();
    }

    private function _load_pathdumps(): self
    {
        $home = $this->_get_env("HOME");
        self::$PATH_DUMPSDS = "$home/backup_bd/";
        return $this;
    }

    private function _load_config(): self
    {
        $this->config = [
            "ipblocker" => ["ipblocker-ro","ipblocker-test"],
            "elchalanwp" => ["elchalanwp-test"],
        ];
        return $this;
    }

    private function _load_dumps()
    {
        $dumps = scandir(self::$PATH_DUMPSDS);
        arsort($dumps);
        $dumps = array_values($dumps);
        $this->logpr($dumps, "dumps", self::LOG_PREFIX);
        $this->dumps = $dumps;
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

    private function _get_lastdump(string $prefix): string
    {
        $pattern = "/{$prefix}[\d]{14}\.sql/";
        foreach ($this->dumps as $file) {
            $results = [];
            preg_match_all($pattern, $file, $results);
            if($found = ($results[0][0] ?? null)) {
                $this->logpr($found, "found", self::LOG_PREFIX);
                return $found;
            }
        }
        return "";
    }

    private function _create_tmp_dump(string $file): void
    {
        $path = self::$PATH_DUMPSDS.$file;
        $this->logpr($path,"path to read", self::LOG_PREFIX);
        $content = file_get_contents($path);
        //$this->logpr($content,"content 1", self::LOG_PREFIX);
        $arcontent = explode("\n",$content);

        //elimina las 12 ultimas
        array_splice($arcontent,-12);

        //elimina las primeras 20 (desde la pos 0 contar 20 posiciones)
        array_splice($arcontent,0,20);

        $content = implode("\n",$arcontent);

        $this->tmpdump = "tmp_".uniqid().".sql";
        $this->tmpdump = self::$PATH_DUMPSDS.$this->tmpdump;
        $r = file_put_contents($this->tmpdump, $content);
        //$this->logpr($content,"content", self::LOG_PREFIX);
        $this->logpr($r, "file_put_contents result on $this->tmpdump", self::LOG_PREFIX);
        sleep(1);
    }

    private function _logtables(string $context): void
    {
        $r = Db::get($context)->get_tables();
        $this->logpr($r, "tables of $context", self::LOG_PREFIX);
    }

    public function run()
    {
        $this->logpr("START DBREPLICATOR", self::LOG_PREFIX);
        //$this->_check_intime();
        $results = [];
        foreach ($this->config as $ctxfrom => $arto)
        {
            $this->logpr($ctxfrom,"ctxfrom", self::LOG_PREFIX);
            $arproject = $this->projects[$ctxfrom] ?? "";
            $this->logpr($arproject,"project from", self::LOG_PREFIX);
            if(!$arproject) continue;

            $dblocal = $arproject["dblocal"];
            $this->logpr($dblocal,"dblocal", self::LOG_PREFIX);
            $prefix = "cron_{$dblocal}_";
            $filename = $this->_get_lastdump($prefix);
            $this->logpr($filename,"temp filename", self::LOG_PREFIX);
            if(!$filename) continue;

            foreach ($arto as $ctxto) {
                $arproject = $this->projects[$ctxto] ?? "";
                $this->logpr($arproject,"project to", self::LOG_PREFIX);
                if(!$arproject) continue;

                list($dblocal, $server, $port, $database, $user, $password) = array_values($arproject);
                $this->_create_tmp_dump($filename);
                $this->logpr($this->tmpdump,"tmpdump", self::LOG_PREFIX);
                if(!is_file($this->tmpdump)) continue;
                
                $command = "/usr/bin/mysql --host={$server} --user={$user} --password={$password} {$database} < $this->tmpdump";
                $this->logpr($command, "command", self::LOG_PREFIX);

                $output = [];
                $result = null;
                $r = exec($command, $output, $result);
                sleep(5);
                $results[$ctxto]["now"] = date("Y-m-d H:i:s");
                $results[$ctxto]["result"] = $result ? "error" : "success";
                $results[$ctxto]["exec"] = $r;
                $results[$ctxto]["output"] = $output;
                $this->_logtables($ctxto);
                unlink($this->tmpdump);
            }//foreach arto

        }//foreach from=>To
        
        $this->log($results,"dbreplicator.run.results", self::LOG_PREFIX);
        $this->logpr("END DBREPLICATOR", "",self::LOG_PREFIX);
    }

}//class DbReplicatorService
