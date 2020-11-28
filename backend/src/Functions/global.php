<?php
namespace App\Functions;

function get_config($filename)
{
    $pathfile = PATH_CONFIGDS.$filename.".php";
    return include($pathfile);
}
