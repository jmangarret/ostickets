<?php
header("Content-Type: text/html; charset=UTF-8");
if (!isset($_SERVER['HTTP_X_PJAX'])) { ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html <?php
if (($lang = Internationalization::getCurrentLanguage())
        && ($info = Internationalization::getLanguageInfo($lang))
        && (@$info['direction'] == 'rtl'))
    echo 'dir="rtl" class="rtl"';
?>>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="cache-control" content="no-cache" />
    <meta http-equiv="pragma" content="no-cache" />
    <meta http-equiv="x-pjax-version" content="<?php echo GIT_VERSION; ?>">
    <title><?php echo ($ost && ($title=$ost->getPageTitle()))?$title:'osTicket :: '.__('Staff Control Panel'); ?></title>
    <!--[if IE]>
    <style type="text/css">
        .tip_shadow { display:block !important; }
    </style>
    <![endif]-->
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/bootstrap.css">
    <script src="<?php echo ROOT_PATH; ?>js/bootstrap.min.js"></script>

    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-1.8.3.min.js?c1b5a33"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery-ui-1.10.3.custom.min.js?c1b5a33"></script>
    <script type="text/javascript" src="./js/scp.js?c1b5a33"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery.pjax.js?c1b5a33"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/filedrop.field.js?c1b5a33"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/jquery.multiselect.min.js?c1b5a33"></script>
    <script type="text/javascript" src="./js/tips.js?c1b5a33"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor.min.js?c1b5a33"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor-osticket.js?c1b5a33"></script>
    <script type="text/javascript" src="<?php echo ROOT_PATH; ?>js/redactor-fonts.js?c1b5a33"></script>
    <script type="text/javascript" src="./js/bootstrap-typeahead.js?c1b5a33"></script>
    <link rel="stylesheet" href="<?php echo ROOT_PATH ?>css/thread.css?c1b5a33" media="all"/>
    <link rel="stylesheet" href="./css/scp.css?c1b5a33" media="all"/>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/redactor.css?c1b5a33" media="screen"/>
    <link rel="stylesheet" href="./css/typeahead.css?c1b5a33" media="screen"/>
    <link type="text/css" href="<?php echo ROOT_PATH; ?>css/ui-lightness/jquery-ui-1.10.3.custom.min.css?c1b5a33"
         rel="stylesheet" media="screen" />
     <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome.min.css?c1b5a33"/>
    <!--[if IE 7]>
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/font-awesome-ie7.min.css?c1b5a33"/>
    <![endif]-->
    <link type="text/css" rel="stylesheet" href="./css/dropdown.css?c1b5a33"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/loadingbar.css?c1b5a33"/>
    <link type="text/css" rel="stylesheet" href="<?php echo ROOT_PATH; ?>css/rtl.css?c1b5a33"/>
    <script type="text/javascript" src="./js/jquery.dropdown.js?c1b5a33"></script>
    <?php
    if($ost && ($headers=$ost->getExtraHeaders())) {
        echo "\n\t".implode("\n\t", $headers)."\n";
    }
    ?>
    <style type="text/css">
        body {
            background-image: url("../images/tuagencia24.jpg");
            background-color: #FFF;
            background-repeat: no-repeat;
            -webkit-background-position: 50% 300px;/* (background-size: 100% 950px)*/
            -moz-background-position: 50% 300px;
            -o-background-position: 50% 300px;
            background-position: 50% 300px;
        }

        #header{
            background-color: #FFE;
        }
    </style>
</head>
<body>
<div id="container">
    <?php
    if($ost->getError())
        echo sprintf('<div id="error_bar">%s</div>', $ost->getError());
    elseif($ost->getWarning())
        echo sprintf('<div id="warning_bar">%s</div>', $ost->getWarning());
    elseif($ost->getNotice())
        echo sprintf('<div id="notice_bar">%s</div>', $ost->getNotice());
    ?>
    <div id="header">
        <p id="info" class="pull-right no-pjax"><?php echo sprintf(__('Welcome, %s.'), '<strong>'.$thisstaff->getFirstName().'</strong>'); ?>
           <?php
            if($thisstaff->isAdmin() && !defined('ADMINPAGE')) { ?>
            | <a href="admin.php" class="no-pjax"><?php echo __('Admin Panel'); ?></a>
            <?php }else{ ?>
            | <a href="index.php" class="no-pjax"><?php echo __('Agent Panel'); ?></a>
            <?php } 
            if($thisstaff->isAdmin()) { ?>
            | <a href="profile.php"><?php echo __('My Preferences'); ?></a>
            <?php } ?>
            | <a href="logout.php?auth=<?php echo $ost->getLinkToken(); ?>" class="no-pjax"><?php echo __('Log Out'); ?></a>
        </p>
        <a href="index.php" class="no-pjax" id="logo">
            <span class="valign-helper"></span>
            <img src="logo.php" alt="osTicket &mdash; <?php echo __('Customer Support System'); ?>"/>
        </a>
    </div>
    <!-- jmangarret 23sept2016 integracion de cinta: cambio del dia CRM. -->
        <?php        

        if ($_SESSION['_auth']['staff']['id']>0){            
            $conex=mysql_connect(DBHOST, DBUSER, DBPASS);            
            $sqlCintaCrm="SELECT announcement FROM vtigercrm600.vtiger_announcement";
            $qryCintaCrm= mysql_query($sqlCintaCrm);
            $rowCintaCrm=mysql_fetch_row($qryCintaCrm);            
            mysql_close($conex);
            echo "<div style='color:#184E81; background-color:#FEFE4C; font-family:Arial; font-weight:bold; position:relative'>";
            echo "<marquee scrolldelay=200>";
            echo "Administrador: ";
            echo $rowCintaCrm[0];            
            echo "</marquee>";
            echo "</div>";        
            //Consultamos la organizacion a la que pertence el agente para luego buscar todos los usuarios de la misma org
            /*
            $sqlOrg="SELECT org_id FROM osticket1911.ost_user WHERE id=".$_SESSION['_auth']['staff']['id'];
            $qryOrg= mysql_query($sqlOrg);
            $rowOrg=mysql_fetch_row($qryOrg);            
            $org_id=$rowOrg[0];    
            */
        }                
        //jmangarret - 12jun2017 - Consulta de emiones por Satelite 
        //Seteamos organizacion 5 (TuAgencia24) para consulta de emisiones por defecto.
        $org_id=5;
        ?>          
    <!-- Fin cinta cambio del dia BD CRM. -->
    <div id="pjax-container" class="<?php if ($_POST) echo 'no-pjax'; ?>">
