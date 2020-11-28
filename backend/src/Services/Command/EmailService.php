<?php
namespace App\Services\Command;
use App\Component\EmailComponent;

class EmailService extends ACommandService
{
    public function run()
    {
        $this->logpr("START EMAILSERVICE");
        $email = new EmailComponent();
        
        $this->logpr("END EMAILSERVICE");
    }
}