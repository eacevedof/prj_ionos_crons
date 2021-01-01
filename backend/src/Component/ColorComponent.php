<?php
namespace App\Component;

class ColorComponent
{
    //strreturn = "\033[{}m{}\033[00m".format(colcode,strval)
    private $colors = [
        "white-1"=>"\033[0m",
        "red-1"=>"\033[31m",
    ];

    private $texts = [];

    public function get_colored($text, $color="white-1"): string
    {
        $pre = $this->colors[$color];
        return "$pre$text";
    }

    public function add($text, $color="white-1"): ColorComponent
    {
        $this->texts[] = $this->get_colored($text,$color);
        return $this;
    }

    public function get()
    {
        return implode("",$this->texts);
    }

    public function pr()
    {
        return $this->get();
    }
}