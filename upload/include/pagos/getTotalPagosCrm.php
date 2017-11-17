<?php
session_start();
?>
<tr>
    <td colspan="4"><b>Total Pagos USD.</b></td>        
    <td><b><?php echo number_format($_SESSION['totTarifaDol'],2); ?></b></td>        
    <td><b>USD</b></td>        
</tr>
<tr>
    <td colspan="4"><b>Total Pagos VEF.</b></td>        
    <td><b><?php echo number_format($_SESSION['totTarifaBs'],2); ?></b></td>        
    <td><b>VEF</b></td>        
</tr>