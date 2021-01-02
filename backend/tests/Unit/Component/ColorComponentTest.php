<?php
//php backend/vendor/bin/phpunit backend/tests/Unit/Component/ColorComponentTest.php
namespace Tests\Unit\Component;

use PHPUnit\Framework\TestCase;
use App\Component\ColorComponent as Color;
use App\Traits\LogTrait as Log;

class ColorComponentTest extends TestCase
{
    use Log;

    public function test_multicolors()
    {
        (new Color())
            ->add("\n\nwhite\n",Color::WHITE)
            ->add("red\n",Color::RED)
            ->add("green\n",Color::GREEN)
            ->add("yellow\n",Color::YELLOW)
            ->add("blue\n",Color::BLUE)
            ->add("magenta\n",Color::MAGENTA)
            ->add("cyan\n",Color::CYAN)
            ->add("dark gray\n",Color::DARK_GRAY)

            ->add("light gray\n",Color::LIGHT_GRAY)
            ->add("light red\n",Color::LIGHT_RED)
            ->add("light green\n",Color::LIGHT_GREEN)
            ->add("light yellow\n",Color::LIGHT_YELLOW)
            ->add("light blue\n",Color::LIGHT_BLUE)
            ->add("light magenta\n",Color::LIGHT_MAGENTA)
            ->add("light cyan\n",Color::LIGHT_CYAN)
            ->add("light white\n",Color::LIGHT_WHITE)

            ->add("background\n",42)
            ->pr("\t");
    }
}