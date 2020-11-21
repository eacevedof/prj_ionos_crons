<?php
/**
 * Actualizado: 21/11/2020
 */
namespace App\Services\Cron\Cleaner;

use App\Services\Cron\AbstractService;
use App\Component\DumpComponent;

final class RepeatedService extends AbstractService
{
    private static $PATH_DUMPSDS = "";

    private $files = [];
    private $prefixes = [];

    public function __construct()
    {
        $home = $this->_get_env("HOME");
        self::$PATH_DUMPSDS = "$home/backup_bd/";
        $this->logpr(self::$PATH_DUMPSDS,"PATH_DUMPS");
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
            array_pop($prefix);
            $prefix = implode("_",$prefix);
            $r[] = $prefix;
        }
        $this->prefixes = array_unique($r);
    }

    private function _get_by_prefix($prefix)
    {
        $r = [];
        foreach ($this->files as $filename)
        {
            if(strpos($filename,$prefix)===0)
                $r[] = $filename;
        }
        return $r;
    }

    private function _get_repeated($files)
    {
        $r = [];
        //buscar
        foreach($files as $file1){
            //si ya está en repetidos se comprueba el sig
            if(in_array($file1,$r)) continue;

            $path1 = self::$PATH_DUMPSDS.$file1;

            foreach ($files as $file2){
                if($file1 == $file2) continue;

                $path2 = self::$PATH_DUMPSDS.$file2;
                $areequal = (new DumpComponent($path1,$path2))->are_thesame();

                if($areequal) $r[] = $file2;

            }//foreach file2

        }//foreach file1

        return $r;
    }

    private function _remove($files)
    {
        foreach ($files as $filename)
        {
            $pathfile = self::$PATH_DUMPSDS.$filename;
            if(is_file($pathfile)){
                $this->logpr("removing file: $pathfile");
                unlink($pathfile);
            }
        }
    }

    public function run()
    {
        $this->logpr("START","repeatedcleaner.run");
        if(!is_dir(self::$PATH_DUMPSDS)) throw new \Exception("No dir found: ".self::$PATH_DUMPSDS);

        $this->files = scandir(self::$PATH_DUMPSDS);
        $this->logpr($this->files,"files to handle");
        $this->_remove_dots();
        $this->_order_desc();
        $this->_load_prefixes();

        $this->logpr($this->prefixes,"prefixes");
        foreach ($this->prefixes as $prefix){
            $this->logpr($prefix,"prefix");
            if($prefix!=="cron_db_eduardoaf") continue;
            $files = $this->_get_by_prefix($prefix);
            $filesrmv = $this->_get_repeated($files);
            $this->logpr($filesrmv,"files to remove");
            //$this->_remove($filesrmv);
        }

        $this->logpr("END","repeatedcleaner.run");
    }

}//class CronDbbackup
