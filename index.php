<?php

session_start();
//para trazer as dependecias que meu projeto precisa
require_once("vendor/autoload.php");

//namespaces//classes que eu vou usar

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;


$app = new Slim();

$app->config('debug', true);

//quando chamar o meu site sem nenhuma rota, executa esse bloco
$app->get('/', function() {
    
    $page = new Page();
    $page->setTpl("index");

});

//rota admin
$app->get('/admin', function() {

    //cria metodo statico
    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("index");

});


$app->get('/admin/login', function(){
    //como esta pagina não precisa de header nem footer
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("login");
});

//rota login
$app->post('/admin/login', function(){

    User::login($_POST["deslogin"], $_POST["despassword"]);

    header("Location: /admin");
});

//rota logout
$app->get('/admin/logout', function (){

   User::logout();

   header("Location: /admin/login");
   exit;
});


$app->run();

 ?>