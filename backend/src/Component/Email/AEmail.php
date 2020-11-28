<?php
namespace App\Component\Email;

use App\Traits\LogTrait as Log;

abstract class AEmail implements IEmail
{
    use Log;
    protected $errors;
    protected $iserror;

    protected function _add_error($msg)
    {
        $this->iserror = true;
        $this->errors[] = $msg;
        return $this;
    }

    public function get_errors(){return $this->errors;}
}