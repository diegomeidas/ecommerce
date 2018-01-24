<?php

namespace Hcode;

class Model{

    private $values = [];

    //@name: nome do metodo que foi passado
    //@args: valor passado para o atributo
    public function __call($name, $args)
    {
        //var recebe as 3 primeiras letras
        $method = substr($name, 0, 3);
        //nome do campo chamado
        $fieldName = substr($name, 3, strlen($name));

        switch ($method){

            case "get":
                return $this->values[$fieldName];
                break;

            case "set":
                $this->values[$fieldName] = $args[0];
                break;
        }
    }

    //metodo para add atributos
    public function setData($data = array()){

        foreach ($data as $key => $value){
            //concatena a chave para a chamada do metodo
            $this->{"set" . $key}($value);
        }
    }

    public function getValues(){
        return $this->values;
    }

}

?>