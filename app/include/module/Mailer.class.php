<?php

//include_once('Mail.php');
//include_once('Mail/mime.php');
$base_path = str_replace('include', '', __DIR__);
require_once ($base_path . 'vendor/PHPMailer/PHPMailerAutoload.php');


/**
 * Created by PhpStorm.
 * User: user1
 * Date: 22.05.16
 * Time: 21:15
 */
class Mailer{

    /**
     * отправка электронной почты
     * @param $to
     * @param $subject
     * @param $message
     * @param $from
     * @return bool
     */
    public static function send($to, $subject, $message, $from, $files=null){
        $method = 'send_' . MAILER;
        return self::$method($to, $subject, $message, $from, $files);
    }


    /**
     * отправка электронной почты через функцию mail
     * @param $to
     * @param $subject
     * @param $message
     * @param $from
     * @return bool
     */
    public static function send_mail($to, $subject, $message, $from, $files=null){
        $headers   = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/plain; charset=utf-8";
        $headers[] = "From: {$from}";
        $headers[] = "Subject: {$subject}";
        $headers[] = "X-Mailer: PHP/".phpversion();
        $res = mail($to, $subject, $message, implode("\r\n", $headers));
        if(!$res) {
            return array('status' => false, 'message' => 'error');
        } else {
            return array('status' => true, 'message' => "Mail to {$to} was send");
        }
    }


    /**
     * отправка электронной почты через PEAR::Mail
     * @param $to
     * @param $subject
     * @param $message
     * @param $from
     * @param null $file
     * @return mixed
     * @throws Exception
     */
    public static function send_pearmail($to, $subject, $message, $from, $file=null){
        if (!is_array($to)){
            $to = array($to);
        }
        $headers   = array();
        $headers['MIME-Version'] = '1.0';
        $headers['Content-type'] = 'text/plain; charset=utf-8';
        $headers['From'] = $from;
        $headers['Subject'] = $subject;
        $headers['X-Mailer'] = 'PHP/'.phpversion();

        $crlf = "\r\n";
        $mime = new Mail_mime(array('eol' => $crlf));

        $mime->setHTMLBody($message);
        if($file != null){
            if (!is_array($file))
                throw new Exception('param "file" must be array: [name]=>filename, [type]=>content-type');
            $mime->addAttachment($file['name'], $file['type']);
        }

        $body = $mime->get();
        $hdrs = $mime->headers($headers);

        $mail =& Mail::factory('mail', '-f ' . $from);
        $res = $mail->send($to, $hdrs, $body);
        if(!$res) {
            return array('status' => false, 'message' => 'error');
        } else {
            return array('status' => true, 'message' => "Mail to {$to} was send");
        }

    }


    /**
     * отправка электронной почты через класс PHPMailers
     * @param $to
     * @param $subject
     * @param $message
     * @param $from
     * @param null $file
     * @return bool
     * @throws phpmailerException
     */
    public static function send_phpmailer($to, $subject, $message, $from, $files=null){
        $use_smtp = Store::get_option('use_smtp');
        $smtp_server = Store::get_option('smtp_server');
        $smtp_auth = boolval(Store::get_option('smtp_auth'));
        $smtp_username = Store::get_option('smtp_username');
        $smtp_password = Store::get_option('smtp_password');
        $smtp_secure = Store::get_option('smtp_secure');
        $smtp_port = intval(Store::get_option('smtp_port'));
        $mail = new PHPMailer();
        if ($use_smtp){
            $mail->isSMTP();
            $mail->Host = $smtp_server;             // Specify main and backup SMTP servers
            $mail->SMTPAuth = boolval($smtp_auth);  // Enable SMTP authentication
            $mail->Username = $smtp_username;       // SMTP username
            $mail->Password = $smtp_password;       // SMTP password
            $mail->SMTPSecure = $smtp_secure;       // Enable TLS encryption, `ssl` also accepted
            $mail->Port = intval($smtp_port);
        }
        $mail->SMTPDebug = 2;
        $mail->setFrom($from, substr($from,0, strpos($from,'@')));
        $mail->addAddress($to, $to);
        $mail->XMailer = 'PHP/'.phpversion();
        $mail->CharSet = 'utf-8';
        $mail->Encoding = 'base64';
        $mail->addCustomHeader('Sender', $from);
        $mail->addCustomHeader('X-PHP-Originating-Script', '1000:class.phpmailer.php');
        $mail->addCustomHeader('Reply-To', $from);
        $mail->Subject  = trim($subject);
        $mail->isHTML(true);
        $mail->Body     = $message;
        $mail->AltBody = strip_tags($message);
        $mail->msgHTML($message, $basedir = ''); /*для автоматической вставки inline изображений*/
        if ($files != null && is_array($files) && count($files)){
            foreach($files as $file){
                $mail->addAttachment($file['name'], basename($file['name']), 'base64', $file['type']);
            }
        }
        ob_start();
        $res = $mail->send();
        $log = ob_get_flush();
        if(!$res) {
            return array('status' => false, 'message' => $log . $mail->ErrorInfo);
        } else {
            return array('status' => true, 'message' => $log . "Mail to {$to} was send");
        }
    }

}