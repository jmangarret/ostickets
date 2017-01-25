<?php
  
  /*error_reporting(E_ALL);
  ini_set('display_errors', '1');*/

  // 2/11/2016 RURIEPE - SE DEFINE DIRECTORIO
  define("INCLUDE_DIR","/home/admin/public_html/ostickets/upload/include/");

  // 13/10/2016 RURIEPE - SE INCLUYE ARCHIVO PARA HACER USO DE LA LIBRERIA DOMPDF
  require_once("../include/DOMpdf/dompdf_config.inc.php");
 
  // 7/11/2016 RURIEPE - ARCHIVO DE CONDIGURA PARA REAILAZAR CONEXION A BASE DE DATOS
  require_once("../include/ost-config.php");


  // 7/11/2016 RURIEPE - VARIABLES PARA CAPTURAR LOS DATOS A USAR
    $id_ticket = $_POST['id'];
    $nombre_cliente = $_POST['nombre_cliente'];
    $correo_cliente = $_POST['correo_cliente'];
    $NotaTarifa = nl2br($_POST['nota_tarifa']);
    $staffid = $_POST['staff_id'];
    $ip_address = $_SERVER["REMOTE_ADDR"];
    $fecha_actual=date("Y-m-d h:i:s");
    $fecha_pdf = date("Y-m-d");
    $_REQUEST['respuesta'] = 'envio a cliente';
  // 7/11/2016 RURIEPE - FIN

  // 7/11/2016 RURIEPE - CONEXION A BASE DE DATOS
    $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

    if (mysqli_connect_errno()) 
    {
      printf("Connect failed: %s\n", mysqli_connect_error());
      exit();
    }
  // 7/11/2016 RURIEPE - FIN

  // 28/11/2016 RURIEPE - CREAR CARPETA terminoscliente CON PERMISOS 775, EN CASO DE NO EXISTIR
    $carpeta = 'terminoscliente';
    if (!file_exists($carpeta)) 
    {
      mkdir($carpeta, 0775);
    }
  // 28/11/2016 RURIEPE - FIN

  // 16/11/2016 RURIEPE - CONSULTA PARA OBTENER EL NUMERO DE TICKET
    $consulta_number="SELECT number FROM ost_ticket WHERE ticket_id = ".$id_ticket;
    $result = $mysqli->query($consulta_number);
    $row = $result->fetch_array();
    $ticket_number = $row['number'];
  // 16/11/2016 RURIEPE - FIN

  // 16/11/2016 RURIEPE - CREACION DEL NOMBRE DEL ARCHIVO PDF
    $cliente =str_replace(' ', '', $nombre_cliente);
    $filename = "TerminosCondiciones".$ticket_number."_".$fecha_pdf."_".$cliente.".pdf";
    $_REQUEST['filename'] = $filename;
  // 16/11/2016 RURIEPE - FIN

  // 13/10/2016 RURIEPE - CUERPO DEL CONTENIDO DEL PDF SE CONCATENA EN LA VARIABLE HTML
    $html="<header>";
      $html.="
      <div  style='text-align:left; border: #58ABE5 10px groove; padding: 0px 25px 60px 25px; margin: 0.9mm 0.9mm 0.9mm 2mm;'>
        <br><br>
        <div style='font-size:14pt;'><center>CONDICIONES Y TERMINOS GENERALES DE TUAGENCIA24.</center></div>
        <br><br>
        <div style='font-size:12pt;'>ACUERDO ENTRE EL CLIENTE <b>".$nombre_cliente."</b> Y TUAGENCIA24.</div>
        <br>
        <div style='color: #419AD6; font-size:14pt; font-weight:bold;'>HUMBERMAR TOURS, C.A INFORMA:</div>
        <ul type = square>
          <div style='text-align:justify; font-size:12pt;'> 
            <li> 
            Al efectuar reservas de vuelos a través de TUAGENCIA24, Usted garantiza que es mayor de edad;  posee plena capacidad para celebrar contratos; solo utiliza el sitio www.tuagencia24.com para reservar pasajes aéreos para Usted o para otra persona para quien Usted tenga autorización de actuar; toda la información que Usted brinda a TUAGENCIA24 es verídica, exacta, actual y completa.
            <br><br>
            <li> 
            Cuando Usted reserva por nuestro intermedio, contrata a través nuestro con la aerolínea. Por ello, estas condiciones son las de carácter general que se aplican a los servicios de intermediación que le presta TUAGENCIA24 y bajo ningún aspecto reemplazan o modifican las limitaciones de responsabilidad establecidas por la aerolínea ni a las condiciones especiales o específicas que cada línea aérea ha definido para el(los) ticket(s) que Usted haya adquirido.
            <br><br>
            <li> 
            Manténgase atento a la información que se le enviará al correo electrónico que nos proporcionó como punto de contacto. 
            <br><br>
            <li> 
            Es su responsabilidad informarse sobre la documentación que usted podría necesitar o las personas que viajen junto a usted para poder realizar su viaje de acuerdo a lo planeado. Verifique con tiempo si necesita: pasaporte, visa, vacunas, permisos para menores de edad u otras exigencias de documentación, tanto para los países destinos como los países en tránsito. Si la requiere y no cuenta con la misma, haga los trámites necesarios para obtenerla con anticipación. Se le recomienda que antes de reservar un boleto y partir, consulte con la Embajada o el Consulado correspondiente por el trámite de su pasaporte, visa o tiempo de estadía ya que los requisitos varían de país bien sea país destino o en tránsito. Es su responsabilidad cumplir con todos los requisitos de entrada exigidos por el país destino o en tránsito.
            <br><br>
            <li> 
            TUAGENCIA24, dejan constancia que su actividad de intermediación en la venta de pasajes aéreos no los hace responsable por los hechos derivados de caso fortuito o fuerza mayor, incluyendo fenómenos climáticos, hechos de la naturaleza,  entre otros, que pudieran acontecer antes o durante el desarrollo del vuelo, y que pudieran eventualmente demorar, interrumpir o impedir la ejecución del mismo.
          </div> 
        </ul>
        <br>
        <div style='color: #419AD6; font-size:14pt; font-weight:bold;'>TERMINOS Y CONDICIONES GENERALES:</div>
        <ol type=I>
          <div style='text-align:justify; font-size:12pt;'>
            <li>
            SERVICIOS PRESTADOS POR  TUAGENCIA24.
            <br><br>
            TUAGENCIA24 es una agencia de viaje que intermedia en la contratación de los servicios de las aerolíneas que operan con nosotros. Nuestro deber es informarle acerca de las características de los itinerarios, gestionar sus solicitudes de reservas, informar sobre los valores correspondientes a las tarifas.
            <br><br>
            TUAGENCIA24 no son proveedores de los vuelos, ni presta tales servicios. La información suministrada relativa a precios, cualidades y características de los vuelos, disponibilidad, condiciones de venta, restricciones, políticas de cancelación o reembolso, entre otras, son impuestas por la propia aerolínea. Cualquier reclamo, demanda o denuncia por la prestación del servicio escogido deberá ser dirigida en contra de dichas aerolíneas, siendo TUAGENCIA24 un tercero ajeno.
            <br><br><br>
            <li>
            INICIO Y FINAL DE LOS SERVICIOS.
            <br><br>
            Los servicios ofrecidos por TUAGENCIA24, son  aéreos, se inician en el momento que el cliente nos contacta y finaliza en el momento en el cliente recibe su boleto y llega a su destino final.           
            <br><br>          
            <li>
            PRECIO DE LOS BOLETOS.
            <br><br>
            Cuando usted reserva uno o más vuelos a través de TUAGENCIA24, usted debe de cancelar el precio o tarifa fijada por la aerolínea, más el cargo por el servicio de intermediación y emisión de  tiquetes. Como el precio o tarifa pertenece a la aerolínea, del pago recaudado TUAGENCIA24 solo percibe el valor correspondiente al servicio de intermediación o cargo por servicio. El precio por el o los pasajes es endosado por TUAGENCIA24 a la  aerolínea.
            <br><br>
            <li>
            PROCESO DE RESERVA. 
            <br><br>
            El buen término de la gestión de la reserva y la confirmación de la compra, dependen del pago íntegro y oportuno del precio y tarifa de todos los servicios. Mientras no se confirme el pago total de la reserva por el medio original de pago, la reserva quedará en suspenso y podría ser cancelada por el proveedor por falta de pago. La emisión de los pasajes y su facturación, representan la confirmación de su reserva.
            <br><br>
            Por razones ajenas a TUAGENCIA24, las aerolíneas podrían modificar ciertas condiciones de los vuelos como horarios o fechas; podrían cancelarlos o reprogramarlos. De producirse alguna de estas variaciones,   TUAGENCIA24,  le informará las alternativas disponibles, y sólo procederá a efectuar nuevas reservas a nuevos valores con su expresa confirmación o aceptación previa.
            <br><br>
            Tenga en cuenta que al adquirir un boleto aéreo, usted adquiere el derecho a viajar, razón por la cual de no presentarse al aeropuerto en tiempo oportuno, salvo que las condiciones establezcan lo contrario, <b>usted podrá no tener devolución alguna del precio abonado.</b>
            <br><br>
            <li>
            DE LOS CAMBIOS O ANULACIONES Y PENALIDADES.
            <br><br>
            La forma de proceder y las condiciones especiales que rigen para casos de anulaciones por desistimiento o de cambio de decisión del consumidor, son determinadas por las aerolíneas. Revíselas antes de reservar, recuerde que hay servicios que por disposición de las aerolíneas no admiten, cambios, ni anulaciones o cancelaciones.
            <br><br>
            En caso de anulación de boleto emitido, el cargo por servicio de intermediación no estará sujeto a reintegro, ya que corresponde a un servicio efectivamente prestado por  TUAGENCIA24, el que es diferente del valor del vuelo contratado con la aerolínea. La postergación o adelanto de las fechas originalmente contratadas, se rigen por las modalidades, condiciones y disponibilidad de cada aerolínea, algunas de los cuales para cambios o cancelaciones exigen el pago de una penalidad.
            <br><br>
            Los cambios, anulaciones o cancelaciones de reservas efectuadas por nuestro intermedio, deben ser gestionados a través de TUAGENCIA24. En cualquiera de los casos anteriores, la aerolínea lo derivará a nuestra empresa. Los cambios de fecha gestionados por intermedio de TUAGENCIA24 constituyen un nuevo servicio y generan cargo por servicio de intermediación.
            <br><br>
            <b>NOTAS DE TARIFAS: </b><br><br>".$NotaTarifa."
            <br><br><br>
            <li>
            DE LAS REGLAS Y RESTRICCIONES. 
            <br><br>
            Cada aerolínea tiene sus propias regulaciones tarifarias. Preste atención a los siguientes puntos: 
            <br><br>
            ° Algunos tipos de boletos y aerolíneas disponen que para poder realizar un cambio, anulación o cancelación, el interesado debe de pagar una penalidad, como condición para poder realizar alguna de las solicitudes mencionadas. Cada aerolínea define su penalidad sin intervención de TUAGENCIA24. En caso de cambio, adicional, podría existir una diferencia de tarifa. Debe de informarse antes de solicitar algún cambio, ya que existen tarifas que no permiten cambio de fecha ni devoluciones por disposición de la aerolínea.
            <br><br>
            Adicionalmente, tenga presente que si nos solicita un cambio, nos estará encomendando una nueva gestión de intermediación, por lo que TUAGENCIA24 realizarán un nuevo cobro por concepto de cargo por servicio por la prestación de sus servicios de intermediación en este cambio.
            <br><br>
            TUAGENCIA24 no se responsabilizan por robo o hurto,  pérdida de equipaje y demás efectos personales de los pasajeros. Le aconsejamos contratar un seguro de viaje para cubrir parte de estos riesgos.
            <br><br>
            E. TICKETS “SOLO IDA”: por disposiciones migratorias de cada país, en caso de ser no residente del país de destino y contratar por nuestro intermedio un pasaje únicamente de ida, Usted podrá ser requerido por las autoridades migratorias a justificar las razones por las que no cuenta con pasaje de regreso. Le sugerimos consultar al consultado correspondiente antes de efectuar la reserva. Las aerolíneas también podrán exigir esta documentación antes de embarcar. Recuerde preste mucha atención a las restricciones o regulaciones de su(s) tarifa (s) antes y al momento de reservar.
            <br><br>
            <li>
            DE LAS DEVOLUCIONES POR ANULACIONES O CANCELACIONES.
            <br><br> 
            Si Usted solicita o requiere una anulación o cancelación, la procedencia y los valores correspondientes serán determinados por la aerolínea en base a las regulaciones del o las tarifas. Los plazos de reembolso o reintegro dependerán de cada aerolínea dentro del marco legal definido en el Convenio de Montreal y el Código Aeronáutico. Cuando le informemos el resultado de la solicitud de anulación o cancelación, le informaremos los plazos promedio de reembolso o reintegro de acuerdo el caso.
            <br><br>
            <li>
            ACEPTACION.
            <br><br>
            Usted luego de haber leído todas las condiciones y términos de TUAGENCIA24, declara conocer y aceptar las presentes condiciones y términos generales de contratación y    dicha aceptación queda ratificada con él envió de aceptación del contrato, anexando el recibo de pago del boleto. 
          </ol> 
        </div>
      </div>
    </header>";
  // 13/10/2016 RURIEPE - FIN

  // 17/11/2016 RURIEPE - VALIDACION DE ARCHIVO PDF SI EXISTE O NO
    
    // 17/11/2016 RURIEPE - SE COLOCA LA RUTA DEL DIRRECTORIO DONDE SER UBICAN LOS ARCHIVOS AL LISTAR
    $directorio = 'terminoscliente/';

    // 17/11/2016 RURIEPE - SE HACE UNSO DE LA APLICACION SCANDIR PARA OBTENER(ARRAY) EL NOMBRE DE LOS ARCHIVOS ENCONTRADOS EN EL DIRECTORIO 
    $fichero  = scandir($directorio);

    // 17/11/2016 RURIEPE - CONTAR LA CANTIDAD DE ELEMENTOS DEL ARRAY 
    $contar_array = count($fichero);

    // 17/11/2016 RURIEPE - SE INICIALIZA EL CONTADOR EN 2 DADO A QUE LAS POSICIONES 0 Y 1 NO SERAN TOMADOS PARA EVALUACION
    $i=1;

    // 17/11/2016 RURIEPE - CICLO PARA EVALUAR LOS ELEMENTOS DE CADA POSICION
    do
    {

      // 17/11/2016 RURIEPE - SE AUMENTA CONTADOR
      $i++;

      // 17/11/2016 RURIEPE - SE BUSCA EN LA CADENA OBTENIDA EL NUMERO DE TICKET
      $archivo_encontrado = strpos($fichero[$i], $ticket_number);

    }while($contar_array != $i  &&  $archivo_encontrado == ''); 

    // 17/11/2016 RURIEPE - SI EL NUMERO DE TICKET NO COINCIDE CON LO ENCONRADO EN EL DIRECTORIO SE PROCEDE A CREAR EL PDF Y EL ENVIO DEL CORREO AL CLIENTE

      if(!$archivo_encontrado)
      {
        // 18/11/2016 RURIEPE - SE REALIZA ECHO A LA VARIABLE FILENAME PARA QUE SEA TOMADO EN EL SUCCESS
        echo $filename;

        // 13/10/2016 RURIEPE - CREACION DE ARCHIVO PDF
  
          // 7/11/2016 RURIEPE - CONSULTA PARA OBTENER EL NOMBRE Y APELLIDO DEL AGENTE CONECTADO 
            $consulta_staff="SELECT firstname, lastname, email FROM ost_staff WHERE staff_id = ".$staffid;
            $result = $mysqli->query($consulta_staff);
            $row = $result->fetch_array();

            $asesor = $row['email'];
          // 7/11/2016 RURIEPE - FIN

          // 7/11/2016 RURIEPE - SE CONCANTENA EL NOMBRE Y APELLIDO EN UNA VRIABLE Y SE CREA UNA VARIABLE CON EL TEXTO PARA AGREGAR A LA TABLA THREAD

            $nombre_staff = $row['firstname'].' '.$row['lastname'];
            $cuerpo = "Se realiza envio de terminos y condiciones al cliente: <b>".$nombre_cliente."</b><br> http://ticket.tuagencia24.com/upload/scp/terminoscliente/".$filename;
          // 7/11/2016 RURIEPE - FIN

          // 5/01/2017 RURIEPE - CONSULTAS SQL: LA PRIMERA PARA OBTENER EL ULTMO ID DEL REGISTRO DE LA TABLA ost_user_email Y LA SEGUNDA PARA OBTENER EL NOMBRE Y EL CORREO DEL USUARIO.

            $consulta_id_email="SELECT MAX(id) FROM  ost_user_email";
            $result = $mysqli->query($consulta_id_email);
            $row = $result->fetch_array();

            $id_email = $row[0];
            $id = $id_email + 1;

            $consulta_usuario="SELECT us.name, ue.address FROM  ost_user AS us
            INNER JOIN ost_user_email AS ue ON ue.id = us.default_email_id 
            WHERE us.name = '".$nombre_cliente."' OR ue.address='".$correo_cliente."'";
            $result = $mysqli->query($consulta_usuario);
            $row = $result->fetch_array();

            $usuario = $row['name'];
            $correo = $row['address'];

          // 5/01/2017 RURIEPE - FIN

          // 5/01/2017 RURIEPE - CONDICION PARA EVALUAR SI LAS VARIABLES USUARIO Y CORREO TIENEN VALORES, EN CASO CONTRARIO DE NO TENER VALORES ESO INDICA QUE LA CONSULTA NO ENCONTRO RESULTADOS EN LA BASE DE DATOS INDICANDO QUE EL CLIENTE NO SE ENCUENTRA REGISTRADO EN EL SISTEMA.
            
            /*if($usuario == $nombre_cliente OR $correo == $correo_cliente)
            {
              echo "cliente/correo";
            }
            /*else
            {*/


              // Instanciamos un objeto de la clase DOMPDF.
              $mipdf = new DOMPDF();
           
              // Definimos el tamaño y orientación del papel que queremos. O por defecto cogerá el que está en el fichero de configuración.
              $mipdf ->set_paper("legal", "portrait");

              // Cargamos el contenido HTML.
              $mipdf->load_html($html, 'UTF-8');

              // Renderizamos el documento PDF.
              $mipdf ->render();

              $pdf = $mipdf->output();//asignamos la salida a una variable
   
              file_put_contents($filename, $pdf);//colocamos la salida en un archivo

              // 24/10/2016 RURIEPE - SE LE ORTOGA TODOS LOS PERMISOS AL DOCUMENTO CREADO
              chmod($filename, 0777);

              // 17/11/2016 RURIEPE - SE MUEVE ARCHIVO A CARPETA terminoscliente, LUEGO DE SER CREADO
              rename ($filename,"terminoscliente/".$filename); 

              // 7/11/2016 RURIEPE - SE CREA REGISTRO EN LA TABLA OST_THREAD PARA INDICAR Y DEJAS RASTRO DE LA CREACION Y ENVIO DE TERMINOS Y CONDICIONES A CLIENTE

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
                $id_ticket,
                $staffid,
                0,
                'R',
                '$nombre_staff',
                ' ',
                'Terminos y Condiciones',
                '$cuerpo',
                'html',
                '$ip_address',
                '$fecha_actual',
                '0000-00-00 00:00:00');");
              // 7/11/2016 RURIEPE - FIN

              $ost_user_email = $mysqli->query("INSERT INTO ost_user_email
              (id,
              user_id,
              address)
              VALUES
              ($id,
              $id,
              '$correo_cliente');");

              $ost_user = $mysqli->query("INSERT INTO ost_user
              (id,
              org_id,
              default_email_id,
              status,
              name,
              created,
              updated)
              VALUES
              ($id,
              0,
              $id,
              0,
              '$nombre_cliente',
              '$fecha_actual',
              '$fecha_actual');");
             
              //echo("Error insert: " . mysqli_error($mysqli));    
              
              // 18/11/2016 RURIEPE -ENVIO DE CORREO ELECTRONICO

                // 19/10/2016 RURIEPE - SE INCLUYE ARCHIVO enviar_email.php PARA CAPTURAR EL VALOR DE LA VRAIBLE VALIDACION PARA REALILZAR EL ENVIO DEL CORREO
                $_REQUEST['filename'];
                include_once('../include/PHPMailer/enviar_email.php');

                // 19/10/2016 RURIEPE - VARIABLES PARA EL ENVIO DE CORREO ELECTRONICO

                $asunto = "Terminos y Condiciones Tu Agencia 24";

                // 3/01/2017 RURIEPE - ENCRIPTACION DE NOMBRE Y CORREO PARA SER ENVIADO URL
                  include_once('encriptacion-aes-inc.php');
              
                  $clave = "krycekvsmulder";
              
                  $nombre_encriptado = encriptar_AES($nombre_cliente,$clave);
                  $correo_encriptado = encriptar_AES($correo_cliente,$clave);
                // 3/01/2017 RURIEPE - FIN

                $valor = explode("@", $correo_cliente);
              
                if($valor[1] != "hotmail.com" && $valor[1] != "HOTMAIL.COM" && $valor[1] && "Hotmail.com")
                {

                  $mensaje = '<table>
                  <tr>
                    <th>----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</th>
                  </tr>
                  <tr>
                    <th style="font-size:12pt;"><i>Terminos y condiciones Tuagencia24.com</i></th>
                  </tr>
                  <tr>
                    <th>----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</th>
                  </tr>
                  <tr>
                    <th style="font-size:12pt; text-align:justify;">Estimado: <b>'.$nombre_cliente.'</b><br><br> En el siguiente correo usted podra realizar la lectura de los terminos y condiciones generales de TuAgencia24. Luego de realizar la lectura del documento debe ingresar en el siguiente enlace <a href="'.$_SERVER["HTTP_HOST"].'/upload/scp/aceptar_terminos.php?enu='.$id_ticket.'&sff='.$staffid.'&en='.$nombre_encriptado.'&ec='.$correo_encriptado.'">Aceptar terminos y condiciones</a></div> para aceptar.</th>
                  </tr>
                  <tr>
                    <th>----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</th>
                  </tr>
                  </table>';
                }
                else
                {

                  $link = '<b>'.$_SERVER["HTTP_HOST"].'/upload/scp/aceptar_terminos.php?enu='.$id_ticket.'&sff='.$staffid.'&en='.$nombre_encriptado.'&ec='.$correo_encriptado.'</b>';

                  $mensaje = '<p style="font-size:12pt; text-align:justify;">Estimado: <b>'.$nombre_cliente.'</b><br><br> En el siguiente correo usted podra realizar la lectura de los terminos y condiciones generales de TuAgencia24. Luego de realizar la lectura del documento debe coiar el siguiente enlace en la barra de navegacion de su explorador para aceptar <br><a>'.$link.'</a></p>';
                }
 
                // 19/10/2016 RURIEPE - LLAMADO DE FUNCION Y ENVIO DE LOS VALORES POR PARAMETRO, PARA REALILZAR EL ENVIO DEL CORREO MEDIANTE 
                $envio=enviarEmail($correo_cliente,$asunto,$mensaje);
              // 18/11/2016 RURIEPE -FIN
           // }
          //5/01/2017 RURIEPE - FIN 
        // 13/10/2016 RURIEPE -FIN   
      }
      else
      {
       // 18/11/2016 RURIEPE - SE REALIZA ECHO PARA QUE SEA TOMADO EN EL SUCCESS
        echo "false";
      }
    // 17/11/2016 RURIEPE - FIN

  // 17/11/2016 RURIEPE - FIN
?>