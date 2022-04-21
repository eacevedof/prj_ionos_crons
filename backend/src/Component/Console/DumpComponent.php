<?php
namespace App\Component\Console;
use App\Component\ConsoleComponent as cmd;
use App\Traits\LogTrait;

final class DumpComponent
{
    use LogTrait;

    private string $name1;
    private string $name2;

    private string $path1;
    private string $path2;

    private string $pathcp1;
    private string $pathcp2;

    private string $pathtmp;

    public function __construct($path1, $path2)
    {
        $this->logpr([$path1, "vs", $path2],"compare","dumpcomponent");
        $this->pathtmp = getenv("HOME")."/mi_temporal";
        $this->path1 = $path1;
        $this->path2 = $path2;
    }

    private function _same_len(): bool
    {
        $cmd = "cat $this->path1 | wc -m";
        $len1 = (int) cmd::exec($cmd)["exec"];

        $cmd = "cat $this->path2 | wc -m";
        $len2 = (int) cmd::exec($cmd)["exec"];

        $this->logpr("l1:$len1, l2:$len2","same_len","dumpcomponent");
        return $len1 === $len2;
    }

    private function _make_temporal_copy(): void
    {
        $this->name1 = basename($this->path1);
        $this->pathcp1 = "$this->pathtmp/$this->name1";
        copy($this->path1, $this->pathcp1);

        $this->name2 = basename($this->path2);
        $this->pathcp2 = "$this->pathtmp/$this->name2";
        copy($this->path2, $this->pathcp2);
        //pr("file1:$path1, file2:$path2","dumpcomponent");
    }
    
    private function _remove_last_line_of_dumpdate(): void
    {
        //se intenta quitar la ultima linea de los dumps:
        //-- Dump completed on 2022-04-02  3:15:08
        //$cmd = "tac file | sed '1,2d' | tac";
        $cmd = "head -n -1 $this->pathcp1 > $this->pathtmp/tmp1.log";
        $r = cmd::exec($cmd);
        $this->logpr($r,"_remove_last_line_of_dumpdate","dumpcomponent");

        $cmd = "mv $this->pathtmp/tmp1.log $this->pathtmp/$this->name1";
        $r = cmd::exec($cmd);
        $this->logpr($r,"_remove_last_line_of_dumpdate","dumpcomponent");

        $cmd = "head -n -1 $this->pathcp2 > $this->pathtmp/tmp1.log";
        $r = cmd::exec($cmd);
        $this->logpr($r,"_remove_last_line_of_dumpdate","dumpcomponent");

        $cmd = "mv $this->pathtmp/tmp1.log $this->pathtmp/$this->name2";
        $r = cmd::exec($cmd);
        $this->logpr($r,"_remove_last_line_of_dumpdate","dumpcomponent");
    }
    
    private function _same_md5_of_content(): bool
    {
        $cmd = "cat $this->pathcp1 | md5sum";
        $md51 = cmd::exec($cmd)["exec"];

        $cmd = "cat $this->pathcp2 | md5sum";
        $md52 = cmd::exec($cmd)["exec"];

        $this->logpr("m1:$md51, m2:$md52","_same_md5_of_content","dumpcomponent");
        return $md51 === $md52;
    }

    private function _clean_temporal(): void
    {
        $cmd = "rm -f $this->pathcp1; rm -f $this->pathcp2";
        $r = cmd::exec($cmd);
        $this->logpr($r,"_clean_temporal","dumpcomponent");
    }

    public function are_thesame(): bool
    {
        if(!$this->_same_len()) return false;
        $this->_make_temporal_copy();
        $this->_remove_last_line_of_dumpdate();
        $r = $this->_same_md5_of_content();
        $this->_clean_temporal();
        return $r;
    }
}