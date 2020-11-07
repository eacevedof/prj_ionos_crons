<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    private const KEY_PATTERN = "^([a-z,\d]+)\s*=(.*?)";

    public function test_key_value_con_dobles_comillas()
    {
        $keypattern = self::KEY_PATTERN;
        $strkeyval = "13service   = \"sfsodmem 
        = a b = xx string to servi\";";
        preg_match_all("#$keypattern#sim", $strkeyval,$result);
        print_r($result);
    }

    public function test_key_value_sin_comillas()
    {
        $keypattern = self::KEY_PATTERN;
        $strkeyval = "13service   = sfsodmem 
        = a b = xx string to servi;";
        preg_match_all("#$keypattern#sim", $strkeyval,$result);
        print_r($result);
    }

    public function test_demo()
    {
        $r = true;
        $this->assertTrue($r);
    }

}//ExampleTest
