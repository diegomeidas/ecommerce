<?php

session_start();
//para trazer as dependecias que meu projeto precisa
require_once("vendor/autoload.php");

//namespaces//classes que eu vou usar

use \Slim\Slim;



$app = new Slim();

$app->config('debug', true);

require_once ("site.php");
require_once ("admin.php");
require_once ("admin-users.php");
require_once ("admin-categories.php");
require_once ("admin-products.php");




$app->run();

 ?>