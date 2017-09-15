<?php
$host="localhost";
$user="vtigercrm";
$pass="AvzHricg4ejxA";
$bd="vtigercrm600";

$mysqli_crm = new mysqli($host, $user, $pass, $bd);

function getContactoPorEmails($emails){
	global $mysqli_crm;	
	$sql	= "SELECT contactid
		      	FROM vtiger_account as a 
			  	INNER JOIN vtiger_contactdetails as c ON a.accountid=c.accountid			     
		      	WHERE (email1 IN ($emails) OR email IN ($emails))";
	$qry=$mysqli_crm->query($sql);
	$row=$qry->fetch_array();

	return $row["contactid"];

}

function setPagosCrm($data){
	global $mysqli_crm;
	//Obtenemos proximo correlativo del CRM @idcrm
	$qryCrm		=$mysqli_crm->prepare("CALL getCrmId()");
	$qryExec 	=$qryCrm->execute();
	//$resultado = $qryCrm->get_result();
	$qryCrm		=$mysqli_crm->query("SELECT @idcrm");
	$resultCrm	=$qryCrm->fetch_array();
	$crmId 		=$resultCrm[0];
	//Obtenemos variables POST ajax jquery
	$ticketid		=$data["ticketid"];
	$contactoid		=$data["contactoid"];
	$fechadepago	=$data["fechadepago"];
	$paymentmethod 	=$data["paymentmethod"];
	$referencia		=$data["referencia"];
	$bancoemisor	=$data["bancoemisor"];
	$bancoreceptor	=$data["bancoreceptor"];
	$currency		=$data["currency"];
	$amount			=$data["amount"];
	//Formateamos fecha compatible con mysql
	$date_convert 	=date_create($fechadepago);
	$fechadepago 	=$date_convert->format('Y-m-d');
	//Registramos pago en tabla principal
	$sql_pagos ="INSERT INTO vtiger_registrodepagos(registrodepagosid,fechapago,paymentmethod,referencia,bancoemisor,bancoreceptor,currency,amount,pagostatus,contactoid,ticketid) ";
	$sql_pagos.="VALUES ('$crmId','$fechadepago','$paymentmethod','$referencia','$bancoemisor','$bancoreceptor','$currency','$amount','Por Verificar',$contactoid,$ticketid) ";
	$qryCrm		=$mysqli_crm->query($sql_pagos);

	if ($mysqli_crm->affected_rows>0){
		//Registramos pago en tabla secundaria
		$sql_pagos2	="INSERT INTO vtiger_registrodepagoscf VALUES($crmId) ";
		$qryCrm		=$mysqli_crm->query($sql_pagos2);
		
		//Registramos tabla relacionada vtiger_crmentity
		$crmUser=1; //Administrador
		$modulo	="RegistroDePagos";
		$label	=$referencia." ".$fechadepago." ".$bancoemisor;
		$creado	=date("Y-m-d H:i:s");
		$idcrm	=$crmId;
		$iduser	=$crmUser;	
		//$qryCrm	=$mysqli_crm->prepare("CALL setCrmEntity('$modulo','$label','$creado','$idcrm','$iduser')");
		$qryCrm	=$mysqli_crm->prepare("CALL setCrmEntity(?,?,?,?,?)");
		$qryCrm->bind_param("sssii",$modulo,$label,$creado,$idcrm,$iduser);
		$qryCrm->execute();	

		echo "Exito";
	}
}

function validarReferenciaBancaria($data){
	global $mysqli_crm;
	$ref=$data["ref"];
	$emi=$data["emisor"];
	$sql ="SELECT registrodepagosid, registrodeventasid, bancoemisor, paymentmethod, fechapago ";
	$sql.="FROM vtiger_registrodepagos ";
	$sql.="WHERE referencia LIKE '%$ref' AND bancoemisor='$emi' ";

	$qry=$mysqli_crm->query($sql);	
	$num=$qry->num_rows;
	if ($num>0){
		$row=$qry->fetch_array();
		$pago 	=$row[0];
		$banco 	=$row[2];
		$metodo =$row[3];
		$fpago=new DateTime($row[4]);
		echo "El Num. de Referencia ya existe! Coincide con un(a) $metodo del Pago: $pago, por el banco $banco del dia ".date_format($fpago,"d/m/Y");
	}	
}

?>