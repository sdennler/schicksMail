<?php

declare(strict_types=1);

namespace schicksMail\schicksMail\Data;

use schicksMail\schicksMail\BotDetector\BotDetector;

class SchicksMailData
{
    protected $data = array();
    protected $error = '';
    protected $valid = true;
    public function __construct(Array $input, private ?BotDetector $botDetector = null){
        $name = trim(str_replace(array("\r", "\n"), ' ', strip_tags($input['name'])));
        $email = filter_var(trim($input['email']), FILTER_SANITIZE_EMAIL);
        $subject = trim(str_replace(array("\r", "\n"), ' ', strip_tags($input['subject'])));
        $message = trim(strip_tags($input['message']));
        $botDetected = isset($this->botDetector) ? $this->botDetector->isBot($input) : false;
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
