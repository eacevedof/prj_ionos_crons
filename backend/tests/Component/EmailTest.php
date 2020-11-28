<?php
namespace Tests\Component;

use PHPUnit\Framework\TestCase;
use App\Component\EmailComponent;

class EmailTest extends TestCase
{

    private function _email($arData,$sEmailTo,$sType="cnt")
    {
        unset($arData["action"]);
        unset($arData["postback"]);

        //bug($arData);die;
        $arErrData = array();
        $sNow = date("YmdHis");
        $sTbody = "";
        foreach ($arData as $sField => $sValue) {
            $sConstant = "tr_he_{$sType}_$sField";
            $sField = get_tr($sConstant);
            $sTbody .= "<tr><td><b>" . htmlentities($sField) . "</b></td>"
                . "<td>" . htmlentities($sValue, ENT_QUOTES) . "</td></tr>\n";
        }
        $sEmailContent = "<html>"
            . "<head></head>"
            . "<body>"
            . "<table>$sTbody</table>"
            . "</body>"
            . "</html>";
        $oComponentMail = new EmailComponent();
        $oComponentMail->set_title_from(ENV_MAILTO_FROM_EMAIL . " - " . $arData["name"]);
        $oComponentMail->set_email_from(ENV_MAILTO_FROM_EMAIL);
        $oComponentMail->set_subject("Theframework.es - Contacto $sNow");
        $oComponentMail->set_content($sEmailContent);
        $oComponentMail->add_email_to($sEmailTo);
        $oComponentMail->send();

        if ($oComponentMail->is_error()) {
            $arErrData["message"] = $oComponentMail->get_error_message();
            $this->log_error("registered.senddata: {$oComponentMail->get_error_message()} ");
        }
        return $arErrData;
    }

    public function test_demo()
    {
        $r = true;
        $this->assertTrue($r);
    }

}//EmailTest
