<?php
//php backend/vendor/bin/phpunit backend/tests/Unit/Component/ColorComponentTest.php
namespace Tests\Unit\Component;

use PHPUnit\Framework\TestCase;
use App\Component\ColorComponent as c;
use App\Traits\LogTrait as Log;

class ColorComponentTest extends TestCase
{
    use Log;

    public function test_red()
    {
        (new c())->add("hola","red-1")->pr();
    }
}