<?php

use \Hcode\Page;

//quando chamar o meu site sem nenhuma rota, executa esse bloco
$app->get('/', function() {

    $page = new Page();
    $page->setTpl("index");

});


?>
