<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Mailer;
use \Hcode\Model;
//use \Rain\Tpl\Exception;

class Category extends Model
{


    //metodo para listar todos os usuarios
    public static function listAll()
    {

        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }

    //METODO PARA SALVAR NOVA CATEGORIA
    public function save()
    {

        $sql = new Sql();

        //chama a procedure
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory" => $this->getidcategory(),
            ":descategory" => $this->getdescategory()
        ));

        $this->setData($results[0]);

        Category::updateFile();
    }

    //METODO GET
    public function get($idcategory)
    {

        $sql = new Sql();

        //chama a procedure
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ":idcategory" => $idcategory
        ]);

        $this->setData($results[0]);
    }

    //METODO DELETE
    public function delete()
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ":idcategory" => $this->getidcategory()
        ]);

        Category::updateFile();
    }

    //METODO PARA EDITAR CATEGORIAS SITE
    public static function updateFile()
    {
        //recebe todas as categorias do BD
        $categories = Category::listAll();

        //cria <li> dinamicamente em categories-menu.html
        $html = [];

        foreach ($categories as $row) {
            array_push($html, '<li><a href="/categories/' . $row['idcategory'] . '">' . $row['descategory'] . '</a></li>');
        }

        //SALVAR O ARQUIVO
        //filename: busca a pasta raiz do arquivo
        //caminho do arquivo: /views/categoris-menu.html
        //implode: transforma o array ($html) em string
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));

    }

    public function getProducts($related = true)
    {
        $sql = new Sql();

        if ($related === true) {
            return $sql->select("
                SELECT * FROM tb_products a WHERE idproduct IN(
                SELECT a.idproduct FROM tb_products a 
                INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                WHERE b.idcategory = :idcategory
                );
                ", [
                ':idcategory' => $this->getidcategory()
            ]);
        } else {
            return $sql->select("
                SELECT * FROM tb_products a WHERE idproduct NOT IN(
                SELECT a.idproduct FROM tb_products a 
                INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                WHERE b.idcategory = :idcategory
                );
                ", [
                ':idcategory' => $this->getidcategory()
            ]);
        }
    }

    //METODO PARA PAGINAÇÃO
    public function getProductsPage($page = 1, $itensPerPage = 2)
    {
        //faz o calculo da pagina
        $start = ($page - 1) * $itensPerPage;

        $sql = new Sql();

        $results = $sql->select(
            "SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_products a 
            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
            INNER JOIN tb_categories c ON c.idcategory = b.idcategory
            WHERE c.idcategory = :idcategory
            LIMIT $start, $itensPerPage;
            ", [
              ':idcategory'=>$this->getidcategory()
        ]);

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");

        return [
            'data'=>Product::checkList($results),    //retorna uma lista com os resultados da pesquisa
            'total'=>$resultTotal[0]["nrtotal"],//retorna a qtde de elementos da pesquisa
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itensPerPage)//retorna qtas paginas gerou //ceil(): converte arredondando p/ cima
        ];
    }

    //METODO PARA ADICIONAR PRODUTOS RELACIONADOS
    public function addProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);

    }

    //METODO PARA REMOVER PRODUTOS RELACIONADOS
    public function removeProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);

    }

















}
?>