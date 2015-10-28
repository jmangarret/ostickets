<?php

$mysqli = new mysqli("localhost", "osticket", "0571ck37", "osticket1911");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

if($_REQUEST["menu"] == "localizador"){

	$query	= "	SELECT CAST( localizador AS CHAR( 100 ) CHARSET utf8 )
				FROM  `ost_ticket__cdata` 
				WHERE UPPER(CAST( localizador AS CHAR( 100 ) CHARSET utf8 )) =  '".strtoupper($_REQUEST["localizador"])."'
					AND CAST( subject AS CHAR( 100 ) CHARSET utf8 ) = 19
					AND CAST( gds AS CHAR( 100 ) CHARSET utf8 ) LIKE  '".$_REQUEST["gds"]."%'";
	$result = $mysqli->query($query);
	$row = $result->num_rows;

	if($row > 0)
		echo 1;
	else
		echo 0;

}
else{

	$string = '<option value="">— Select —</option>';

	$query = "	SELECT 
					a.submenu_id,a.submenu_value 
				FROM  
					`ost_list_items_custom` a,
					ost_list_items b
				WHERE 
					a.submenu_id = b.id
					AND a.menu_id = ".$_REQUEST["menu"]." 
				ORDER BY b.sort ASC";
	$result = $mysqli->query($query);

	while($row = $result->fetch_array()){
		$string .= '<option value="'.$row[0].'">'.$row[1].'</option>';
	}

	echo $string;

}

?>