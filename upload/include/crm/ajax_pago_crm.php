<?php
include("include/crm/conexion_crm.php");
include("include/crm/ost_funciones.php");

//Obtenemos userid a traves del email del usuario que inicio sesion
$emails 	=getUserEmail($userid);

//Bsucamos por tabla cuentas y contactos del CRM, no con la de usuarios.
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
	//Cargamos pago en crm y lo borramos del temporal
	setPagosCrm($data);
	updatePagosTemp($row["id"],$ticketid);	
	//delPagosTemp($row["id"]);
}
createThreadPagos($userid,$ticketid);

?>