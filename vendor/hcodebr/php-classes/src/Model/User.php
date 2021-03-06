<?php

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Mailer;
use \Hcode\Model;
//use \Rain\Tpl\Exception;

class User extends Model {

    const SESSION = "User";
    const SECRET = "HcodePhp_Secret";

    protected $fields = [
        "iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister"
    ];

    //METODO PARA PEGAR A SESSÃO DO USUARIO
    public static function getFromSession()
    {
        $user = new User();

        //verifica se a sessão esta definida e se é maior que 0
        if(isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0){

            //cria um novo usuario nessa sessão
            $user->setData($_SESSION[User::SESSION]);
        }

        return $user;
    }

    //METODO PARA CHECAR SE O USUARIO ESTA LOGADO
    public static function checkLogin($inadmin = true)
    {
        if(
            !isset($_SESSION[User::SESSION])
            || !$_SESSION[User::SESSION]
            || !(int)$_SESSION[User::SESSION]["iduser"] > 0
        ){
            //não esta logado
            return false;
        }else{
            //esta logado
            //caso esteja tentando acessar o admin, verifica se é admin
            if($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true){
                return true;
            }else if($inadmin === false){
                return true;
            }else{
                return false;
            }
        }
    }

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

        if(User::checkLogin($inadmin)){

            header("Location: /admin/login");
            exit;
        }
    }

    //metodo para destrui sessão
    public static function logout(){

        $_SESSION[User::SESSION] = NULL;
    }

    //metodo para listar todos os usuarios
    public static function listAll(){

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

    }

    public function save(){

        $sql = new Sql();

        //chama a procedure
        $results = $sql->select("CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()

        ));

        $this->setData($results[0]);

    }

    public function get($iduser){

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array(
        ":iduser"=>$iduser
        ));

        $this->setData($results[0]);
    }

    public function update(){

        $sql = new Sql();

        //chama a procedure
        $results = $sql->select("CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser"=>$this->getiduser(),
            ":desperson"=>$this->getdesperson(),
            ":deslogin"=>$this->getdeslogin(),
            ":despassword"=>$this->getdespassword(),
            ":desemail"=>$this->getdesemail(),
            ":nrphone"=>$this->getnrphone(),
            ":inadmin"=>$this->getinadmin()

        ));

        $this->setData($results[0]);
    }

    public function delete(){
        $sql = new Sql();

        //chama a procedure
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser"=>$this->getiduser()
        ));
    }


    /*
    public static function getForgot($email){

        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_persons a INNER JOIN tb_users b USING(idperson) WHERE a.desemail = :email;", array(
            ":email"=>$email
        ));

        if(count($results) === 0){
            throw new \Exception("Não foi possível recuperar a senha.");
        }else{

            $data = $results[0];

            //cria registro na recuperação de senhas
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip", array(
                ":iduser"=>$data["iduser"],
                ":desip"=>$_SERVER["REMOTE_ADDR"]
            ));

            if(count($results2) === 0){
                throw new \Exception("Não foi possível recuperar a senha.");
            }else{

                $dataRecovery = $results2[0];

                //encriptografar
                base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery["idrecovery"],MCRYPT_MODE_ECB   ));

                $link = "http://localDiego.com/admin/forgot/reset?code=$code";
                //email //nome da pessoa //assunto //tpl //
                $mailer = new Mailer($data["desemail"], $data["desperson"], "Redefinir senha Hcode", "forgot", array(
                    "name"  => $data["desperson"],
                    "link"  => $link
                ));

                $mailer->send();

                return $data;
            }
        }

    }
    */









    public static function getForgot($email, $inadmin = true)
    {
        $sql = new Sql();
        $results = $sql->select("
         SELECT * FROM tb_persons a
         INNER JOIN tb_users b USING(idperson)
         WHERE a.desemail = :email;
     ", array(
            ":email"=>$email
        ));
        if (count($results) === 0)
        {
            throw new \Exception("Não foi possível recuperar a senha.");
        }
        else
        {
            $data = $results[0];
            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser"=>$data['iduser'],
                ":desip"=>$_SERVER['REMOTE_ADDR']
            ));
            if (count($results2) === 0)
            {
                throw new \Exception("Não foi possível recuperar a senha.");
            }
            else
            {
                $dataRecovery = $results2[0];
                $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
                $code = openssl_encrypt($dataRecovery['idrecovery'], 'aes-256-cbc', User::SECRET, 0, $iv);
                $result = base64_encode($iv.$code);
                if ($inadmin === true) {
                    $link = "http://localDiego.com/admin/forgot/reset?code=$result";
                } else {
                    $link = "http://www.localDiego.com/forgot/reset?code=$result";
                }
                $mailer = new Mailer($data['desemail'], $data['desperson'], "Redefinir senha da Hcode Store", "forgot", array(
                    "name"=>$data['desperson'],
                    "link"=>$link
                ));
                $mailer->send();
                return $link;
            }
        }
    }



    public static function validForgotDecrypt($result)
    {
        $result = base64_decode($result);
        $code = mb_substr($result, openssl_cipher_iv_length('aes-256-cbc'), null, '8bit');
        $iv = mb_substr($result, 0, openssl_cipher_iv_length('aes-256-cbc'), '8bit');;
        $idrecovery = openssl_decrypt($code, 'aes-256-cbc', User::SECRET, 0, $iv);
        $sql = new Sql();
        $results = $sql->select("
         SELECT * FROM tb_userspasswordsrecoveries a
         INNER JOIN tb_users b USING(iduser)
         INNER JOIN tb_persons c USING(idperson)
         WHERE a.idrecovery = :idrecovery
         AND a.dtrecovery IS NULL
         AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();", array(
            ":idrecovery"=>$idrecovery
        ));
        if (count($results) === 0)
        {
            throw new \Exception("Não foi possível recuperar a senha.");
        }
        else
        {
            return $results[0];
        }
    }

    public static function setForgotUsed($idrecovery)
    {

        $sql = new Sql();
        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array(
            ":idrecovery"=>$idrecovery
        ));
    }

    //FUNÇÃO ALTERAR SENHA USUARIO
    public function setPassword($password)
    {
        $sql = new Sql();
        $sql->query("UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array(
            ":password"=>$password,
            ":iduser"=>$this->getiduser()
        ));

    }



}
?>