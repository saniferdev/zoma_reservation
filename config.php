<?php

ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1); 
error_reporting(E_ALL);

$key    			= "48WX7UMQE1V58SB1TTD75VX1UBX1RVCC";
$url    			= "https://www.zoma.mg";

$url_soap  			= "http://192.168.130.55:8124/soap-wsdl/syracuse/collaboration/syracuse/CAdxWebServiceXmlCC?wsdl";

$login 				= 'admin';
$password 			= 'mW33Yjf8q88Bex';
$codeLang 			= 'FRA';
//$poolAlias 			= 'POOLBAS'; //Pool de developpement
$poolAlias 			= 'PROD'; //Pool de production

$sqlServerHost           = '192.168.130.71';
$sqlServerDatabase 	= 'COMMANDE_ZOMA';
$sqlServerUser 		= 'commande_x3';
$sqlServerPassword 	= 'WesoKhu640Rfz0Yi';

$connectionInfo 	= array("Database" => $sqlServerDatabase, "UID" => $sqlServerUser, "PWD" => $sqlServerPassword, "CharacterSet" => "UTF-8");
$link 				= sqlsrv_connect($sqlServerHost, $connectionInfo);
if (!$link) {
     die( print_r( sqlsrv_errors(), true));
}

?>