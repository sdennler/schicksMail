<?php

$config['debug']         = true;
$config['emailTo']       = 'recipient@example.com';
$config['emailFrom']     = $config['emailTo'];
$config['name']          = 'Site Name';


require_once '../vendor/autoload.php';
use SDennler\schicksMail\Controller\schicksMailController;
$controller = new schicksMailController($config);
$controller->run();
