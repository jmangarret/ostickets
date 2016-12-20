<?php
    include_once(INCLUDE_DIR.'staff/login.header.php');
    $info = ($_POST && $errors)?Format::htmlchars($_POST):array();
?>

        <form action="login.php" method="POST" autocomplete="off">
            <?php csrf_token(); ?>
            <input type="hidden" name="do" value="scplogin">
            <div class="login__form">

                <script>
                    //14/12/2016 RURIEPE - PERSONALIZACION DE ALERTS Y CAPTURA DE VARIABLE PHP
                    var msg = "<?php echo $msg; ?>";

                    if(msg != "Autentificación Requerida")
                    {
                        swal("Error", msg , "error");
                    }  
                </script>
    
                <div class="login__row">
                    <svg class="login__icon name svg-icon" viewBox="0 0 20 20">
                        <path d="M0,20 a10,8 0 0,1 20,0z M10,0 a4,4 0 0,1 0,8 a4,4 0 0,1 0,-8" />
                    </svg>
                    <input type="text" class="login__input name" name="userid" id="name" value="<?php echo $info['userid']; ?>" placeholder="Email ó Nombre de usuario" autocorrect="off" autocapitalize="off"/>
                </div>
                <div class="login__row">
                    <svg class="login__icon pass svg-icon" viewBox="0 0 20 20">
                        <path d="M0,20 20,20 20,8 0,8z M10,13 10,16z M4,8 a6,8 0 0,1 12,0" />
                    </svg>
                    <input type="password" class="login__input pass" name="passwd" id="pass" placeholder="<?php echo __('Password'); ?>" autocorrect="off" autocapitalize="off" />
                </div>
                <button type="submit" class="login__submit2" name="submit">
                    <b>Iniciar Sesión</b>
                </button>  
                <a href="/ostickets/upload/">
                    <button type="button" class="login__agente2" >
                        <b>Soy Satélite / Freelance</b>
                    </button>
                </a>  
                <div id="footer">
                    <p>Copyright &copy; <?php echo date('Y'); ?> <?php echo (string) $ost->company ?: 'osTicket.com'; ?> - All rights reserved.</p>
                    <a id="poweredBy" href="http://osticket.com" target="_blank"><?php echo __('Helpdesk software - powered by osTicket'); ?></a>
                </div>      
            </div> 
        </form>
        <?php
            $ext_bks = array();
            foreach (StaffAuthenticationBackend::allRegistered() as $bk)
            if ($bk instanceof ExternalAuthentication)
                $ext_bks[] = $bk;

            if (count($ext_bks)) 
            { 
        ?>
                <div class="or">
                    <hr/>
                </div>
        <?php
                foreach ($ext_bks as $bk) 
                { 
        ?>
                    <div class="external-auth"><?php $bk->renderExternalLink(); ?></div>
        <?php
                }
            } 
        ?>
    </body>
</html>

