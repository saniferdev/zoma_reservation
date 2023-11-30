<?php
include('config.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function envoiMail($adresse,$sujet,$objet){
    $mail = new PHPMailer(true);
    $mail->setLanguage('fr', '/PHPMailer/language/');
    $mail->IsSMTP(); 
    $mail->SMTPOptions = array(
      'ssl' => array(
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
      )
    );
    $mail->SMTPDebug  = 4;  
    $mail->SMTPAuth   = true;  
    $mail->Host       = 'mail.fripesenligne.fr';                     
    $mail->Username   = 'kibocom@fripesenligne.fr';                    
    $mail->Password   = 'dx2sy3gl%`3@';                              
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
    $mail->Port       = 465;   
    $mail->SetFrom('informatique@fripesenligne.fr', 'ZOMA');
    
    $mail->Subject    = $sujet;
    $mail->CharSet    = 'UTF-8';
    $mail->Body       = $objet;
    $mail->AddAddress($adresse);
    $mail->AddCC('rojonirina.scecomm@sanifer.mg');
    $mail->AddCC('ass2.scecomm@sanifer.mg');
    $mail->AddCC('ass1.scecomm@sanifer.mg');
    $mail->AddCC('ass4.scecomm@sanifer.mg');
    $mail->AddCC('miora.technico@sanifer.mg');
    //$mail->AddCC('informatique@sanifer.mg');
    $mail->AddCC('tsiry.web@talys.mg');
    $mail->AddCC('romy.web@talys.mg');
    $mail->AddBCC('winny.devinfo@talys.mg');
    $mail->addReplyTo('ass2.scecomm@sanifer.mg', 'Joujou');
    $mail->isHTML(true);
    if(!$mail->Send()) {
      $return         = "Erreur d'envoi de mail: ".$mail->ErrorInfo;
    } else {
      $return         = 'Mail envoyé avec succès!';
    }
    return $return;
  }

function envoiMailKibo($adresse,$sujet,$objet){
    $mail = new PHPMailer(true);
    $mail->setLanguage('fr', '/PHPMailer/language/');
    $mail->IsSMTP(); 
    $mail->SMTPOptions = array(
      'ssl' => array(
      'verify_peer' => false,
      'verify_peer_name' => false,
      'allow_self_signed' => true
      )
    );
    $mail->SMTPDebug  = 4;  
    $mail->SMTPAuth   = true;  
    $mail->SMTPSecure = 'tls'; 
    $mail->Host       = gethostbyname('smtp.gmail.com');
    $mail->Port       = 587;
    $mail->Username   = 'sanifer.informatique@gmail.com';
    $mail->Password   = 'pjdaddtyzcajzaef';
    $mail->SetFrom('sanifer.informatique@gmail.com', 'KIBO');
    $mail->Subject    = $sujet;
    $mail->CharSet    = 'UTF-8';
    $mail->Body       = $objet;
    $mail->AddAddress('Commande.Kibo@kibo.mg');
    $mail->AddCC('informatique@sanifer.mg');
    $mail->AddCC('fabien.tozzo@talys.mg');
    $mail->AddCC('romy.web@talys.mg');
    $mail->AddCC('Assist.livraison@kibo.mg');
    $mail->AddCC('livraison.kibo@kibo.mg');
    $mail->AddBCC('winny.info@talys.mg');
    $mail->addReplyTo('informatique@sanifer.mg');
    $mail->isHTML(true);
    if(!$mail->Send()) {
      $return         = "Erreur d'envoi de mail: ".$mail->ErrorInfo;
    } else {
      $return         = 'Mail envoyé avec succès!';
    }
    return $return;
  }

require_once("classes/api.php");

$api 		        = new API();
$api->link 	    = $link;
$api->url 	    = $url;
$api->key 	    = $key;
$api->url_soap  = $url_soap;
$api->login     = $login;
$api->password  = $password;
$api->codeLang  = $codeLang;
$api->poolAlias = $poolAlias;

$api->getOrder();


?>