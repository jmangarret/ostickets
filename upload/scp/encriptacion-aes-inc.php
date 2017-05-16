<?php

	// 3/01/2017 RURIEPE - FUNCION PARA ENCRIPTAR DATOS
    function encriptar_AES($string, $key)
    {
      $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
      $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM );
      mcrypt_generic_init($td, $key, $iv);
      $encrypted_data_bin = mcrypt_generic($td, $string);
      mcrypt_generic_deinit($td);
      mcrypt_module_close($td);
      $encrypted_data_hex = bin2hex($iv).bin2hex($encrypted_data_bin);
      return $encrypted_data_hex;
    }
  // 3/01/2017 RURIEPE - FIN

  // 3/01/2017 - DESENCRIPTAR LOS VALORES PASADOS POR URL. NOMBRE Y CORREO ELECTRONICO
    function desencriptar_AES($encrypted_data_hex, $key)
    {
      $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
      $iv_size_hex = mcrypt_enc_get_iv_size($td)*2;
      $iv = pack("H*", substr($encrypted_data_hex, 0, $iv_size_hex));
      $encrypted_data_bin = pack("H*", substr($encrypted_data_hex, $iv_size_hex));
      mcrypt_generic_init($td, $key, $iv);
      $decrypted = mdecrypt_generic($td, $encrypted_data_bin);
      mcrypt_generic_deinit($td);
      mcrypt_module_close($td);
      return $decrypted;
    }
  // 3/01/2017 - FIN

  function limpiar_cadena($string)
  {
    $string = str_replace("\n","[NEWLINE]",$string);
    $string=htmlentities($string);
    $string=preg_replace('/[^(\x20-\x7F)]*/','',$string);
    $string=html_entity_decode($string);     
    $string = str_replace("[NEWLINE]","\n",$string);
    return $string;
  }

?>