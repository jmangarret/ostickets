<?php
if(!defined('OSTCLIENTINC')) die('Access Denied!');
$info=array();
if($thisclient && $thisclient->isValid()) {
    $info=array('name'=>$thisclient->getName(),
                'email'=>$thisclient->getEmail(),
                'phone'=>$thisclient->getPhoneNumber());
}

$info=($_POST && $errors)?Format::htmlchars($_POST):$info;

$form = null;
if (!$info['topicId'])
    $info['topicId'] = $cfg->getDefaultTopicId();

if ($info['topicId'] && ($topic=Topic::lookup($info['topicId']))) {
    $form = $topic->getForm();
    if ($_POST && $form) {
        $form = $form->instanciate();
        $form->isValidForClient();
    }
}

$count = 0;
foreach ($_POST as $key => $value) {
    if($count < 3 || $count > 3) {$count++;continue;}
    $submenu = $value[0];
    $count++;
}

?>
<h1><?php echo __('Open a New Ticket');?></h1>
<p><?php echo __('Please fill in the form below to open a new ticket.');?></p>
<form id="ticketForm" method="post" action="open.php" enctype="multipart/form-data" onsubmit="">
  <?php csrf_token(); ?>
  <input type="hidden" name="a" value="open">
  <table width="800" cellpadding="1" cellspacing="0" border="0">
    <tbody>
    <tr style="">
        <td class="required"><?php echo __('Tipo de Solicitud');?>:</td>
        <td>
            <select id="topicId" name="topicId" onchange="javascript:
                    var data = $(':input[name]', '#dynamic-form').serialize();
                    $.ajax(
                      'ajax.php/form/help-topic/' + this.value,
                      {
                        data: data,
                        dataType: 'json',
                        success: function(json) {
                          $('#dynamic-form').empty().append(json.html);
                          $(document.head).append(json.media);
                        }
                      });">
                <option value="" selected="selected">&mdash; <?php echo __('Seleccione un tipo de solicitud');?> &mdash;</option>
                <?php
                if($topics=Topic::getPublicHelpTopics()) {
                    foreach($topics as $id =>$name) {
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $id, ($info['topicId']==$id)?'selected="selected"':'', $name);
                    }
                } else { ?>
                    <option value="0" ><?php echo __('General Inquiry');?></option>
                <?php
                } ?>
            </select>
            <font class="error">*&nbsp;<?php echo $errors['topicId']; ?></font>
        </td>
    </tr>
<?php
        if (!$thisclient) {
            $uform = UserForm::getUserForm()->getForm($_POST);
            if ($_POST) $uform->isValid();
            $uform->render(false);
        }
        else { ?>
            <!--<tr><td colspan="2" width="25%"><hr /></td></tr>
        <tr><td><?php echo __('Email'); ?>:</td><td><?php echo $thisclient->getEmail(); ?></td></tr>
        <tr><td><?php echo __('Client'); ?>:</td><td><?php echo $thisclient->getName(); ?></td></tr>-->
        <?php } ?>
    </tbody>
    <tbody id="dynamic-form">
        <?php if ($form) {
            include(CLIENTINC_DIR . 'templates/dynamic-form.tmpl.php');
        } ?>
    </tbody>
    <tbody id="fm"><?php
        $tform = TicketForm::getInstance();
        if ($_POST) {
            $tform->isValidForClient();
        }
        $tform->render(false); ?>
    </tbody>
    <tbody>
    <?php
    if($cfg && $cfg->isCaptchaEnabled() && (!$thisclient || !$thisclient->isValid())) {
        if($_POST && $errors && !$errors['captcha'])
            $errors['captcha']=__('Please re-enter the text again');
        ?>
    <tr class="captchaRow">
        <td class="required"><?php echo __('CAPTCHA Text');?>:</td>
        <td>
            <span class="captcha"><img src="captcha.php" border="0" align="left"></span>
            &nbsp;&nbsp;
            <input id="captcha" type="text" name="captcha" size="6" autocomplete="off">
            <em><?php echo __('Enter the text shown on the image.');?></em>
            <font class="error">*&nbsp;<?php echo $errors['captcha']; ?></font>
        </td>
    </tr>
    <?php
    } ?>
    <tr><td colspan=2>&nbsp;</td></tr>
    </tbody>
  </table>
