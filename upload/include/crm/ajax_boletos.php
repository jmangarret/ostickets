<?php
session_start();
$_SESSION['totTarifaBs'] =0;
$_SESSION['totTarifaDol']=0;
$_SESSION['totPagosDol']=0;
$_SESSION['totPagosBs']=0;
$_SESSION['boletosid']=0;
$_SESSION['pagosid']=array();

$dir_actual=getcwd(); //upload/include/crm
$include_dir=dirname($dir_actual); //devuelve directorio padre o anterior
define("INCLUDE_DIR",$include_dir."/");
require_once INCLUDE_DIR.'ost-config.php';
include("../funciones/commons.php");
include("../funciones/crm.functions.php");
include("../funciones/pagos.functions.php");

$conex=mysql_connect(DBHOST, DBUSER, DBPASS);   
$org_id     =$_REQUEST["org_id"];
$org_name   =$_REQUEST["org_name"];
$isStaff    =$_REQUEST["isStaff"];
$strbus     =trim($_REQUEST["strbus"]);
$fecha1     =trim($_REQUEST["fecha1"]);
$fecha2     =trim($_REQUEST["fecha2"]);

$totTarifa=0;
$totBaseBs=0;
$totBaseDol=0;
$totFee=0;
$totGeneral=0;
$totTarifaDol=0;
$totFeeDol=0;
$totGeneralDol=0;
$totComisionBs=0;
$comisionBs=0;
//Validamos si es una Satelite con comision adicional
//Id 4- Bestravel
//Id 8- BEstravel Sucursal
if ($org_id==4 || $org_id==8){
    $comision=true;
    $porcentaje=1.5;
}
?>
<div id="basic_search">
    <table border="0">
        <tbody>
        <tr>            
            <?php
            /////Si inicia sesion como Agente, es Perfil Staff y la peticion viene de include/staff/header.inc.php
            if ($isStaff){
                //retrocedemos un directorio para salir de la carpeta SCP
                $urlAjax="../include/crm/ajax_boletos.php";
                echo "<td>";
                echo "<strong>Satelite:</strong> ";
                echo "<select name='satelite' id='select_satelites' onchange='setOrganizacion(this)' style=width:300px>";
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
                $urlAjax="include/crm/ajax_boletos.php";
            }
            /////Fin org staff
            ?>    
            <td nowrap>                              
            <strong>Desde:</strong>
            <input type="text" id ="fecha1" name="desde" style="width:90px" placeholder="aaaa-mm-dd"> 
            <strong>Hasta:</strong>  
            <input type="text" id ="fecha2" name="hasta" style="width:90px" placeholder="aaaa-mm-dd">&nbsp;
            <strong>Boleto:</strong>
            <input type="text" id="strbus" name="strbus" size="30" placeholder="Localizador, boleto, pasajero">

            <td><input type="button" name="buscar" id="buscar" value="Buscar"></td>   
            <td><input type="hidden" name="org_id" id="org_id" value="<?php echo $org_id; ?>"></td>   
            <td><input type="hidden" name="org_name" id="org_name" value="<?php echo $org_name; ?>"></td>   
            <td><input type="hidden" name="isStaff" id="isStaff" value="<?php echo $isStaff; ?>"></td>   
        </tr>
        </tbody>
    </table>
</div>

