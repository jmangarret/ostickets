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
                <input type="text" size="30" name="fechadepago" id="fechadepago" value="" required>
            </td>        
            <td width="150" class="required">  
                Concepto:
            </td>
            <td>
                <input type="text" size="30" name="concepto" id="concepto_de_pago" value="" required>                                      
            </td>
        </tr>
        <tr>
            <td width="150" class="required">
               Metodo de Pago:
            </td>
            <td>
                <select name="paymentmethod" id="paymentmethod" required>
                    <option value="">Seleccione...</option>
                </select>
            </td>
            <td width="150" class="required">
               Referencia Bancaria:
            </td>
            <td>
                <input type="text" size="30" name="referencia" value="" required>
            </td>
        </tr>
        <tr>
            <td width="150" class="required">
               Banco Emisor:
            </td>
            <td>
                <select name="bancoemisor" id="bancoemisor" required>
                    <option value="">Seleccione...</option>
                </select>
            </td>
            <td width="150" class="required">
               Banco Receptor:
            </td>
            <td>
                <select name="bancoreceptor" id="bancoreceptor" required>
                    <option value="">Seleccione...</option>
                </select>
            </td>
        </tr>
        <tr>
            <td width="150" class="required">
               Moneda:
            </td>
            <td>
                <select name="currency" id="currency" required>                    
                </select>

            </td>
            <td width="150" class="required">
               Monto Pagado:
            </td>
            <td>
                <input type="text" size="30" name="amount" value="" required>
            </td>        
        </tr>    
    </tbody>
</table>
<p style="text-align:center;">
    <input type="submit" name="submit" id="save" value="Agregar pago">
    <input type="reset"  name="reset2"  value="Restablecer">    
    <input type="hidden" name="user_id"  value="<?php echo $user_id; ?>">            
</p>
</form>

<div id="result_pagos"></div>

<script src="js/pagos.js"></script>
