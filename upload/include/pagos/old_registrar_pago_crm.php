<?php
session_start();
include("conexion_crm.php");
include("ost_funciones.php");
$mysqli = new mysqli($host, $user, $pass, $bd);
//Obtenemos proximo correlativo del CRM
$qryCrm		=$mysqli->query("CALL getCrmId()");
$qryCrm		=$mysqli->query("SELECT @idcrm");
$resultCrm	=$qryCrm->fetch_row();
$crmId 		=$resultCrm[0];
//Obtenemos variables POST ajax jquery
$nrodeticket	=$_REQUEST["nrodeticket"];
$fechadepago	=$_REQUEST["fechadepago"];
$paymentmethod 	=$_REQUEST["paymentmethod"];
$referencia		=$_REQUEST["referencia"];
$bancoemisor	=$_REQUEST["bancoemisor"];
$bancoreceptor	=$_REQUEST["bancoreceptor"];
$currency		=$_REQUEST["currency"];
$amount			=$_REQUEST["amount"];
//Formateamos fecha compatible con mysql
$date_convert 	=date_create($fechadepago);
$fechadepago 	=$date_convert->format('Y-m-d');
//Registramos pago en tabla principal
$sql_pagos ="INSERT INTO vtiger_registrodepagos(registrodepagosid,fechapago,paymentmethod,referencia,bancoemisor,bancoreceptor,currency,amount,pagostatus) ";
$sql_pagos.="VALUES ('$crmId','$fechadepago','$paymentmethod','$referencia','$bancoemisor','$bancoreceptor','$currency','$amount','Por Verificar') ";
$qryCrm		=$mysqli->query($sql_pagos);

if ($mysqli->affected_rows>0){
	//Registramos pago en tabla secundaria
	$sql_pagos2	="INSERT INTO vtiger_registrodepagoscf VALUES($crmId) ";
	$qryCrm		=$mysqli->query($sql_pagos2);

	//Obtener userid a traves del email del usuario que inicio sesion
	//este se compara solo con tabla cuentas y contactos del CRM, no con la de usuarios.
	$ost_user=$_SESSION['_auth']['user']['id'];
	$crmUser=1; //Administrador
	//Registramos tabla relacionada vtiger_crmentity desde SP
	$modulo	="RegistroDePagos";
	$label	=$referencia." ".$fechadepago." ".$bancoemisor;
	$creado	=date("Y-m-d H:i:s");
	$idcrm	=$crmId;
	$iduser	=$crmUser;	
	$qryCrm	=$mysqli->query("CALL setCrmEntity('$modulo','$label','$creado','$idcrm','$iduser')");
	echo "Exito";
}else{
	echo "<h1>Fallo insercion en CRM...</h1>".$sql_pagos.$mysqli->error;
}
//Cerramos conexion
$mysqli->close();
?>