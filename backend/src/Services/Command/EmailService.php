<?php
namespace App\Services\Command;
use App\Component\Email\EmailComponent;
use function App\Functions\get_config;

class EmailService extends ACommandService
{

    private function _send_smtp()
    {
        $this->logpr("emailservice._send_smptp");
        $emails = get_config("emails");

        $config = $emails["configs"][0];
        $contact = $emails["contacts"][0];

        $r = (new EmailComponent($config))
            ->set_from($emails["contacts"][1])
            ->add_to($contact)
            ->set_subject("PRUEBA SMTP 1")
            ->set_content("PRUEBA CONTENT")
            ->send()
            ->get_errors()
        ;
        $this->logpr($r, "emailservice._send_smtp result");
    }

    private function _send_phpmail()
    {
        $this->logpr("emailservice._send_phpmail");
        $this->logpr("emailservice._send_phpmail result");
    }

    public function run()
    {
        $this->logpr("START EMAILSERVICE");
        error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);
        $this->_send_smtp();
        $this->_send_phpmail();
        $this->logpr("END EMAILSERVICE");
    }
}