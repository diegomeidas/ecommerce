<?php


use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;


//ROTA PARA PRODUTOS
$app->get("/admin/products", function (){

    //verifica se esta logado
    User::verifyLogin();

    $products = Product::listAll();

    $page = new PageAdmin();

    //chama o tpl passando uma var
    $page->setTpl("products", [
        'products'=>$products
    ]);
});

//ROTA PARA CADASTRAR PRODUTOS
$app->get("/admin/products/create", function (){

    //verifica se esta logado
    User::verifyLogin();

    $page = new PageAdmin();

    //chama o tpl passando uma var
    $page->setTpl("products-create");
});

$app->post("/admin/products/create", function (){

    //verifica se esta logado
    User::verifyLogin();

    $product = new Product();
    $product->setData($_POST);
    $product->save();

    header("Location: /admin/products");
    exit;
});

//ROTA PARA EDITAR PRODUTOS
$app->get("/admin/products/:idproduct", function ($idproduct){

    //verifica se esta logado
    User::verifyLogin();

    $product = new Product();

    $product->get((int)$idproduct);

    $page = new PageAdmin();

    //chama o tpl passando uma var
    $page->setTpl("products-update", [
        'product'=>$product->getValues()
    ]);
});

$app->post("/admin/products/:idproduct", function ($idproduct){

    //verifica se esta logado
    User::verifyLogin();

    $product = new Product();

    $product->get((int)$idproduct);

    $product->setData($_POST);
    $product->save();

    //adicionar foto //recebe arquivos
    $product->setPhoto($_FILES["file"]);

    header("Location: /admin/products");
    exit;

});

//ROTA EXCLUIR PRODUTOS
$app->get("/admin/products/:idproduct/delete", function ($idproduct){

    //verifica se esta logado
    User::verifyLogin();

    $product = new Product();

    $product->get((int)$idproduct);

    $product->delete();

    header("Location: /admin/products");
    exit;

});


?>