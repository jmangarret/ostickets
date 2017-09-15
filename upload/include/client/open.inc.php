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
  <table width="80%" cellpadding="1" cellspacing="0" border="0" id="tabla1">
    <tbody>
    <tr style="">
        <td class="required"><?php echo __('Tipo de Solicitud');?>:</td>
        <td>
            <select id="topicId" name="topicId">
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
  <?php /* jmangarret - sept2017 - DIV para mostrar pagos cargados */ ?>
  <div id="listadoPagos"></div>
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

<div class="modal fade" id="modalPagos" role="dialog" width="90%">
    <div class="modal-dialog" width="100%">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" id="close-modal">&times;</button >
                <h1>Añadir nuevo pago</h1>
            </div>            
            <div class="modal-body">
            <?php $user_id=$thisclient->getId(); ?>
            <?php include("include/pagos/form_pagos.php"); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-info" data-dismiss="modal" id="close-pagos">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<?php
//jmangarret - 08ago2017 - Refactorizacion de codigo - Pase de query a funciones php y validaciones javascript a archivo js
include("include/funciones/commons.php");

//jmangarret - 23ago2017 - Obtencion de tipo de satelite en div oculto para leerlo con jquery
$tipoSatelite=getTipoSatelite($thisclient->getId());
echo "<div id=tipoSatelite style=display:none>$tipoSatelite</div>";

$limite = getLimiteCredito();

$limiteDisponible=getLimiteDisponible();

$fechaDeSaldo=getFechaModificacionSaldo();

if($limiteDisponible <= 0){
    $limite2 = "<font color='FF0000'>BsF ".number_format($limiteDisponible,2,",",".")."<br>Saldo deudor pendiente. </font>";
    ?>
    <script>
        //$("#ticketForm p").prepend('<input type="submit" value="<?php echo __("Create Ticket");?>" id="create">');
        $("#ticketForm p").prepend("<big><font color='FF0000'><b>Tiene pendiente un saldo deudor.<br>No puede emitir localizador.</b></font></big><br><br><div id='btn_create'></div>");
        $("#create").fadeOut("fast");

        //Inicio Billy 10/02/2016 Si el select de detalle su solicitud es igual a emitir  localizador el boton de crear tickets se oculta si no es emitir localizador aparece
        $("select:eq(1)").change(function(){
            alert(this.value);
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
}else{
    $limite2 = "BsF ".number_format($limiteDisponible,2,",",".");      
}
?>
<!--Inicio Billy 29/01/2016-->
<script src="<?php echo ROOT_PATH; ?>js/autoNumeric.js"></script>
<!--Fin Billy 29/01/2016-->
<script type="text/javascript" src="include/client/open.validate.js"></script>
<script type="text/javascript">
    setLimiteCredito('<?=$limite?>','<?=$limite2?>','<?=date("d-m-Y h:i:s a",strtotime($fechaDeSaldo))?>');
    valSelects('<?=$limiteDisponible?>');
</script>

<?php
//Val Departamentos en Tipo de Solicitud
$valUserAgent=getValUserAgent();
$dep=getDefaultDpto();

if($valUserAgent > 0){   
    $dptoUser=getDptoUserAgent();
    if(!empty($dptoUser)){
        ?>
        <script type="text/javascript">
            $('#topicId option:contains(<?=$dptoUser?>)').each(function(){
                if ($(this).text() == "<?=$dptoUser?>") {
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
}else{
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


