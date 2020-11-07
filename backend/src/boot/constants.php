<?php
//constants.php 20200721
define("DS",DIRECTORY_SEPARATOR);

$pathpublic = $_SERVER["DOCUMENT_ROOT"];
if($pathpublic) $sPath = realpath($pathpublic.DS."..");
else $sPath = realpath(dirname($_SERVER["PHP_SELF"])."/../");
define("PATH_ROOT",$sPath);
//print_r(PATH_ROOT); DIE;

$sPath = realpath(PATH_ROOT.DS."public");
define("PATH_PUBLIC",$sPath);//carpeta public

$sPath = realpath(PATH_ROOT.DS."vendor");
define("PATH_VENDOR",$sPath);

$sPath = realpath(PATH_ROOT.DS."src");
define("PATH_SRC",$sPath);
define("PATH_CONFIG",PATH_ROOT.DS."config");

$sPath = realpath(PATH_ROOT.DS."logs");
define("PATH_LOGS",$sPath);