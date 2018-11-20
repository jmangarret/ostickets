<?php
session_start();
define("INCLUDE_DIR","../");
require_once '../ost-config.php';
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
//Listar pago
$tabla	='';
$user_id		=$_REQUEST["user_id"];
$sql_pagos_list	="SELECT * FROM ost_pagos_temp WHERE ticket_id=0 AND user_id=$user_id";
$qry_pagos_list	=$mysqli->query($sql_pagos_list);
if ($mysqli->affected_rows>0){
	$tabla = "<table class=table>";
	$tabla.= "<thead>";
	$tabla.= "<tr>";
	$tabla.= "<th>Fecha del Pago</th>";
	$tabla.= "<th>Forma de Pago</th>";
	$tabla.= "<th>Referencia</th>";
	$tabla.= "<th>Origen del Pago</th>";
	$tabla.= "<th>Destino del Pago</th>";
	$tabla.= "<th>Moneda</th>";
	$tabla.= "<th>Monto</th>";
	$tabla.= "<th>Accion</th>";
	$tabla.= "</tr>";
	$tabla.= "</thead>";
	$tabla.= "<tbody>";
	while ($result=$qry_pagos_list->fetch_assoc()) {
		$id=$result['id'];
		$tabla.= "<tr>";
		$tabla.= "<td>".$result['fechadepago']."</td>";
		$tabla.= "<td>".$result['formadepago']."</td>";
		$tabla.= "<td>".$result['referencia']."</td>";
		$tabla.= "<td>".$result['emisor']."</td>";
		$tabla.= "<td>".$result['receptor']."</td>";
		$tabla.= "<td>".$result['moneda']."</td>";
		$tabla.= "<td>".$result['monto']."</td>";	
		$tabla.= "<td>";
			$tabla.= "<a onclick='eliminarPago($id);'>";
			$tabla.= "<img src='assets/default/images/icons/b_drop.png'>";
			$tabla.= "</a>";
		$tabla.= "</td>";	
		$tabla.= "</tr>";
	}
	$tabla.= "</tbody>";
	$tabla.= "</table>";
}else{
	echo 'false';
	die();
}
echo $tabla;
//Cerramos conexion
$mysqli->close();
?>