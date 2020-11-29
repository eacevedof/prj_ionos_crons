<?php
namespace App\Services\Command;
use App\Component\Email\EmailComponent;
use function App\Functions\get_config;
use App\Component\ConsoleComponent as Console;

final class CheckDomainService extends ACommandService
{
    private $domains;
    private $emails;
    private $result = [];
    
    private function _loader()
    {
        $this->domains = get_config("domains");
        $this->emails = get_config("emails");
    }

    private function _send()
    {
        $now = date("Y-m-d H:i:s");
        $config = $this->emails["configs"][0];
        $r = (new EmailComponent($config))
            ->set_from($config["email"])
            ->add_to($this->emails["contacts"][0])  //gmail
            ->set_subject("Check Doamin Service $now")
            ->set_content(print_r($this->result,1))
            ->send()
            ->get_errors()
        ;
        $this->logpr($r,"is error?");
    }
    
    public function run()
    {
        $this->_loader();
        foreach ($this->domains as $domain)
        {
            $cmd = "curl -I $domain";
            $r = Console::exec($cmd);
            $this->logpr($r,$domain);
            sleep(1);
            $this->result[] = $r;
        }
        $this->_send();
    }
}