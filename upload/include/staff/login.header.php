<?php
defined('OSTSCPINC') or die('Invalid path');
$ruta = "/ostickets/upload/assets/default/css";
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
        <meta http-equiv="refresh" content="7200" />
        <title>osTicket :: <?php echo __('Agent Login'); ?></title>
        <!--<link rel="stylesheet" href="css/login.css" type="text/css" />-->
        <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome.min.css?c1b5a33"/>
        <meta name="robots" content="noindex" />
        <meta http-equiv="cache-control" content="no-cache" />
        <meta http-equiv="pragma" content="no-cache" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
        <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-1.8.3.min.js?c1b5a33"></script>

        <!--14/12/2016 RURIEPE - LIBRERIA JS Y CSS PARA PERSONALIZACION DE ALERTAS-->
            <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/style.css?c1b5a33"/>
            <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/sweetalert.css?c1b5a33"/>
            <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/sweetalert-dev.js?c1b5a33"></script>
        <!--14/12/2016 RURIEPE - FIN-->

        <script type="text/javascript">
            $(document).ready(function() 
            {
                $("input:not(.dp):visible:enabled:first").focus();
            });
        </script>

        <style type="text/css">
            
            #footer
            {
                text-align: center;
                font-size: 11px;
                color: #333;
            }
            #footer a 
            {
                color: #333;
            }
            #footer p 
            {
                margin: 10px 0 0 0;
            }
            #footer #poweredBy 
            {
                display: block;
                width: 126px;
                height: 23px;
                outline: none;
                text-indent: -9999px;
                margin: 0 auto;
                background: url('../assets/default/images/poweredby.png') top left no-repeat;
                background-size: auto 20px;
            }
        </style>
    </head>
    <body>
       <div class="cont_agente">
            <div class="demo">
                <div class="login">
                    <div>
                        <img src="../images/logo.png"  height="130" style="position:relative; top:60px; left:-15px;" >
                    </div>


