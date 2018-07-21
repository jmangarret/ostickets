<?php
session_start();
include("../funciones/commons.php");
include("../funciones/crm.functions.php");
include("../funciones/pagos.functions.php");
$accountid 	=$_POST["accountid"];
$desde 		=$_POST["desde"];
$hasta 		=$_POST["hasta"];
$totSaldoBs	=$_SESSION["totSaldoBs"];
$totSaldoDol=$_SESSION["totSaldoDol"];
/*Obtenemos nombre de cuenta satelite*/
$satelite 	=getCuentaCrm($accountid);
/*Generamos nombre de reporte*/
$nombre 	="Cierre de Reporte ".$satelite." del ".$desde." al ".$hasta;
/*Obtenemos correlativo crm*/
$qryId=mysqli_query($mysqli_crm,"CALL getCrmId()");
$qryId=mysqli_query($mysqli_crm,"SELECT @idcrm");
$rowId=mysqli_fetch_row($qryId);
$crmId=$rowId[0];
/*Creamos registro de cierre de reporte*/
$sqlCierre 	="INSERT INTO vtiger_cierres(cierresid,accountid,nombre,desde,hasta,saldobs,saldodol,status) ";
$sqlCierre	.="VALUES ($crmId,$accountid,'$nombre','$desde','$hasta',$totSaldoBs,$totSaldoDol, 1) ";
$qryCierre 	=mysqli_query($mysqli_crm,$sqlCierre);
/*Buscamos actual cierre del satelite*/
$sqlActual 	="SELECT MAX(cierresid) FROM vtiger_cierres WHERE accountid=$accountid";
$qryActual 	=mysqli_query($mysqli_crm,$sqlActual);
$rowActual 	=mysqli_fetch_row($qryActual);
$idCierre  	=$rowActual[0];
/*Creamos regsitro de crm obligatorio*/
$crmUser=1; //Administrador
$modulo	="Cierres";
$label	=$nombre;
$creado	=date("Y-m-d H:i:s");
$idcrm	=$idCierre;
$iduser	=$crmUser;	
$qryCrm	=$mysqli_crm->prepare("CALL setCrmEntity(?,?,?,?,?)");
$qryCrm->bind_param("sssii",$modulo,$label,$creado,$idcrm,$iduser);
$qryCrm->execute();	
/*Recorremos listado de Boletos guardados en session*/
foreach ($_SESSION['boletosid'] as $key => $value) {
	$idBoleto=$value;
    $sqlBoletos="UPDATE vtiger_boletos SET cierresid=$idCierre, status_satelite='Procesado' WHERE boletosid=$idBoleto";
    $qry=mysqli_query($mysqli_crm,$sqlBoletos);
    /*Insertamos en tabla relacion del crm*/
    $sqlCrmRel1="INSERT INTO vtiger_crmentityrel VALUES($idCierre,'Cierres',$idBoleto,'Boletos')";
    $qry=mysqli_query($mysqli_crm,$sqlCrmRel1);  
    /*  
    echo "<br>1";
    echo $sqlBoletos;
    echo "<br>2";
    echo $sqlCrmRel1;
    */
}
/*Recorremos listado de pagos seleccionados en session*/
foreach ($_SESSION['pagosid'] as $key => $value) {
	$idPago=$value;
    $sqlPagos="UPDATE vtiger_registrodepagos SET cierresid=$idCierre, status_satelite='Procesado' WHERE registrodepagosid=$idPago";
    $qry=mysqli_query($mysqli_crm,$sqlPagos);
    /*Insertamos en tabla relacion del crm*/
    $sqlCrmRel2="INSERT INTO vtiger_crmentityrel VALUES($idCierre,'Cierres',$idPago,'RegistroDePagos')";
    $qry=mysqli_query($mysqli_crm,$sqlCrmRel2);    
    /*
    echo "<br>3";
    echo $sqlPagos;
    echo "<br>4";
    echo $sqlCrmRel2;
    */
}

echo "Reporte Procesado! ";
echo "<br>";
echo '<a href="http://registro.tuagencia24.com/index.php?module=Cierres&view=Detail&record='.$idCierre.'"><label class="btn btn-primary"> VER EN CRM </label></a>';
?>
