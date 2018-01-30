<?php

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


?>
