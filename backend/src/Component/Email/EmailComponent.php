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

    private $sPathAttachment;

    /**
     * @param string|array $mxEmailTo array tipo array[email1,email2...)
     * @param string $subject
     * @param string|array $mxcontent array tipo $arLines = array["line text 1","line text 2"..) or string
     */
    public function __construct($smtp=[])
    {
        $this->_load_primitives()
            ->_load_smtp($smtp)
            ->_load_headers()
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

    private function _load_headers()
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

    private function _load_smtp($config)
    {
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

    private function _load_pear()
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

    private function _send_pear()
    {
        $this->log("_send_pear()",__CLASS__);
        $this->_load_pear();
        
        try
        {
            $objsmtp = \Mail::factory("smtp",$this->arsmtp);

            $headers = [];
            $headers["Content-Type"] = "text/html; charset=UTF-8";
            if(is_array($this->emails_to))
                $this->emails_to = implode(", ",$this->emails_to);
            $headers["To"] = $this->emails_to;
            if($this->emails_cc) $headers["Cc"] = implode(", ",$this->emails_cc);
            if($this->emails_bcc) $bcc = ", ".implode(", ",$this->emails_bcc);
            $headers["Subject"] = $this->subject;
            //bug($headers);die;
            $headers["From"] = $this->email_from;

            $objmime = new \Mail_mime(["eol"=>PHP_EOL]);
            //$objmime->setTXTBody("texto body"); //texto sin html
            $objmime->setHTMLBody($this->content); //texto con html

            if($this->sPathAttachment)
                $objmime->addAttachment($this->sPathAttachment,"text/plain");

            $arMime = [
                "text_encoding" => "7bit",
                "text_charset" => "UTF-8",
                "html_charset" => "UTF-8",
                "head_charset" => "UTF-8"
            ];

            //do not ever try to call these lines in reverse order
            $content = $objmime->get($arMime);
            $headers = $objmime->headers($headers);
            //la única forma de enviar con copia oculta es añadirlo a los receptores
            $stremailsto = $headers["To"].$bcc;
            //->send es igual a: mail($recipients, $subject,$body,$text_headers);
            $objemail = $objsmtp->send($stremailsto, $headers, $content);

            if(\PEAR::iserror($objemail))
                $this->_add_error($objemail->getMessage());
        }
        catch(Exception $oEx)
        {
            $this->_add_error($oEx->getMessage());
        }

        return $this->iserror;
    }//send_smtp

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

    private function _build_header()
    {
        $header = implode(PHP_EOL,$this->headers);
        $this->header = $header;
    }

    /**
     * uses function mail(...)
     * @return boolean
     */
    private function _send_nosmtp()
    {
        $this->log("_send_nosmtp()",__CLASS__);

        $this->_nosmtp_header_from()
            ->_nosmtp_header_cc()
            ->_nosmtp_header_bcc()
            ->_build_header()
        ;
        
        if($this->emails_to)
        {
            if(is_array($this->emails_to))
                $this->emails_to = implode(", ",$this->emails_to);

            //TRUE if success
            /*
            telnet 127.0.0.1 25
            220 eduardosvc ESMTP Sendmail 8.14.4/8.14.4/Debian-4.1ubuntu1; Thu, 25 Feb 2016 10:47:44 +0100; 
            (No UCE/UBE) logging access from: caser.loc(OK)-caser.loc [127.0.0.1]
            */
            $this->log("antes de llamar a funcion mail",__CLASS__);
            //$this->log("mailsto:$this->emails_to,subject:$this->subject,content:$this->content,header:$this->header");
            $r = mail($this->emails_to,$this->subject,$this->content,$this->header);
            $this->log($r,__CLASS__." status mail(..)");
            if($r == FALSE)
            {
                $this->_add_error("Error sending email!");
            }
        }
        else
        {
            $this->_add_error("No target emails!");
        }
        return $this->iserror;
    }//_send_nosmtp


    /**
     * Utiliza la funcion mail. Se puede recuperar el error con $this->get_error_message();
     * @return boolean TRUE if error occurred
     */
    public function send()
    {
        if($this->issmtp)
            return $this->_send_pear();
        return $this->_send_nosmtp();
    }

    //**********************************
    //             SETS
    //**********************************
    public function set_subject($subject){$this->subject = $subject; return $this;}
    public function set_email_from($stremail){$this->email_from = $stremail; return $this;}
    public function set_emails_to($mxEmails){$this->emails_to = $mxEmails; return $this;}
    public function add_email_to($stremail){$this->emails_to[]=$stremail; return $this;}
    public function set_emails_cc($arEmails){$this->emails_cc = $arEmails; return $this;}
    public function add_email_cc($stremail){$this->emails_cc[]=$stremail; return $this;}
    public function set_emails_bcc($arEmails){$this->emails_bcc = $arEmails; return $this;}
    public function add_email_bcc($stremail){$this->emails_bcc[]=$stremail; return $this;}
    public function set_header($header){$this->header = $header; return $this;}
    public function set_content($mxcontent){(is_array($mxcontent))? $this->content=implode(PHP_EOL,$mxcontent): $this->content = $mxcontent; return $this;}
    public function set_title_from(string $title){$this->title_from = $title; return $this;}

    /**
     *  Required
    "MIME-Version: 1.0"
    "Content-type: text/html; charset=iso-8859-1"
    Optional
     *  "From: Recordatorio <cumples@example.com>"
    "To: Mary <mary@example.com>, Kelly <kelly@example.com>"
    "Cc: birthdayarchive@example.com"
    "Bcc: birthdaycheck@example.com"
     * mail($to,$subject,$message,$headers);
     *
     * @param string $header Cualquer linea anterior
     */
    public function add_header(string $header){$this->headers[] = $header; return $this;}
    public function clear_headers(){$this->headers=[]; return $this;}


}//ComponentMail