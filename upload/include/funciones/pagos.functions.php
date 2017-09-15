<?php
global $mysqli;
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

function getPagosTemp($idUser,$ticketid=0){
	global $mysqli;
	$sqlPagosTemp="SELECT * FROM ost_pagos_temp WHERE ticket_id=$ticketid AND user_id=$idUser";
	$qryPagosTemp=$mysqli->query($sqlPagosTemp);
	$rows = array();	
	while ($row = $qryPagosTemp->fetch_array()) {
  		$rows[] = $row;
	}	

	return $rows;	
}

function delPagosTemp($id){
	global $mysqli;
	$sql="DELETE FROM ost_pagos_temp WHERE id=$id";
	$qry=$mysqli->query($sql);

	return true;
}

function updatePagosTemp($id,$ticketid){
	global $mysqli;
	$sql="UPDATE ost_pagos_temp SET ticket_id=$ticketid WHERE id=$id";
	$qry=$mysqli->query($sql);

	return true;
}

function createThreadPagos($userid,$ticketid){
	global $mysqli;
	$tabla.= "<table class=table>";
	$tabla.= "<thead>";
	$tabla.= "<tr>";
	$tabla.= "<td><b>Fecha del Pago</b></td>";
	$tabla.= "<td><b>Forma de Pago</b></td>";
	$tabla.= "<td><b>Referencia</b></td>";
	$tabla.= "<td><b>Origen del Pago</b></td>";
	$tabla.= "<td><b>Destino del Pago</b></td>";
	$tabla.= "<td><b>Moneda</b></td>";
	$tabla.= "<td><b>Monto</b></td>";
	$tabla.= "</tr>";
	$tabla.= "</thead>";
	$tabla.= "<tbody>";	
	$ost_pagos=getPagosTemp($userid,$ticketid);	
	foreach ($ost_pagos as $key => $result) {
		$tabla.= "<tr>";
		$tabla.= "<td>".$result['fechadepago']."</td>";
		$tabla.= "<td>".$result['formadepago']."</td>";
		$tabla.= "<td>".$result['referencia']."</td>";
		$tabla.= "<td>".$result['emisor']."</td>";
		$tabla.= "<td>".$result['receptor']."</td>";
		$tabla.= "<td>".$result['moneda']."</td>";
		$tabla.= "<td>".$result['monto']."</td>";				
		$tabla.= "</tr>";
	}
	$tabla.= "</tbody>";
	$tabla.= "</table>";
	
	$hoy=date("Y-m-d H:i:s");
	$sql="INSERT INTO ost_ticket_thread(ticket_id,user_id,thread_type,poster,body,format,ip_address,created,updated) 
			VALUES($ticketid,$userid,'M','','$tabla','html','::1','$hoy','$hoy')";
	$qry=$mysqli->query($sql);

	return true;
}
?>