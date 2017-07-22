<?php
include("conexion_crm.php");
switch ($_REQUEST["select"]) {
	case 'paymentmethod':
		$qry=mysql_query("SELECT * from vtiger_paymentmethod",$con2);
		while ($res=mysql_fetch_array($qry)) {
			$data.="<option value='".$res["paymentmethodid"]."'>".utf8_encode($res["paymentmethod"])."</option>";
		}
		echo $data;
		break;
	
	case 'bancoemisor':
		$qry=mysql_query("SELECT * from vtiger_bancoemisor",$con2);
		while ($res=mysql_fetch_array($qry)) {
			$data.="<option value='".$res["bancoemisorid"]."'>".utf8_encode($res["bancoemisor"])."</option>";
		}
		echo $data;
		break;
	
	case 'bancoreceptor':
		$qry=mysql_query("SELECT * from vtiger_bancoreceptor",$con2);
		while ($res=mysql_fetch_array($qry)) {
			$data.="<option value='".$res["bancoreceptorid"]."'>".utf8_encode($res["bancoreceptor"])."</option>";
		}
		echo $data;
		break;
	
	case 'currency':
		$qry=mysql_query("SELECT * from vtiger_currency",$con2);
		while ($res=mysql_fetch_array($qry)) {
			$data.="<option value='".$res["currencyid"]."'>".$res["currency"]."</option>";
		}
		echo $data;
		break;
	
	default:
		# code...
		break;
}
?>