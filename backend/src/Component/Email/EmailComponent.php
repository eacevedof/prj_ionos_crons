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
        $this->emails_cc = [];
        $this->emails_bcc = [];
        $this->subject = "";
        $this->content = "";
        $this->attachments = [];
        return $this;
    }

    private function _load_smtp($config)
    {
        //$this->logpr($config,"_load_smtp.config");
        if($config)
        {
            $this->issmtp               = true;
            $this->arsmtp["host"]       = $config["host"] ?? "";
            $this->arsmtp["port"]       = $config["port"] ?? "25";
            $this->arsmtp["auth"]       = $config["auth"] ?? true;;
            $this->arsmtp["username"]   = $config["username"] ?? "";
            $this->arsmtp["password"]   = $config["password"] ?? "";
            $this->arsmtp["debug"]      = $config["debug"] ?? false;
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

    private function _get_smtp_mime()
    {
        return [
            "text_encoding" => "7bit",
            "text_charset" => "UTF-8",
            "html_charset" => "UTF-8",
            "head_charset" => "UTF-8"
        ];
    }

    private function _get_smtp_to()
    {
        $to[] = $this->headers["To"] ?? "";
        if(trim($this->headers["Bcc"] ?? "")!=="")
            $to[] = $this->headers["Bcc"];

        unset($this->headers["Bcc"]);
        $r = implode(", ",$to);
        return $r;
    }

    private function _smtp_attachment($arattach, $objmime)
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
            $this->_load_smtp_libs()
                ->_smtp_headers()
            ;

            $objmime = new \Mail_mime(["eol"=>PHP_EOL]);

            //$objmime->setTXTBody("texto body"); //texto sin html
            $objmime->setHTMLBody($this->content); //texto con html

            foreach ($this->attachments as $ardata)
                $this->_smtp_attachment($ardata, $objmime);

            //do not ever try to call these lines in reverse order
            $armime = $this->_get_smtp_mime();
            $content = $objmime->get($armime);

            //la única forma de enviar con copia oculta es añadirlo a los receptores
            $stremailsto = $this->_get_smtp_to();
            $headers = $objmime->headers($this->headers);
            $objsmtp = \Mail::factory("smtp",$this->arsmtp);
            //->send es igual a: mail($recipients, $subject, $body, $headers);

            $this->logpr($this->arsmtp,"arsmtp ->");
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

    private function _nosmtp_header_mime()
    {
        $NL = "\r\n";
        $uid = uniqid();
        $this->headers = [
            "MIME-Version: 1.0",
            //"Content-Type: multipart/mixed; boundary=\"$uid\"",
            //"This is a MIME encoded message.",
            //"--$uid",
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

    private function _get_nosmtp_attachment(array $arattach)
    {
        return "";
        //https://stackoverflow.com/questions/12301358/send-attachments-with-php-mail
        $pathfile = $arattach["path"];
        if(!is_file($pathfile)) return "";

        $mime = $arattach["mime"] ?? "application/octet-stream";
        $alias = $arattach["filename"] ?? basename($pathfile);

        $content = file_get_contents($pathfile);
        if(!$content) return "";

        $content = chunk_split(base64_encode($content));
        // a random hash will be necessary to send mixed content
        $separator = md5(time());

        $body[] = "";
        $body[] = "-- $separator";
        $body[] = "Content-Type: $mime; name=\"$alias\"";
        $body[] = "Content-Transfer-Encoding: base64";
        $body[] = "Content-Disposition: attachment";
        $body[] = $content;
        $body[] = "--$separator--";
        $body[] = "";

        return implode(PHP_EOL, $body);
    }

    private function _send_nosmtp()
    {
        try {
            if($this->emails_to)
            {
                $this->_nosmtp_header_mime()
                    ->_nosmtp_header_from()
                    ->_nosmtp_header_cc()
                    ->_nosmtp_header_bcc()
                    ->_nomstp_header()
                ;

                $this->emails_to = implode(", ",$this->emails_to);
                foreach ($this->attachments as $arattach)
                    $this->content .= $this->_get_nosmtp_attachment($arattach);

                $this->logpr($this->content,"content");
                $this->logpr($this->header, "header");
                $r = mail($this->emails_to, $this->subject, $this->content, $this->header);
                if(!$r)
                    $this->_add_error("Error sending email!");
            }
            else
            {
                $this->_add_error("No target emails!");
            }
        }
        catch (\Exception $e)
        {
            $this->_add_error($e->getMessage());
        }
        finally {
            return $this;
        }
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
    public function add_attachment($arattach=["path"=>"","mime"=>"","filename"=>""]){$this->attachments[] = $arattach; return $this;}

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