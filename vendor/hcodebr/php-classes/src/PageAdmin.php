<?php

namespace Hcode;

class PageAdmin extends Page{

    public function __construct($opts = array(), $tpl_dir = "/views/admin/")
    {
        //utiliza o metodo da classe Page
        parent::__construct($opts, $tpl_dir);

    }
}

?>