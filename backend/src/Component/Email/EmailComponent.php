<?php
namespace App\Component\Email;

class EmailComponent extends AEmail
{

    private $title_from;
    private $email_from;

    private $emails_to;
    private $emails_cc;
    private $emails_bcc;

    private $subject;
    private $content;

    //no smtp
    private $headers;
    private $header;

    //smtp
    private $issmtp;
    private $arsmtp;

    private $attachments;

    /**
     * @param string|array $mxEmailTo array tipo array[email1,email2...)
     * @param string $subject
     * @param string|array $mxcontent array tipo $arLines = array["line text 1","line text 2"..) or string
     */
    public function __construct($smtp=[])
    {
        $this->_load_primitives()
            ->_load_smtp($smtp)
        ;

    }//__construct

    private function _load_primitives()
    {
        $this->title_from = "";
        $this->email_from = "";
        $this->emails_to = [];
        $this->subject = "";
        $this->content = "";
        return $this;
    }

    private function _load_smtp($config)
    {
        $this->logpr($config,"CCCONNNF");
        if($config)
        {
            $this->issmtp               = true;
            $this->arsmtp["host"]       = $config["host"] ?? "";
            $this->arsmtp["port"]       = $config["port"] ?? "25";
            $this->arsmtp["auth"]       = $config["auth"] ?? true;;
            $this->arsmtp["username"]   = $config["username"] ?? "";
            $this->arsmtp["password"]   = $config["password"] ?? "";
        }
        return $this;
    }

    private function _load_smtp_libs()
    {
        //errorson();
        //necesita tener instalado:
        // PEAR https://pear.php.net/manual/en/installation.checking.php
        // Mail,Mail_Mime,Net_SMTP,Net_Socket
        // sudo pear install <pack> --alldeps

        //C:\xampp\php>pear list
        require_once("Mail.php");
        require_once("Mail/mime.php");
        return $this;
    }

    private function _smtp_headers()
    {
        $this->headers = [];
        $this->headers["Content-Type"] = "text/html; charset=UTF-8";

        if(is_array($this->emails_to))
            $this->emails_to = implode(", ",$this->emails_to);
        $this->headers["To"] = $this->emails_to;

        if($this->emails_cc) $this->headers["Cc"] = implode(", ",$this->emails_cc);
        //if($this->emails_bcc) $bcc = ", ".implode(", ",$this->emails_bcc);
        $this->headers["Subject"] = $this->subject;
        $this->headers["From"] = $this->email_from;
        $this->logpr($this->headers,"smtpheaderrs");
    }

    private function _get_smtp_mime()
    {
        return [
            "text_encoding" => "7bit",
            "text_charset" => "UTF-8",
            "html_charset" => "UTF-8",
            "head_charset" => "UTF-8"
        ];
    }

    private function _send_smtp()
    {
        $this->logpr("send_smtp");
        try
        {
            $this->_load_smtp_libs()
                ->_smtp_headers()
            ;

            $objmime = new \Mail_mime(["eol"=>PHP_EOL]);

            //$objmime->setTXTBody("texto body"); //texto sin html
            $objmime->setHTMLBody($this->content); //texto con html

            foreach ($this->attachments as $ardata)
                $objmime->addAttachment($ardata["path"], $ardata["mime"]);

            //do not ever try to call these lines in reverse order
            $armime = $this->_get_smtp_mime();
            $content = $objmime->get($armime);
            $headers = $objmime->headers($this->headers);

            $this->logpr($headers,"headers 222");
            //la única forma de enviar con copia oculta es añadirlo a los receptores
            $stremailsto = $headers["To"].", ".$this->headers["Cc"];

            $objsmtp = \Mail::factory("smtp",$this->arsmtp);
            //->send es igual a: mail($recipients, $subject, $body, $headers);
            $objemail = $objsmtp->send($stremailsto, $headers, $content);

            $this->logpr($objemail,"objmail");
            if(\PEAR::iserror($objemail))
                $this->_add_error($objemail->getMessage());
        }
        catch(Exception $oEx)
        {
            $this->_add_error($oEx->getMessage());
        }

        return $this;
    }

