<?php
require __DIR__."/../vendor/autoload.php";

if(PHP_SAPI) $_REQUEST = $argv;
(new \App\Controllers\DispatcherController())();
