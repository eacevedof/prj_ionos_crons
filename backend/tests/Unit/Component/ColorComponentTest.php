<?php
//php backend/vendor/bin/phpunit backend/tests/Unit/Component/ColorComponentTest.php
namespace Tests\Unit\Component;
use PHPUnit\Framework\TestCase;
use App\Component\ColorComponent as Color;

class ColorComponentTest extends TestCase
{

    public function test_two_colors()
    {
        $title = "
        Title in magenta!
        ";
        $text = "
        It has survived not only five centuries, but also the leap into electronic typesetting, 
        remaining essentially unchanged. It was popularised in the 1960s with the release of 
        etraset sheets containing Lorem Ipsum passages, and more recently with desktop 
        publishing software like Aldus PageMaker including versions of Lorem Ipsum.
        ";
        echo Color::text($title, Color::MAGENTA);
        echo Color::text($text, Color::LIGHT_CYAN);
    }

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