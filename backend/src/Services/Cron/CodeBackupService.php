<?php
/**
 * Actualizado: 28/03/2021
 *
 * se lanza a las 03:15 todos los días
 * 15 3 * * *  /usr/bin/php7.4
 */
namespace App\Services\Cron;

use function App\Functions\get_config;

final class CodeBackupService extends ACronService
{
    private $codes;
    private $data;

    private function _load_config(): self
    {
        $this->codes = get_config("code");
        return $this;
    }

    private function _load_params(): self
    {
        $this->data["codekey"] = trim($this->_get_param("c")) ?? "";
        return $this;
    }

    private function _get_excluded($codekey): string
    {
        $paths = $this->codes[$codekey];
        $pathfrom = trim($paths["from"]);

        $excludesubs =  array_map(
            function($subpath) use($pathfrom) {
                $subpath = trim($subpath);
                return "$pathfrom/$subpath/*";
            },
            array_merge($paths["exclude"] ?? [], [".git"])
        );

        if($excludesubs) return "-x ".implode(" ",$excludesubs);
    }

    private function _backup_single($codekey): string
    {
        $now = date("Ymd-His");
        $paths = $this->codes[$codekey];
        $pathfrom = $paths["from"];
        $pathto = $paths["to"];

        $pathzip = "$pathto/{$codekey}_$now.zip";
        $exclude = $this->_get_excluded($codekey);

        //comprime sin carpeta .git
        $command = "zip -r $pathzip $pathfrom $exclude";
        $this->logpr($command, "command");
        exec($command, $output, $result);
        return "$codekey resultado: $result"; // 0:ok, 1:error
    }

    public function run(): void
    {
        $this->logpr("START","codebackupservice.run");
        $this->_load_config()->_load_params();

        if($codekey = $this->data["codekey"])
        {
            $results[] = $this->_backup_single($codekey);
        }
        else
        {
            foreach(array_keys($this->codes) as $codekey)
                $results[] = $this->_backup_single($codekey);
        }

        $this->logpr($results,"codebackupservice.run.results");
        $this->logpr("END","codebackupservice.run");
    }

}//class CronDbbackup
