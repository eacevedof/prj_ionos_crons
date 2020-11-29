<?php
namespace App\Services\Command;
use App\Component\Email\EmailComponent;
use function App\Functions\get_config;

class EmailService extends ACommandService
{
    private $emails;

    public function __construct()
    {
        $this->emails = get_config("emails");
    }


    public function run()
    {
        $this->logpr("START EMAILSERVICE");
        error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);
        //$this->_send_smtp();  //todo ok con 1 solo attach
        $this->_send_phpmail();
        $this->logpr("END EMAILSERVICE");
    }
}