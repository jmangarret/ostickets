$(document).ready(function() {
    //Select nro de ticket - eliminado
    $.ajax({
        data: {select: "nrodeticket"},
        type: "POST",
        url: 'include/select_tickets.php',
        success: function(response){                   
            $("#nrodeticket").append(response);
            $("#nrodeticket").select2();
        }
    });    
    //Select metodo de pago
    $.ajax({
        data: {select: "paymentmethod"},
        type: "POST",
        url: 'include/funciones/selects.crm.php',
        success: function(response){                                                                  
            $("#paymentmethod").append(response);
            $("#paymentmethod").select2();
        }
    });
	//Select banco emisor
    $.ajax({
        data: {select: "bancoemisor"},
        type: "POST",
        url: 'include/funciones/selects.crm.php',
        success: function(response){                                                                  
            $("#bancoemisor").append(response);
            $("#bancoemisor").select2();
        }
    });
	//Select banco receptor
	$.ajax({
        data: {select: "bancoreceptor"},
        type: "POST",
        url: 'include/funciones/selects.crm.php',
        success: function(response){                                                                  
            $("#bancoreceptor").append(response);
            $("#bancoreceptor").select2();
        }
    });
	//Select moneda de pago
	$.ajax({
        data: {select: "currency"},
        type: "POST",
        url: 'include/funciones/selects.crm.php',
        success: function(response){                                                                  
            $("#currency").append(response);
            $("#currency").select2();
        }
    });
	//Calendario fecha de pago
    $(function() {
        $("#fechadepago").datepicker();
        $("#fechadepago" ).datepicker('option', {dateFormat: 'dd-mm-yy'});
    });
    //Boton Restablecer formulario de pago
    $("input[name='reset2']").click(function(){        
        $("#paymentmethod").val('').trigger('change');
        $("#bancoemisor").val('').trigger('change');
        $("#bancoreceptor").val('').trigger('change');
        $("#currency").val('').trigger('change');
    });
    //Boton agregar pago - formulario submit
    $("#form_pago").submit(function(event){      
     var parametros = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "include/pagos/guardar_pago_temp.php",
            data: parametros,
             beforeSend: function(objeto){
                $("#result_pagos").html("<b>Registrando pago...</b>");
              },
            success: function(response){
                if (response==="Exito"){
                    $.ajax({
                        type: "POST",
                        url: "include/pagos/listar_pago_temp.php",
                        data: parametros,
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
    //Boton cerrar modal pagos - open.inc.php 
    $("#close-pagos").click(function(event){ 
     var parametros = $("#form_pago").serialize();   
        $.ajax({
            type: "POST",
            url: "include/pagos/listar_pago_temp.php",
            data: parametros,
            success: function(response){
                $("#listadoPagos").html(response);                              
            }
        });     
    });
//fin document ready
});

//Icono Eliminar en lista pago
function eliminarPago(id){        
    $.ajax({
        type: "POST",
        url: "include/pagos/eliminar_pago_temp.php",
        data: {"id": id},
        success: function(response){                
            $("#close-pagos").click();
        }
    });  
}

        