<?php

session_start();
//para trazer as dependecias que meu projeto precisa
require_once("vendor/autoload.php");

//namespaces//classes que eu vou usar

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;


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

//ROTA PARA CATEGORIAS
$app->get("/admin/categories", function (){

    //verifica se esta logado
    User::verifyLogin();

    //instancia classe Category e lista todos os objetos
    $categories = Category::listAll();

    $page = new PageAdmin();
    //chama o tpl passando uma var
    $page->setTpl("categories", [
        'categories'=>$categories
    ]);


});

//ROTA PARA CRIAR CATEGORIAS
$app->get("/admin/categories/create", function (){

    //verifica se esta logado
    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("categories-create");
});

//ROTA PARA CRIAR CATEGORIAS
$app->post("/admin/categories/create", function (){

    //verifica se esta logado
    User::verifyLogin();

    //instancia um novo objeto
    $category = new Category();

    //recebe o que vem do POST
    $category->setData($_POST);

    //salva
    $category->save();

    //redireciona
    header("Location: /admin/categories");
    exit;
});

//ROTA PARA EXCLUIR CATEGORIAS
$app->get("/admin/categories/:idcategory/delete", function($idcategory){

    //verifica se esta logado
    User::verifyLogin();

    //instancia nova categoria
    $category = new Category();

    //recebe o que vem do get
    $category->get((int)$idcategory);

    //metodo delete
    $category->delete();

    //redireciona
    header("Location: /admin/categories");
    exit;
});

//ROTA PARA EDITAR CATEGORIAS
$app->get("/admin/categories/:idcategory", function($idcategory){

    //verifica se esta logado
    User::verifyLogin();

    //instancia nova categoria
    $category = new Category();

    //recebe o que vem do get
    $category->get((int)$idcategory);

    $page = new PageAdmin();
    $page->setTpl("categories-update", [
        'category'=>$category->getValues()
    ]);

});

$app->post("/admin/categories/:idcategory", function($idcategory){

    //instancia nova categoria
    $category = new Category();

    //recebe o que vem do get convertendo para int
    $category->get((int)$idcategory);

    //altera o que vier do post
    $category->setData($_POST);

    //salva
    $category->save();

    header("Location: /admin/categories");
    exit;
});

//ROTA PARA CATEGORIAS SITE
$app->get("/categories/:idcategory", function ($idcategory){

    //instancia nova categoria
    $category = new Category();

    //recebe o que vem do get convertendo para int
    $category->get((int)$idcategory);

    //instancia nova pagina
    $page = new Page();

    //tpl para nova categoria
    $page->setTpl("category", [
        'category'=>$category->getValues(),
        'products'=>[]
    ]);
});








$app->run();

 ?>