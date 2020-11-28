<?php
namespace App\Component\Email;



class EmailComponent extends AEmail
{
    
    
    private $sFromTitle;
    private $emailfrom;
    private $mxemails_to;
    private $arEmailsCc;
    private $arEmailsBcc;

    private $subject;
    private $sContent;
    private $sHeader;

    private $headers;

    private $issmtp;
    private $smtp;

    private $sSmtpHost;
    private $sSmtpPort;
    private $isSmtpAuth;
    private $sSmtpUser;
    private $sSmtpPassw;

    private $sPathAttachment;




    /**
     *
     * @param string|array $mxEmailTo array tipo array[email1,email2...)
     * @param string $subject
     * @param string|array $mxContent array tipo $arLines = array["line text 1","line text 2"..) or string
     */
    public function __construct($smtp=[])
    {
        parent::__construct();
        $this->_load_smtp($smtp);
        
        $this->sFromTitle = "";

        $this->emailfrom = $this->sSmtpUser;
        
        $this->mxemails_to = [];
        $this->headers = [];
        $this->headers[] = "MIME-Version: 1.0";
        //$this->headers[] = "Content-Type: text/html; charset=ISO-8859-1";
        $this->headers[] = "Content-Type: text/html; charset=UTF-8";
        //add boundary string and mime type specification
        $this->_header[] = "Content-Transfer-Encoding: 8bit";
        if($mxEmailTo) $this->mxemails_to[] = $mxEmailTo;
        $this->subject = $subject;

        if(is_array($mxContent))
            $mxContent = implode(PHP_EOL,$mxContent);
        $this->sContent = $mxContent;

    }//__construct

    private function _load_smtp($config)
    {
        if($config)
        {
            $this->issmtp     = true;
            $this->sSmtpHost  = $config["host"] ?? "";
            $this->sSmtpPort  = $config["port"] ?? "25";
            $this->isSmtpAuth = $config["auth"] ?? "";;
            $this->sSmtpUser  = $config["user"] ?? "";
            $this->sSmtpPassw = $config["password"] ?? "";
        }
    }

    private function _send_pear()
    {
        $this->log("_send_pear()",__CLASS__);
        //errorson();
        //necesita tener instalado:
        // PEAR https://pear.php.net/manual/en/installation.checking.php
        // Mail,Mail_Mime,Net_SMTP,Net_Socket
        // sudo pear install <pack> --alldeps

        //C:\xampp\php>pear list
        require_once("Mail.php");
        require_once("Mail/mime.php");

        $arSmtp = [];
        $arSmtp["host"] = $this->sSmtpHost;
        $arSmtp["port"] = $this->sSmtpPort;
        $arSmtp["auth"] = $this->isSmtpAuth;
        $arSmtp["username"] = $this->sSmtpUser;
        $arSmtp["password"] = $this->sSmtpPassw;
        //$arSmtp["debug"] = 1;
        //bug($arSmtp,"arSmtp");die;
        try
        {
            $oSmtp = \Mail::factory("smtp",$arSmtp);

            $headers = [];
            $headers["Content-Type"] = "text/html; charset=UTF-8";
            //$headers["From"] = $this->emailfrom;
            if(is_array($this->mxemails_to))
                $this->mxemails_to = implode(", ",$this->mxemails_to);
            $headers["To"] = $this->mxemails_to;
            if($this->arEmailsCc) $headers["Cc"] = implode(", ",$this->arEmailsCc);
            if($this->arEmailsBcc) $sBcc = ", ".implode(", ",$this->arEmailsBcc);
            $headers["Subject"] = $this->subject;
            //bug($headers);die;
            $headers["From"] = $this->emailfrom;

            $oMime = new \Mail_mime(["eol"=>PHP_EOL]);
            //$oMime->setTXTBody("texto body"); //texto sin html
            $oMime->setHTMLBody($this->sContent); //texto con html

            if($this->sPathAttachment)
                $oMime->addAttachment($this->sPathAttachment,"text/plain");

            $arMime = [
                "text_encoding" => "7bit",
                "text_charset" => "UTF-8",
                "html_charset" => "UTF-8",
                "head_charset" => "UTF-8"
            ];

            //do not ever try to call these lines in reverse order
            $sContent = $oMime->get($arMime);
            $headers = $oMime->headers($headers);
            //la única forma de enviar con copia oculta es añadirlo a los receptores
            $stremailsTo = $headers["To"].$sBcc;
            //->send es igual a: mail($recipients, $subject,$body,$text_headers);
            $objemail = $oSmtp->send($stremailsTo,$headers,$sContent);

            if(PEAR::iserror($objemail))
            {
                $this->_add_error($objemail->getMessage());
            }
        }
        catch(Exception $oEx)
        {
            $this->_add_error($oEx->getMessage());
        }
        //bug($this->arErrorMessages);die;
        return $this->iserror;
    }//send_smtp

