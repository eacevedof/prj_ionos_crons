<?php
/**
 * @author Eduardo Acevedo Farje.
 * @link www.eduardoaf.com
 * @name App\Component\Db\MysqlComponent
 * @file component_mysql.php v2.1.2
 * @date 29-06-2019 17:08 SPAIN
 * @observations
 */
namespace App\Component\Db;

use App\Traits\LogTrait;

class MysqlComponent
{
    use LogTrait;

    private $arConn;
    private $isError;
    private $arErrors;
    private $iAffected;

    public function __construct($arConn=["server"=>"127.0.0.1","database"=>"","port"=>"","user"=>"","password"=>""])
    {
        $this->isError = false;
        $this->arErrors = [];
        $this->arConn = $arConn;
    }

    private function get_conn_string()
    {
        if(!$this->arConn)
            throw new \Exception("No database config passed");

        $arCon["mysql:host"] = $this->arConn["server"] ?? "";
        $arCon["dbname"] = $this->arConn["database"] ?? "";
        $arCon["port"] = $this->arConn["port"] ?? "";
        //$arCon["ConnectionPooling"] = (isset($this->arConn["pool"])?$this->arConn["pool"]:"0");

        $sString = "";
        foreach($arCon as $sK=>$sV)
            $sString .= "$sK=$sV;";

        return $sString;
    }//get_conn_string

    private function get_rowcol($arResult,$iCol=NULL,$iRow=NULL)
    {
        if(is_int($iCol) || is_int($iRow))
        {
            $arColnames = $arResult[0];
            $arColnames = array_keys($arColnames);
//bug($arColnames);
            $sColname = (isset($arColnames[$iCol])?$arColnames[$iCol]:"");
            if($sColname)
                $arResult = array_column($arResult,$sColname);

            if(isset($arResult[$iRow]))
                $arResult = $arResult[$iRow];
        }
        return $arResult;
    }

    public function query($sSQL,$iCol=NULL,$iRow=NULL)
    {
        $arResult = [];
        try
        {
            //devuelve server y bd
            $sConn = $this->get_conn_string();
            //pr($sConn,"component_mysql.query");die;
            //https://stackoverflow.com/questions/38671330/error-with-php7-and-sql-server-on-windows
            $oPdo = new \PDO(
                $sConn,
                $this->arConn["user"],$this->arConn["password"],
                [
                    \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                ]
            );
            //$oPdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION );
            $this->log($sSQL,"MysqlComponent.query");
            $oCursor = $oPdo->query($sSQL);
            if($oCursor===false)
            {
                $this->add_error("exec-error: $sSQL");
            }
            else
            {
                while($arRow = $oCursor->fetch(\PDO::FETCH_ASSOC))
                    $arResult[] = $arRow;

                $this->iAffected = count($arResult);

                if($arResult)
                    $arResult = $this->get_rowcol($arResult,$iCol,$iRow);
            }
        }
        catch(\PDOException $oE)
        {
            $sMessage = "exception:{$oE->getMessage()}";
            $this->add_error($sMessage);
            $this->log($sSQL,"MysqlComponent.query error: $sMessage");
        }
        return $arResult;
    }//query

    public function exec($sSQL)
    {
        try
        {
            $sConn = $this->get_conn_string();
            //https://stackoverflow.com/questions/19577056/using-pdo-to-create-table
            $oPdo = new \PDO($sConn,$this->arConn["user"],$this->arConn["password"]
                ,[\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]);
            $oPdo->setAttribute(\PDO::ATTR_ERRMODE,\PDO::ERRMODE_EXCEPTION );
            $this->log($sSQL,"MysqlComponent.exec");
            $mxR = $oPdo->exec($sSQL);

            $this->iAffected = $mxR;
            if($mxR===false)
            {
                $this->add_error("exec-error: $sSQL");
            }
            return $mxR;
        }
        catch(\PDOException $oE)
        {
            $sMessage = "exception:{$oE->getMessage()}";
            $this->add_error($sMessage);
            $this->log($sSQL,"MysqlComponent.exec error: $sMessage");
        }
    }//exec

    private function add_error($sMessage){$this->isError = TRUE;$this->iAffected=-1; $this->arErrors[]=$sMessage;}
    public function is_error(){return $this->isError;}
    public function get_errors(){return $this->arErrors;}
    public function get_error($i=0){return isset($this->arErrors[$i])?$this->arErrors[$i]:NULL;}
    public function show_errors(){echo "<pre>".var_export($this->arErrors,1);}

    public function add_conn($k,$v){$this->arConn[$k]=$v;}
    public function set_conn($config){$this->arConn = $config;}

    public function get_conn($k){return $this->arConn[$k];}
    public function get_affected(){return $this->iAffected;}
    public function get_dbname(){return $this->arConn["database"] ?? "";}

}//MysqlComponent