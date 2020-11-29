<?php
namespace Tests\Unit\Component;

use PHPUnit\Framework\TestCase;
use App\Component\Email\EmailComponent;
use function App\Functions\get_config;
use App\Traits\LogTrait as Log;

class EmailComponentTest extends TestCase
{
    use Log;
    private $emails;

    public function setUp(): void
    {
        $this->emails = get_config("emails");
    }

    public function test_smtp()
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
        $this->assertEmpty($r);
    }

    public function test_phpmail()
    {
        $this->logpr("emailservice._send_phpmail");
        $now = date("Y-m-d H:i:s");

        $r = (new EmailComponent())
            //->set_from($this->emails["contacts"][1]) //aqui si se disfraza
            ->set_from($this->emails["configs"][0]["email"])
            ->set_title_from("No Reply title")  //el titulo llega
            ->add_to($this->emails["contacts"][1])
            ->add_cc($this->emails["contacts"][2])
            ->add_bcc($this->emails["contacts"][0])
            ->set_subject("PRUEBA PHPMAIL 2 $now")
            ->set_content("
                <h6>PRUEBA CONTENT PHPMAIL 2</h6>
                <p>
                    Demo usando phpmail
                </p> 
                <b>$now</b>
            ")
            /**
            //con adjuntos no llega a yahoo
            ->add_attachment([
            "path"=>PATH_CONFIGDS."domains.example.php",
            ])
            ->add_attachment([
            "path"=>PATH_CONFIGDS."projects.example.php",
            ])
            /**/
            ->send()
            ->get_errors()
        ;
        $this->logpr($r, "test_phpmail");
        $this->assertEmpty($r);
    }

}//EmailComponentTest
