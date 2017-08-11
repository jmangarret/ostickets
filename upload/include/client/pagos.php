<script src="js/selects_crm.js"></script>
<style type="text/css">
    input[type=text],select{ width: 200px; }
</style>

<form action="" method="post" id="form_pago" > 
 <table class="form_table" width="95%" border="0" cellspacing="5" cellpadding="5">
    <tbody>
        <tr>
            <td width="180" class="required">
              <!-- Nro de Ticket:-->
            </td>
            <td>
            <!--
                <select name="nrodeticket" id="nrodeticket">
                    <option value="">Seleccione...</option>
                </select>                
            -->
            </td>
            <td width="180" class="required">
               Fecha del Pago:
            </td>
            <td>
                <input type="text" size="30" name="fechadepago" id="fechadepago" value="">
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
               Metodo de Pago:
            </td>
            <td>
                <select name="paymentmethod" id="paymentmethod">
                    <option value="">Seleccione...</option>
                </select>
            </td>
            <td width="180" class="required">
               Referencia Bancaria:
            </td>
            <td>
                <input type="text" size="30" name="referencia" value="">
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
               Banco Emisor:
            </td>
            <td>
                <select name="bancoemisor" id="bancoemisor">
                    <option value="">Seleccione...</option>
                </select>
            </td>
            <td width="180" class="required">
               Banco Receptor:
            </td>
            <td>
                <select name="bancoreceptor" id="bancoreceptor">
                    <option value="">Seleccione...</option>
                </select>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
               Moneda:
            </td>
            <td>
                <select name="currency" id="currency">
                    <option value="">Seleccione...</option>
                </select>

            </td>
            <td width="180" class="required">
               Monto Pagado:
            </td>
            <td>
                <input type="text" size="30" name="amount" value="">
            </td>
        
        </tr>        
<!--
        <tr>
            <td width="180" colspan="2">
                Adjuntar pago:
            </td>
        <tr>            
            <td colspan="4">
                <?php
                 include("templates/redactor.tmpl.php") 
                ?>
            </td>
        </tr>
    -->
    </tbody>
</table>
<p style="text-align:center;">
    <input type="submit" name="submit" id="save" value="Agregar pago">
    <input type="reset"  name="reset"  value="Restablecer">
    <input type="button" name="cancel" value="Cancelar">
</p>
</form>
<div id="result_pagos"></div>

<script type="text/javascript">
    $(function() {
        $("#fechadepago").datepicker();
        $("#fechadepago" ).datepicker('option', {dateFormat: 'dd-mm-yy'});
    });

    $("#form_pago").submit(function(event){      
     var parametros = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "include/crm/registrar_pago_crm.php",
            data: parametros,
             beforeSend: function(objeto){
                $("#result_pagos").html("<b>Registrando pago en CRM...</b>");
              },
            success: function(result){
                if (result=="Exito"){
                    alert("Registro de pagos creado en CRM con Exito!")
                    $("#content").load("include/client/pagos.php");
                }else{
                    $("#content").html(response);
                }            
            }
        });
        event.preventDefault();
    });
</script>

<?php

?>