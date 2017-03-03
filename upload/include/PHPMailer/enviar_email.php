<?php

 	/*error_reporting(E_ALL);
  	ini_set('display_errors', '1');*/

  	require 'PHPMailerAutoload.php';

	//03/11/2016 RURIEPE- CONFIGURACION DE ENVIO DE CORREO ELECTRONICO
		function enviarEmail($email,$asunto,$mensaje,$asesor)
		{
			$server_username = "info@tuagencia24.com";
			$mail = new PHPMailer;

			//03/11/2016 RURIEPE - CONFIGURACION DE SMTP (Protocolo para la Transferencia Simple de Correo) 
				$mail->isSMTP();
				$mail->SMTPDebug = 0;
				$mail->Debugoutput = 'html';
				$mail->Host = 'smtp.gmail.com';
				$mail->Port = 587;
				$mail->SMTPSecure = 'tls';
				$mail->SMTPAuth = true;
				$mail->Username = $server_username;
				$mail->Password = "AUDEtuagencia24";
			//03/11/2016 RURIEPE - SMTP

			//Usamos el SetFrom para decirle al script quien envia el correo
			$mail->SetFrom($server_username, "Tu Agencia 24");

			//Con respuesta a  Ases@r y  Info
			$mail->addReplyTo($asesor,"Asesor");
			$mail->addReplyTo("info@tuagencia24.com","TuAgencia24");

			//Usamos el AddAddress para agregar un destinatario
			$mail->AddAddress($email, "Cliente - Pasajero");

			//Con copia a info
			$mail->AddCC("info@tuagencia24.com");

			//Ponemos el asunto del mensaje
			$mail->Subject = $asunto;
 
			//Contenido del correo
			$mail->MsgHTML($mensaje);

			//Adjuntar archivo
			$url ="terminoscliente/".$_REQUEST['filename'];
			$mail->AddAttachment($url,"Terminos y Condiciones.pdf");

			//Enviamos el correo
			if(!$mail->send()) 
			{
  				//echo "Hubo un error: " . $correo->ErrorInfo;
  				$enviado = 0;
			} 
			else 
			{
  				//echo "Mensaje enviado con exito.";
  				$enviado=1;

			}
			return $enviado;
		}

	//03/11/2016 RURIEPE - FIN
?>
	


	
