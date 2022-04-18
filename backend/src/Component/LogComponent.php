<?php
namespace App\Component;

final class LogComponent
{
    private const DS = DIRECTORY_SEPARATOR;
    
    private string $pathfolder;
    private string $subtype;
    private string $filename;

    public function __construct(string $subtype="", string $pathfolder="", string $prefix="")
    {
        $this->pathfolder = $pathfolder;
        $this->subtype = $subtype;

        $this->filename = "app_".date("Ymd").".log";
        if($prefix = trim($prefix)) $this->filename = "app_${$prefix}_".date("Ymd").".log";

        if(!$pathfolder) $this->pathfolder = __DIR__;
        if(!$subtype) $this->subtype = "debug";
        //intenta crear la carpeta de logs
        $this->_fix_folder();
    }

    private function _fix_folder(): void
    {
        $sLogFolder = $this->pathfolder.self::DS
            .$this->subtype.self::DS;
        //die($sLogFolder);
        if(!is_dir($sLogFolder)) @mkdir($sLogFolder);
    }

    private function merge(string $content, string $title=""): string
    {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "127.0.0.1";
        $merged = "-- [".date("Ymd-His")." - ip:$ip]\n";
        if($title) $merged .= $title.":\n";
        if($content) $merged .= $content."\n\n";
        return $merged;
    }

    public function save($mxVar, string $title=""): bool
    {
        if(!is_string($mxVar))
            $mxVar = var_export($mxVar,1);

        $sPathFile = $this->pathfolder.self::DS
            .$this->subtype.self::DS
            .$this->filename;
        
        $oCursor = is_file($sPathFile) ? fopen($sPathFile,"a") : fopen($sPathFile,"x");
        if ($oCursor===false)
            return false;
            
        $tosave = $this->merge($mxVar,$title);
        if (!$tosave) return true;
        fwrite($oCursor,"");
        fwrite($oCursor, $tosave);
        fclose($oCursor); 
        return true;
    }//save

    public function set_filename(string $value):self {$this->filename="$value.log"; return $this;}
    
    public function set_subfolder(string $value):self {$this->subtype=$value; return $this;}
}