<?php
/**
 * Actualizado: 07/11/2020
 * crontab -l
 * 15 3 * * *  /usr/bin/php7.4  $HOME/mi_common/crons/cron_dbbackup.php
 */
namespace App\Services\Cron;

final class DumpscleanerService extends AbstractService
{
    private static $PATH_DUMPS = "";
    private const KEEP_LIMIT = 10;

    private $files = [];
    private $prefixes = [];

    public function __construct()
    {
        $home = $this->_get_env("HOME");
        self::$PATH_DUMPS = "$home/backup_bd/";
        $this->logpr(self::$PATH_DUMPS,"PATH_DUMPS");
    }

    private function _remove_dots()
    {
        $k = array_search(".",$this->files);
        if($k !== false) unset($this->files[$k]);
        $k = array_search("..",$this->files);
        if($k !== false) unset($this->files[$k]);
    }

    private function _order_desc(){arsort($this->files);}

    private function _load_prefixes()
    {
        $r = [];
        foreach ($this->files as $filename)
        {
            $prefix = explode("_",$filename);
            $prefix = array_pop($prefix);
            $prefix = implode("_",$prefix);
            $r[] = $prefix;
        }
        $this->logpr($r,"prefixes??");
        $this->prefixes = array_unique($r);
    }

    private function _get_by_prefix($prefix)
    {
        $r = [];
        foreach ($this->files as $filename)
        {
            if(strstr($filename,$prefix))
                $r[] = $filename;
        }
        return $r;
    }

    private function _get_remanent($files)
    {
        $r = [];
        foreach ($files as $i=>$filename)
        {
            if($i>=self::KEEP_LIMIT)
                $r[] = $filename;
        }
        return $r;
    }

    private function _remove($files)
    {
        foreach ($files as $filename)
        {
            $pathfile = self::$PATH_DUMPS.DS.$filename;
            if(is_file($pathfile)){
                $this->logpr("removing file: $pathfile");
                //unlink($pathfile);
            }
        }
    }

    public function run()
    {
        $this->logpr("START","dumpscleaner.run");
        if(!is_dir(self::$PATH_DUMPS)) throw new \Exception("No dir found: ".self::$PATH_DUMPS);

        $this->files = scandir(self::$PATH_DUMPS);
        //$this->logpr($this->files,"FILES");
        $this->_remove_dots();
        $this->_order_desc();
        $this->_load_prefixes();

        $this->logpr($this->prefixes,"prefixes");
        foreach ($this->prefixes as $prefix){
            $files = $this->_get_by_prefix($prefix);
            if(count($files)<=self::KEEP_LIMIT)
                continue;
            $filesrmv = $this->_get_remanent($files);
            $this->logpr($filesrmv,"files to remove");
            $this->_remove($filesrmv);
        }

        $this->logpr("END","dumpscleaner.run");
    }

}//class CronDbbackup
