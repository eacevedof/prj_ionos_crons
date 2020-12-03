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
        $r = EmailComponent::get($config)
            ->set_from($config["email"])
            ->add_to($this->emails["contacts"][0])  //gmail
            ->set_subject("Check Doamin Service $now")
            ->set_content(
                "<pre>".
                print_r($this->result,1)
                ."</pre>"
            )
            ->send()
            ->get_errors()
        ;
        $this->logpr($r,"is error?");
    }

    private function _clean_ok()
    {
        $noks = [];
        $errors = ["HTTP/1.1 403 Forbidden","HTTP/1.1 404 Not Found","HTTP/1.1 301 Moved Permanently"];
        foreach ($this->result as $domain=>$result)
        {
            if($result["status"] === "nok")
            {
                $noks[$domain] = $result;
            }
            elseif (!$result["output"][0] || in_array($result["output"][0],$errors))
            {
                $noks[$domain] = $result;
            }
        }
        $this->result = $noks;
    }

    public function run()
    {
        $this->_loader();
        foreach ($this->domains as $prot => $domains)
        {
            foreach ($domains as $domain)
            {
                $url = "$prot://$domain";
                $cmd = "curl -I $url";
                $r = Console::exec($cmd);
                $this->logpr($r,$cmd);
                sleep(1);
                $this->result[$domain] = $r;
            }
        }
        $this->_clean_ok();
        $this->_send();
    }
}