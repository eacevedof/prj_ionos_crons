<?php
namespace App\Component;

use App\Component\Db\MysqlComponent;

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
        $this->projects = include(PATH_CONFIG.DS."projects.php");
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

    public function query($sql,$c=null,$r=null)
    {
        return $this->db->query($sql,$c,$r);
    }

    public function exec($sql)
    {
        return $this->db->exec($sql);
    }

    public function is_table($tablename)
    {
        $sql = "
        SELECT t.TABLE_NAME AS t
        FROM information_schema.TABLES as t
        WHERE 1
        AND TABLE_SCHEMA=DATABASE() -- la bd seleccionada
        AND t.TABLE_NAME='$tablename'        
        ";
        $table = $this->db->query($sql,0,0);
        return $table;
    }

    public function get_tables()
    {
        $sql = "
        SELECT table_schema AS db,
        TABLE_NAME AS t,
        COALESCE(TABLE_ROWS,0) AS irows,
        ROUND(SUM(COALESCE(data_length,0) + COALESCE(index_length,0)) / 1024 / 1024, 2) AS mb
        FROM information_schema.TABLES 
        WHERE 1
        AND TABLE_SCHEMA=DATABASE()
        GROUP BY db, t
        ORDER BY db, t, irows DESC, mb DESC
        ";
        $r = $this->db->query($sql);
        //pr($sql,"get_tables");
        return $r;
    }

    public function is_conn()
    {
        $sql = "
        SELECT t.TABLE_NAME AS t
        FROM information_schema.TABLES as t
        WHERE 1
        AND TABLE_SCHEMA=DATABASE()
        LIMIT 1
        ";

        $this->db->query($sql,0,0);
        return !$this->db->is_error();
    }
}