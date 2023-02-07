<?php

$config['emailTo']   = 'recipient@example.com';
$config['emailFrom'] = $config['emailTo'];
$config['name']      = 'Site Name';
$config['debug']     = false;


require_once '../vendor/autoload.php';
use SDennler\schicksMail\Controller\schicksMailController;
$controller = new schicksMailController($config);
$controller->run();
