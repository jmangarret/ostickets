   $("#fm tr:eq(11) td:eq(0) div:eq(0)").css("display","block"); //10/02/2016 Billy se sumo 1 al tr

   function setLimiteCredito(limite,disponible,fecha){    
        $("#fm tr:eq(11) td:eq(0) div:eq(0)").prepend(  //10/02/2016 Billy se sumo 1 al tr
        "<div style='text-align:right;display:block;'>"+
            "L&iacute;mite de Cr&eacute;dito Total: <b>"+limite+"</b>"+
            "<br>"+
            "Disponible: <b>"+disponible+"</b><br>"+
            "Actualizado al "+fecha+            
        "</div>");    
   }

   
    $('input:eq(2)').keypress(function (e) {
        var regex = new RegExp("^[a-zA-Z0-9]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str))
            return true;
        e.preventDefault();
        return false;
    });

/*Inicio Billy 29/01/2016 Validacion de campo numerico en numero de tarjeta de credito y en cedula*/   
    $('input:eq(3),input:eq(6)').keypress(function (e) {
        var regex = new RegExp("^[0-9]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str))
            return true;
        e.preventDefault();
        return false;
    });

/*Fin Billy 29/01/2016 Validacion de campo numerico en numero de tarjeta de credito y en cedula*/


/*Inicio Billy 29/01/2016 Funcion dar formato de moneda al input del monto de la tarjeta de credito y valida que no sean letras*/
    jQuery(function($) {
        $("input:eq(7)").autoNumeric({aSep: '.', aDec: ','});
    });
/*Fin Billy 29/01/2016 Funcion dar formato de moneda al input del monto de la tarjeta de credito y valida que no sean letras*/


/*Inicio Billy 29/01/2016 Validacion de campo numerico y / en la fecha de vencimiento de la tarjeta de credito*/    
    $('input:eq(4)').keypress(function (e) {
        var regex = new RegExp("^[0-9/]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str))
            return true;
        e.preventDefault();
        return false;   
    });
/*Fin Billy 29/01/2016 Validacion de campo numerico y / en la fecha de vencimiento de la tarjeta de credito*/ 


/*Inicio Billy 5/02/2016 Validacion de campo caracter en banco y nombre*/
    $('input:eq(5)').keypress(function (e) {
        var regex = new RegExp("^[a-zA-Z ]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str))
            return true;
        e.preventDefault();
        return false;
    });

/*Fin Billy 5/02/2016 Validacion de campo caracter en banco y nombre*/

    $("tr:eq(3),tr:eq(4),tr:eq(5),tr:eq(6),tr:eq(7),tr:eq(8),tr:eq(9),tr:eq(10),tr:eq(11),tr:eq(13)").hide(0); //Billy 5/02/2016 Agregamos que el campo monto y status localizador se encuentre no visible al cargar el formulario
    $("input:eq(2),input:eq(5)").css("text-transform","uppercase");
    $("select:eq(1)").empty();
    $("select:eq(1)").append('<option value="">— Select —</option>');
    $("select:eq(0),select:eq(1)").prop('required',true);
    
    $("input:eq(2)").change(function(){$("input:eq(2)").val().toUpperCase();});
    $("input:eq(5)").change(function(){$("input:eq(5)").val().toUpperCase();});

    $("input:eq(2)").attr("pattern","[A-Za-z0-9]{6}");
    $("input:eq(2)").attr("title","6 digitos alfanumericos");

/*Inicio Billy 29/01/2016 Titulo al input de nº tarjeta de credito, fecha de vencimiento, cedula y monto tarjeta de credito*/
    $("input:eq(3)").attr("title","Sólo 16 digitos numéricos");
    $("input:eq(4)").attr("title","Ejemplo 01/16");
    $("input:eq(6)").attr("title","Sólo 8 digitos numéricos");
    $("input:eq(7)").attr("title","Sólo números y para agregar decimales utilice (,)");
/*Fin Billy 29/01/2016 Titulo al input de nº tarjeta de credito, fecha de vencimiento, cedula y monto tarjeta de credito*/


/*Inicio Billy 29/01/2016 Validacion de maxima longitud al input de nº tarjeta de credito, fecha de vencimiento y cedula*/
    $("input:eq(3)").attr("maxlength","16"); 
    $("input:eq(4)").attr("maxlength","5");
    $("input:eq(6)").attr("maxlength","8");
/*Fin Billy 29/01/2016 Validacion de maxima longitud al input de nº tarjeta de credito, fecha de vencimiento y cedula*/

    //onChange original Select Tipo de Solicitud (Help Topic)
    /* 
    $("#topicId").change(function(){
        alert("change 1");
        var data = $(':input[name]', '#dynamic-form').serialize();
        $.ajax(
          'ajax.php/form/help-topic/' + this.value, //url_get linea 40 ajax.php
          {
            data: data,
            dataType: 'json',
            success: function(json) {
              $('#dynamic-form').empty().append(json.html);
              $(document.head).append(json.media);
            }
          });
    });
    */
    //onChange Select Tipo de Solicitud (Help Topic)
    $("select:eq(0)").change(function(){        
        $.ajax({
            data: { menu : $("select:eq(0) option:selected").val() },
            type: "POST",
            url: 'include/client/ajax_login.php',
            success: function(response){
                $("select:eq(1)").empty();
                $("select:eq(1)").append(response);
            }
        });
        if($("select:eq(0)").val() == 19){
            //$("tr:eq(3)").show("slow");
            //$("select:eq(2)").prop('required',true); Se deshabilita required GDS
            $("input:eq(9)").val("Pendiente");
        }
        else if($("select:eq(0)").val() == 20){
            $("tr:eq(3),tr:eq(4),tr:eq(5),tr:eq(6),tr:eq(7),tr:eq(8),tr:eq(9),tr:eq(10)").hide("slow");
            $("input:eq(9),input:eq(7),input:eq(6),input:eq(5),input:eq(4),input:eq(3),input:eq(2)").val("");
            $("input").removeAttr('required');
            $("#codigo").remove();
            $("select:eq(2)").val("");
            $("select:eq(2)").removeAttr('required');
        }
        else{
            $("tr:eq(6),tr:eq(7),tr:eq(8),tr:eq(9),tr:eq(10)").hide("slow");
            $("input:eq(7),input:eq(6),input:eq(5),input:eq(4),input:eq(3),input:eq(9)").val("");
            $("input:eq(7),input:eq(6),input:eq(5),input:eq(4),input:eq(3)").removeAttr('required');
            $("#codigo").remove();
            $("tr:eq(3),tr:eq(4),tr:eq(5)").hide("slow");
            $("select:eq(2)").removeAttr('required');
            $("select:eq(2)").val("");
        }

    });
    //onChange Select Detalle de Solicitud
    $("select:eq(1)").change(function(){    
        if(($("select:eq(0)").val() == 19 && $("select:eq(1)").val() != 23) || ($("select:eq(0)").val() == 21 && $("select:eq(1)").val() == 33)){
            if($("select:eq(0)").val() == 19 && $("select:eq(1)").val() != 23){
                //$("tr:eq(10)").show("slow"); ////////////Billy 5/02/2016 se quito el input de la cedula para que no aparezca cuando el tipo de solicitud sea emitir localizador
                $("input:eq(2)").prop('required',true);
                $("tr:eq(3)").show("slow");
            }else{
                $("input:eq(2)").removeAttr('required');
                $("input:eq(2)").val("");
            }   
            $("tr:eq(4)").show("slow");
            $("input:eq(2)").prop('required',true);
        }else{
            $("tr:eq(4)").hide("slow");
            $("input:eq(2)").removeAttr('required');
            $("input:eq(2)").val("");
        }
        
        if($("select:eq(1)").val() == 19 || $("select:eq(1)").val() == 26){
            $("tr:eq(5)").show("slow");
            $("select:eq(3)").prop('required',true);
            $("input:eq(2)").prop('required',true);
            $("select:eq(2)").prop('required',true);
        }else{
            $("tr:eq(5)").hide("slow");
            $("select:eq(2)").removeAttr('required');
            $("select:eq(3)").removeAttr('required');
            $("select:eq(3)").val("");
        }

        if($("select:eq(1)").val() == 31){
            $("input:eq(2)").removeAttr('required');
            $("select:eq(2)").removeAttr('required');
            $("tr:eq(3)").hide(0);
            $("tr:eq(4)").hide(0);
        }
        if($("select:eq(0)").val() != 19){
            $("input:eq(2)").removeAttr('required');
            $("select:eq(2)").removeAttr('required');
        }
        //jmangarret ago2017 - onchange para abrir modal pagos: 
        //opciones pago de reporte en curso y pago de reporte pasado (credito)
        if ($(this).val()==27 || $(this).val()==28){            
            //jmangarret 23ago2017 -  Condicion tipo de satelite - function getTipoSatelite
            if ($("#tipoSatelite").text()=="Verificar Credito"){
                $('.modal-content').css('width','800px');
                $('.modal-dialog').css( 'margin-left','25%');
                $('#modalPagos').modal('show');          
            }            
        }                
        //opciones emitir localizador (pago adjunto)
        if ($(this).val()==19){            
            //jmangarret 23ago2017 -  Condicion tipo de satelite - function getTipoSatelite
            if ($("#tipoSatelite").text()=="Pago Adjunto"){
                $('.modal-content').css('width','800px');
                $('.modal-dialog').css( 'margin-left','25%');
                $('#modalPagos').modal('show');          
            }            
        }

    });

    $('select:eq(3)').change(function(){
        if( $('select:eq(3)').val() == 14 || $('select:eq(3)').val() == 50){
            $("input:eq(6),input:eq(5),input:eq(4),input:eq(3)").prop('required',true);
            $("tr:eq(6),tr:eq(7),tr:eq(8),tr:eq(9),tr:eq(10)").show("slow");
            $("td:eq(10)").append("<small id='codigo' style='display:none;'>Para c&oacute;digo de seguridad de TDC y autorizaci&oacute;n, contactar por tel&eacute;fono.</small>");
            $("td:eq(16)").append("<small id='codigo1' style='display:none;'>Ejemplo 01/16</small>"); /*Billy 29/01/2016 Ejemplo de como se debe llenar la fecha de vencimiento de la tarjeta de credito*/
            $("#codigo").show("slow");
            $("#codigo1").show("slow"); /*Billy 29/01/2016 Ejemplo de como se debe llenar la fecha de vencimiento de la tarjeta de credito*/
        }
        else{
            $("tr:eq(6),tr:eq(7),tr:eq(8),tr:eq(9),tr:eq(10)").hide("slow");
            $("input:eq(6),input:eq(5),input:eq(4),input:eq(3)").val("");
            $("input:eq(6),input:eq(5),input:eq(4),input:eq(3)").removeAttr('required');
            $("#codigo").remove();
        }
        if( $('select:eq(3)').val() == 50){
            $("input:eq(7)").prop('required',true);
            $("tr:eq(11)").show("slow"); //Billy 5/02/2016 Agrego el campo de monto si la opcion es tdc + cash
            
        }
        else{
            $("tr:eq(11)").hide("slow"); ////Billy 5/02/2016 quito el campo de monto si la opcion es tdc
            $("input:eq(7)").val("");
            $("input:eq(7)").removeAttr('required');
        }
    });

    $.ajax({
        data: { menu : $("select:eq(0) option:selected").val() },
        type: "POST",
        url: 'include/client/ajax_login.php',
        success: function(response){
            $("select:eq(1)").empty();
            $("select:eq(1)").append(response);
        }
    });

    $("tr:eq(4) td:eq(1)").append("<div id='repeat' style='display:none;color:#F00;'><big><br>El ticket no puede ser creado. Localizador duplicado. Contacte a su asesor.<br><br></big></div>");

    function valSelects(disponible){
        $('input:eq(2),select:eq(0),select:eq(1),select:eq(2)').change(function(){            
            if($('select:eq(0)').val() == 19 && $('select:eq(1)').val() == 19 && $('input:eq(2)').val() != "" && parseFloat(disponible) > 0){
                $.ajax({
                    data: { menu : "localizador", localizador : $('input:eq(2)').val(), gds : $('select:eq(2)').val() },
                    type: "POST",
                    url: 'include/client/ajax_login.php',
                    success: function(response){
                        if(response == 1){
                            $("#repeat").show("slow");
                            $("#create").hide();
                        }
                        else{
                            $("#repeat").hide();
                            $("#create").show();
                        }
                    }
                });
            }
            else{
                $("#repeat").hide();
            }
        });    
    }
    

    $("#create").click(function(){
        if($("select:eq(0)").val() != "" && $("select:eq(1)").val() != "" && $("div").eq(10).text() == ""){
            $("div").eq(10).prepend("<b>"+$('select:eq(0) :selected').text()+" - "+$('select:eq(1) :selected').text()+"</b><br><br>");
        }
    });

    $("input:eq(2)").attr("pattern","[A-Za-z0-9]{6}");

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