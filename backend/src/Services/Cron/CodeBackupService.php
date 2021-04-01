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
        $parts = $this->_get_parts_from($codekey);
        $end = $parts["end"];

        $excludesubs =  array_map(
            function($subpath) use($end) {
                $subpath = trim($subpath);
                return strstr($subpath,"*") ? "\"./$end/$subpath\"" : "\"./$end/$subpath/*\"";
            },
            array_merge($this->codes[$codekey]["exclude"] ?? [], [".git"])
        );

        if($excludesubs) return "-x ".implode(" ",$excludesubs);
    }

    private function _get_parts_from($codekey): array
    {
        $paths = $this->codes[$codekey];
        $pathfrom = trim($paths["from"]);
        $folders = explode("/",$pathfrom);
        return [
            "pathfrom"  => $pathfrom,
            "pathprev"      => implode("/", array_slice($folders, 0, -1)),
            "end"       => end($folders)
        ];
    }

    private function _backup_single($codekey): string
    {
        $now = date("Ymd-His");
        $parts = $this->_get_parts_from($codekey);
        $pathprev = $parts["pathprev"];

        $pathto = $this->codes[$codekey]["to"];
        $pathzip = "$pathto/{$codekey}_$now.zip";

        $end = "./".$parts["end"];

        $exclude = $this->_get_excluded($codekey);

        //comprime sin carpeta .git
        $command = "cd $pathprev; zip -r $pathzip $end $exclude";

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
