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
            ->set_from($contact)
            ->add_to($emails["contacts"][0])
            ->set_subject("esto es un simple asunto")
            ->set_content("un contenido x")
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