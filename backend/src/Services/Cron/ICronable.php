<?php
namespace App\Services\Cron;

interface ICronable
{
    public function run();
}