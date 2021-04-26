<?php
include 'smtp/PHPMailerAutoload.php';

class EmailSender
{
    public function sendOtp($to, $sOtp)
    {
        $subject = "Customer verifivcation";
        $msg = "Your OTP for verification is $sOtp";
        return smtp_mailer($to, $subject, $msg);
    }
    
    public function smtp_mailer($to, $subject, $msg)
    {
        $mail = new PHPMailer();
        $mail->SMTPDebug = 3;
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = 'tls';
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 587;
        $mail->IsHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Username = "rohitrjadhav19@gmail.com";
        $mail->Password = "password";
        $mail->SetFrom("rohitrjadhav19@gmail.com");
        $mail->Subject = $subject;
        $mail->Body = $msg;
        $mail->AddAddress($to);
        $mail->SMTPOptions = array('ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => false,
        ));

        return $mail->Send();
    }

}
