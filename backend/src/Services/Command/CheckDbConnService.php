<?php
namespace App\Services\Command;

use App\Factories\Db as db;

class CheckDbConnService extends AbstractService
{
    private $context;
    private $result;

    public function __construct()
    {
        parent::__construct();
        $this->context = $this->_get_context();
        $this->result = [];
    }

    private function _get_context()
    {
        $context = $this->_get_param(2);
        $context = trim($context);
        return $context;
    }

    private function _is_conn($context)
    {
        return db::get($context)->is_conn();
    }

    private function _pr()
    {
        print_r($this->result);
    }

    private function _single($context)
    {
        $this->result[$context] = "ok";
        if(!$this->_is_conn($context))
            $this->result[$context] = "nok";
    }

    public function run()
    {
        if($this->context)
        {
            $this->_single($this->context);
            $this->_pr();
            return;
        }

        $contexts = array_keys($this->projects);
        foreach ($contexts as $context)
        {
            if(in_array($context,["upload","tools"]))
                continue;
            $this->_single($context);
        }
        $this->_pr();
    }

}