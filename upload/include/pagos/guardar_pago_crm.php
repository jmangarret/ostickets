<?php
include("include/funciones/crm.functions.php");
include("include/funciones/commons.php");
include("include/funciones/pagos.functions.php");
//Obtenemos userid a traves del email del usuario que inicio sesion
$emails 	=getUserEmail($userid);
//Buscamos por tabla cuentas y contactos del CRM, no con la de usuarios.
$contactoid =getContactoPorEmails($emails);
//Recorremos pagos cargados
$pagosTemp=getPagosTemp($userid);
foreach ($pagosTemp as $key => $row){	
	$data=array();
	$data["ticketid"]		=$ticketid;
	$data["userid"]			=$userid;
	$data["contactoid"]		=$contactoid;
	$data["fechadepago"]	=$row["fechadepago"];
	$data["paymentmethod"]	=$row["formadepago"];
	$data["referencia"]		=$row["referencia"];
	$data["bancoemisor"]	=$row["emisor"];
	$data["bancoreceptor"]	=$row["receptor"];
	$data["currency"]		=$row["moneda"];
	$data["amount"]			=$row["monto"];
	$data["concepto"]		=$row["concepto"];
	//Cargamos pago en crm y actualizamos ticket en el temporal
	setPagosCrm($data);
	updatePagosTemp($row["id"],$ticketid);		
}
//Creamos hilo del ticket con detalle del pago
createThreadPagos($userid,$ticketid);

?>