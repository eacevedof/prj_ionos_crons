<?php
namespace App\Component\Email;

final class EmailComponent
{
    public static function get(array $config=[])
    {
        if($config) return (new PearEmail($config));
        return (new FuncEmail());
    }

    public static function pear(array $config=[]): PearEmail
    {
        return (new PearEmail($config));
    }

    public static function fn_mail(): FuncEmail
    {
        return (new FuncEmail());
    }
}