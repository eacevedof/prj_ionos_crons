<?php
/**
 * Actualizado: 27/11/2020
 * Replica un dump en bds ReadOnly
 */
namespace App\Services\Cron;

use App\Factories\Db;
use App\Component\ConsoleComponent as cmd;
use App\Component\Console\DumpComponent;


final class DbReplicatorService extends ACronService
{
    private static string $PATH_DUMPS_DS;
    private static string $PATH_TEMP_DS;
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
        self::$PATH_DUMPS_DS = "$home/backup_bd/";
        self::$PATH_TEMP_DS = "$home/mi_temporal/";
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

    private function _load_dumps(): void
    {
        $dumps = scandir(self::$PATH_DUMPS_DS);
        //ordena descendentemente
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

    private function _create_tmp_dump_with_console(string $dumpfile): void
    {
        $this->logpr($dumpfile,"_create_tmp_dump_with_console dumpfile", self::LOG_PREFIX);
        if (!is_file($pathfile = self::$PATH_DUMPS_DS.$dumpfile)) return;

        $justname = str_replace(".sql", "", $dumpfile);
        $tmp1 = "tmp_{$justname}_".uniqid().".sql";
        $this->tmpdump = self::$PATH_TEMP_DS.$tmp1;

        $cmd = "cp $pathfile $this->tmpdump";
        $r = cmd::exec($cmd);
        $this->logpr($r, $cmd, self::LOG_PREFIX);

        if (!is_file($this->tmpdump)) {
            $this->logerr("File {$dumpfile} not copied to $this->tmpdump");
            return;
        }

        //elimina ultimas 12 lineas
        $tmp2 = "tmp_{$justname}_".uniqid()."_rm.sql";
        $cmds = [
            "cd ".self::$PATH_TEMP_DS,
            "head -n -11 $this->tmpdump > ./$tmp2",
            "mv ./$tmp2 ./$tmp1"
        ];
        $r = cmd::exec_inline($cmds);
        $this->logpr($r, "elimina ultimas 12 lineas", self::LOG_PREFIX);

        //elimina primeras 20 lineas
        $cmds = [
            "cd ".self::$PATH_TEMP_DS,
            "sed -i '1,20d' ./$tmp1",
        ];
        $r = cmd::exec_inline($cmds);
        $this->logpr($r, "elimina primeras 20 lineas", self::LOG_PREFIX);
    }

    private function _create_tmp_dump_with_raw_php(string $file): void
    {
        $path = self::$PATH_DUMPS_DS.$file;
        $this->logpr($path,"path to read", self::LOG_PREFIX);
        $content = file_get_contents($path);
        $this->logpr(strlen($content),"content read chars", self::LOG_PREFIX);
        $arcontent = explode("\n", $content);
        unset($content);

        $this->logpr(count($arcontent),"lines in content", self::LOG_PREFIX);
        //elimina las 12 ultimas
        array_splice($arcontent,-12);
        //elimina las primeras 20 (desde la pos 0 contar 20 posiciones)
        array_splice($arcontent,0,20);

        $content = implode("\n",$arcontent);
        unset($arcontent);
        $this->tmpdump = "tmp_".uniqid().".sql";
        $this->tmpdump = self::$PATH_DUMPS_DS.$this->tmpdump;
        $this->logpr($this->tmpdump, "this->tmpdump", self::LOG_PREFIX);
        try {
            $r = file_put_contents($this->tmpdump, $content);
        }
        catch (\Exception $e) {
            $this->logerr($e->getMessage(),"error saving on file $this->tmpdump", self::LOG_PREFIX);
        }

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
        $this->logpr("START DBREPLICATOR", "", self::LOG_PREFIX);
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
                $this->_create_tmp_dump_with_console($filename);
                if(!is_file($this->tmpdump)) continue;
                
                $command = "/usr/bin/mysql --host={$server} --user={$user} --password={$password} {$database} < $this->tmpdump";
                $r = cmd::exec($command);
                $this->logpr($r, "restore tmpdump", self::LOG_PREFIX);

                continue;
                $command = "rm -f $this->tmpdump";
                $r = cmd::exec($command);
                $this->logpr($r, "remove tmpdump", self::LOG_PREFIX);

                $this->_logtables($ctxto);
                //unlink($this->tmpdump);
            }//foreach arto

        }//foreach from=>To

        $this->logpr("END DBREPLICATOR", "",self::LOG_PREFIX);
    }

}//class DbReplicatorService
