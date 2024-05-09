<?php

$config['emailTo']         = 'recipient@example.com';
$config['emailFrom']       = $config['emailTo'];
$config['name']            = 'Site Name';
$config['debug']           = false;
$config['hCaptchaSitekey'] = '[[your-sitekey]]';
$config['hCaptchaSecret']  = '[[your-secret]]';


require_once '../vendor/autoload.php';
use schicksMail\schicksMail\Controller\schicksMailController;
use schicksMail\schicksMail\BotDetector\HcaptchaBotDetector;
$botDetector = new HcaptchaBotDetector($config['hCaptchaSitekey'], $config['hCaptchaSecret']);
$controller = new schicksMailController($config, $botDetector);
$controller->run();
