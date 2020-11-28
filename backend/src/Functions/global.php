<?php
namespace App\Functions;

function config($filename)
{
    $pathfile = PATH_CONFIGDS.$filename.".php";
    return include($pathfile);
}
