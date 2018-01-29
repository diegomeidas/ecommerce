<?php

//classe para envio de email

namespace Hcode;
use Rain\Tpl;

class Mailer{

    const USERNAME = "diegomeidaspinheiro@gmail.com";
    const PASSWORD = "Di@120012";
    const NAME_FROM = "Diego Testes";

    private $mail;

    /**
     * Mailer constructor.
     * @param $toAdress
     * @param $toName
     * @param $subject
     * @param $tplName
     * @param array $data
     */
    public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
    {

        //configurar o tpl
        $config = array(
            "tpl_dir" => $_SERVER["DOCUMENT_ROOT"] . "/views/email/",
            "cache_dir" => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
            "debug" => false
        );
        Tpl::configure($config);
        $tpl = new Tpl;

        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);
        }

        $html = $tpl->draw($tplName, true);


        $this->mail = new \PHPMailer;

        //Tell PHPMailer to use SMTP
        $this->mail->isSMTP();

        $this->mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );


        $this->mail->SMTPDebug = 1;

        //$this->mail->Debugoutput = 'html';

        $this->mail->Host = 'smtp.gmail.com';

        $this->mail->Port = 587;

        $this->mail->SMTPSecure = 'tls';

        $this->mail->SMTPAuth = true;

        $this->mail->Username = Mailer::USERNAME;

        $this->mail->Password = Mailer::PASSWORD;

        //como sera recebida
        $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);


        //responder para:
        //para quem vc quer enviar  esse email (pode add destinatarios)
        $this->mail->addAddress($toAddress, $toName);

        //assunto
        $this->mail->Subject = $subject;

        $this->mail->msgHTML($html);

        //texto para aparecer caso o arquivo html nao funcionar
        $this->mail->AltBody = 'HTML não funcionou';

        //para adicionar anexos ao email
        //$this->mail->addAttachment('images/phpmailer_mini.png');

        if (!$this->mail->send()) {
            echo "Mailer Error: " . $this->mail->ErrorInfo;
        } else {
            echo "Message sent!";

        }
    }

    public function send()
    {
        return $this->mail->send();
    }
}

?>