<hr/>
  <p style="text-align:center;">
        <input type="submit" value="<?php echo __("Create Ticket");?>" id="create">
        <input type="reset" name="reset" value="<?php echo __('Reset');?>">
        <input type="button" name="cancel" value="<?php echo __('Cancel'); ?>" onclick="javascript:
            $('.richtext').each(function() {
                var redactor = $(this).data('redactor');
                if (redactor && redactor.opts.draftDelete)
                    redactor.deleteDraft();
            });
            window.location.href='index.php';">
  </p>
</form>

<?php
    $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }
    $query = "  SELECT 
                    a.value 
                FROM 
                    ost_form_entry_values a,
                    ost_form_entry b,
                    ost_user c
                WHERE
                    b.object_type = 'O'
                    AND b.object_id = c.org_id
                    AND a.entry_id = b.id
                    AND a.field_id = 90
                    AND c.id = ".$_SESSION["_auth"]["user"]["id"];
    $result = $mysqli->query($query);
    $filas  = $result->fetch_array();
    $limite = "BsF ".number_format($filas[0],2,",",".");

    $query = "  SELECT 
                    a.value 
                FROM 
                    ost_form_entry_values a,
                    ost_form_entry b,
                    ost_user c
                WHERE
                    b.object_type = 'O'
                    AND b.object_id = c.org_id
                    AND a.entry_id = b.id
                    AND a.field_id = 91
                    AND c.id = ".$_SESSION["_auth"]["user"]["id"];
    $result = $mysqli->query($query);
    $filas  = $result->fetch_array();
    $limiteDisponible = $filas[0];


//Inicio Billy 11/02/2016 Se agrego la fecha de la ultima modificacion del saldo disponible

$limit="select b.date from ost_user as a inner join ost_auditoria_limite_credito as b on a.org_id=b.org_id where a.id=". $_SESSION["_auth"]["user"]["id"]." ORDER BY b.date DESC Limit 1";  //Query para consultar en la base de datos la ultima fecha de actualizacion 


$limit2 = $mysqli->query($limit);
$row = $limit2->fetch_array();



    if($limiteDisponible <= 0){

        $limite2 = "<font color='FF0000'>BsF ".number_format($filas[0],2,",",".")."<br>Saldo deudor pendiente. </font>";

//Fin Billy 11/02/2016 Se agrego la fecha de la ultima modificacion del saldo disponible
        ?>

        <script>
            //$("#ticketForm p").prepend('<input type="submit" value="<?php echo __("Create Ticket");?>" id="create">');
            $("#ticketForm p").prepend("<big><font color='FF0000'><b>Tiene pendiente un saldo deudor.<br>No puede emitir localizador.</b></font></big><br><br><div id='btn_create'></div>");
            $("#create").fadeOut("fast");

            //Billy 10/02/2016 Ahora se toma solo en cuenta si es emitir localizador u otra opcion para crear tickets
            //$("select:eq(0)").change(function(){
            //     if($("select:eq(0)").val() != 19){
            //         $("#create").fadeIn('slow');
            //     }
            //     else{
            //         $("#create").fadeOut("fast");
            //     }
            // });

            //Inicio Billy 10/02/2016 Si el select de detalle su solicitud es igual a emitir  localizador el boton de crear tickets se oculta si no es emitir localizador aparece
            $("select:eq(1)").change(function(){
                if($("select:eq(1)").val() != 19){
                    $("#create").fadeIn('slow');
                }
                else{
                    $("#create").fadeOut("fast");
                }
            });
            //Fin Billy 10/02/2016 Si el select de detalle su solicitud es igual a emitir  localizador el boton de crear tickets se oculta si no es emitir localizador aparece

        </script>

        <?php
    }
    else{

        $limite2 = "BsF ".number_format($filas[0],2,",",".");
        
        ?>

        <!--<script>
            $("#ticketForm p").prepend('<input type="submit" value="<?php echo __("Create Ticket");?>" id="create">');
        </script>-->

        <?php
    }
?>

