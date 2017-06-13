<?php
$conex=mysql_connect("localhost", "osticket", "0571ck37");   
//Consultamos los email de los usuarios de la organizacion filtrar los boletos.
$org_id =$_REQUEST["org_id"];
$org_name =$_REQUEST["org_name"];
$isStaff=$_REQUEST["isStaff"];
$strbus =trim($_REQUEST["strbus"]);
$fecha1 =trim($_REQUEST["fecha1"]);
$fecha2 =trim($_REQUEST["fecha2"]);

$sqlEmail=" SELECT address FROM osticket1911.ost_user_email 
            WHERE user_id IN (SELECT id FROM osticket1911.ost_user WHERE org_id=$org_id)";
$qryEmail= mysql_query($sqlEmail);
$emails=array();
while ($rowEmail=mysql_fetch_row($qryEmail)) {
    $emails[]=$rowEmail[0];                    
}        
$matches = "'".implode("','",$emails)."'";
/// $db Colocar nombre de base de datos del CRM en Produccion ///
$bd="vtigercrm600";
$query	= "SELECT fecha_emision, l.localizador, passenger, boleto1, gds, b.status, paymentmethod, amount, b.monto_base, b.fee, currency  
		      FROM $bd.vtiger_account as a 
			     INNER JOIN $bd.vtiger_contactdetails as c ON a.accountid=c.accountid
			     INNER JOIN $bd.vtiger_localizadores as l ON l.contactoid=c.contactid
				    AND localizadoresid NOT IN (SELECT crmid FROM $bd.vtiger_crmentity WHERE deleted=1 AND setype='Localizadores') 
			     INNER JOIN $bd.vtiger_boletos as b ON b.localizadorid=l.localizadoresid 
				    AND boletosid NOT IN (SELECT crmid FROM $bd.vtiger_crmentity WHERE deleted=1 AND setype='Boletos')
		      WHERE (email1 IN ($matches) OR email IN ($matches))";

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
$totTarifa=0;
$totBaseBs=0;
$totBaseDol=0;
$totFee=0;
$totGeneral=0;
$totTarifaDol=0;
$totFeeDol=0;
$totGeneralDol=0;
$totComisionBs=0;

//Validamos si es una Satelite con comision adicional
//Id 4- Bestravel
//Id 8- BEstravel Sucursal
if ($org_id==4 || $org_id==8){
    $comision=true;
    $porcentaje=1.5;
}
?>
<!--CONSOLE.LOG PARA DEBUG-->
<script type="text/javascript">
<?php 
//Elimnamos satos de linea, break line, tabulaciones para poder mostrarlos en el console.log
$LOG = str_replace(array("\r\n", "\r", "\n"), "", $query);
echo "console.log(\"".$matches."\")"; 
?>
</script>