<br>
<?php
//Obtenemos los emails de la org //Consultamos los boletos segun los criterios seleccionados
$emails =getOrgEmails($org_id);
$boletos=getBoletosSatelites($strbus,$fecha1,$fecha2,$emails);
$totreg = count($boletos);
$criterio = " Todos los boletos ";
if ($strbus)             $criterio = " Coincidencias de $strbus ";
if ($fecha1 && $fecha2)  $criterio.= " Desde ".date("d/m/Y", strtotime($fecha1))." - Hasta ".date("d/m/Y", strtotime($fecha2));
?>
<details open="open">
<summary><b>Detalle de Boletos - Mostrando 1 - <?php echo $totreg . $criterio . " - " . $org_name; ?></b></summary>
<table id="ticketTable" class="table" width="90%" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th width="120"><a href="#"><b>Fecha</b></th>    
            <th width="120"><a href="#"><b>Localizador</b></th>                        
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
   $arrayBoletos=array();
   foreach ($boletos as $row) {
        $fecha = date("d/m/Y", strtotime($row["fecha_emision"]));     
        $total =$row["amount"] + $row["fee"]; 
        if ($row["currency"]=="VEF"){
            $totFee     =$totFee + $row["fee"];    
            $totTarifa  =$totTarifa + $row["amount"];          
            $totBaseBs  =$totBaseBs + $row["monto_base"];          
            if ($comision==true && $row["status"]<>"Anulado") {
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
            <td nowrap >
                <a href="#" title="<?php echo $row[passenger]; ?>">
                    <?php echo $row["localizador"]; ?>  
                </a>
            </td>            
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
    $arrayBoletos[]=$row["boletosid"];
    $accountid=$row["accountid"];
	} //Fin While         
    $_SESSION['boletosid']=$arrayBoletos;
    ?>
    <tr>
        <td colspan="6"><b>Total USD.</b></td>        
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
        <td colspan="6"><b>Total VEF.</b></td>        
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
</details>
<?php
$_SESSION["totTarifaBs"] =$totGeneral;
$_SESSION["totTarifaDol"]=$totGeneralDol;
?>
<!-- TABLA RESUMEN DE PAGOS -->
<details open="open">
    <summary><b>Detalle de Pagos - <?php echo $org_name; ?></b></summary>    
    <hr>
    <table class="table" width="90%" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th width="120"><a href="#"><b>Concepto</b></th>    
            <th width="120"><a href="#"><b>Banco</b></th>                        
            <th width="120"><a href="#"><b>Fecha</b></th>                                                
            <th width="120"><a href="#"><b>Referencia</b></th>                    
            <th width="120"><a href="#"><b>Total</b></th>     
            <th width="120"><a href="#"><b>Moneda</b></th>     
        </tr>
    </thead>
    <tbody id="tablapagos">    
        <!-- SECCION PARA PAGOS SELECCIONADOS DESDE EL POPUP -->
    </tbody>    
    <tbody id="totalpagos">    
        <!-- SECCION PARA TOTAL PAGOS SELECCIONADOS DESDE EL POPUP -->
    </tbody>    
    </table>
</details>
<!-- FIN TABLA RESUMEN DE PAGOS -->
<div align="center">
    <td><input type="hidden" name="accountid" id="accountid" value="<?php echo $accountid; ?>"></td>   
    <input type="button" name="addPago" value="Agregar Pago" onclick="javascript:openPagos()">
    <input type="button" name="closeReport" id="closeReport" value="Cerrar Reporte">
</div>
<div align="center" id="success-cierre"></div>
<script>
    /*tooltip para datos del pasajero al señalar localizador*/
    $('.tooltip').tooltip(); 
    $("#select_satelites").val("<?php echo $org_id; ?>");
    /*Boton buscar boletos segun filtros*/
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
    /*Modal para agregar pagos*/
    function openPagos(){
        var idOrg=$("#org_id").val();    
        if (idOrg>0){
            popupwindow('../include/pagos/popup_pagos.php?id='+idOrg,'Pagos',800,600);
            return true;        
        }else{
            alert("Debe Seleccionar un Satelite para poder ver sus Pagos...");
            return false;
        }
    }
    function popupwindow(url, title, w, h) {
      var left = (screen.width/2)-(w/2);
      var top = (screen.height/2)-(h/2);
      return window.open(url, title, 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width='+w+', height='+h+', top='+top+', left='+left);
    }     
    function setOrganizacion(elem){
        document.getElementById("org_id").value=elem.value;
        document.getElementById("org_name").value=elem.options[elem.selectedIndex].text;
    }
    $(function() {
        $("#fecha1").datepicker();
        $("#fecha1").datepicker('option', {dateFormat: 'yy-mm-dd'});
        $("#fecha1").val("<?php echo $fecha1; ?>");
      });

    $(function() {
        $("#fecha2").datepicker();
        $("#fecha2").datepicker('option', {dateFormat: 'yy-mm-dd'});
        $("#fecha2").val("<?php echo $fecha2; ?>");
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
    /*Boton Cerrar Reporte*/
    $("#closeReport").click(function(){
        var accountid   = $("#accountid").val();    
        var fecha1      = $("#fecha1").val();
        var fecha2      = $("#fecha2").val();
        $.ajax({
            data: {
                "accountid":accountid,
                "desde":fecha1,
                "hasta":fecha2,
            },
            type: "post",
            url: "../include/pagos/cerrar_reporte.php",
            success:function(response){
                $("#success-cierre").html(response);
            }
        });
    });
</script>

<!--CONSOLE.LOG PARA DEBUG-->
<script type="text/javascript">
<?php 
//Elimnamos satos de linea, break line, tabulaciones para poder mostrarlos en el console.log
$LOG = str_replace(array("\r\n", "\r", "\n"), "", $query);
echo "console.log(\"".$LOG."\")"; 
?>
</script>

