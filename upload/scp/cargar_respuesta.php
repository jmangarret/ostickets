<?php
    
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
    
    // 4/01/2017 RURIEPE - CAPTURA DE VALORES
        $numticket = $_REQUEST['numticket'];
        $respuesta = $_REQUEST['respuesta'];
        $nomcliente = $_REQUEST['nomcliente'];
        $corrcliente = $_REQUEST['corrcliente'];
        $asesor = $_REQUEST['asesor'];
        $ip_address = $_SERVER["REMOTE_ADDR"];
        $fecha_actual=date("Y-m-d h:i:s");
        $_REQUEST['respuesta'] = 'respuesta_cliente';

    // 4/01/2017 RURIEPE - FIN
     
    // 4/01/2017 RURIEPE - LIMPIAR CADENA PARA ELIMINAR LOS ESPACIOS AL FINAL.
        include_once('encriptacion-aes-inc.php');

        $nombre_cliente = limpiar_cadena($nomcliente);
        $correo_cliente = limpiar_cadena($corrcliente);

    // 4/01/2017 RURIEPE - FIN

    // 26/12/2016 RURIEPE - SE DEFINE DIRECTORIO
   define("INCLUDE_DIR","/home/admin/public_html/ostickets/upload/include/");

    // 26/12/2016 RURIEPE - ARCHIVO DE CONFIGURACION PARA REAILAZAR CONEXION A BASE DE DATOS
    require_once("../include/ost-config.php");


    // 26/12/2016 RURIEPE - CONEXION A BASE DE DATOS
        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

        if (mysqli_connect_errno()) 
        {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
    // 26/12/2016 RURIEPE - FIN

    // 5/01/2017 RURIEPE - CONSULTA PARA OBTENER EL NOMBRE Y ID DEL USUARIO
        $consulta_usuario="SELECT us.name,us.id, ue.address FROM  ost_user AS us
        INNER JOIN ost_user_email AS ue ON ue.id = us.default_email_id 
        WHERE us.name = '".$nombre_cliente."' AND ue.address='".$correo_cliente."'";
        $result = $mysqli->query($consulta_usuario);
        $row = $result->fetch_array();

        $usuario = $row['name'];
        $id_usuario = $row['id'];
    // 5/01/2017 RURIEPE - FIN

    // 6/01/2017 RURIEPE - CONSULTA PARA OBTENER EL CORREO DEL ASESOR
        $consulta_staff="SELECT firstname, lastname, email FROM ost_staff WHERE staff_id = ".$asesor;
        $result = $mysqli->query($consulta_staff);
        $row = $result->fetch_array();

        $email_asesor = $row['email'];
    // 6/01/2017 RURIEPE - FIN

    // 6/01/2017 RURIEPE - CONSULTA PARA OBTENER EL NUMERO DE TICKET
        $consulta_ticket="SELECT tic.number FROM ost_ticket AS tic WHERE tic.ticket_id = $numticket";
        $result = $mysqli->query($consulta_ticket);
        $row = $result->fetch_array();

        $number = $row['number'];
    // 6/01/2017 RURIEPE - FIN

    // 5/01/2017 RURIEPE - CONSULTA PARA OBTENER EL NOMBRE Y ID DEL USUARIO
        if($usuario)
        {
            $ost_thread = $mysqli->query("INSERT INTO ost_ticket_thread
            (pid,
            ticket_id,
            staff_id,
            user_id,
            thread_type,
            poster,
            source,
            title,
            body,
            format,
            ip_address,
            created,updated)
            VALUES
            (0,
            $numticket,
            $asesor,
            $id_usuario,
            'R',
            '$usuario',
            ' ',
            'Terminos y Condiciones',
            '$respuesta',
            'html',
            '$ip_address',
            '$fecha_actual',
            '0000-00-00 00:00:00');");

            $_REQUEST['respuesta'];
            include_once('../include/PHPMailer/enviar_email.php');

            /*$enviar_a = array(
            array('correo' => 'ruriepe18@gmail.com', 'nombre_correo' => 'Asesor')
            //array('correo' => 'info@tuagencia24.com', 'nombre_correo' => 'Tu Agencia 24')
            );*/
            $asunto = 'Terminos y Condiciones Tuagencia24.com';
            $mensaje = '<table>
              <tr>
                <th>-----------------------------------------------------------------------------------------------------------------------------------------------------------------</th>
              </tr>
              <tr>
                <th style="font-size:12pt;"><i>Terminos y condiciones Tuagencia24.com</i></th>
              </tr>
              <tr>
                <th>-----------------------------------------------------------------------------------------------------------------------------------------------------------------</th>
              </tr>
              <tr>
                <th style="font-size:12pt; text-align:center;">El cliente: <b>'.$nombre_cliente.'</b> ha cargado su respuesta al ticket <a href="'.$_SERVER["HTTP_HOST"].'/upload/scp/tickets.php?id='.$numticket.'">#'.$number.'</a></div>.</th>
              </tr>
              <tr>
                <th>-----------------------------------------------------------------------------------------------------------------------------------------------------------------</th>
              </tr>
            </table>';
            //$mensaje= 'Terminos y Condiciones Tuagencia24.com';
            $correo = 'ruriepe18@gmail.com';
 
            // 19/10/2016 RURIEPE - LLAMADO DE FUNCION Y ENVIO DE LOS VALORES POR PARAMETRO, PARA REALILZAR EL ENVIO DEL CORREO MEDIANTE PHPMAILER
           
            $envio=enviarEmail($correo,$asunto,$mensaje);
        }
    // 5/01/2016 RURIEPE - FIN
?>