<?php
namespace App\Factories;

use App\Component\QueryComponent;

final class Db
{
    public static function get(string $context)
    {
       return new QueryComponent($context);
    }
}