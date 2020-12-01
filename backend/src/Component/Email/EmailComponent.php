<?php
namespace App\Component\Email;

final class EmailComponent
{
    public static function get(array $config=[])
    {
        if($config) return (new PearEmail($config));
        return (new FuncEmail());
    }

}