    private function _nosmtp_header_mime()
    {
        $this->headers = [
            "MIME-Version: 1.0",
            //"Content-Type: text/html; charset=ISO-8859-1";
            "Content-Type: text/html; charset=UTF-8",

            //add boundary string and mime type specification
            "Content-Transfer-Encoding: 8bit",
        ];
        return $this;
    }

    private function _nosmtp_header_from()
    {
        if($this->email_from)
        {
            $this->headers[] = "From: $this->title_from <$this->email_from>";
            $this->headers[] = "Return-Path: <$this->email_from>";
            $this->headers[] = "X-Sender: $this->email_from";
        }
        return $this;
    }

    private function _nosmtp_header_cc()
    {
        if($this->emails_cc)
            $this->headers[] = "Cc: ".implode(", ",$this->emails_cc);
        return $this;
    }

    private function _nosmtp_header_bcc()
    {
        if($this->emails_bcc)
            $this->headers[] = "Bcc: ".implode(", ",$this->emails_bcc);
        return $this;
    }

    private function _nomstp_header()
    {
        $header = implode(PHP_EOL,$this->headers);
        $this->header = $header;
    }
    
    private function _send_nosmtp()
    {
        $this->log("_send_nosmtp()");
        if($this->emails_to)
        {
            $this->_nosmtp_header_mime()
                ->_nosmtp_header_from()
                ->_nosmtp_header_cc()
                ->_nosmtp_header_bcc()
                ->_nomstp_header()
            ;

            $this->emails_to = implode(", ",$this->emails_to);

            //TRUE if success
            /*
            telnet 127.0.0.1 25
            220 eduardosvc ESMTP Sendmail 8.14.4/8.14.4/Debian-4.1ubuntu1; Thu, 25 Feb 2016 10:47:44 +0100; 
            (No UCE/UBE) logging access from: caser.loc(OK)-caser.loc [127.0.0.1]
            */
            $this->log("antes de llamar a funcion mail");
            $r = mail($this->emails_to, $this->subject, $this->content, $this->header);
            $this->log($r,"email result");
            if($r===false)
                $this->_add_error("Error sending email!");
        }
        else
        {
            $this->_add_error("No target emails!");
        }
        return $this;
    }

    public function send()
    {
        if($this->issmtp)
            return $this->_send_smtp();
        return $this->_send_nosmtp();
    }

    public function set_subject($subject){$this->subject = $subject; return $this;}

    public function set_from($stremail){$this->email_from = $stremail; return $this;}
    public function set_title_from(string $title){$this->title_from = $title; return $this;}
    public function add_to($stremail){$this->emails_to[]=$stremail; return $this;}
    public function add_cc($stremail){$this->emails_cc[]=$stremail; return $this;}
    public function add_bcc($stremail){$this->emails_bcc[]=$stremail; return $this;}
    public function set_nosmtp_header($header){$this->header = $header; return $this;}
    public function set_content($mxcontent){(is_array($mxcontent))? $this->content=implode(PHP_EOL,$mxcontent): $this->content = $mxcontent; return $this;}
    public function add_attachment($arattach=["path"=>"","mime"=>"","as-file"=>""]){$this->attachments[] = $arattach; return $this;}

    /**
     *  Required
    "MIME-Version: 1.0"
    "Content-type: text/html; charset=iso-8859-1"
    Optional
    "From: Recordatorio <cumples@example.com>"
    "To: Mary <mary@example.com>, Kelly <kelly@example.com>"
    "Cc: birthdayarchive@example.com"
    "Bcc: birthdaycheck@example.com"
    mail($to,$subject,$message,$headers);
     *
     * @param string $header Cualquer linea anterior
     */
    public function add_nosmtp_header(string $header){$this->headers[] = $header; return $this;}

}