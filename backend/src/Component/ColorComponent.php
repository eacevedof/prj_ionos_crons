<?php
// https://misc.flogisoft.com/bash/tip_colors_and_formatting
namespace App\Component;

class ColorComponent
{
    public const DEFAULT        = 39;
    public const WHITE          = 0;

    public const RED            = 31;
    public const GREEN          = 32;
    public const YELLOW         = 33;
    public const BLUE           = 34;
    public const MAGENTA        = 35;
    public const CYAN           = 36;
    public const LIGHT_GRAY     = 37;

    public const DARK_GRAY      = 90;
    public const LIGHT_RED      = 91;
    public const LIGHT_GREEN    = 92;
    public const LIGHT_YELLOW   = 93;
    public const LIGHT_BLUE     = 94;
    public const LIGHT_MAGENTA  = 95;
    public const LIGHT_CYAN     = 96;
    public const LIGHT_WHITE    = 97;

    private $texts = [];

    private function _get_tag(int $code): string
    {
        //"\033[{}m{}\033[00m".format(colcode,strval)
        return sprintf("\033[%sm", $code);
    }
    
    public function get_colored(string $text, int $color=self::DEFAULT): string
    {
        $pre = $this->_get_tag($color);
        $def = $this->_get_tag(self::DEFAULT);
        return "$pre$text$def";
    }

    public function add(string $text, int $color=self::DEFAULT): ColorComponent
    {
        $this->texts[] = $this->get_colored($text,$color);
        return $this;
    }

    public function get(string $glue=""): string
    {
        return implode($glue, $this->texts);
    }

    public function pr(): void
    {
        echo $this->get();
    }

    public static function text(string $text, int $color=self::DEFAULT): string
    {
        return (new self())->add($text,$color)->get();
    }
}