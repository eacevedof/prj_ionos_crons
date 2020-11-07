<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    private function match_test()
    {
        $keypattern = "^([a-z,\d]+)\s*=(.*?)";
        $strkeyval = "13service   = \"sfsodmem string to servi\";";
        preg_match_all("#$keypattern#im", $strkeyval,$result);
        print_r($result);
    }

    public function test_demo()
    {
        $r = true;
        $this->assertTrue($r);
    }

}//ExampleTest
