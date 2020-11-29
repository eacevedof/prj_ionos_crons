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

    private function _send_smtp()
    {
        $this->logpr("emailservice._send_smptp");

        $config = $this->emails["configs"][0];
        $now = date("Y-m-d H:i:s");

        $r = (new EmailComponent($config))
            //si no se pone from no se hace el envio, si se pone uno distinto aplica
            //el usuario en la config de smtp
            ->set_from($this->emails["contacts"][1])
            ->add_to($this->emails["contacts"][0])      //hotmail
            ->add_cc($this->emails["contacts"][1])      //gmail
            ->add_bcc($this->emails["contacts"][2])     //yahoo
            ->set_subject("PRUEBA SMTP 1 $now")
            ->set_content("PRUEBA CONTENT 1 $now")
            ->add_attachment([
                "path"=>PATH_CONFIGDS."domains.example.php",
            ])
            ->add_attachment([
                "path"=>PATH_CONFIGDS."projects.example.php",
            ])
            ->send()
            ->get_errors()
        ;
        if(!$r) $r = "OK";
        $this->logpr($r, "emailservice._send_smtp result");
    }

    private function _send_phpmail()
    {
        $this->logpr("emailservice._send_phpmail");
        $now = date("Y-m-d H:i:s");

        $r = (new EmailComponent())
            //->set_from($this->emails["contacts"][1]) //aqui si se disfraza
            ->set_from($this->emails["configs"][0]["email"])
            ->set_title_from("No Reply title")  //el titulo llega
            ->add_to($this->emails["contacts"][0])      //hotmail
            ->add_cc($this->emails["contacts"][1])      //gmail
            ->add_cc($this->emails["contacts"][2])     //yahoo
            ->set_subject("PRUEBA PHPMAIL 2 $now")
            ->set_content("
                <h6>PRUEBA CONTENT PHPMAIL 2</h6>
                <p>
                    Demo usando phpmail
                </p> 
                <b>$now</b>
            ")
            ->add_attachment([
                "path"=>PATH_CONFIGDS."domains.example.php",
            ])
            ->add_attachment([
                "path"=>PATH_CONFIGDS."projects.example.php",
            ])
            ->send()
            ->get_errors()
        ;
        if(!$r) $r = "OK";
        $this->logpr($r,"emailservice._send_phpmail result");
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