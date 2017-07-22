<?php
define('DBTYPE','mysql');
define('DBHOST','localhost');
define('DBNAME','osticket1911');
define('DBUSER','osticket');
define('DBPASS','0571ck37');
$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

switch ($_REQUEST["select"]) {
	case 'nrodeticket':
		$qry = $mysqli->query("SELECT * FROM ost_ticket WHERE status_id<>3 ORDER BY ticket_id DESC LIMIT 1000");
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