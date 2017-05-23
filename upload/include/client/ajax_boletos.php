<?php
$conex=mysql_connect("localhost", "osticket", "0571ck37");   
//Consultamos los email de los usuarios de la organizacion filtrar los boletos.
$org_id=$_REQUEST["org_id"];
$strbus=trim($_REQUEST["strbus"]);
$fecha1=trim($_REQUEST["fecha1"]);
$fecha2=trim($_REQUEST["fecha2"]);

$sqlEmail="
        SELECT address FROM osticket1911.ost_user_email 
            WHERE user_id IN (SELECT id FROM osticket1911.ost_user WHERE org_id=$org_id)";
$qryEmail= mysql_query($sqlEmail);
$emails=array();
while ($rowEmail=mysql_fetch_row($qryEmail)) {
    $emails[]=$rowEmail[0];                    
}        
$matches = "'".implode("','",$emails)."'";
/// Colocar nombre de base de datos del CRM en Produccion ///
$bd="vtigercrm600";
/// $db nombre de base de datos del CRM en Produccion ///
$query	= "		
	SELECT fecha_emision, l.localizador, passenger, boleto1, gds, itinerario, paymentmethod, amount, currency, b.status
		FROM $bd.vtiger_account as a 
			INNER JOIN $bd.vtiger_contactdetails as c ON a.accountid=c.accountid
			INNER JOIN $bd.vtiger_localizadores as l ON l.contactoid=c.contactid
				AND localizadoresid NOT IN (SELECT crmid FROM $bd.vtiger_crmentity WHERE deleted=1 AND setype='Localizadores') 
			INNER JOIN $bd.vtiger_boletos as b ON b.localizadorid=l.localizadoresid 
				AND boletosid NOT IN (SELECT crmid FROM $bd.vtiger_crmentity WHERE deleted=1 AND setype='Boletos')
		WHERE email1 IN ($matches) OR email IN ($matches)		
";
if (!$strbus && !$fecha1 && !$fecha2){
    $criterio = " Todos los boletos ";
}

if ($strbus){
    $query.=" AND (l.localizador LIKE '%$strbus%' OR boleto1 LIKE '%$strbus%' OR passenger LIKE '%$strbus%') ";
    $criterio = " Coincidencias de $strbus ";
}
if ($fecha1 && $fecha2){
    $query.=" AND fecha_emision BETWEEN '$fecha1' AND '$fecha2' ";
    $criterio.= " Desde ".date("d/m/Y", strtotime($fecha1))." - Hasta ".date("d/m/Y", strtotime($fecha2));
}
$query.=" ORDER BY fecha_emision DESC";
$result = mysql_query($query);
$totreg = mysql_num_rows($result);
//echo $query;
?>

<div id="basic_search">
    <table>
        <tbody>
        <tr>
            <td>
                <input type="text" id="strbus" name="strbus" size="30" placeholder="Localizador, boleto, pasajero">                
                Desde: <input type="text" id ="fecha1" name="desde" style="width:90px" placeholder="aaaa-mm-dd"> 
                Hasta: <input type="text" id ="fecha2" name="hasta" style="width:90px" placeholder="aaaa-mm-dd"> &nbsp;
            </td>
            <td><input type="button" name="buscar" id="buscar" value="Buscar"></td>   
            <td><input type="hidden" name="org_id" id="org_id" value="<?php echo $org_id; ?>"></td>   
            <script type="text/javascript">
            $("#buscar").click(function(){
                $.ajax({
                    data: { 
                        org_id : $("#org_id").val(),
                        strbus : $("#strbus").val(),
                        fecha1 : $("#fecha1").val(),
                        fecha2 : $("#fecha2").val()
                    },
                    type: "POST",
                    url: 'include/client/ajax_boletos.php',
                    success: function(response){                                                                  
                        $("#content").html(response);
                        }
                    });
            });
            </script>            
        </tr>
        </tbody>
    </table>
</div>

<br>
<b>Mostrando 1 - <?php echo $totreg . $criterio; ?></b>

<table id="ticketTable" class="table" width="100%" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th width="120"><a href="#"><b>Fecha</b></a></th>    
            <th width="120"><a href="#"><b>Localizador</b></a></th>            
            <th width="120"><a href="#"><b>Pasajero</b></a></th>
            <th width="120"><a href="#"><b>Boleto</b></a></th>                              
            <th width="120"><a href="#"><b>GDS</b></a></th>
            <th width="120"><a href="#"><b>Ruta</b></a></th>            
            <th width="120"><a href="#"><b>F. de Pago</b></a></th>           
            <th width="120"><a href="#"><b>Tarifa</b></a></th>           
            <th width="120"><a href="#"><b>Moneda</b></a></th>           
            <th width="120"><a href="#"><b>Status</b></a></th>           
        </tr>
    </thead>
    <tbody>
   <?php
   while ($row=mysql_fetch_row($result)) { 
    $fecha = date("d/m/Y", strtotime($row[0]));       
    ?>
    <tr>
        <td nowrap><?php echo $fecha; ?></td>
        <td nowrap><?php echo $row[1]; ?></td>
        <td nowrap><?php echo $row[2]; ?></td>
        <td nowrap><?php echo $row[3]; ?></td>
        <td nowrap><?php echo $row[4]; ?></td>
        <td nowrap><?php echo $row[5]; ?></td>
        <td nowrap><?php echo $row[6]; ?></td>
        <td nowrap><?php echo $row[7]; ?></td>
        <td nowrap><?php echo $row[8]; ?></td>
        <td nowrap><?php echo $row[9]; ?></td>        
    </tr>
    
	<?php
	}        
   ?>
    </tbody>
</table>

<script>
$(function() {
    $("#fecha1").datepicker();
    $("#fecha1").datepicker('option', {dateFormat: 'yy-mm-dd'});
  });

$(function() {
    $("#fecha2").datepicker();
    $("#fecha2").datepicker('option', {dateFormat: 'yy-mm-dd'});
  });

$(function($){
    $.datepicker.regional['es'] = {
        closeText: 'Cerrar',
        prevText: '<Ant',
        nextText: 'Sig>',
        currentText: 'Hoy',
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
        dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
        dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
        weekHeader: 'Sm',
        dateFormat: 'dd/mm/yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['es']);
});

</script>