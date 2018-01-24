<?php

namespace Hcode;
use Rain\Tpl;

class Page{
    //atributos
    private $tpl;
    private $options =  [];
    private $defaults = [
        "header"=>true,
        "footer"=>true,
        "data"=>[]];


    //metodos
    public function __construct($opts = array(), $tpl_dir = "/views/"){
        //array_merge: caso não venha parametro na chamada do metodo usa $defaults
        $this->options = array_merge($this->defaults, $opts);

        $config = array(
            //DOCUMENT_ROOT: pega onde esta o root do projeto
            "base_url"      => null,
            "tpl_dir"       => $_SERVER["DOCUMENT_ROOT"] . $tpl_dir,
            "cache_dir"     => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
            "debug"         => false // set to false to improve the speed
        );

        Tpl::configure( $config );


        $this->tpl = new Tpl;

        //atribuição de variaveis que váo aparecer no template
        if ($this->options['data'])
            $this->setData($this->options['data']);

        //desenhar o tpl na tela// carrega o HEADER
        if ($this->options['header'] === true)
            $this->tpl->draw("header", false);

    }

    //metodo data
    public function setData($data = array())
    {
        foreach ($data as $key => $value){
            $this->tpl->assign($key, $value);
        }
    }

    //metodo para o html
    public function setTpl($name, $data = array(), $returnHtml = false)
    {
        $this->setData($data);

        return $this->tpl->draw($name, $returnHtml);

    }

    //metodo pra carregar o FOOTER
    public function __destruct(){

    if ($this->options['footer'] === true)
        $this->tpl->draw("footer", false);


    }
}

?>