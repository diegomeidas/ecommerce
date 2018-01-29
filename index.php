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

    //verifica se esta logado no sistema
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
    exit;
});

//rota logout
$app->get('/admin/logout', function (){

   User::logout();

   header("Location: /admin/login");
   exit;
});

//rota usuarios
$app->get("/admin/users", function (){

    //verifica se esta logado no sistema
    User::verifyLogin();

    $users = User::listAll();

    $page = new PageAdmin();

    $page->setTpl("users", array(
        "users"=>$users
    ));
});


$app->get("/admin/users/create", function (){

    //verifica se esta logado no sistema
    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("users-create");
});

//rota para deletar
$app->get("/admin/users/:iduser/delete", function ($iduser){

    User::verifyLogin();
    $user = new User();
    $user->get((int)$iduser);
    $user->delete();
    header("Location: /admin/users");
    exit;

});


$app->get('/admin/users/:iduser', function($iduser){
    User::verifyLogin();
    $user = new User();
    $user->get((int)$iduser);
    $page = new PageAdmin();
    $page ->setTpl("users-update", array(
        "user"=>$user->getValues()
    ));
});

//rota para salvar
$app->post("/admin/users/create", function (){

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

    $user->setData($_POST);

    $user->save();

    header("Location: /admin/users");
    exit;


});
//rota para salvar
$app->post("/admin/users/:iduser", function ($iduser){

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

    $user->get((int)$iduser);

    $user->setData($_POST);

    $user->update();

    header("Location: /admin/users");
    exit;
});

//rota do forgot (email)
$app->get("/admin/forgot", function (){

    //como esta pagina não precisa de header nem footer
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot");
});

$app->post("/admin/forgot", function(){

    $user = User::getForgot($_POST["email"]);

    header("Location: /admin/forgot/sent");
    exit;
});

//ROTA FORGOT-SENT
$app->get("/admin/forgot/sent", function (){

    //como esta pagina não precisa de header nem footer
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);
    $page->setTpl("forgot-sent");
});

//ROTA FORGOT RESET
$app->get("/admin/forgot/reset", function(){

    $user = User::validForgotDecrypt($_GET["code"]);

    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);

    $page->setTpl("forgot-reset", array(
        "name"=>$user["desperson"],
        "code"=>$_GET["code"]
    ));
});

$app->post("/admin/forgot/reset", function(){

    $forgot = User::validForgotDecrypt($_POST["code"]);

    User::setForgotUsed($forgot["idrecovery"]);

    //carregar los dados do usuário
    $user = new User();
    $user->get((int)$forgot["iduser"]);

    //criptografar a senha
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost"=>12]);

    //metodo para gerar o hash da senha
    $user->setPassword($password);

    //tpl para apresentação visual
    $page = new PageAdmin([
        "header"=>false,
        "footer"=>false
    ]);

    $page->setTpl("forgot-reset-success");


});










$app->run();

 ?>