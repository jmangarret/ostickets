<?php
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    // 3/01/2017 RURIEPE - CAPTURA DE VALORES
        $id_ticket = $_GET['enu'];
        $id_staff = $_GET['sff'];
    // 3/01/2017 RURIEPE -FIN

    // 3/01/2017 RURIEPE - DESENCRIPTAR NOMBRE Y CORREO
        include_once('encriptacion-aes-inc.php');

        $clave = "krycekvsmulder";

        $nombre_desencriptado = desencriptar_AES($_GET['en'],$clave);
        $correo_desencriptado = desencriptar_AES($_GET['ec'],$clave);
    // 3/01/2017 RURIEPE - FIN
?>

<!-- 23/12/2016 RURIEPE - ALERT PARA ACERPTAR O NO LOS TERMINOS Y CONDICIONES-->
    <!DOCTYPE html> 
    <html> 
        <head> 

            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
            <title>Tuagencia24.com</title> 

            <!--23/12/2016 RURIEPE - LLAMADO DE LIBRERIA JQUERY-->
            <script src="../../upload/js/jquery-1.12.0.js"></script>

            <!--23/12/2016 RURIEPE - LLAMADO DE LIBRERIA PARA ALERT ALERTIFY-->
            <script type="text/javascript" src="../include/alertify/js/alertify.js"></script>

            <!--23/12/2016 RURIEPE - LLAMADO DE CSS PARA DISEÑO DE ALERT ALERTIFY-->
            <link rel="stylesheet" href="../include/alertify/css/alertify.css" />
            <link rel="stylesheet" href="../include/alertify/css/default.css" />
        </head>

        <body>

        <div id="mensaje">
He leido y acepto los terminos y condiciones de Tuagencia24
        </div>

            <input type="hidden" name="numticket" id="numticket" value="<?php echo $id_ticket?>">
            <input type="hidden" name="nomcliente" id="nomcliente" value="<?php echo $nombre_desencriptado?>">
            <input type="hidden" name="corrcliente" id="corrcliente" value="<?php echo $correo_desencriptado?>">
            <input type="hidden" name="asesor" id="asesor" value="<?php echo $id_staff?>">
           
       
            <script type="text/javascript" >
      
                $('#mensaje').hide();

                var pre = document.createElement('pre');

                pre.style.maxHeight = "400px";
                pre.style.overflowWrap = "break-word";
                pre.style.margin = "-16px -16px -16px 0";
                pre.style.paddingBottom = "24px";
                pre.appendChild(document.createTextNode($('#mensaje').text()));

                var numticket = $("#numticket").val();
                var nomcliente = $("#nomcliente").val();
                var corrcliente = $("#corrcliente").val();
                var asesor = $("#asesor").val();

                alertify.confirm(pre, function()
                {
               
                    var respuesta = "El cliente ha aceptado los terminos y condiciones.";
                    jQuery.ajax(
                    {
                        url: 'cargar_respuesta.php',
                        type: 'POST',
                        data: ('numticket='+numticket+'&respuesta='+respuesta+'&nomcliente='+nomcliente+'&corrcliente='+corrcliente+'&asesor='+asesor),
                        success: function(data)
                        { 
                            //alert(data);
                            location.href="mensaje_terminos.html";
                        },
                    });
                },
                function()
                {
                    var respuesta = "El cliente no esta de acuerdo con los terminos y condiciones";

                    jQuery.ajax(
                    {
                        url: 'cargar_respuesta.php',
                        type: 'POST',
                        data: ('numticket='+numticket+'&respuesta='+respuesta+'&nomcliente='+nomcliente+'&corrcliente='+corrcliente+'&asesor='+asesor),
                        success: function(data)
                        { 
                            //alert(data);
                            location.href="mensaje_terminos.html";
                        },
                    });
                }).setting('labels',{'ok':'Acepto', 'cancel': 'No Acepto'});
            </script>
        </body>
    </html>
<!-- 23/12/2016 RURIEPE - FIN-->


