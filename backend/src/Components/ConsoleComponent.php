<?php
namespace App\Components;

final class ConsoleComponent
{
    private const KEY_PATTERN = "^([a-z,\d]+)\s*=\s*";

    private $argv = [];
    private $request = [];

    public function __construct($argv=[])
    {
        $this->argv = $argv;
    }

    private function _load_file(){$this->request["__FILE__"] = $this->argv[0];}


    private function _get_matches($strkeyval)
    {
        //https://regex101.com/
        $result = [];
        $keypattern = self::KEY_PATTERN;
        preg_match_all("#$keypattern#gm", $strkeyval,$result);
    }

    private function _get_splitted($strkeyval)
    {

    }

    private function _get_param($strkeyval)
    {
        $r = [];
        if(strstr($strkeyval,"=")){

        }
        else{

        }
    }

    public function get_request()
    {
        $this->_load_file();

        return $this->request;
    }

}