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
        "green"         =>  "\033[32m",
        "yellow"        =>  "\033[33m",
        "blue"          =>  "\033[34m",
        "magenta"       =>  "\033[35m",
        "cyan"          =>  "\033[36m",
        "light-gray"    =>  "\033[37m",

        "dark-gray"     =>  "\033[90m",
        "light-red"     =>  "\033[91m",
        "light-green"   =>  "\033[92m",
        "light-yellow"  =>  "\033[93m",
        "light-blue"    =>  "\033[94m",
        "light-magenta" =>  "\033[95m",
        "light-cyan"    =>  "\033[96m",
        "light-white"   =>  "\033[97m",
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