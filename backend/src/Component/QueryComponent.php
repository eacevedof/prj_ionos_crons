<?php
namespace App\Component;
use function App\Functions\get_config;
use App\Component\Db\MysqlComponent;

final class QueryComponent
{
    private $context;
    /**
     * @var MysqlComponent $db
     */
    private $db;
    private $projects;

    public function __construct(string $context="ipblocker")
    {
        $this->projects = get_config("projects");
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
        $dbname = $this->db->get_dbname();
        $sql = "
        SELECT t.TABLE_NAME AS t
        FROM information_schema.TABLES as t
        WHERE 1
        AND TABLE_SCHEMA='$dbname' -- la bd seleccionada
        AND t.TABLE_NAME='$tablename'        
        ";
        $table = $this->db->query($sql,0,0);
        return $table;
    }

    public function get_tables()
    {
        $dbname = $this->db->get_dbname();
        $sql = "
        SELECT table_schema AS db,
        TABLE_NAME AS t,
        COALESCE(TABLE_ROWS,0) AS irows,
        ROUND(SUM(COALESCE(data_length,0) + COALESCE(index_length,0)) / 1024 / 1024, 2) AS mb
        FROM information_schema.TABLES 
        WHERE 1
        AND TABLE_SCHEMA='$dbname'
        GROUP BY db, t
        ORDER BY db, t, irows DESC, mb DESC
        ";
        $r = $this->db->query($sql);
        //pr($sql,"get_tables");
        return $r;
    }

    public function is_conn()
    {
        $dbname = $this->db->get_dbname();
        $sql = "
        SELECT t.TABLE_NAME AS t
        FROM information_schema.TABLES as t
        WHERE 1
        AND TABLE_SCHEMA='$dbname'
        LIMIT 1
        ";

        $this->db->query($sql,0,0);
        return !$this->db->is_error();
    }
}