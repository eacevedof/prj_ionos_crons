<?php
//constants.php 20200721
define("DS",DIRECTORY_SEPARATOR);

$thisdir = __DIR__;
$sPath = realpath($thisdir."/../../");
define("PATH_ROOT",$sPath);
define("PATH_ROOTDS",PATH_ROOT.DS);
//print_r(PATH_ROOT);

$sPath = realpath(PATH_ROOTDS."public");
define("PATH_PUBLIC",$sPath);//carpeta public

$sPath = realpath(PATH_ROOTDS."vendor");
define("PATH_VENDOR",$sPath);

$sPath = realpath(PATH_ROOTDS."src");
define("PATH_SRC",$sPath);
define("PATH_CONFIG",PATH_ROOTDS."config");
define("PATH_CONFIGDS",PATH_ROOTDS."config".DS);

$sPath = realpath(PATH_ROOTDS."logs");
define("PATH_LOGS",$sPath);