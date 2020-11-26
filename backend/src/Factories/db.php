<?php
namespace App\Factories;

use App\Component\QueryComponent;

function db($context="ipblocker"){
    return new QueryComponent($context);
}