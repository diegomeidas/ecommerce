<?php

use Hcode\Model\Category;
use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Cart;

//quando chamar o meu site sem nenhuma rota, executa esse bloco
$app->get('/', function() {

    //listar os produtos
    $products = Product::listAll();

    $page = new Page();
    $page->setTpl("index", [
        'products'=>Product::checkList($products)
    ]);

});

//ROTA PARA CATEGORIAS SITE / E PAGINAÇÃO
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

//ROTA DETALHES PRODUTOS
$app->get("/products/:desurl", function($desurl){

    $product = new Product();
    $product->getFromURL($desurl);

    $page = new Page();
    $page->setTpl("product-detail",[
        'product'=>$product->getValues(),
        'categories'=>$product->getCategories()
    ]);
});

//ROTA CARRINHO DE COMPRAR
$app->get("/cart", function (){

    $cart = Cart::getFromSession();
    $page = new Page();
    $page->setTpl("cart");
});


?>
