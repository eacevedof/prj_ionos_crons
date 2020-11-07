<?php
namespace App\Crons;

interface ICronable
{
    public function run();
}