<script type="text/javascript">

    $("#fm tr:eq(11) td:eq(0) div:eq(0)").css("display","block"); //10/02/2016 Billy se sumo 1 al tr
    $("#fm tr:eq(11) td:eq(0) div:eq(0)").prepend(  //10/02/2016 Billy se sumo 1 al tr
        "<div style='text-align:right;display:block;'>"+
            "L&iacute;mite de Cr&eacute;dito Total: <b><?=$limite?></b>"+
            "<br>"+
            "Disponible: <b><?=$limite2?></b><br>"+
            "Actualizado al <?=date("d-m-Y h:i:s a",strtotime($row['date']))?>"+ //se agregro la ultima fecha de actualizacion del monto disponible
        "</div>");
    
    $('input:eq(2)').keypress(function (e) {
        var regex = new RegExp("^[a-zA-Z0-9]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str))
            return true;
        e.preventDefault();
        return false;
    });

/*Inicio Billy 29/01/2016 Validacion de campo numerico en numero de tarjeta de credito y en cedula*/   
    $('input:eq(3),input:eq(6)').keypress(function (e) {
        var regex = new RegExp("^[0-9]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str))
            return true;
        e.preventDefault();
        return false;
    });

/*Fin Billy 29/01/2016 Validacion de campo numerico en numero de tarjeta de credito y en cedula*/


/*Inicio Billy 29/01/2016 Funcion dar formato de moneda al input del monto de la tarjeta de credito y valida que no sean letras*/
    jQuery(function($) {
        $("input:eq(7)").autoNumeric({aSep: '.', aDec: ','});
    });
/*Fin Billy 29/01/2016 Funcion dar formato de moneda al input del monto de la tarjeta de credito y valida que no sean letras*/


/*Inicio Billy 29/01/2016 Validacion de campo numerico y / en la fecha de vencimiento de la tarjeta de credito*/    
            $('input:eq(4)').keypress(function (e) {
        var regex = new RegExp("^[0-9/]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str))
            return true;
        e.preventDefault();
        return false;   
    });
/*Fin Billy 29/01/2016 Validacion de campo numerico y / en la fecha de vencimiento de la tarjeta de credito*/ 


/*Inicio Billy 5/02/2016 Validacion de campo caracter en banco y nombre*/
    $('input:eq(5)').keypress(function (e) {
        var regex = new RegExp("^[a-zA-Z ]+$");
        var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
        if (regex.test(str))
            return true;
        e.preventDefault();
        return false;
    });

/*Fin Billy 5/02/2016 Validacion de campo caracter en banco y nombre*/

    $("tr:eq(3),tr:eq(4),tr:eq(5),tr:eq(6),tr:eq(7),tr:eq(8),tr:eq(9),tr:eq(10),tr:eq(11),tr:eq(13)").hide(0); //Billy 5/02/2016 Agregamos que el campo monto y status localizador se encuentre no visible al cargar el formulario
    $("input:eq(2),input:eq(5)").css("text-transform","uppercase");
    $("select:eq(1)").empty();
    $("select:eq(1)").append('<option value="">— Select —</option>');
    $("select:eq(0),select:eq(1)").prop('required',true);
    
    $("input:eq(2)").change(function(){$("input:eq(2)").val().toUpperCase();});
    $("input:eq(5)").change(function(){$("input:eq(5)").val().toUpperCase();});

    $("input:eq(2)").attr("pattern","[A-Za-z0-9]{6}");
    $("input:eq(2)").attr("title","6 digitos alfanumericos");

/*Inicio Billy 29/01/2016 Titulo al input de nº tarjeta de credito, fecha de vencimiento, cedula y monto tarjeta de credito*/
    $("input:eq(3)").attr("title","Sólo 16 digitos numéricos");
    $("input:eq(4)").attr("title","Ejemplo 01/16");
    $("input:eq(6)").attr("title","Sólo 8 digitos numéricos");
    $("input:eq(7)").attr("title","Sólo números y para agregar decimales utilice (,)");
/*Fin Billy 29/01/2016 Titulo al input de nº tarjeta de credito, fecha de vencimiento, cedula y monto tarjeta de credito*/


/*Inicio Billy 29/01/2016 Validacion de maxima longitud al input de nº tarjeta de credito, fecha de vencimiento y cedula*/
    $("input:eq(3)").attr("maxlength","16"); 
    $("input:eq(4)").attr("maxlength","5");
    $("input:eq(6)").attr("maxlength","8");
/*Fin Billy 29/01/2016 Validacion de maxima longitud al input de nº tarjeta de credito, fecha de vencimiento y cedula*/


    //Help Topic
    $("select:eq(0)").change(function(){
        $.ajax({
            data: { menu : $("select:eq(0) option:selected").val() },
            type: "POST",
            url: 'include/client/ajax_login.php',
            success: function(response){
                $("select:eq(1)").empty();
                $("select:eq(1)").append(response);
            }
        });
        if($("select:eq(0)").val() == 19){
            //$("tr:eq(3)").show("slow");
            //$("select:eq(2)").prop('required',true); Se deshabilita required GDS
            $("input:eq(9)").val("Pendiente");
        }
        else if($("select:eq(0)").val() == 20){
            $("tr:eq(3),tr:eq(4),tr:eq(5),tr:eq(6),tr:eq(7),tr:eq(8),tr:eq(9),tr:eq(10)").hide("slow");
            $("input:eq(9),input:eq(7),input:eq(6),input:eq(5),input:eq(4),input:eq(3),input:eq(2)").val("");
            $("input").removeAttr('required');
            $("#codigo").remove();
            $("select:eq(2)").val("");
            $("select:eq(2)").removeAttr('required');
        }
        else{
            $("tr:eq(6),tr:eq(7),tr:eq(8),tr:eq(9),tr:eq(10)").hide("slow");
            $("input:eq(7),input:eq(6),input:eq(5),input:eq(4),input:eq(3),input:eq(9)").val("");
            $("input:eq(7),input:eq(6),input:eq(5),input:eq(4),input:eq(3)").removeAttr('required');
            $("#codigo").remove();
            $("tr:eq(3),tr:eq(4),tr:eq(5)").hide("slow");
            $("select:eq(2)").removeAttr('required');
            $("select:eq(2)").val("");
        }

    });

    $("select:eq(1)").change(function(){        
        if(($("select:eq(0)").val() == 19 && $("select:eq(1)").val() != 23) || ($("select:eq(0)").val() == 21 && $("select:eq(1)").val() == 33)){
            if($("select:eq(0)").val() == 19 && $("select:eq(1)").val() != 23){
                //$("tr:eq(10)").show("slow"); ////////////Billy 5/02/2016 se quito el input de la cedula para que no aparezca cuando el tipo de solicitud sea emitir localizador
                $("input:eq(2)").prop('required',true);
                $("tr:eq(3)").show("slow");
            }
            else{
                $("input:eq(2)").removeAttr('required');
                $("input:eq(2)").val("");
            }   
            $("tr:eq(4)").show("slow");
            $("input:eq(2)").prop('required',true);

        }
        else{
            $("tr:eq(4)").hide("slow");
            $("input:eq(2)").removeAttr('required');
            $("input:eq(2)").val("");
        }
        if($("select:eq(1)").val() == 19 || $("select:eq(1)").val() == 26){
            $("tr:eq(5)").show("slow");
            $("select:eq(3)").prop('required',true);
            $("input:eq(2)").prop('required',true);
            $("select:eq(2)").prop('required',true);
        }
        else{
            $("tr:eq(5)").hide("slow");
            $("select:eq(2)").removeAttr('required');
            $("select:eq(3)").removeAttr('required');
            $("select:eq(3)").val("");
        }
        if($("select:eq(1)").val() == 31){
            $("input:eq(2)").removeAttr('required');
            $("select:eq(2)").removeAttr('required');
            $("tr:eq(3)").hide(0);
            $("tr:eq(4)").hide(0);
        }
        if($("select:eq(0)").val() != 19){
            $("input:eq(2)").removeAttr('required');
            $("select:eq(2)").removeAttr('required');
        }
        /*Jonathan 12/05/2017 FIX TEMPORAL Para poder enfocar al Cotizar SOTO 
        select:eq(0)=19 Aereo  - Tipo de Solicitud 
        select:eq(1)=23 Cotizar SOTO - Detalle de Solicitud
        select:eq(2)=11 SOTO -GDS 
        tr:eq(3)=GDS 
        tr:eq(4)=LOCALIZADOR */
        /* An invalid form control with name='496fabf6e20ce01c[]' is not focusable. */
        /* An invalid form control with name='a276bde2a28a098f' is not focusable. */

    });

    $('select:eq(3)').change(function(){
        if( $('select:eq(3)').val() == 14 || $('select:eq(3)').val() == 50){
            $("input:eq(6),input:eq(5),input:eq(4),input:eq(3)").prop('required',true);
            $("tr:eq(6),tr:eq(7),tr:eq(8),tr:eq(9),tr:eq(10)").show("slow");
            $("td:eq(10)").append("<small id='codigo' style='display:none;'>Para c&oacute;digo de seguridad de TDC y autorizaci&oacute;n, contactar por tel&eacute;fono.</small>");
            $("td:eq(16)").append("<small id='codigo1' style='display:none;'>Ejemplo 01/16</small>"); /*Billy 29/01/2016 Ejemplo de como se debe llenar la fecha de vencimiento de la tarjeta de credito*/
            $("#codigo").show("slow");
            $("#codigo1").show("slow"); /*Billy 29/01/2016 Ejemplo de como se debe llenar la fecha de vencimiento de la tarjeta de credito*/
        }
        else{
            $("tr:eq(6),tr:eq(7),tr:eq(8),tr:eq(9),tr:eq(10)").hide("slow");
            $("input:eq(6),input:eq(5),input:eq(4),input:eq(3)").val("");
            $("input:eq(6),input:eq(5),input:eq(4),input:eq(3)").removeAttr('required');
            $("#codigo").remove();
        }
        if( $('select:eq(3)').val() == 50){
            $("input:eq(7)").prop('required',true);
            $("tr:eq(11)").show("slow"); //Billy 5/02/2016 Agrego el campo de monto si la opcion es tdc + cash
            
        }
        else{
            $("tr:eq(11)").hide("slow"); ////Billy 5/02/2016 quito el campo de monto si la opcion es tdc
            $("input:eq(7)").val("");
            $("input:eq(7)").removeAttr('required');
        }
    });

    $.ajax({
        data: { menu : $("select:eq(0) option:selected").val() },
        type: "POST",
        url: 'include/client/ajax_login.php',
        success: function(response){
            $("select:eq(1)").empty();
            $("select:eq(1)").append(response);
            $("select:eq(1)").val("<?=$submenu?>")
        }
    });

    $("tr:eq(4) td:eq(1)").append("<div id='repeat' style='display:none;color:#F00;'><big><br>El ticket no puede ser creado. Localizador duplicado. Contacte a su asesor.<br><br></big></div>");
    $('input:eq(2),select:eq(0),select:eq(1),select:eq(2)').change(function(){
        if($('select:eq(0)').val() == 19 && $('select:eq(1)').val() == 19 && $('input:eq(2)').val() != "" && parseFloat("<?=$limiteDisponible;?>") > 0){
            $.ajax({
                data: { menu : "localizador", localizador : $('input:eq(2)').val(), gds : $('select:eq(2)').val() },
                type: "POST",
                url: 'include/client/ajax_login.php',
                success: function(response){
                    if(response == 1){
                        $("#repeat").show("slow");
                        $("#create").hide();
                    }
                    else{
                        $("#repeat").hide();
                        $("#create").show();
                    }
                }
            });
        }
        else{
            $("#repeat").hide();
        }
    });

    $("#create").click(function(){
        if($("select:eq(0)").val() != "" && $("select:eq(1)").val() != "" && $("div").eq(10).text() == ""){
            $("div").eq(10).prepend("<b>"+$('select:eq(0) :selected').text()+" - "+$('select:eq(1) :selected').text()+"</b><br><br>");
        }
    });

    $("input:eq(2)").attr("pattern","[A-Za-z0-9]{6}");
        
</script>

<?php
$mysqli = new mysqli("localhost", "osticket", "0571ck37", "osticket1911");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$query = "SELECT staff_name FROM  `ost_user_agent` WHERE user_id = ".$_SESSION["_auth"]["user"]["id"];
$result = $mysqli->query($query);
$rowcount = mysqli_num_rows($result);

$query2 = " SELECT b.dept_name
            FROM  `ost_config` a, ost_department b
            WHERE a.id =89
                AND a.value = b.dept_id";
$result2 = $mysqli->query($query2);
$row2 = $result2->fetch_array();

$dep = $row2[0];

if($rowcount > 0){

    $row = $result->fetch_array();

    if(!empty($row[0])){
        ?>
        <script type="text/javascript">
            $('#topicId option:contains(<?=$row[0]?>)').each(function(){
                if ($(this).text() == "<?=$row[0]?>") {
                    $("#topicId option:nth-child(0)").removeAttr("selected");
                    $(this).attr('selected', 'selected');
                    return false;
                }
                return true;
            });
        </script>
        <?php
    }
    else{
        ?>
        <script type="text/javascript">
            $('#topicId option:contains(<?=$dep?>)').each(function(){
                if ($(this).text() == "<?=$dep?>") {
                    $("#topicId option:nth-child(0)").removeAttr("selected");
                    $(this).attr('selected', 'selected');
                    return false;
                }
                return true;
            });
        </script>
        <?php
    }

}

else{

?>
<script type="text/javascript">
    $('#topicId option:contains(<?=$dep?>)').each(function(){
        if ($(this).text() == "<?=$dep?>") {
            $("#topicId option:nth-child(0)").removeAttr("selected");
            $(this).attr('selected', 'selected');
            return false;
        }
        return true;
    });
</script>
<?php

}

/* free result set */
$result->close();

/* close connection */
$mysqli->close();

?>

<!--Inicio Billy 29/01/2016-->
<script src="<?php echo ROOT_PATH; ?>js/autoNumeric.js"></script>
<!--Fin Billy 29/01/2016-->