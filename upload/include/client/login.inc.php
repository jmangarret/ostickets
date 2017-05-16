<?php

    if(!defined('OSTCLIENTINC')) die('Access Denied');

    $email=Format::input($_POST['luser']?:$_GET['e']);
    $passwd=Format::input($_POST['lpasswd']?:$_GET['t']);

    $content = Page::lookup(Page::getIdByType('banner-client'));

    if ($content) 
    {
        list($title, $body) = $ost->replaceTemplateVariables( array($content->getName(), $content->getBody()));
    }
    else 
    {
        $title = __('Sign In');
        $body = __('To better serve you, we encourage our clients to register for an account and verify the email address we have on record.');
    }
?>

<?php 
    if($errors['err']) 
    { 
        $error = $errors['err']; 
    ?>
      
    <script>
        //09/12/2016 RURIEPE - PERSONALIZACION DE ALERTS Y CAPTURA DE VARIABLE PHP
        var error = "<?php echo $error; ?>";
        swal("Error", error , "error");
    </script>
<?php 
    }
    elseif($msg) 
    { 
?>
    <script>
        //09/12/2016 RURIEPE - PERSONALIZACION DE ALERTS Y CAPTURA DE VARIABLE PHP
        var msg = "<?php echo $msg; ?>";
        swal("Notice", msg , "error");
    </script>
<?php 
    }
    elseif($warn) 
    { 
?>
    <script>
        //09/12/2016 RURIEPE - PERSONALIZACION DE ALERTS Y CAPTURA DE VARIABLE PHP
        var warn = "<?php echo $warn; ?>";
        swal("Warning", warn , "error");
    </script>
<?php 
    } 
?>
<script type="text/javascript">

    $(document).ready(function() 
    {
        $("#login_user").hide(); 
        $("#sat_free").click(function(event) 
        {
            $("#background").addClass("cont");
            $("#login_general").hide();             
            $("#login_user").show();
        });
    });         
</script>

<div class="login__form" id="login_general">
    <button type="submit" class="login__submit" id="sat_free">
        <b>Satélite / Freelance</b>
    </button>  
    <br><br>
     <a href="<?php echo ROOT_PATH; ?>scp/">
        <button type="button" class="login__agente">
            <b>Asesor TuAgencia24.com</b>
        </button>
    </a>
    <br><br><br><br>
    <div id="footer">
        <p style="color: white;">Copyright &copy; <?php echo date('Y'); ?> <?php echo (string) $ost->company ?: 'osTicket.com'; ?> - All rights reserved.</p>
        <a id="poweredBy" href="http://osticket.com" target="_blank"><?php echo __('Helpdesk software - powered by osTicket'); ?></a>
    </div>       
</div> 

<form action="login.php" method="POST" autocomplete="off">
    <?php csrf_token(); ?>

    <div class="login__form" id="login_user">
    <p style="font-size: 22px; color: white; text-shadow: -1px -1px 1px #000, 2px -2px 2px #000, -1px 1px 1px #000, 2px 2px 2px #000;">Satélite / Freelance</p>
    <br>
        <div class="login__row">
            <svg class="login__icon name svg-icon" viewBox="0 0 20 20">
                <path d="M0,20 a10,8 0 0,1 20,0z M10,0 a4,4 0 0,1 0,8 a4,4 0 0,1 0,-8" />
            </svg>
            <input type="text" class="login__input name" placeholder="Email ó Nombre de usuario" id="luser" name="luser"/>
        </div>
        <div class="login__row">
            <svg class="login__icon pass svg-icon" viewBox="0 0 20 20">
                <path d="M0,20 20,20 20,8 0,8z M10,13 10,16z M4,8 a6,8 0 0,1 12,0" />
            </svg>
            <input type="password" class="login__input pass" placeholder="Contraseña" id="lpasswd" name="lpasswd"/>
        </div>
        <br>
        <button type="submit" class="login__submit">
            <b>Iniciar Sesión</b>
        </button> 
        <br>
        <div id="footer">
        	<p style="color: white;">Copyright &copy; <?php echo date('Y'); ?> <?php echo (string) $ost->company ?: 'osTicket.com'; ?> - All rights reserved.</p>
        	<a id="poweredBy" href="http://osticket.com" target="_blank"><?php echo __('Helpdesk software - powered by osTicket'); ?></a>
    	</div>       
    </div> 

</form>
