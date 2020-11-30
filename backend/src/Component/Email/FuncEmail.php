<?php
namespace App\Component\Email;

final class FuncEmail extends AEmail
{

    //php-mail
    private $title_from;
    private $email_from;
    private $header;
    private $boundary;

    /**
     * optional smtp config array in case of using PEAR
     * @param array $smtp
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
        $this->headers =[];
        return $this;
    }

    private function _phpmail_header_mime()
    {
        $headers = [
            "MIME-Version: 1.0",
            "Content-Type: text/html; charset=\"UTF-8\"",
            "Content-Transfer-Encoding: 8bit",
        ];

        if($this->boundary)
        {
            $headers = [
                "MIME-Version: 1.0",
                "Content-Type: multipart/mixed; boundary=\"$this->boundary\"",
                "Content-Transfer-Encoding: 7bit",
                "This is a MIME encoded message."
            ];
        }
        $this->headers = array_merge($this->headers,$headers);
        return $this;
    }

    private function _phpmail_header_from()
    {
        $this->headers[] = "From: $this->title_from <$this->email_from>";
        $this->headers[] = "Return-Path: <$this->email_from>";
        $this->headers[] = "X-Sender: $this->email_from";
        return $this;
    }

    private function _phpmail_header_cc()
    {
        if($this->emails_cc)
            $this->headers[] = "cc: ".implode(", ",$this->emails_cc);
        return $this;
    }

    private function _phpmail_header_bcc()
    {
        if($this->emails_bcc)
            $this->headers[] = "bcc: ".implode(", ",$this->emails_bcc);
        return $this;
    }

    private function _phpmail_header()
    {
        $header = implode(PHP_EOL, $this->headers);
        $this->header = $header;
        return $this;
    }

    private function _phpmail_boundary()
    {
        if($this->attachments)
            $this->boundary = md5(uniqid());
        return $this;
    }

    private function _get_phpmail_multipart()
    {
        if(!$this->boundary) return "";
        $content[] = "--$this->boundary";
        $content[] = "Content-Type: text/html; charset=UTF-8";
        $content[] = "Content-Transfer-Encoding: 8bit";
        return implode(PHP_EOL, $content);
    }

    private function _get_phpmail_attachment(array $arattach)
    {
        //https://stackoverflow.com/questions/12301358/send-attachments-with-php-mail
        $pathfile = $arattach["path"];
        if(!is_file($pathfile)) return "";

        $mime = $arattach["mime"] ?? "application/octet-stream";
        $alias = $arattach["filename"] ?? basename($pathfile);

        $content = file_get_contents($pathfile);
        if(!$content) return "";

        $content = chunk_split(base64_encode($content));
        $separator = $this->boundary;

        $body[] = "";
        $body[] = "--$separator";
        $body[] = "Content-Type: $mime; name=\"$alias\"";
        $body[] = "Content-Transfer-Encoding: base64";
        $body[] = "Content-Disposition: attachment; ";
        $body[] = $content;
        $body[] = "--$separator--";
        $body[] = "";

        return implode(PHP_EOL, $body);
    }

    private function _send_phpmail()
    {
        try {
            if($this->email_from && $this->emails_to)
            {
                $this->_phpmail_boundary()
                    ->_phpmail_header_from()
                    ->_phpmail_header_mime()
                    ->_phpmail_header_cc()
                    ->_phpmail_header_bcc()
                    ->_phpmail_header()
                ;

                $content = $this->_get_phpmail_multipart().PHP_EOL;
                $content .= $this->content.PHP_EOL;

                foreach ($this->attachments as $arattach)
                    $content .= $this->_get_phpmail_attachment($arattach);

                $this->logpr($this->emails_to,"TO ->");
                $this->logpr($this->header, "HEADER ->");
                $this->logpr($content,"BODY ->");

                $this->emails_to = implode(", ",$this->emails_to);
                $r = mail($this->emails_to, $this->subject, $content, $this->header);
                if(!$r) {
                    $this->_add_error("Error sending email!");
                    $this->_add_error(error_get_last());
                }
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
        return $this->_send_phpmail();
    }

    public function set_subject($subject){$this->subject = $subject; return $this;}

    public function set_from($stremail){$this->email_from = $stremail; return $this;}
    public function set_title_from(string $title){$this->title_from = $title; return $this;}
    public function add_to($stremail){$this->emails_to[]=$stremail; return $this;}
    public function add_cc($stremail){$this->emails_cc[]=$stremail; return $this;}
    public function add_bcc($stremail){$this->emails_bcc[]=$stremail; return $this;}
    public function set_phpmail_header($header){$this->header = $header; return $this;}
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
    public function add_phpmail_header(string $header){$this->headers[] = $header; return $this;}

}