<?php } else {
    header('X-PJAX-Version: ' . GIT_VERSION);
    if ($pjax = $ost->getExtraPjax()) { ?>
    <script type="text/javascript">
    <?php foreach (array_filter($pjax) as $s) echo $s.";"; ?>
    </script>
    <?php }
    foreach ($ost->getExtraHeaders() as $h) {
        if (strpos($h, '<script ') !== false)
            echo $h;
    } ?>
    <title><?php echo ($ost && ($title=$ost->getPageTitle()))?$title:'osTicket :: '.__('Staff Control Panel'); ?></title><?php
} # endif X_PJAX ?>
    <ul id="nav">
<?php include STAFFINC_DIR . "templates/navigation.tmpl.php"; ?>

    <!--jmangarret - 09-06-2017 - Pestaña para consultar emisiones por satelites - Perfil agentes -->
    <li class="inactive"><a id="emisiones" class="tickets" href="#">Emisiones CRM</a></li>
   <!-- <li class="inactive"><a id="buscadorsoto" class="tickets" href="#">Buscador SOTO</a></li>-->
    <li class="inactive"><a id="cryptoCalculator" class="tickets" href="#">Crypto Calculator</a></li>
    <script type="text/javascript">
    $("#emisiones").click(function(){    
        $("ul#nav li").removeClass("active");
        $("ul#nav li").addClass("inactive");
        $("ul#nav li:nth-child(5)").addClass("active");             

        $("#content").html("Cargando... <img src='images/FhHRx-Spinner.gif'>");                
        $.ajax({
            data: { org_id : <?php echo $org_id ? $org_id : 5; ?>, isStaff : true},
            type: "POST",
            url: '../include/crm/ajax_boletos.php',
            success: function(response){                                                                  
                $("#content").html(response);
                }
            });
    });    
    //jmangarret - 09-07-2017 - Pestaña Buscador SOTO - Perfil agentes -->    
    $("#buscadorsoto").click(function(){        
        $("ul#nav li").removeClass("active");
        $("ul#nav li").addClass("inactive");
        $("ul#nav li:nth-child(6)").addClass("active");             
        $("#content").html("Cargando... <img src='images/FhHRx-Spinner.gif'>");
        $("#content").html("<iframe width=100% height=600 frameborder=0 align=middle src=http://humbermar.aramix.es/AereoBuscador/AereoBuscadorPaso1.aspx?SesionInactiva=1></iframe>");
    });  
     //yohenig - 20-11-2018 - Pestaña Crypto Calculator - Perfil agentes -->   
    $("#cryptoCalculator").click(function(){
            $("#content").html("Cargando... <img src='images/FhHRx-Spinner.gif'>");
            $("#cryptoCalculator").parent().prev().children("a").removeClass("active");
            $("#cryptoCalculator").addClass("active");

            $("#content").html("<iframe width=100% height=800 frameborder=0 align=middle src=http://calc.tuagencia24.com/></iframe>");

    });  
    </script>  
    <!-- Fin buscador soto -->

    </ul>    
    <ul id="sub_nav">
<?php include STAFFINC_DIR . "templates/sub-navigation.tmpl.php"; ?>
    </ul>
    <div id="content">
        <?php if($errors['err']) { ?>
            <div id="msg_error"><?php echo $errors['err']; ?></div>
        <?php }elseif($msg) { ?>
            <div id="msg_notice"><?php echo $msg; ?></div>
        <?php }elseif($warn) { ?>
            <div id="msg_warning"><?php echo $warn; ?></div>
        <?php } ?>
