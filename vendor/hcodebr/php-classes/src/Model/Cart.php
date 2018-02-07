<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model\User;
use \Hcode\Model;
//use \Rain\Tpl\Exception;

class Cart extends Model
{
    //sessão para manter as informações da compra
    const SESSION = "Cart";

    //METODO PARA PEGAR A SESSÃO DO USUARIO
    public static function getFromSession()
    {
        $cart = new Cart();

        //verifica se a sessão ja esta aberta e se ja existe um ID
        if(isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0){

            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);
        }else{

            //carrega o carrinho a partir do dessessionid
            $cart->getFromSessionID();

            //se não conseguiu recuperar o carrinho então cria um novo
            if(!(int)$cart->getidcart() > 0){

                $data = ['dessessionid'=>session_id()];

                if(User::checkLogin(false) === true){

                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser();
                }

                $cart->setData($data);
                $cart->save();
                $cart->setToSession();
            }
        }

        return $cart;
    }

    //METODO COLOCAR O CARRINHO NA SESSÃO
    public function setToSession()
    {
        $_SESSION[Cart::SESSION] = $this->getValues();
    }

    //METODO GET
    public function get($idcart)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_carts WHERE idcart = :idcart", [
           ':idcart'=>$idcart
        ]);

        //verifica se existe uma sessão aberta
        if(count($results) > 0){
            $this->setData($results[0]);
        }
    }

    //METODO PARA PEGAR A SESSÃO DO ID
    public function getFromSessionID()
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_carts WHERE dessessioid = :dessessionid", [
            ':dessessionid'=>session_id()
        ]);

        //verifica se existe uma sessão do id
        if(count($results) > 0){
            $this->setData($results[0]);
        }
    }

    public function save()
    {
        $sql = new Sql();
        $results = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [
            ':idcart'=>$this->getidcart(),
            ':dessessionid'=>$this->getdessessionid(),
            ':iduser'=>$this->getiduser(),
            ':deszipcode'=>$this->getdeszipcode(),
            ':vlfreight'=>$this->getvlfreight(),
            ':nrdays'=>$this->getnrdays()
        ]);
        $this->setData($results[0]);
    }





















}
?>