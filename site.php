<?php

use Hcode\Model\Category;
use \Hcode\Page;
use \Hcode\Model\Product;

//quando chamar o meu site sem nenhuma rota, executa esse bloco
$app->get('/', function() {

    //listar os produtos
    $products = Product::listAll();

    $page = new Page();
    $page->setTpl("index", [
        'products'=>Product::checkList($products)
    ]);

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
        'products'=>Product::checkList($category->getProducts())
    ]);
});


?>
