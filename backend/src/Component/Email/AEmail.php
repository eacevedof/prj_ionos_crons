<?php
namespace App\Component\Email;

use App\Traits\LogTrait as Log;

abstract class AEmail implements IEmail
{
    use Log;

    protected $headers = [];
    protected $emails_to = [];
    protected $emails_cc = [];
    protected $emails_bcc = [];
    protected $attachments = [];

    protected $subject = "";
    protected $content = "";

    protected $issmtp = false;
    protected $errors = [];
    protected $iserror = false;

    protected function _add_error($msg)
    {
        $this->iserror = true;
        $this->errors[] = $msg;
        return $this;
    }

    public function get_errors(){return $this->errors;}
}