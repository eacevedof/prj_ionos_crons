<?php
namespace App\Component\Email;

final class EmailComponent
{
    public static function get_by_func()
    {
        return (new FuncEmail());
    }

    public static function get_by_pear(array $config)
    {
        return (new PearEmail($config));
    }
}