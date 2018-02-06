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

    //confere pagina que esta sendo passada
    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

    //instancia nova categoria
    $category = new Category();

    //recebe o que vem do get convertendo para int
    $category->get((int)$idcategory);

    $pagination = $category->getProductsPage($page);

    $pages = [];
    for($i = 1; $i <= $pagination['pages']; $i++ ){
        array_push($pages, [
           'link'=>'/categories/' . $category->getidcategory() . '?page=' . $i,
           'page'=>$i
        ]);
    }

    //instancia nova pagina
    $page = new Page();

    //tpl para nova categoria
    $page->setTpl("category", [
        'category'=>$category->getValues(),
        'products'=>$pagination["data"],
        'pages'=>$pages
    ]);
});

//ROTA PARA PAGINAÇÃO


?>