    /**
     * uses function mail(...)
     * @return boolean
     */
    private function _send_nosmtp()
    {
        $this->log("_send_nosmtp()",__CLASS__);
        $this->_build_header_from();
        $this->_build_header_cc();//crea header: Cc
        $this->_build_header_bcc();//crea header: Bcc
        //crea los header en $this->_header
        $this->_build_header();

        $this->log("mailsto:$this->mxemails_to,subject:$this->subject,header:$this->sHeader",__CLASS__."_send_nosmtp()");
        if($this->mxemails_to)
        {
            if(is_array($this->mxemails_to))
                $this->mxemails_to = implode(", ",$this->mxemails_to);

            //TRUE if success
            /*
            telnet 127.0.0.1 25
            220 eduardosvc ESMTP Sendmail 8.14.4/8.14.4/Debian-4.1ubuntu1; Thu, 25 Feb 2016 10:47:44 +0100; 
            (No UCE/UBE) logging access from: caser.loc(OK)-caser.loc [127.0.0.1]
            */
            $this->log("antes de llamar a funcion mail",__CLASS__);
            //$this->log("mailsto:$this->mxemails_to,subject:$this->subject,content:$this->sContent,header:$this->sHeader");
            $r = mail($this->mxemails_to,$this->subject,$this->sContent,$this->sHeader);
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

    private function _build_header()
    {
        $sHeader = implode(PHP_EOL,$this->headers);
        $this->sHeader = $sHeader;
    }

    private function _build_header_from()
    {
        if($this->emailfrom)
        {
            $this->headers[] = "From: $this->sFromTitle <$this->emailfrom>";
            $this->headers[] = "Return-Path: <$this->emailfrom>";
            $this->headers[] = "X-Sender: $this->emailfrom";
        }
    }

    private function _build_header_cc()
    {
        if($this->arEmailsCc)
            $this->headers[] = "Cc: ".implode(", ",$this->arEmailsCc);
    }

    private function _build_header_bcc()
    {
        if($this->arEmailsBcc)
            $this->headers[] = "Bcc: ".implode(", ",$this->arEmailsBcc);
    }

    //**********************************
    //             SETS
    //**********************************
    public function set_subject($subject){$this->subject = $subject;}
    public function set_email_from($stremail){$this->emailfrom = $stremail;}
    public function set_emails_to($mxEmails){$this->mxemails_to = $mxEmails;}
    public function add_email_to($stremail){$this->mxemails_to[]=$stremail;}
    public function set_emails_cc($arEmails){$this->arEmailsCc = $arEmails;}
    public function add_email_cc($stremail){$this->arEmailsCc[]=$stremail;}
    public function set_emails_bcc($arEmails){$this->arEmailsBcc = $arEmails;}
    public function add_email_bcc($stremail){$this->arEmailsBcc[]=$stremail;}
    public function set_header($sHeader){$this->sHeader = $sHeader;}
    public function set_content($mxContent){(is_array($mxContent))? $this->sContent=implode(PHP_EOL,$mxContent): $this->sContent = $mxContent;}
    public function set_title_from($sTitle){$this->sFromTitle = $sTitle;}

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
     * @param string $sHeader Cualquer linea anterior
     */
    public function add_header($sHeader){$this->headers[] = $sHeader;}
    public function clear_headers(){$this->headers=[];}

    public function set_smtp_use($isOn=TRUE){$this->issmtp=$isOn;}
    public function set_smtp_host($sValue){$this->sSmtpHost=$sValue;}
    public function set_smtp_port($sValue){$this->sSmtpPort=$sValue;}
    public function set_smtp_auth($isOn=TRUE){$this->isSmtpAuth=$isOn;}
    public function set_smtp_user($sValue){$this->sSmtpUser=$sValue;}
    public function set_smtp_passw($sValue){$this->sSmtpPassw=$sValue;}


}//ComponentMail