<?php
$conex=mysql_connect("localhost", "osticket", "0571ck37");   
//Consultamos los email de los usuarios de la organizacion filtrar los boletos.
$org_id=$_REQUEST["org_id"];
$sqlEmail="SELECT address FROM osticket1911.ost_user_email WHERE user_id IN (SELECT id FROM osticket1911.ost_user WHERE org_id=$org_id)";
$qryEmail= mysql_query($sqlEmail);
$emails=array();
while ($rowEmail=mysql_fetch_row($qryEmail)) {
    $emails[]=$rowEmail[0];                    
}        
$matches = "'".implode("','",$emails)."'";
//Colocar nombre de base de datos del CRM en Produccion
$bd="crmtest";
$query	= "		
	SELECT l.localizador, gds, passenger, itinerario, boleto1, fecha_emision, amount, currency, b.status
		FROM $bd.vtiger_account as a 
			INNER JOIN $bd.vtiger_contactdetails as c ON a.accountid=c.accountid
			INNER JOIN $bd.vtiger_localizadores as l ON l.contactoid=c.contactid
				AND localizadoresid NOT IN (SELECT crmid FROM $bd.vtiger_crmentity WHERE deleted=1 AND setype='Localizadores') 
			INNER JOIN $bd.vtiger_boletos as b ON b.localizadorid=l.localizadoresid 
				AND boletosid NOT IN (SELECT crmid FROM $bd.vtiger_crmentity WHERE deleted=1 AND setype='Boletos')
		WHERE email1 IN ($matches) OR email IN ($matches) AND (a.account_type='Satelite' OR a.account_type LIKE '%Freelance%')
		ORDER BY fecha_emision DESC
";
$result = mysql_query($query);
?>

<table id="ticketTable" class="table" width="100%" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th width="120"><a href="#"><b>Localizador</b></a></th>
            <th width="120"><a href="#"><b>GDS</b></a></th>
            <th width="120"><a href="#"><b>Pasajero</b></a></th>
            <th width="120"><a href="#"><b>Ruta</b></a></th>
            <th width="120"><a href="#"><b>Boleto</b></a></th>           
            <th width="120"><a href="#"><b>Emision</b></a></th>           
            <th width="120"><a href="#"><b>Tarifa</b></a></th>           
            <th width="120"><a href="#"><b>Moneda</b></a></th>           
            <th width="120"><a href="#"><b>Status</b></a></th>           
        </tr>
    </thead>
    <tbody>
   <?php
   while ($row=mysql_fetch_row($result)) {    
    ?>
    <tr>
        <td><?php echo $row[0]; ?></td>
        <td><?php echo $row[1]; ?></td>
        <td><?php echo $row[2]; ?></td>
        <td><?php echo $row[3]; ?></td>
        <td><?php echo $row[4]; ?></td>
        <td><?php echo $row[5]; ?></td>
        <td><?php echo $row[6]; ?></td>
        <td><?php echo $row[7]; ?></td>
        <td><?php echo $row[8]; ?></td>        
    </tr>
    
	<?php
	}        
   ?>
    </tbody>
</table>