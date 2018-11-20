<?php
include("crm.functions.php");
$data="";
switch ($_REQUEST["select"]) {
	case 'paymentmethod':
		$qry=$mysqli_crm->query("SELECT * from vtiger_paymentmethod");
		while ($res=$qry->fetch_assoc()) {
			$data.="<option value='".$res["paymentmethod"]."'>".utf8_encode($res["paymentmethod"])."</option>";
		}
		echo $data;
		break;
	
	case 'bancoemisor':
		$qry=$mysqli_crm->query("SELECT * from vtiger_bancoemisor");
		while ($res=$qry->fetch_assoc()) {
			$data.="<option value='".$res["bancoemisor"]."'>".utf8_encode($res["bancoemisor"])."</option>";
		}
		echo $data;
		break;
	
	case 'bancoreceptor':
		$qry=$mysqli_crm->query("SELECT * from vtiger_bancoreceptor");
		while ($res=$qry->fetch_assoc()) {
			$data.="<option value='".$res["bancoreceptor"]."'>".utf8_encode($res["bancoreceptor"])."</option>";
		}
		echo $data;
		break;
	
	case 'currency':
		$qry=$mysqli_crm->query("SELECT * from vtiger_currency order by sortorderid");
		while ($res=$qry->fetch_assoc()) {
			$data.="<option value='".$res["currency"]."'>".$res["currency"]."</option>";
		}
		echo $data;
		break;
	
	default:
		# code...
		break;
}
?>