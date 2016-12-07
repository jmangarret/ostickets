<?php

    //7/11/2016 RURIEPE - SE DEFINE DIRECTORIO
    define("INCLUDE_DIR","/home/admin/public_html/ostickets/upload/include/");

    //7/11/2016 RURIEPE - ARCHIVO DE CONDIGURA PARA REAILAZAR CONEXION A BASE DE DATOS
    require_once("../include/ost-config.php");


    //7/11/2016 RURIEPE - CONEXION A BASE DE DATOS
        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

        if (mysqli_connect_errno()) 
        {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
    //7/11/2016 RURIEPE - FIN

    //7/11/2016 RURIEPE - CONSULTA PARA OBTENER EL NOMBRE Y CORREO DEL USUARIO CREADO EN EL TICKET
        $consulta_usuario="SELECT us.name, ue.address FROM ost_ticket AS tic
        INNER JOIN ost_user AS us ON us.id = tic.user_id
        INNER JOIN ost_user_email AS ue ON ue.id = us.default_email_id 
        WHERE tic.ticket_id = ".$_GET["id"];
        $result = $mysqli->query($consulta_usuario);
        $row = $result->fetch_array();

        $nombre_cliente = $row['name'];
        $correo = $row['address'];
    //7/11/2016 RURIEPE - FIN

        $server = $_SERVER['SERVER_NAME'];
?>


<!-- 0/11/2016 RURIEPE - HTML PARA CREAR FORMULARIO PARA EL ENVIO DE VALORES PARA CREACION DE PDF-->
    <!DOCTYPE html> 
    <html> 
        <head> 
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> 
            <title>Datos de cliente para creación de pdf</title> 

            <!--7/11/2016 RURIEPE - LLAMADO DE LIBRERIA JQUERY-->
            <script src="../../upload/js/jquery-1.12.0.js"></script>
             <!--17/11/2016 RURIEPE - LLAMADO DE LIBRERIA PARA ALERT ALERTIFY-->
            <script type="text/javascript" src="../include/alertify/js/alertify.js"></script>
      
            <!--7/11/2016 RURIEPE - LLAMADO DE CSS PARA DISEÑO DE FORMULARIO-->
            <link href="css/estilo_formulario.css" rel="stylesheet" />
            <!--7/11/2016 RURIEPE - LLAMADO DE CSS PARA DISEÑO DE ALERT ALERTIFY-->
            <link rel="stylesheet" href="../include/alertify/css/alertify.core.css" />
            <link rel="stylesheet" href="../include/alertify/css/alertify.default.css" />
        </head>
        <body> 
            <div class="contact_form"> 
                <ul> 
                    <li> 
                        <h2 >Datos del cliente</h2> 
                    </li> 
                    <li> 
                        <label for="nombre_cliente">Nombre:</label> 
                        <input type="text" name="nombre_cliente" id="nombre_cliente" value="<?php echo $nombre_cliente;?>" required /> 
                    </li> 
                    <li> 
                        <label for="correo_cliente">Correo electrónico:</label> 
                        <input type="email" name="correo_cliente" id="correo_cliente" value="<?php echo $correo;?>" required />
                        <input type="hidden" id="id" value="<?php echo $_GET["id"];?>" />
                        <input type="hidden" id="staff_id" value="<?php echo $_GET["staffid"];?>" />
                    </li> 
                    <li> 
                        <label for="nota_tarifa">Notas de tarifa:</label> 
                        <textarea name="nota_tarifa" id="nota_tarifa" cols="40" rows="6" required></textarea> 
                    </li> 
                    <br>
                    <button class="submit" type="submit" id="ticket-pdf">Generar PDF</button> 
                    <div id="cargando"></div>      
                </ul>
            </div> 
        </body>

        <script type="text/javascript" >
            $(document).ready(function() 
            { 
                var servername = "<?php echo $server; ?>" ; 

                // 7/11/2016 RURIEPE - EVENTO PARA DETECTAR CUANDO SE REALICE CLICK AL BOTON Generar PDF
                    $('#ticket-pdf').click(function()
                    { 
        	           var nombre_cliente = $("#nombre_cliente").val();
                       var correo_cliente = $("#correo_cliente").val();
                       var nota_tarifa = $("#nota_tarifa").val();
                       var id = $("#id").val();
                       var staff_id = $("#staff_id").val();

            	       jQuery.ajax(
            	       {
                	       url: 'terminos.php',
                	       type: 'POST',
                	       data: ('nombre_cliente='+nombre_cliente+'&correo_cliente='+correo_cliente+'&nota_tarifa='+nota_tarifa+'&id='+id+'&staff_id='+staff_id),
                            beforeSend: function()
                            {
                                //08/11/2016 RURIEPE - SE OCULTA EL BOTON AL MOMENTO DE SE PRESIONA Y SE INDICA QUE EL SISTEMA EST GENERAN EL PDF
                                $('#ticket-pdf').hide();
                                $('#cargando').html('<div align="center"><b><h2>Generando PDF...</h2></b></div>');
                            },
                	       success: function(filename)
                	       { 
                                //alert(filename);
                                //18/11/2016 RURIEPE - CONDICION QUE EVALUA EL VALOR QUE ES LEIDO DEL ARCHIVO TERMINOS.PHP, SI ESTE VALOR ES FALSE ES INDICA QUE EL RCHIVO PDF YA EXISTE POR ENDE SE LE INDICA UN MENSAJE AL USUARIO Y SE CIERRA LA VENTANA EMERGENTE, EN CASO CONTRARIO SE REALILA LA CREACION DEL PDF, SE ENVIA AL CORREO DEL CLIENTE Y SE VISUALIZA.
                                if(filename != "false")
                                {

                                    //08/11/2016 RURIEPE - SE CIERRA VENTANA EMERGENTE,SE APERTURA EL PDF CREADO Y SE ACTULIZA VENTANA PADRE
                                        $('#cargando').html(); 
                                        window.close();
                                        window.opener.document.location="tickets.php?id="+id;
                                        window.open('terminoscliente/'+filename, '_black');      
                                    //08/11/2016 RURIEPE - FIN
                                }
                                else
                                {
                                   alertify.alert("<b>Ya existe un términos y condiciones creado</b>", function () 
                                   {
                                        window.close();
                                    });
                                } 
                                //18/11/2016 RURIEPE - FIN
                	       },
                	       error: function()
                	       {                     
                    	       alert('Ha ocurrido un error');
                	       }
            	       });
                    });
                // 7/11/2016 RURIEPE - FIN
            }); 
        </script> 
    </html>
<!-- 0/11/2016 RURIEPE - FIN-->