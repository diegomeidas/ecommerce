<?php 
//para trazer as dependecias que meu projeto precisa
require_once("vendor/autoload.php");

//namespaces//classes que eu vou usar
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;

$app = new Slim();

$app->config('debug', true);

//quando chamar o meu site sem nenhuma rota, executa esse bloco
$app->get('/', function() {
    
    $page = new Page();
    $page->setTpl("index");

});

//rota admin
$app->get('/admin', function() {

    $page = new PageAdmin();
    $page->setTpl("index");

});

$app->run();

 ?>