<?php
namespace App\Controllers;

if(is_file("cron_dbbackup.php")) include("cron_dbbackup.php");

class MainController
{
    public function index()
    {
        echo "maincontroller.index";
    }
}