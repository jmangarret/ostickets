<script src="js/selects_crm.js"></script>
<style type="text/css">
    input[type=text],select{ width: 160px; }
</style>
<?php if (!$user_id) $user_id=0; ?>
<form action="" method="post" id="form_pago" > 
 <table class="table" width="95%" border="0">
    <tbody>
        <tr>
            <td width="150" class="required">
               Fecha del Pago:
            </td>
            <td>
                <input type="text" size="30" name="fechadepago" id="fechadepago" value="">
            </td>        
            <td width="150" class="required"> 
            <!-- Campo Oculto con id de usuario-->           
            </td>
            <td>            
               <input type="hidden" size="30" name="user_id" value="<?php echo $user_id; ?>">
            </td>
        </tr>
        <tr>
            <td width="150" class="required">
               Metodo de Pago:
            </td>
            <td>
                <select name="paymentmethod" id="paymentmethod">
                    <option value="">Seleccione...</option>
                </select>
            </td>
            <td width="150" class="required">
               Referencia Bancaria:
            </td>
            <td>
                <input type="text" size="30" name="referencia" value="">
            </td>
        </tr>
        <tr>
            <td width="150" class="required">
               Banco Emisor:
            </td>
            <td>
                <select name="bancoemisor" id="bancoemisor">
                    <option value="">Seleccione...</option>
                </select>
            </td>
            <td width="150" class="required">
               Banco Receptor:
            </td>
            <td>
                <select name="bancoreceptor" id="bancoreceptor">
                    <option value="">Seleccione...</option>
                </select>
            </td>
        </tr>
        <tr>
            <td width="150" class="required">
               Moneda:
            </td>
            <td>
                <select name="currency" id="currency">
                    <option value="">Seleccione...</option>
                </select>

            </td>
            <td width="150" class="required">
               Monto Pagado:
            </td>
            <td>
                <input type="text" size="30" name="amount" value="">
            </td>        
        </tr>    
    </tbody>
</table>
<p style="text-align:center;">
    <input type="submit" name="submit" id="save" value="Agregar pago">
    <input type="reset"  name="reset2"  value="Restablecer">    
    <input type="hidden" name="option"  value="create">    
</p>
</form>
<div id="result_pagos"></div>

<script type="text/javascript">
    $(function() {
        $("#fechadepago").datepicker();
        $("#fechadepago" ).datepicker('option', {dateFormat: 'dd-mm-yy'});
    });

    $("input[name='reset2']").click(function(){        
        $("#paymentmethod").val('').trigger('change');
        $("#bancoemisor").val('').trigger('change');
        $("#bancoreceptor").val('').trigger('change');
        $("#currency").val('').trigger('change');
    });

    $("#form_pago").submit(function(event){      
     var parametros = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "include/ajax_ost_pagos.php",
            data: parametros,
             beforeSend: function(objeto){
                $("#result_pagos").html("<b>Registrando pago...</b>");
              },
            success: function(response){
                if (response=="Exito"){
                    $.ajax({
                        type: "POST",
                        url: "include/ajax_ost_pagos.php",
                        data: {
                            "option": "list",
                            "user_id": <?php echo $user_id; ?>
                        },
                        success: function(response){
                            $("#result_pagos").html(response);  
                            $("input[name='reset2']").click();
                        }
                    });
                    
                }else{
                    $("#result_pagos").html(response);
                }            
            }
        });
        event.preventDefault();
    });
</script>

<?php

?>