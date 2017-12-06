<?php
$host="localhost";
$user="vtigercrm";
$pass="AvzHricg4ejxA";
$bd="vtigercrm600";

$host="localhost";
$user="root";
$pass="root";
//$bd="crmtuagencia24";

$mysqli_crm = new mysqli($host, $user, $pass, $bd);
$mysqli_crm = mysqli_connect($host, $user, $pass, $bd);

function getContactoPorEmails($emails){
	global $mysqli_crm;	
	$sql	= "SELECT contactid
		      	FROM vtiger_account as a 
			  	INNER JOIN vtiger_contactdetails as c ON a.accountid=c.accountid			     
		      	WHERE (email1 IN ($emails) OR email IN ($emails))";
	$qry=mysqli_query($mysqli_crm,$sql);
	$row=mysqli_fetch_array($qry);
	
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
	$concepto		=$data["concepto"];
	//Formateamos fecha compatible con mysql
	$date_convert 	=date_create($fechadepago);
	$fechadepago 	=$date_convert->format('Y-m-d');
	//Registramos pago en tabla principal
	$sql_pagos ="INSERT INTO vtiger_registrodepagos(registrodepagosid,fechapago,paymentmethod,referencia,bancoemisor,bancoreceptor,currency,amount,pagostatus,contactoid,ticketid,observacion) ";
	$sql_pagos.="VALUES ('$crmId','$fechadepago','$paymentmethod','$referencia','$bancoemisor','$bancoreceptor','$currency','$amount','Por Verificar',$contactoid,$ticketid,'$concepto') ";
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

function getLocalizadores($contactoid){
	global $mysqli_crm;
	$sql= "SELECT * FROM vtiger_localizadores WHERE contactoid=$contactoid ORDER BY localizadoresid DESC LIMIT 10";
	$qry= $mysqli_crm->query($sql);
	$array=array();
	//return $qry->fetch_all();
	while ($row=$qry->fetch_array()) {
		$locid 	= $row["localizadoresid"];
		$loc 	= $row["localizador"];
		$gds 	= $row["gds"];
		$total 	= number_format($row["totalloc"],2);
		$fecha 	= date_format(date_create(getCrmEntity($locid,"Localizadores","createdtime")),"d-m-Y");
		
		$array[]=array("loc"=>$loc,"fecha"=>$fecha,"gds"=>$gds,"total"=>$total);
	}

	return $array;
}

function getBoletosSatelites($strbus="",$fecha1="",$fecha2="",$emails=""){
	global $mysqli_crm;
	$query	= "SELECT fecha_emision, l.localizador, passenger, boleto1, gds, b.status, paymentmethod, amount, b.monto_base, b.fee, currency,boletosid,a.accountid,contactid  
	      FROM vtiger_account as a 
		     INNER JOIN vtiger_contactdetails as c ON a.accountid=c.accountid
		     INNER JOIN vtiger_localizadores as l ON l.contactoid=c.contactid
			    AND localizadoresid NOT IN (SELECT crmid FROM vtiger_crmentity WHERE deleted=1 AND setype='Localizadores') 
		     INNER JOIN vtiger_boletos as b ON b.localizadorid=l.localizadoresid 
			    AND boletosid NOT IN (SELECT crmid FROM vtiger_crmentity WHERE deleted=1 AND setype='Boletos')
	      WHERE (email1 IN ($emails) OR email IN ($emails))";

	if ($strbus){
		$query.=" AND (l.localizador LIKE '%$strbus%' OR boleto1 LIKE '%$strbus%' OR passenger LIKE '%$strbus%') ";	
	}
	if ($fecha1 && $fecha2){
		$query.=" AND fecha_emision BETWEEN '$fecha1' AND '$fecha2' ";		
	}
	$query.=" ORDER BY fecha_emision DESC";
	$qry= $mysqli_crm->query($query);	
	$rows=$qry->fetch_all(MYSQLI_ASSOC);

	return $rows;

}
function getPagosCrm($contactoid){
	global $mysqli_crm;
	$sql= "SELECT * FROM vtiger_registrodepagos WHERE contactoid=$contactoid AND observacion LIKE '%Pago de reporte%' ORDER BY registrodepagosid DESC";
	$qry= $mysqli_crm->query($sql);
	$array=array();
	//return $qry->fetch_all();
	while ($row=$qry->fetch_array()) {
		$pagoid = $row["registrodepagosid"];
		$banco	= $row["bancoreceptor"];
		$fecha 	= $row["fechapago"];
		$ref 	= $row["referencia"];		
		$obs 	= $row["observacion"];		
		$total 	= number_format($row["amount"],2);
		$fecha 	= date_format(date_create($fecha),"d-m-Y");
		
		$array[]=array("obs"=>$obs,"banco"=>$banco,"fecha"=>$fecha,"ref"=>$ref,"total"=>$total,"id"=>$pagoid);
	}

	return $array;
}

function getPagoById($id){
	global $mysqli_crm;
	$array 	=array();
	$sql 	="SELECT * FROM vtiger_registrodepagos WHERE registrodepagosid=$id";
	$qry 	=$mysqli_crm->query($sql);		
	$row 	=$qry->fetch_array();

	$array["id"] 	= $row["registrodepagosid"];
	$array["banco"]	= $row["bancoreceptor"];
	$array["ref"] 	= $row["referencia"];		
	$array["obs"] 	= $row["observacion"];		
	$array["total"] = number_format($row["amount"],2);
	$array["fecha"] = date_format(date_create($row["fechapago"]),"d-m-Y");
	$array["moneda"]= $row["currency"];
	
	return $array;
}

function getCrmEntity($id,$modulo,$campo){
	global $mysqli_crm;
	$sql 	= "SELECT * FROM vtiger_crmentity WHERE crmid=$id AND setype='$modulo' AND deleted=0";
	$qry 	= $mysqli_crm->query($sql);
	$row 	= $qry->fetch_array();
	$val 	= $row[$campo];
	
	return $val;	
}

function getCuentaCrm($id){
	global $mysqli_crm;
	$sql 	= "SELECT accountname FROM vtiger_account WHERE accountid=$id";
	$qry 	= $mysqli_crm->query($sql);
	$row 	= $qry->fetch_array();
	$val 	= $row[$campo];
	
	return $val;	
}

?>
