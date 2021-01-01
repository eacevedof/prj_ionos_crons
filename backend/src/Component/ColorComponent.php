<?php
// https://misc.flogisoft.com/bash/tip_colors_and_formatting
namespace App\Component;

class ColorComponent
{
    //strreturn = "\033[{}m{}\033[00m".format(colcode,strval)
    private $colors = [
        "default"       =>  "\033[39m",
        "white"         =>  "\033[0m",
        "red"           =>  "\033[31m",
        "yellow"        =>  "\033[33m",
        "magenta"       =>  "\033[35m",
    ];

    private $texts = [];

    public function get_colored(string $text, string $color="default"): string
    {
        $pre = $this->colors[$color];
        $def = $this->colors["default"];
        return "$pre$text$def";
    }

    public function add(string $text, string $color="default"): ColorComponent
    {
        $this->texts[] = $this->get_colored($text,$color);
        return $this;
    }

    public function get(): string
    {
        return implode("",$this->texts);
    }

    public function pr(): void
    {
        echo $this->get();
    }
}