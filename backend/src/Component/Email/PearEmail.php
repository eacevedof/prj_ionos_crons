<?php
namespace App\Component\Email;

final class PearEmail extends AEmail
{
    private $arconfig;

    /**
     * optional smtp config array in case of using PEAR
     * @param array $arconfig
     */
    public function __construct($arconfig=[])
    {
        $this->_load_config($arconfig);
    }//__construct

    private function _load_config($config)
    {
        //$this->logpr($config,"_load_config.config");
        if($config)
        {
            $this->issmtp               = true;
            $this->arconfig["host"]       = $config["host"] ?? "";
            $this->arconfig["port"]       = $config["port"] ?? "25";
            $this->arconfig["auth"]       = $config["auth"] ?? true;;
            $this->arconfig["username"]   = $config["username"] ?? "";
            $this->arconfig["password"]   = $config["password"] ?? "";
            $this->arconfig["debug"]      = $config["debug"] ?? false;
        }
        return $this;
    }

    private function _load_libs()
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

    private function _headers()
    {
        $this->headers = [];
        $this->headers["Content-Type"] = "text/html; charset=UTF-8";
        $this->headers["From"] = $this->email_from;

        $this->headers["To"] = implode(", ",$this->emails_to);
        if($this->emails_cc)
            $this->headers["Cc"] = implode(", ",$this->emails_cc);

        //creo que esto no va
        if($this->emails_bcc)
            $this->headers["Bcc"] = implode( ", ", $this->emails_bcc);

        $this->headers["Subject"] = $this->subject;

        //$this->headers["Replay-To"] = $this->emails_to;
    }

    private function _get_mime()
    {
        return [
            "text_encoding" => "7bit",
            "text_charset" => "UTF-8",
            "html_charset" => "UTF-8",
            "head_charset" => "UTF-8"
        ];
    }

    private function _get_to()
    {
        $to[] = $this->headers["To"] ?? "";
        if(trim($this->headers["Bcc"] ?? "")!=="")
            $to[] = $this->headers["Bcc"];

        unset($this->headers["Bcc"]);
        $r = implode(", ",$to);
        return $r;
    }

    private function _attachment($arattach, $objmime)
    {
        /*
        https://pear.php.net/manual/en/package.mail.mail-mime.addattachment.php
        boolean addAttachment (1 string $file , 2 string $c_type = 'application/octet-stream' , 3 string $name = '' , 4 boolean $isfile = true ,
        5 string $encoding = 'base64' , 6 string $disposition = 'attachment' , 7 string $charset = '' , 8 string $language = '' , 9 string $location = '' ,
        10 string $n_encoding = null , 11 string $f_encoding = null , 12 string $description = '' , 13 string $h_charset = null )
        */
        $pathfile = $arattach["path"];
        if(!is_file($pathfile)) return "";

        $mime = $arattach["mime"] ?? "application/octet-stream";
        $alias = $arattach["filename"] ?? basename($pathfile);
        $objmime->addAttachment($pathfile, $mime, $alias);
    }

    private function _send_smtp()
    {
        //$this->logpr("send_smtp");
        try
        {
            $this->_load_libs()
                ->_headers()
            ;

            $objmime = new \Mail_mime(PHP_EOL);

            //$objmime->setTXTBody("texto body"); //texto sin html
            $objmime->setHTMLBody($this->content); //texto con html

            foreach ($this->attachments as $ardata)
                $this->_attachment($ardata, $objmime);

            //do not ever try to call these lines in reverse order
            $armime = $this->_get_mime();
            $content = $objmime->get($armime);

            //la única forma de enviar con copia oculta es añadirlo a los receptores
            $stremailsto = $this->_get_to();
            $headers = $objmime->headers($this->headers);
            $objsmtp = \Mail::factory("smtp",$this->arconfig);
            //->send es igual a: mail($recipients, $subject, $body, $headers);

            $this->logpr($this->arconfig,"arconfig ->");
            $this->logpr($headers,"headers ->");
            $this->logpr($stremailsto,"to ->");
            $this->logpr($content,"content ->");
            $objemail = $objsmtp->send($stremailsto, $headers, $content);

            //$this->logpr($objemail,"objmail");
            if(\PEAR::iserror($objemail))
                $this->_add_error($objemail->getMessage());
        }
        catch(Exception $oEx)
        {
            $this->_add_error($oEx->getMessage());
        }

        return $this;
    }

    public function send()
    {
        if($this->issmtp)
            return $this->_send_smtp();
        return $this->_send_phpmail();
    }



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
    public function add_header(string $header){$this->headers[] = $header; return $this;}

}