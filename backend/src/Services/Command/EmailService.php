<?php
namespace App\Services\Command;
use App\Component\Email\EmailComponent;
use function App\Functions\get_config;

class EmailService extends ACommandService
{
    private $emails;
    private $data;
    
    public function __construct()
    {
        parent::__construct();
        $this->emails = get_config("emails");
        $this->_load_params();
    }

    private function _load_params()
    {
        $this->data["subject"] = $this->_get_arg("s");
        $this->data["content"] = $this->_get_arg("c");
        $this->data["path"] = $this->_get_arg("p");
    }

    private function _send()
    {
        $this->logpr("emailservice._send");
        $this->logpr($this->data,"data");die;

        $config = $this->emails["configs"][0];

        $r = (new EmailComponent($config))
            ->set_from($config["email"])
            ->add_to($this->emails["contacts"][0])  //gmail
            ->set_subject($this->data["subject"])
            ->set_content($this->data["content"])
            ->add_attachment([
                "path"=>$this->data["path"],
            ])
            ->send()
            ->get_errors()
        ;
        $this->logpr($r, "error?");
    }
    
    public function run()
    {
        $this->logpr("START EMAILSERVICE");
        //error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);
        $this->_send();
        $this->logpr("END EMAILSERVICE");
    }
}