<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;
//use \Rain\Tpl\Exception;

class User extends Model {

    const SESSION = "User";

    protected $fields = [
        "iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister"
    ];

    public static function login($login, $password):User
    {

        //buscar o rach no BD e validar
        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
           ":LOGIN"=>$login
        ));
        //verificar se encontrou
        if(count($results) === 0){
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
        $data = $results[0];

        //verificar senha
        if(password_verify($password, $data["despassword"]) === true){

            $user = new User();

            $user->setData($data);

            //cria sessão para login
            $_SESSION[User::SESSION] = $user->getValues();

            return $user;


        }else{
            throw new \Exception("Usuário inexistente ou senha inválida.");
        }
    }
    //metodo verifica se login foi feito
    public static function verifyLogin($inadmin = true){

        if(
            !isset($_SESSION[User::SESSION])
            || !$_SESSION[User::SESSION]
            || !(int)$_SESSION[User::SESSION]["iduser"] > 0
            || (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin)
        {

                header("Location: /admin/login");

        }
    }

    //metodo para destrui sessão
    public static function logout(){

        $_SESSION[User::SESSION] = NULL;
    }
}
?>