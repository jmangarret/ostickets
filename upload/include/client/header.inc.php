<?php
$title=($cfg && is_object($cfg) && $cfg->getTitle())
    ? $cfg->getTitle() : 'osTicket :: '.__('Support Ticket System');
$signin_url = ROOT_PATH . "login.php"
    . ($thisclient ? "?e=".urlencode($thisclient->getEmail()) : "");
$signout_url = ROOT_PATH . "logout.php?auth=".$ost->getLinkToken();

header("Content-Type: text/html; charset=UTF-8");
?>
<!DOCTYPE html>
<html <?php
if (($lang = Internationalization::getCurrentLanguage())
        && ($info = Internationalization::getLanguageInfo($lang))
        && (@$info['direction'] == 'rtl'))
    echo 'dir="rtl" class="rtl"';
?>>
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
    <link rel="stylesheet" href="<?php echo ROOT_PATH; ?>scp/css/typeahead.css?c1b5a33"
         media="screen" />
    <link type="text/css" href="<?php echo ROOT_PATH; ?>css/ui-lightness/jquery-ui-1.10.3.custom.min.css?c1b5a33"
        rel="stylesheet" media="screen" />
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
    <?php
    if($ost && ($headers=$ost->getExtraHeaders())) {
        echo "\n\t".implode("\n\t", $headers)."\n";
    }
    ?>
    <style type="text/css">
        body {
            background-image: url("images/tuagencia24.jpg");
            background-color: #FFF;
            background-repeat: no-repeat;
            -webkit-background-position: 50% 450px;/* (background-size: 100% 950px)*/
            -moz-background-position: 50% 450px;
            -o-background-position: 50% 450px;
            background-position: 50% 450px;
            background-size: 50% 50%;
        }
    </style>
</head>
<body>
    <div id="container">
        <div id="header">
            <div class="pull-right flush-right">
            <p>
             <?php
                if ($thisclient && is_object($thisclient) && $thisclient->isValid()
                    && !$thisclient->isGuest()) {
                 echo Format::htmlchars($thisclient->getName()).'&nbsp;|';
                 ?>
                <!--<a href="<?php echo ROOT_PATH; ?>profile.php"><?php echo __('Profile'); ?></a>|--> 
                <a href="<?php echo ROOT_PATH; ?>tickets.php"><?php echo sprintf(__('Tickets <b>(%d)</b>'), $thisclient->getNumTickets()); ?></a> -
                <a href="<?php echo $signout_url; ?>"><?php echo __('Sign Out'); ?></a>
            <?php
            } elseif($nav) {
                if ($cfg->getClientRegistrationMode() == 'public') { ?>
                    <?php echo __('Guest User'); ?> | <?php
                }
                if ($thisclient && $thisclient->isValid() && $thisclient->isGuest()) { ?>
                    <a href="<?php echo $signout_url; ?>"><?php echo __('Sign Out'); ?></a><?php
                }
                elseif ($cfg->getClientRegistrationMode() != 'disabled') { ?>
                    <a href="<?php echo $signin_url; ?>"><?php echo __('Sign In'); ?></a>
<?php
                }
            } ?>
            </p>
            <p>
