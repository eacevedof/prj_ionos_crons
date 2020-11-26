<?php
namespace App\Component;

use App\Components\Db\MysqlComponent;

class QueryComponent
{
    private $context;
    /**
     * @var MysqlComponent $db
     */
    private $db;
    private $projects;

    public function __construct($context="ipblocker")
    {
        $this->projects = include_once(PATH_CONFIG.DS."projects.php");
        $this->context = $context;
        $this->_load_db();
    }

    private function _get_config()
    {
        $config = $this->projects[$this->context] ?? null;
        return $config;
    }


    private function _load_db()
    {
        $config = $this->_get_config();
        if(!$config)
            throw new \Exception("No config found for context '$this->context'");

        $this->db = new MysqlComponent($config);
    }

    public function query($sql)
    {
        return $this->db->query($sql);
    }

    public function exec($sql)
    {
        return $this->db->exec($sql);
    }
}