<?php
//constants.php 20200721
define("DS",DIRECTORY_SEPARATOR);

$thisdir = __DIR__;
$sPath = realpath($thisdir."/../../");
define("PATH_ROOT",$sPath);
//print_r(PATH_ROOT);

$sPath = realpath(PATH_ROOT.DS."public");
define("PATH_PUBLIC",$sPath);//carpeta public

$sPath = realpath(PATH_ROOT.DS."vendor");
define("PATH_VENDOR",$sPath);

$sPath = realpath(PATH_ROOT.DS."src");
define("PATH_SRC",$sPath);
define("PATH_CONFIG",PATH_ROOT.DS."config");

$sPath = realpath(PATH_ROOT.DS."logs");
define("PATH_LOGS",$sPath);