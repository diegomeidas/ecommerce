<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Mailer;
use \Hcode\Model;
//use \Rain\Tpl\Exception;

class Category extends Model {


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
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
        ));

        $this->setData($results[0]);
    }

    //METODO GET
    public function get($idcategory)
    {

        $sql = new Sql();

        //chama a procedure
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ":idcategory"=>$idcategory
        ]);

        $this->setData($results[0]);
    }

    //METODO DELETE
    public function delete()
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ":idcategory"=>$this->getidcategory()
        ]);
    }

















}
?>