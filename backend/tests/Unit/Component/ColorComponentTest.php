<?php
//php backend/vendor/bin/phpunit backend/tests/Unit/Component/ColorComponentTest.php
namespace Tests\Unit\Component;

use PHPUnit\Framework\TestCase;
use App\Component\ColorComponent as Color;
use App\Traits\LogTrait as Log;

class ColorComponentTest extends TestCase
{
    use Log;

    public function test_red()
    {
        (new Color())
            ->add("\n\nwhite\n","white")
            ->add("red\n","red")
            ->add("green\n","green")
            ->add("yellow\n","yellow")
            ->add("blue\n","blue")
            ->add("magenta\n","magenta")
            ->add("cyan\n","cyan")
            ->add("dark gray\n","dark-gray")

            ->add("light gray\n","light-gray")
            ->add("light red\n","light-red")
            ->add("light green\n","light-green")
            ->add("light yellow\n","light-yellow")
            ->add("light blue\n","light-blue")
            ->add("light magenta\n","light-magenta")
            ->add("light cyan\n","light-cyan")
            ->add("light white\n","light-white")
            ->pr();
    }
}