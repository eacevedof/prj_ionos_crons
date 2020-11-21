<?php
namespace App\Component;

class DumpComponent
{
    private $str1;
    private $str2;

    public function __construct($path1, $path2)
    {
        //pr("file1:$path1, file2:$path2","dumpcomponent");
        $this->str1 = file_get_contents($path1);
        $this->str2 = file_get_contents($path2);
    }

    private function _remove_lastline()
    {
        $arstr1 = explode("\n",$this->str1);
        $arstr2 = explode("\n",$this->str2);

        //-- Dump completed on 2020-11-21  3:15:07
        array_pop($arstr1);
        array_pop($arstr2);
        array_pop($arstr1);
        array_pop($arstr2);
        $c1 = count($arstr1);
        $c2 = count($arstr2);
        pr("file1:$c1, file2:$c2","dumpcomponent");

        $this->str1 = implode("\n",$arstr1);
        $this->str2 = implode("\n",$arstr2);
    }

    public function are_thesame()
    {
        $this->_remove_lastline();
        $md1 = md5($this->str1); $this->str1 = null;
        $md2 = md5($this->str2); $this->str2 = null;

        pr("file1:$md1, file2:$md2","dumpcomponent");
        return $md1 === $md2;
    }
}