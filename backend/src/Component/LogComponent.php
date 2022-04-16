<?php
namespace App\Component;

final class LogComponent
{
    const DS = DIRECTORY_SEPARATOR;
    
    private string $pathfolder;
    private string $subtype;
    private string $filename;

    public function __construct($subtype="", $pathfolder="", $prefix="")
    {
        $this->pathfolder = $pathfolder;
        $this->subtype = $subtype;
        $this->filename = "app_".date("Ymd").".log";
        if($prefix) $this->filename = "app_${$prefix}_".date("Ymd").".log";
        if(!$pathfolder) $this->pathfolder = __DIR__;
        if(!$subtype) $this->subtype = "debug";
        //intenta crear la carpeta de logs
        $this->_fix_folder();
    }

    private function _fix_folder()
    {
        $sLogFolder = $this->pathfolder.self::DS
            .$this->subtype.self::DS;
        //die($sLogFolder);
        if(!is_dir($sLogFolder)) @mkdir($sLogFolder);
    }

    private function merge($sContent,$sTitle)
    {
        $ip = $_SERVER["REMOTE_ADDR"] ?? "127.0.0.1";
        $sReturn = "-- [".date("Ymd-His")." - ip:$ip]\n";
        if($sTitle) $sReturn .= $sTitle.":\n";
        if($sContent) $sReturn .= $sContent."\n\n";
        return $sReturn;
    }

    public function save($mxVar,$sTitle=NULL)
    {
        if(!is_string($mxVar))
            $mxVar = var_export($mxVar,1);

        $sPathFile = $this->pathfolder.self::DS
            .$this->subtype.self::DS
            .$this->filename;

        if(is_file($sPathFile))
            $oCursor = fopen($sPathFile,"a");
        else
            $oCursor = fopen($sPathFile,"x");

        if($oCursor !== FALSE)
        {
            $sToSave = $this->merge($mxVar,$sTitle);
            fwrite($oCursor,""); //Grabo el caracter vacio
            if(!empty($sToSave)) fwrite($oCursor,$sToSave);
            fclose($oCursor); //cierro el archivo.
        }
        else
        {
            return FALSE;
        }
        return TRUE;
    }//save

    public function set_filename($sValue){$this->filename="$sValue.log";}
    public function set_subfolder($sValue){$this->subtype="$sValue";}
}