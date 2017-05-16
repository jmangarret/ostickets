<?php
/*********************************************************************
    index.php

    Helpdesk landing page. Please customize it to fit your needs.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
/*require('client.inc.php');
$section = 'home';
require(CLIENTINC_DIR.'header.inc.php');
?>

<!-- inicio 21/01/2016 Billy-->

<div style="text-align:right;">

 <!-- Trigger the modal with a button -->
  <button type="button" class="btn btn-sm btn-link text" data-toggle="modal" data-target="#myModal" style="text-decoration:none">Nuevas Funciones del Sistema</button>
</div>

<!-- Fin 21/01/2016 Billy-->



<div id="landing_page">
    <?php
    if($cfg && ($page = $cfg->getLandingPage()))
        echo $page->getBodyWithImages();
    else
        echo  '<h1>'.('Bienvenido al Centro de Soporte').'</h1>';
    ?>
    <div id="new_ticket" class="pull-left">
        <h3><?php echo __('Open a New Ticket');?></h3>
        <br>
        <div><?php echo __('Please provide as much detail as possible so we can best assist you. To update a previously submitted ticket, please login.');?></div>
    </div>

    <div id="check_status" class="pull-right">
        <h3><?php echo __('Check Ticket Status');?></h3>
        <br>
        <div><?php echo __('We provide archives and history of all your current and past support requests complete with responses.');?></div>
    </div>

    <div class="clear"></div>
    <div class="front-page-button pull-left">
        <p>
            <a href="open.php" class="green button"><?php echo __('Open a New Ticket');?></a>
        </p>
    </div>
    <div class="front-page-button pull-right">
        <p>
            <a href="<?php if(is_object($thisclient)){ echo 'tickets.php';} else {echo 'view.php';}?>" class="blue button"><?php echo __('Check Ticket Status');?></a>
        </p>
    </div>




<!-- Inicio 20/01/2016 Billy Ventana modal con los links de descarga de los manuales de usuarios por cada nueva funcion del sistema-->

<link rel="stylesheet" href="/upload/css/bootstrap.css">
  <script src="/upload/js/jquery-1.12.0.js"></script>
  <script src="/upload/js/bootstrap.min.js"></script>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h3 class="modal-title">Nuevas Funciones del Sistema</h3>
        </div>

        <!--Body del Modal-->
        <div class="modal-body">
        <table class="table">
        <thead>
           <tr>
           <th style="text-align:center;"><b>Descripcion</b></th>
           <th style="text-align:center;"><b>Enlace de descarga</b></th>
           </tr> 
        </thead>
        <tbody>
            <tr>
                <td><p>Ahora puedes crear tickets para realizar solicitudes y/o requerimientos a clientes finales. Para verficar como hacerlo puedes descargar el archivo.</p></td>
                <td><br><a href="/upload/news/Crear ticket.pdf" download="Crear Ticket">Descargar Archivo</a></td>
            </tr>
            <tr>
                <td><p>Ahora puedes consultar el límite de crédito total y el saldo disponible.<br>Para verficar como hacerlo puedes descargar el archivo.</p></td>
                <td><br><a href="/upload/news/Limite de credito.pdf" download="Limite de credito">Descargar Archivo</a></td>
            </tr>
            <tr>
                <td><p>Ahora puedes adjuntar archivos de hasta 4 Mb a los Tickets.<br>Para verficar como hacerlo puedes descargar el archivo.</p></td>
                <td><br><a href="/upload/news/Archivos adjuntos.pdf" download="Archivos Adjuntos">Descargar Archivo</a></td>
            </tr>
            <tr>
                <td><p>Ahora puedes realizar búsquedas más exactas de los tickets a través de la función de búsquedas avanzadas.<br>Para verficar como hacerlo puedes descargar el archivo.</p></td>
                <td><br><a href="/upload/news/Busqueda avanzada.pdf" download="Busqueda Avanzada">Descargar Archivo</a></td>
            </tr>
        </tbody>
        </table>
        </div>
        


        <div class="modal-footer">
          <button type="button" class="btn btn-info" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
      <!--Fin del Body del Modal-->
    </div>
  </div>
</div>

<!-- Fin 29/01/2016 Billy Ventana modal con los links de descarga de los manuales de usuarios por cada nueva funcion del sistema-->


</div>
<div class="clear"></div>
<?php
if($cfg && $cfg->isKnowledgebaseEnabled()){
    //FIXME: provide ability to feature or select random FAQs ??
?>
<p><?php echo sprintf(
    __('Be sure to browse our %s before opening a ticket'),
    sprintf('<a href="kb/index.php">%s</a>',
        __('Frequently Asked Questions (FAQs)')
    )); ?></p>
</div>
<?php
} ?>
<?php require(CLIENTINC_DIR.'footer.inc.php'); ?>*/

header("Location: login.php");


