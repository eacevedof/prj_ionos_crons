<?php
namespace App\Services\Command;
use App\Component\EmailComponent;

class EmailService extends ACommandService
{
    private function _pear()
    {

        error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);

        require_once "Mail.php";

        $host = "ssl://smtp.dreamhost.com";
        $username = "tucorreo@example.com";
        $password = "la contraseña de tu correo";
        $port = "465";
        $to = "correo_destinatario@example.com";
        $email_from = "tucorreo@example.com";
        $email_subject = "Línea de asunto aquí:";
        $email_body = "Lo que tu quieras";
        $email_address = "responder-a@example.com";

        $headers = array ('From' => $email_from, 'To' => $to, 'Subject' => $email_subject, 'Reply-To' => $email_address);
        $smtp = \Mail::factory('smtp', array ('host' => $host, 'port' => $port, 'auth' => true, 'username' => $username, 'password' => $password));
        $mail = $smtp->send($to, $headers, $email_body);


        if (\PEAR::isError($mail)) {
            echo("<p>" . $mail->getMessage() . "</p>");
        } else {
            echo("<p>Message successfully sent!</p>");
        }
    }

    private function _send()
    {
        error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED ^ E_STRICT);
        // Varios destinatarios
        $para  = 'xxx@yahoo.es';// . ', '; // atención a la coma
        //$para .= 'wez@example.com';

        // título
        $título = 'Recordatorio de cumpleaños para Agosto';

        // mensaje
        $mensaje = '
        <html>
        <head>
        <title>Recordatorio de cumpleaños para Agosto</title>
        </head>
        <body>
        <p>¡Estos son los cumpleaños para Agosto!</p>
        <table>
        <tr>
        <th>Quien</th><th>Día</th><th>Mes</th><th>Año</th>
        </tr>
        <tr>
        <td>Joe</td><td>3</td><td>Agosto</td><td>1970</td>
        </tr>
        <tr>
        <td>Sally</td><td>17</td><td>Agosto</td><td>1973</td>
        </tr>
        </table>
        </body>
        </html>
        ';

        // Para enviar un correo HTML, debe establecerse la cabecera Content-type
        $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Cabeceras adicionales
        $cabeceras .= 'To: Mary <mary@example.com>, Kelly <kelly@example.com>' . "\r\n";
        $cabeceras .= 'From: Recordatorio <cumples@example.com>' . "\r\n";
        //$cabeceras .= 'Cc: birthdayarchive@example.com' . "\r\n";
        //$cabeceras .= 'Bcc: birthdaycheck@example.com' . "\r\n";

        // Enviarlo
        mail($para, $título, $mensaje, $cabeceras);
    }

    public function run()
    {
        $this->logpr("START EMAILSERVICE");
        //$email = new EmailComponent();
        $this->_send();
        //$this->_pear();
        $this->logpr("END EMAILSERVICE");
    }
}