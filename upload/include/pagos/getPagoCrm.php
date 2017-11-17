<?php
session_start();
include("../funciones/commons.php");
include("../funciones/crm.functions.php");
include("../funciones/pagos.functions.php");

$pago=getPagoById($_REQUEST["id"]);

$fila ="<tr>";
$fila.="<td>";
$fila.=$pago["obs"];
$fila.="</td>";
$fila.="<td>";
$fila.=$pago["banco"];
$fila.="</td>";
$fila.="<td>";
$fila.=$pago["fecha"];
$fila.="</td>";
$fila.="<td>";
$fila.=$pago["ref"];
$fila.="</td>";
$fila.="<td>";
$fila.="<b>".$pago["total"]."</b>";
$fila.="</td>";
$fila.="<td>";
$fila.="<b>".$pago["moneda"]."</b>";
$fila.="</td>";
$fila.="</tr>";		
if ($pago["moneda"]=="VEF") $_SESSION['totPagosBs'] +=str_replace(",", "", $pago["total"]);
if ($pago["moneda"]=="USD") $_SESSION['totPagosDol']+=$pago["total"];

echo $fila;

?>