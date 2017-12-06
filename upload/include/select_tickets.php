<?php
$dir_actual=getcwd(); //upload/include
define("INCLUDE_DIR",$dir_actual."/");//seteamos include_dir para evitar die error osticket
require_once INCLUDE_DIR.'ost-config.php';

$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$data="";
switch ($_REQUEST["select"]) {
	case 'nrodeticket':
		$qry = $mysqli->query("SELECT ticket_id, CONCAT(ost_ticket.number,' - ', ost_user.name) as number FROM ost_ticket 
						INNER JOIN ost_user ON ost_ticket.user_id=ost_user.id
						WHERE status_id<>3 ORDER BY ticket_id DESC LIMIT 1000");
		
		while ($res = $qry->fetch_array(MYSQLI_ASSOC)) {
			$data.="<option value='".$res["ticket_id"]."'>".utf8_encode($res["number"])."</option>";
		}
		
		echo $data;
		break;
	
	default:
		# code...
		break;
}
?>


    