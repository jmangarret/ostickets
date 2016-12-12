<?php

    $title=($cfg && is_object($cfg) && $cfg->getTitle()) ? $cfg->getTitle() : 'osTicket :: '.__('Support Ticket System');
    $signin_url = ROOT_PATH . "login.php" . ($thisclient ? "?e=".urlencode($thisclient->getEmail()) : "");
    $signout_url = ROOT_PATH . "logout.php?auth=".$ost->getLinkToken();

    header("Content-Type: text/html; charset=UTF-8");
?>

<!DOCTYPE html>
    <html 
        <?php
        if (($lang = Internationalization::getCurrentLanguage()) && 
            ($info = Internationalization::getLanguageInfo($lang)) && 
            (@$info['direction'] == 'rtl'))
            echo 'dir="rtl" class="rtl"';
        ?>
    >
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo Format::htmlchars($title); ?></title>
        <meta name="description" content="customer support platform">
        <meta name="keywords" content="osTicket, Customer support system, support ticket system">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
	    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/osticket.css?c1b5a33" media="screen"/>
        <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/theme.css?c1b5a33" media="screen"/>
        <link rel="stylesheet" href="<?php echo ASSETS_PATH; ?>css/print.css?c1b5a33" media="print"/>
        <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>scp/css/typeahead.css?c1b5a33" media="screen" />
        <link type="text/css" href="<?php echo ROOT_PATH; ?>css/ui-lightness/jquery-ui-1.10.3.custom.min.css?c1b5a33" rel="stylesheet" media="screen" />
        <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/thread.css?c1b5a33" media="screen"/>
        <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/redactor.css?c1b5a33" media="screen"/>
        <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome.min.css?c1b5a33"/>
        <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/flags.css?c1b5a33"/>
        <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/rtl.css?c1b5a33"/>
        <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-1.8.3.min.js?c1b5a33"></script>
        <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-ui-1.10.3.custom.min.js?c1b5a33"></script>
        <script src="<?php echo ROOT_PATH; ?>js/osticket.js?c1b5a33"></script>
        <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/filedrop.field.js?c1b5a33"></script>
        <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery.multiselect.min.js?c1b5a33"></script>
        <script src="<?php echo ROOT_PATH; ?>scp/js/bootstrap-typeahead.js?c1b5a33"></script>
        <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor.min.js?c1b5a33"></script>
        <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor-osticket.js?c1b5a33"></script>
        <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor-fonts.js?c1b5a33"></script>

        <!--9/12/2016 RURIEPE - LIBRERIA JS Y CSS PARA PERSONALIZACION DE ALERTAS-->
            <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/style.css?c1b5a33"/>
            <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/sweetalert.css?c1b5a33"/>
            <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/sweetalert-dev.js?c1b5a33"></script>
        <!--9/12/2016 RURIEPE - FIN-->

        <?php
            if($ost && ($headers=$ost->getExtraHeaders())) 
            {
                echo "\n\t".implode("\n\t", $headers)."\n";
            }
        ?>
    </head>

    <body>
        <div class="cont">
            <div class="demo">
                <div class="login">
                    <div>
                        <img src="images/logo.jpg" width="210" height="200" style="position:relative; top:50px; left:50px;" >
                    </div>