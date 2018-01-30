<?php

use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

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


?>