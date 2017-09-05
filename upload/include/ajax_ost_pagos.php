<?php
session_start();
$dir_actual=getcwd(); //upload/include/
define("INCLUDE_DIR",$dir_actual."/");
require_once INCLUDE_DIR.'ost-config.php';
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

switch ($_REQUEST["option"]) {
	case 'create':
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
		//Formateamos fecha compatible con mysql
		$date_convert 	=date_create($fechadepago);
		$fechadepago 	=$date_convert->format('Y-m-d');
		//Registramos pago en tabla principal
		$sql_pagos ="INSERT INTO ost_pagos_temp ";
		$sql_pagos.="VALUES (0,0,'$user_id','$fechadepago','$paymentmethod','$referencia','$bancoemisor','$bancoreceptor','$currency','$amount') ";
		$qryPagos	=$mysqli->query($sql_pagos);

		if ($mysqli->affected_rows>0){
			echo "Exito";
		}else{
			echo "<h1>Fallo Insert...</h1>".$sql_pagos.$mysqli->error;
		}	
		break;

	case 'list':
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
		echo "</tr>";
		echo "</thead>";
		echo "<tbody>";
		$user_id		=$_REQUEST["user_id"];
		$sql_pagos_list	="SELECT * FROM ost_pagos_temp WHERE ticket_id=0 AND user_id=$user_id";
		$qry_pagos_list	=$mysqli->query($sql_pagos_list);
		if ($mysqli->affected_rows>0){
			while ($result=$qry_pagos_list->fetch_assoc()) {
				echo "<tr>";
				echo "<td>".$result['fechadepago']."</td>";
				echo "<td>".$result['formadepago']."</td>";
				echo "<td>".$result['referencia']."</td>";
				echo "<td>".$result['emisor']."</td>";
				echo "<td>".$result['receptor']."</td>";
				echo "<td>".$result['moneda']."</td>";
				echo "<td>".$result['monto']."</td>";				
				echo "</tr>";
			}
		}
		echo "</tbody>";
		echo "</table>";
		break;

	default:
		# code...
		break;
}

//Cerramos conexion
$mysqli->close();
?>