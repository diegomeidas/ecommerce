<?php


use \Hcode\PageAdmin;
use \Hcode\Model\User;


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


?>