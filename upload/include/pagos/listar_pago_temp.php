<?php
session_start();
define("INCLUDE_DIR","../");
require_once '../ost-config.php';
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
//Listar pago
echo "<table class=table>";
echo "<thead>";
echo "<tr>";
echo "<th>Fecha del Pago</th>";
echo "<th>Forma de Pago</th>";
echo "<th>Referencia</th>";
echo "<th>Origen del Pago</th>";
echo "<th>Destino del Pago</th>";
echo "<th>Moneda</th>";
echo "<th>Monto</th>";
echo "<th>Accion</th>";
echo "</tr>";
echo "</thead>";
echo "<tbody>";
$user_id		=$_REQUEST["user_id"];
$sql_pagos_list	="SELECT * FROM ost_pagos_temp WHERE ticket_id=0 AND user_id=$user_id";
$qry_pagos_list	=$mysqli->query($sql_pagos_list);
if ($mysqli->affected_rows>0){
	while ($result=$qry_pagos_list->fetch_assoc()) {
		$id=$result['id'];
		echo "<tr>";
		echo "<td>".$result['fechadepago']."</td>";
		echo "<td>".$result['formadepago']."</td>";
		echo "<td>".$result['referencia']."</td>";
		echo "<td>".$result['emisor']."</td>";
		echo "<td>".$result['receptor']."</td>";
		echo "<td>".$result['moneda']."</td>";
		echo "<td>".$result['monto']."</td>";	
		echo "<td>";
			echo "<a onclick='eliminarPago($id);'>";
			echo "<img src='assets/default/images/icons/b_drop.png'>";
			echo "</a>";
		echo "</td>";	
		echo "</tr>";
	}
}else{
	//echo "SQL Error: ". $sql_pagos_list;
}
echo "</tbody>";
echo "</table>";

//Cerramos conexion
$mysqli->close();
?>