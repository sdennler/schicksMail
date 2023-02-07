<?php

declare(strict_types=1);

namespace schicksMail\schicksMail\Controller;

use JetBrains\PhpStorm\NoReturn;
use PHPMailer\PHPMailer\PHPMailer;
use schicksMail\schicksMail\Data\SchicksMailData;
use schicksMail\schicksMail\Validator\ConfigValidator;

class schicksMailController
{
    private array $config;
    private SchicksMailData $data;
    private PHPMailer $mail;

    public function __construct(array $config)
    {
        $validator = new ConfigValidator();
        $this->config = $validator->validate($config);
    }
    public function run(): void
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            $this->displayError('Nono ✋', 403);
        }

        $this->processInputData();
        $this->sendMail();
    }

    private function processInputData(): void
    {
        $data = new SchicksMailData($_POST);
        unset($_POST);

        if ($data->isInvalid()) {
            $error = $data->error();
            $this->displayError($error, 400);
        }

        $this->data = $data;
    }

    private function sendMail(): void
    {
        $this->mail = new PHPMailer;
        $this->mail->isMail();
        // $this->mail->isSMTP();
        // $this->mail->Host = 'localhost';
        // $this->mail->Port = 25;

        $this->mail->setFrom($this->config['emailFrom'], $this->config['name']);
        $this->mail->addAddress($this->config['emailTo'], $this->config['name']);

        if (!$this->mail->addReplyTo($this->data->get('email'), $this->data->get('name'))) {
            $this->displayError('There is some thing wrong with your email. 😕', 400);
        }

        $this->mail->isHTML(false);
        $this->mail->CharSet = 'UTF-8';
        $this->mail->Subject = sprintf('Message via %s: %s',$this->config['name'], $this->data->get('subject'));
        $this->mail->Body = $this->data->body();

        if (!$this->mail->send()) {
            $this->displayError("I could not send your message 😭\nPleases try later or an other contact way.", 500);
        }
        $this->displayError("Thank you for your message! 🙋\nI will answer soon.", 200);
    }

    #[NoReturn]
    private function displayError(string $error, int $code): void
    {
        if($this->config['debug'] && isset($this->data)){
            $error .= ' I: '.var_export($this->data, true);
            if(isset($this->mail)){
                $error .= ' M: '.$this->mail->ErrorInfo;
            }
        }

        if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
            http_response_code($code);
            die($error);
        }
        die("<!DOCTYPE html><html><body><h1>$error</h1></body></html>");
    }
}
