<?php
session_start();
?>
<tr>
    <td colspan="4"><b>Total Pagos USD.</b></td>        
    <td><b><?php echo number_format($_SESSION['totPagosDol'],2); ?></b></td>        
    <td><b>USD</b></td>        
</tr>
<tr>
    <td colspan="4"><b>Total Pagos VEF.</b></td>        
    <td><b><?php echo number_format($_SESSION['totPagosBs'],2); ?></b></td>        
    <td><b>VEF</b></td>        
</tr>
<?php
/*Seccion de Saldos*/
$_SESSION['totSaldoDol']=$_SESSION['totTarifaDol'] - $_SESSION['totPagosDol'];
$_SESSION['totSaldoBs'] =$_SESSION['totTarifaBs']  - $_SESSION['totPagosBs'];
/*Colores para saldo dolares*/
if ($_SESSION['totSaldoDol']>0)  $colorSaldoDol="background-color: crimson; color:white";
if ($_SESSION['totSaldoDol']<0)  $colorSaldoDol="background-color: limegreen; color:white";
if ($_SESSION['totSaldoDol']==0) $colorSaldoDol="background-color: yellow; color:black";
/*Colores para saldo bs*/
if ($_SESSION['totSaldoBs']>0) 	$colorSaldoBs ="background-color: crimson; color:white";
if ($_SESSION['totSaldoBs']<0) 	$colorSaldoBs ="background-color: limegreen; color:white";
if ($_SESSION['totSaldoBs']==0) $colorSaldoBs ="background-color: yellow; color:black";
?>
<tr style="<?php echo $colorSaldoDol?>">
    <td colspan="4"><b>Total Saldo USD.</b></td>        
    <td><b><?php echo number_format($_SESSION['totSaldoDol'],2); ?></b></td>        
    <td><b>USD</b></td>        
</tr>
<tr style="<?php echo $colorSaldoBs?>">
    <td colspan="4"><b>Total Saldo VEF.</b></td>        
    <td><b><?php echo number_format($_SESSION['totSaldoBs'],2); ?></b></td>        
    <td><b>VEF</b></td>        
</tr>
