<?php
namespace Alph\Services;

use PHPMailer\PHPMailer\PHPMailer;

class Mail
{
    /**
     * PHPMailer object
     */
    public $mail;

    /**
     * Create a new mail
     */
    public function __construct(\PDO $db, string $subject, string $message, $to)
    {
        // Create a new PHPMailer instance
        $mail = new PHPMailer;

        // Use SMTP with PHPMailer
        $mail->isSMTP();

        // Disable SMTP debugging
        $mail->SMTPDebug = 0;

        // Set the hostname of the mail server
        $mail->Host = 'smtp.gmail.com';

        // Set the SMTP port number to 587 for authenticated TLS
        $mail->Port = 587;

        // Set the encryption system
        $mail->SMTPSecure = 'tls';

        // Tell PHPMailer to use authentication system
        $mail->SMTPAuth = true;

        // Define ssl options
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];

        // Select the mailaddress and mailpassword from database
        $result = $db->query("SELECT `key`, value FROM CONFIG WHERE `key`='mailaddress' OR `key`='mailpassword'");

        // Check if number of rows are 2
        if ($result->rowCount() == 2) {
            $mail_config = [];

            // Store the email address and password
            foreach ($result as $value) {
                $mail_config[$value["key"]] = $value["value"];
            }

            // Email to use to send a mail
            $mail->Username = $mail_config["mailaddress"];

            // Password to use for SMTP authentication
            $mail->Password = $mail_config["mailpassword"];

            // Set the email sender
            $mail->setFrom($mail_config["mailaddress"], 'Alph Terminal');

            // Set email receivers
            foreach ($to as $address) {
                $mail->addAddress($address);
            }

            // Set the subject line
            $mail->Subject = $subject;

            // Set the HTML core message
            $mail->msgHTML($message);


            $this->mail = $mail;
        }
    }

    /**
     * Send the mail
     */
    public function send()
    {
        //send the message, check for errors
        if (!$this->mail->send()) {
            echo "Mailer Error: " . $this->mail->ErrorInfo;
        } else {
            echo "Message sent!";
        }
    }
}