<div id="basic_search">
    <table>
        <tbody>
        <tr>
            <td nowrap>
                <input type="text" id="strbus" name="strbus" size="30" placeholder="Localizador, boleto, pasajero">                
                Desde: <input type="text" id ="fecha1" name="desde" style="width:90px" placeholder="aaaa-mm-dd"> 
                Hasta: <input type="text" id ="fecha2" name="hasta" style="width:90px" placeholder="aaaa-mm-dd">&nbsp;
            <?php
            /////Si inicia sesion como Agente, es Perfil Staff y la peticion viene de include/staff/header.inc.php
            if ($isStaff){
                //retrocedemos un directorio para salir de la carpeta SCP
                $urlAjax="../include/client/ajax_boletos.php";
                echo "<td>";
                echo "Satelite: <select name='satelite' id='select_satelites' onchange='setOrganizacion(this)'>";
                echo "<option value=0>Seleccionar</option>";
                echo "</select>";
                echo "</td>";  
                $sqlOrg="SELECT id,name FROM osticket1911.ost_organization ORDER BY name";
                $qryOrg = mysql_query($sqlOrg);                          
                while ($rowOrg = mysql_fetch_row($qryOrg)) {
                    ?>
                     <script type="text/javascript">                        
                        $("#select_satelites").append(new Option("<?php echo $rowOrg[1]; ?>", "<?php echo $rowOrg[0]; ?>"));                        
                    </script>
                    <?php                    
                }
            }else{
                $urlAjax="include/client/ajax_boletos.php";
            }
            /////Fin org staff
            ?>              
            <td><input type="button" name="buscar" id="buscar" value="Buscar"></td>   
            <td><input type="hidden" name="org_id" id="org_id" value="<?php echo $org_id; ?>"></td>   
            <td><input type="hidden" name="org_name" id="org_name" value="<?php echo $org_name; ?>"></td>   
            <td><input type="hidden" name="isStaff" id="isStaff" value="<?php echo $isStaff; ?>"></td>   
            <script type="text/javascript">
            $("#buscar").click(function(){
                var org_id = $("#org_id").val();
                var org_name = $("#org_name").val();
                var strbus = $("#strbus").val();
                var fecha1 = $("#fecha1").val();
                var fecha2 = $("#fecha2").val();
                var isStaff= $("#isStaff").val();
                $("#content").html("Cargando... <img src='images/FhHRx-Spinner.gif'>");

                $.ajax({
                    data: { 
                        org_id : org_id,
                        org_name : org_name,
                        strbus : strbus,
                        fecha1 : fecha1,
                        fecha2 : fecha2,
                        isStaff: isStaff
                    },
                    type: "POST",
                    url: '<?php echo $urlAjax; ?>',
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
<b>Mostrando 1 - <?php echo $totreg . $criterio . " - " . $org_name; ?></b>

<table id="ticketTable" class="table" width="90%" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th width="120"><a href="#"><b>Fecha</b></th>    
            <th width="120"><a href="#"><b>Localizador</b></th>            
            <th width="120"><a href="#"><b>Pasajero</b></th>
            <th width="120"><a href="#"><b>Boleto</b></th>                                                
            <th width="120"><a href="#"><b>GDS</b></th>                    
            <th width="120"><a href="#"><b>Status</b></th>     
            <th width="120"><a href="#"><b>F.de.Pago</b></th>                       
            <th width="120"><a href="#"><b>Tarifa</b></th>           
            <th width="120"><a href="#"><b>MontoBase</b></th>             
            <?php 
            if ($comision==true){
            echo '<th width="120"><a href="#"><b>Comision</b></th>';
            }                       
            ?>
            <th width="120"><a href="#"><b>Fee</b></th>        
            <th width="120"><a href="#"><b>Total</b></th>       
            <th width="120"><a href="#"><b>Moneda</b></th>           
            
        </tr>
    </thead>
    <tbody>
   <?php
   while ($row=mysql_fetch_array($result)) { 
    $fecha = date("d/m/Y", strtotime($row["fecha_emision"]));     
    $total =$row["amount"] + $row["fee"]; 
    if ($row["currency"]=="VEF"){
        $totFee     =$totFee + $row["fee"];    
        $totTarifa  =$totTarifa + $row["amount"];          
        $totBaseBs  =$totBaseBs + $row["monto_base"];          
        if ($comision==true) {
            //Calculamos Comision Bs. MontoBase*Porcentaje / 100
            $comisionBs     =$row["monto_base"]*$porcentaje/100; 
            if ($row["gds"]<>"Amadeus") $comisionBs=0;
            $total          =$total-$comisionBs;
            $totComisionBs  =$totComisionBs+$comisionBs;   
        }       
        $totGeneral =$totGeneral + $total; 
    }      
    if ($row["currency"]=="USD"){
        $totFeeDol     =$totFeeDol + $row["fee"];    
        $totBaseDol    =$totBaseDol + $row["monto_base"];
        $totTarifaDol  =$totTarifaDol + $row["amount"];  
        $totGeneralDol =$totGeneralDol + $row["amount"] + $row["fee"];        
    }  
    
    ?>
    <tr>
        <td nowrap><?php echo $fecha; ?></td>
        <td nowrap><?php echo $row["localizador"]; ?></td>
        <td nowrap><?php echo substr($row["passenger"],0,25); //Solo 30 Caracteres ?></td>
        <td nowrap><?php echo $row["boleto1"]; ?></td>
        <td nowrap><?php echo $row["gds"]; ?></td>
        <td nowrap><?php echo $row["status"]; ?></td>
        <td nowrap><?php echo $row["paymentmethod"]; ?></td>
        <td nowrap><?php echo number_format($row["amount"],2); ?></td>
        <td nowrap><?php echo number_format($row["monto_base"],2); ?></td>                        
        <?php 
        if ($comision==true){
        echo '<td nowrap>'.number_format($comisionBs,2).'</td>';
        }                       
        ?>            
        <td nowrap><?php echo number_format($row["fee"],2); ?></td>
        <td nowrap><?php echo number_format($total,2); ?></td>
        <td nowrap><?php echo $row["currency"]; ?></td>        
    </tr>    
	<?php
	}            
   ?>
    <tr>
        <td colspan="7"><b>Total USD.</b></td>        
        <td><b><?php echo number_format($totTarifaDol,2); ?></b></td>        
        <td><b><?php echo number_format($totBaseDol,2); ?></b></td>        
        <?php 
        if ($comision==true){
        echo '<td><b>0.00</b></td>';
        }                       
        ?>          
        <td><b><?php echo number_format($totFeeDol,2); ?></b></td>        
        <td><b><?php echo number_format($totGeneralDol,2); ?></b></td>
        <td><b>USD</b></td>        
    </tr>

    <tr>
        <td colspan="7"><b>Total VEF.</b></td>        
        <td><b><?php echo number_format($totTarifa,2); ?></b></td>        
        <td><b><?php echo number_format($totBaseBs,2); ?></b></td>        
        <?php 
        if ($comision==true){
        echo '<td><b>'.number_format($totComisionBs,2).'</b></td>';
        }                       
        ?>                  
        <td><b><?php echo number_format($totFee,2); ?></b></td>        
        <td><b><?php echo number_format($totGeneral,2); ?></b></td>
        <td><b>VEF</b></td>        
    </tr>

    </tbody>
</table>

<script>
function setOrganizacion(elem){
    document.getElementById("org_id").value=elem.value;
    document.getElementById("org_name").value=elem.options[elem.selectedIndex].text;
}
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