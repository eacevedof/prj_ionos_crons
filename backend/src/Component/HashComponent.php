<?php
namespace App\Component;

class HashComponent
{
    private $str1;
    private $str2;

    public function __construct($str1, $str2)
    {
        $this->str1 = $str1;
        $this->str2 = $str2;
    }

    public function are_thesame()
    {
        //-- Dump completed on 2020-11-21  3:15:07
        $md1 = md5($this->str1);
        $md2 = md5($this->str2);
        return $md1 === $md2;
    }
}