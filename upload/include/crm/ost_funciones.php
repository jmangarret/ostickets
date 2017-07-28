<?php
$dir_actual=getcwd(); //upload/include/crm
$include_dir=dirname($dir_actual); //devuelve directorio padre o anterior
define("INCLUDE_DIR",$include_dir."/");
require_once INCLUDE_DIR.'ost-config.php';

global $mysqli;
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

function getUserEmail($idUser){
	return $idUser;	
}

?>