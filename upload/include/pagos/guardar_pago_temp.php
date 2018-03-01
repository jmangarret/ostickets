<?php
session_start();
define("INCLUDE_DIR","../");
require_once("../ost-config.php");
include_once("../funciones/crm.functions.php");
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
//Registrar pago
//Obtenemos variables POST ajax jquery
$fechadepago	=$_REQUEST["fechadepago"];
$paymentmethod 	=$_REQUEST["paymentmethod"];
$referencia		=$_REQUEST["referencia"];
$bancoemisor	=$_REQUEST["bancoemisor"];
$bancoreceptor	=$_REQUEST["bancoreceptor"];
$currency		=$_REQUEST["currency"];
$amount			=$_REQUEST["amount"];
$user_id		=$_REQUEST["user_id"];
$concepto		=$_REQUEST["concepto"];
//Formateamos fecha compatible con mysql
$date_convert 	=date_create($fechadepago);
$fechadepago 	=$date_convert->format('Y-m-d');
//Validamos Referencia Bancaria
$data=array("ref"=>$referencia,"emisor"=>$bancoemisor);
$valRef=validarReferenciaBancaria($data);
if ($valRef){
	echo $valRef;
}else{
	//Registramos pago en tabla principal
	$sql_pagos ="INSERT INTO ost_pagos_temp ";
	$sql_pagos.="VALUES (0,0,'$user_id','$fechadepago','$paymentmethod','$referencia','$bancoemisor','$bancoreceptor','$currency','$amount','$concepto') ";
	$qryPagos	=$mysqli->query($sql_pagos);
	if ($mysqli->affected_rows>0){
		echo "Exito";
	}else{
		echo "<h1>Fallo Insert...</h1>".$sql_pagos.$mysqli->error;
	}	
}
//Cerramos conexion
$mysqli->close();
?>