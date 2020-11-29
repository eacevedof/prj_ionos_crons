<?php
namespace App\Component;

final class ConsoleComponent
{
    private const KEY_PATTERN = "^([\-]{0,2}[a-z,\d]+)\s*=\s*";

    private $argv = [];
    private $request = [];

    public function __construct($argv=[])
    {
        $this->argv = $argv;
    }

    private function _load_file(){$this->request["__FILE__"] = $this->argv[0];}

    private function _get_key($strkeyval)
    {
        //https://regex101.com/
        $result = [];
        $keypattern = self::KEY_PATTERN;
        preg_match_all("#$keypattern#sim", $strkeyval,$result);
        #print_r($result);
        return $result[0][0] ?? "";
    }

    private function _get_keycleaned($key)
    {
        $key = str_replace("=","",$key);
        $key = trim($key);
        $i = strpos($key,"--");
        if($i===0) $key = substr($key,2);
        $i = strpos($key,"-");
        if($i===0) $key = substr($key,1);
        //print_r($key);die;
        return $key;
    }

    private function _get_splitted($strkeyval)
    {
        $key = $this->_get_key($strkeyval);
        $value = str_replace($key,"",$strkeyval);
        $value = trim($value);
        $key = $this->_get_keycleaned($key);
        return [
            "key"=>$key, "value"=>$value
        ];
    }

    private function _get_request($strkeyval,$i)
    {
        if(strstr($strkeyval,"=")){
            return $this->_get_splitted($strkeyval);
        }
        return ["key"=>$i,"value"=>$strkeyval];
    }

    public function get_request()
    {
        $this->_load_file();
        foreach ($this->argv as $i=>$strkeyval){
            $param = $this->_get_request($strkeyval, $i);
            $this->request[$param["key"]] = $param["value"];
        }
        return $this->request;
    }
}