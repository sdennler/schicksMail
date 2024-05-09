<?php

$debug         = false;
$emailTo       = '';
$emailFrom     = '';
$name          = '';
$hCaptchaSitekey = '[[your-sitekey]]';
$hCaptchaSecret = '[[your-secret]]';

$pathPHPMailer = '../PHPMailer-master/';


if ($_SERVER["REQUEST_METHOD"] != "POST") {
    displayError('Nono ✋', 403);
}


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require $pathPHPMailer.'src/Exception.php';
require $pathPHPMailer.'src/PHPMailer.php';
require $pathPHPMailer.'src/SMTP.php';

try{
    $captcha = new captcha($hCaptchaSitekey, $hCaptchaSecret, $_POST);
    $bot = !$captcha->validate();
}catch(\Exception $e){
    displayError(sprintf('There was a error with the captcha 😕 (%s)', $e->getMessage()), 400);
}

$input = new sendMailData($_POST, $bot);
unset($_POST);

if ($input->isInvalid()) {
    $error = $input->error();
    displayError($error, 400);
}


$mail = new PHPMailer;
$mail->isMail();
// $mail->isSMTP();
// $mail->Host = 'localhost';
// $mail->Port = 25;

$mail->setFrom($emailFrom, $name);
$mail->addAddress($emailTo, $name);

if (!$mail->addReplyTo($input->get('email'), $input->get('name'))) {
    displayError('There is some thing wrong with your email. 😕', 400);
}

$mail->isHTML(false);
$mail->CharSet = 'UTF-8';
$mail->Subject = "Message via $name: ".$input->get('subject');
$mail->Body = $input->body();

if (!$mail->send()) {
    displayError("I could not send your message 😭\nPleases try later or an other contact way.", 400);
}
displayError("Thank you for your message! 🙋\nI will answer soon.", 200);


class sendMailData{
    protected $data = array();
    protected $error = '';
    protected $valid = true;
    public function __construct(Array $input, bool $botDetected){
        $name = trim(str_replace(array("\r", "\n"), ' ', strip_tags($input['name'])));
        $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
        $subject = trim(str_replace(array("\r", "\n"), ' ', strip_tags($input['subject'])));
        $message = trim(strip_tags($input['message']));
        if (
            !$name
            | !filter_var($email, FILTER_VALIDATE_EMAIL)
            | !$message
            | $botDetected
        ) {
            $this->valid = false;
            $this->error = 'Some fields are not filled the way I like it. 😕';
        }
        $this->data['name'] = $name;
        $this->data['email'] = $email;
        $this->data['subject'] = $subject;
        $this->data['message'] = $message;
        $this->data['botDetected'] = $botDetected;
    }
    public function isInvalid(){
        return !$this->valid;
    }
    public function error(){
        return $this->error;
    }
    public function get($key){
        return $this->data[$key];
    }
    public function body(){
        $msg = "You got a contact! 🙌\n\n";
        foreach($this->data as $key => $value){
            $msg .= "$key: $value\n";
        }
        return $msg;
    }
}

class captcha{
    private const VERIFY_URL = 'https://api.hcaptcha.com/siteverify';
    private string|false $response;
    public function __construct(
        private string $sitekey,
        private string $secret,
        array $input,
    ){
        $this->response = $input['h-captcha-response'] ?? false;
    }
    public function validate():bool{
        if(empty($this->response)){
            return false;
        }

        $options = [
            'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query([
                    'sitekey' => $this->sitekey,
                    'secret' => $this->secret,
                    'response' => $this->response,
                ]),
            ],
        ];
        $context = stream_context_create($options);

        $response = file_get_contents(self::VERIFY_URL, false, $context);
        if ($response === false) {
            throw new RuntimeException('Captcha could not be validated');
        }

        $response = json_decode($response);
        if (!is_object($response)) {
            throw new RuntimeException('Captcha returned wrong value');
        }

        return $response->success;
    }
}

function displayError($error, $code){
    global $debug, $input, $mail;
    if($debug && isset($input)){
        $error .= ' I: '.var_export($input, true);
        if(isset($mail)){
            $error .= ' M: '.$mail->ErrorInfo;
        }
    }

    if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
        http_response_code($code);
        die($error);
    }
    die("<!DOCTYPE html><html><body><h1>$error</h1></body></html>");
}
