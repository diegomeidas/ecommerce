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

    //constante para erros
    const SESSION_ERROR = "CartError";



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




    //METODO ADICIONAR PRODUTO NO CARRINHO
    public function addProduct(Product $product)
    {
        $sql = new SQL();
        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:idcart, :idproduct)", [
           ':idcart'=>$this->getidcart(),
           ':idproduct'=>$product->getidproduct()
        ]);

        //alterar valor do frete qdo aumentar os produtos do carrinho
        $this->getCalculateTotal();

    }



    //METODO PARA REMOVER PRODUTOS DO CARRINHO
    public function removeProduct(Product $product, $all = false)
    {
        $sql = new Sql();

        if($all){
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [
                ':idcart'=>$this->getidcart(),
                ':idproduct'=>$product->getidproduct()
            ]);
        }else{
            $sql->query("UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [
                ':idcart'=>$this->getidcart(),
                ':idproduct'=>$product->getidproduct()
            ]);
        }

        //alterar valor do frete qdo diminuir os produtos do carrinho
        $this->getCalculateTotal();

    }



    //METODO PARA LISTAR TODOS OS PRODUTOS DO CARRINHO
    public function getProducts()
    {
        $sql = new Sql();
        $rows = $sql->select("
            SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, 
                  COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal 
            FROM tb_cartsproducts a 
            INNER JOIN tb_products b ON a.idproduct = b.idproduct 
            WHERE a.idcart = :idcart AND a.dtremoved IS NULL 
            GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
            ORDER BY b.desproduct", [
           ':idcart'=>$this->getidcart()
        ]);

        return Product::checkList($rows);
    }



    //METODO PARA CALCULAR O FRETE REFERENTE PRODUTOS DO CARRINHO
    public function getProductsTotals()
    {
        $sql = new Sql();
        $results = $sql->select("
            SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, 
                SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
            FROM tb_products a 
            INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct 
            WHERE b.idcart = :idcart AND dtremoved IS NULL;", [
                ':idcart'=>$this->getidcart()
        ]);

        if(count($results) > 0){
            return $results[0];
        }else{
            return [];
        }
    }



    //METODO PARA RECEBER O CEP
    public function setFreight($nrzipcode)
    {
        $nrzipcode = str_replace('-', '', $nrzipcode);

        $totals = $this->getProductsTotals();
        //verifica se ha produtos no carrinho
        if($totals['nrqtd'] > 0){

            //ajusta a altura do produto (não pode ser inferior a 2cm)
            if($totals['vlheight'] < 2)
                $totals['vlheight'] = 2;

            //ajusta comprimento (deve ser >= 16cm)
            if($totals['vllength'] < 16)
                $totals['vllength'] = 16;

            //ajusta largura (deve ser >= 11cm)
            if($totals['vlwidth'] < 11)
                $totals['vlwidth'] = 11;

            //função para informações da empresa
            $qs = http_build_query([
               'nCdEmpresa'=>'',
               'sDsSenha'=>'',
               'nCdServico'=>'40010', //existem outras opções
               'sCepOrigem'=>'09853120',
               'sCepDestino'=>$nrzipcode,
               'nVlPeso'=>$totals['vlweight'],
               'nCdFormato'=>'1',
               'nVlComprimento'=>$totals['vllength'],
               'nVlAltura'=>$totals['vlheight'],
               'nVlLargura'=>$totals['vlwidth'],
               'nVlDiametro'=>'0',
               'sCdMaoPropria'=>'S',
               'nVlValorDeclarado'=>$totals['vlprice'],
               'sCdAvisoRecebimento'=>'S'
            ]);


            //ler xml
            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?" . $qs);

            $result = $xml->Servicos->cServico;

            //verifica se houve erro e apresenta ao usuario
            if($result->MsgErro != ''){

                Cart::setMsgError($result->MsgErro);

            }else{

                Cart::clearMsgError();
            }

            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);

            $this->save();

            return $result;

        }else{

        }
    }


    //TROCAR ',' POR '.' DOS VALORES
    public function formatValueToDecimal($value):float
    {
        $value = str_replace('.', '', $value);
        return str_replace(',', '.', $value);
    }



    //TRATAR ERROS
    public static function setMsgError($msg)
    {
        $_SESSION[Cart::SESSION_ERROR] = $msg;
    }

    public static function getMsgError()
    {
        $msg = (isset($_SESSION[Cart::SESSION_ERROR])) ? $_SESSION[Cart::SESSION_ERROR]: "";

        Cart::clearMsgError();

        return $msg;
    }
    //LIMPA A SESSÃO
    public static function clearMsgError()
    {
        $_SESSION[Cart::SESSION_ERROR] = NULL;
    }



    //METODO PARA ATUALIZAR VALOR DO FRETE
    public function updateFreight()
    {
        if($this->getdeszipcode() != ''){
            $this->setFreight($this->getdeszipcode());
        }
    }



    //METODO PARA CALCULAR O TOTAL DO CARRINHO
    public function getValues()
    {
        $this->getCalculateTotal();
        return parent::getValues();
    }

    public function getCalculateTotal()
    {
        //atualiza o frete
        $this->updateFreight();

        $totals = $this->getProductsTotals();
        $this->setvlsubtotal($totals['vlprice']);
        $this->setvltotal($totals['vlprice'] + $this->getvlfreight());
    }



















}
?>