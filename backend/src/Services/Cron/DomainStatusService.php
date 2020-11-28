<?php
/**
 * Comprueba el estado del dominio
 */
namespace App\Services\Cron;

use App\Factories\Db;
use function App\Functions\get_config;

final class DomainStatusService extends ACronService
{
    private $domains;
    
    public function __construct()
    {
        $this->_load_domains();
    }

    private function _load_domains()
    {
        $this->domains = get_config("domains");    
        return $this;
    }

    public function run()
    {
        $this->logpr("START DOMAINSTATUS");

        $this->logpr("END DOMAINSTATUS");
    }

}//class DOMAINSTATUSService