<?php
if (($all_langs = Internationalization::availableLanguages())
    && (count($all_langs) > 1)
) {
    $count = 0;
    foreach ($all_langs as $code=>$info) {
        if($count == 0) {$count++;continue;}
        list($lang, $locale) = explode('_', $code);
?>
        <a  href="?<?php echo urlencode($_GET['QUERY_STRING']); ?>&amp;lang=<?php echo $code;
            ?>" title="<?php echo Internationalization::getLanguageDescription($code); ?>"><img src="images/vzla.png" height="13px" width="25px"></a>
<?php }
} ?>
            </p>
            </div>
            <a class="pull-left" id="logo" href="<?php echo ROOT_PATH; ?>index.php"
            title="<?php echo __('Support Center'); ?>">
                <span class="valign-helper"></span>
               <!-- <img src="<?php echo ROOT_PATH; ?>logo.php" border=0 alt="<?php
                echo $ost->getConfig()->getTitle(); ?>">-->
                <img src="images/tuagencia24.jpg" border=0 alt="comision">
                <!--<img src="images/comision.gif" border=0 alt="comision">-->
            </a>
        </div>
        <div class="clear"></div>
        <!-- jmangarret 23sept2016 integracion de cinta: cambio del dia CRM. -->
            <?php
            if ($_SESSION['_auth']['user']['id']>0){
                $conex=mysql_connect(DBHOST, DBUSER, DBPASS);            
                $sqlCintaCrm="SELECT announcement FROM vtigercrm600.vtiger_announcement";
                $qryCintaCrm= mysql_query($sqlCintaCrm);
                $rowCintaCrm=mysql_fetch_row($qryCintaCrm);            
                mysql_close($conex);            
                echo "<div style='color:#0F64B4; background-color:#FAF250; font-family:Arial; font-weight:bold'>";
                echo "<marquee scrolldelay=200>";
                echo "Administrador: ";
                echo $rowCintaCrm[0];
                echo "</marquee>";
                echo "</div>";
            }                
            ?>          
        <!-- Fin cinta cambio del dia BD CRM. -->
        <?php
        if($nav){ ?>
        <ul id="nav" class="flush-left"><?php
            if($nav && ($navs=$nav->getNavLinks()) && is_array($navs)){
                foreach($navs as $name =>$nav) {/*MICOD: URL modificada*/
                    echo sprintf('<li><a class="%s %s" href="%s">%s</a></li>%s',$nav['active']?'active':'',$name,(ROOT_PATH.$nav['href']."?clean=1"),$nav['desc'],"\n");
                    //ORIGINAL!!!--->echo sprintf('<li><a class="%s %s" href="%s">%s</a></li>%s',$nav['active']?'active':'',$name,(ROOT_PATH.$nav['href']),$nav['desc'],"\n");
                }
                //echo '<li><a class="tickets" href="#">Emisiones</a></li>';
            } 
            ?>
        </ul>
        <?php
        }else{ ?>
         <hr>
        <?php
        } 
        ?>
        <div id="content">

         <?php if($errors['err']) { ?>
            <div id="msg_error"><?php echo $errors['err']; ?></div>
         <?php }elseif($msg) { ?>
            <div id="msg_notice"><?php echo $msg; ?></div>
         <?php }elseif($warn) { ?>
            <div id="msg_warning"><?php echo $warn; ?></div>
         <?php } ?>

<!--

<?php

if(isset($_SESSION["_auth"]["user"]["id"])){

    $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

    $open = "SELECT ticket.ticket_id,ticket.`number`,ticket.dept_id,isanswered, dept.ispublic, cdata.subject,dept_name, status.name 
                as status, status.state, ticket.source, ticket.created ,count(attach_id) as attachments FROM ost_ticket ticket 
                LEFT J4IN ost_ticket_status status ON (status.id = ticket.status_id) LEFT JOIN ost_ticket__cdata cdata 
                ON (cdata.ticket_id = ticket.ticket_id) LEFT JOIN ost_department dept ON (ticket.dept_id=dept.dept_id) 
                LEFT JOIN ost_ticket_collaborator collab ON (collab.ticket_id = ticket.ticket_id AND collab.user_id = ".$thisclient->getId()." ) 
                LEFT JOIN ost_ticket_attachment attach ON ticket.ticket_id=attach.ticket_id WHERE ( ticket.user_id= ".$thisclient->getId()." OR collab.user_id= ".$thisclient->getId()." ) 
                AND status.state IN ('open') AND cast(cdata.localizador as char(100) charset utf8) LIKE '%%' 
                GROUP BY ticket.ticket_id ORDER BY ticket.created ASC";
    $result_open = $mysqli->query($open);
    $n_abiertos = mysqli_num_rows($result_open);


    $close = "SELECT ticket.ticket_id,ticket.`number`,ticket.dept_id,isanswered, dept.ispublic, cdata.subject,dept_name, status.name 
                as status, status.state, ticket.source, ticket.created ,count(attach_id) as attachments FROM ost_ticket ticket 
                LEFT JOIN ost_ticket_status status ON (status.id = ticket.status_id) LEFT JOIN ost_ticket__cdata cdata 
                ON (cdata.ticket_id = ticket.ticket_id) LEFT JOIN ost_department dept ON (ticket.dept_id=dept.dept_id) 
                LEFT JOIN ost_ticket_collaborator collab ON (collab.ticket_id = ticket.ticket_id AND collab.user_id = ".$thisclient->getId()." ) 
                LEFT JOIN ost_ticket_attachment attach ON ticket.ticket_id=attach.ticket_id WHERE ( ticket.user_id= ".$thisclient->getId()." OR collab.user_id= ".$thisclient->getId()." ) 
                AND status.state IN ('closed') AND cast(cdata.localizador as char(100) charset utf8) LIKE '%%' 
                GROUP BY ticket.ticket_id ORDER BY ticket.created ASC";
    $result_close = $mysqli->query($close);
    $n_cerrados = mysqli_num_rows($result_close);

}

?>

<script type="text/javascript">
    $("#nav li a:eq(2)").text("Tickets (<?=($n_cerrados+$n_abiertos)?>)");
</script>   

-->