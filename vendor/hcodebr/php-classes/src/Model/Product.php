<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;


class Product extends Model {


    //metodo para listar todos os usuarios
    public static function listAll()
    {

        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
    }

    //metodo para tratar as fotos para o checkphoto
    public static function checkList($list)
    {
        //&: modifica a $row e a $list
        foreach($list as &$row){

            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();
        }
        return $list;
    }

    //METODO PARA SALVAR NOVA CATEGORIA
    public function save()
    {
        $sql = new Sql();
        //chama a procedure
        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
        ));

        $this->setData($results[0]);

    }

    //METODO GET
    public function get($idproduct)    {

        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
            ":idproduct"=>$idproduct
        ]);

        $this->setData($results[0]);
    }

    //METODO DELETE
    public function delete()    {

        $sql = new Sql();
        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ":idproduct"=>$this->getidproduct()
        ]);
    }

    //METODO PARA ADD FOTO
    public function checkPhoto()
    {
        //se existir esse caminho retorna  a imagem
        if(file_exists(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
            "res" . DIRECTORY_SEPARATOR .
            "site" . DIRECTORY_SEPARATOR .
            "img" . DIRECTORY_SEPARATOR .
            "products" . DIRECTORY_SEPARATOR .
            $this->getidproduct() . ".jpg"
        )){

            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
        }else{

            $url = "/res/site/img/product.jpg";
        }

        return $this->setdesphoto($url);
    }

    //METODO PARA VER SE EXISTE FOTO
    public function getValues()
    {
        //verifica se existe foto
        $this->checkPhoto();

        $values = parent::getValues();

        return $values;
    }

    //METODO PARA RECEBER FOTOS
    public function setPhoto($file)
    {
        //VERIFICAR A EXTENSÃO DO ARQUIVO
        //cria um array  a partir do '.'
        $extension = explode('.', $file['name']);
        $extension = end($extension);

        switch($extension){

            case "jpg":
            case "jpeg":
                //coloca a img no GB do PHP
                $image = imagecreatefromjpeg($file["tmp_name"]);
                break;

            case "gif":
                $image = imagecreatefromgif($file["tmp_name"]);
                break;

            case "png":
                $image = imagecreatefrompng($file["tmp_name"]);
                break;
        }

        //converte a img para jpg
        $dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
            "res" . DIRECTORY_SEPARATOR .
            "site" . DIRECTORY_SEPARATOR .
            "img" . DIRECTORY_SEPARATOR .
            "products" . DIRECTORY_SEPARATOR .
            $this->getidproduct() . ".jpg";

        imagejpeg($image, $dist);
        imagedestroy($image);

        //carrega a foto
        $this->checkPhoto();

    }

    //METODO PARA RECEBER URL - DETALHES PRODUTOS
    public function getFromURL($desurl)
    {
        $sql = new Sql();
        //LIMIT 1: retorna uma linha somente
        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl LIMIT 1", [
           ':desurl'=>$desurl
        ]);

        $this->setData($rows[0]);
    }

    //METODO PEGAR AS CATEGORIAS RELACIONADAS
    public function getCategories()
    {
        $sql = new Sql();
        return $sql->select("
            SELECT * FROM tb_categories a INNER JOIN tb_productscategories b 
            ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct", [
           ':idproduct'=>$this->getidproduct()
        ]);
    }


}
?>