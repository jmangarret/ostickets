<!--Inicio Billy 25/01/2016-->

<link rel="stylesheet" href="/ostickets/upload/css/bootstrap.min.css">
<script src="/ostickets/upload/css/bootstrap.min.js"></script>

<!--Fin Billy 25/01/2016-->


<?php
if(!defined('OSTCLIENTINC') || !is_object($thisclient) || !$thisclient->isValid()) die('Access Denied');

$qs = array();

$search=($_REQUEST['a']=='search');
$searchTerm='';
//make sure the search query is 3 chars min...defaults to no query with warning message
if($search) {
  $searchTerm=$_REQUEST['query'];
  if( ($_REQUEST['query'] && strlen($_REQUEST['query'])<3)
      || (!$_REQUEST['query'] && isset($_REQUEST['basic_search'])) ){ //Why do I care about this crap...
      $search=false; //Instead of an error page...default back to regular query..with no search.
      $errors['err']=__('Search term must be more than 3 chars');
      $searchTerm='';
  }
}

$status=null;
if(isset($_REQUEST['status'])) { //Query string status has nothing to do with the real status used below.
    $qs += array('status' => $_REQUEST['status']);
    //Status we are actually going to use on the query...making sure it is clean!
    $status=strtolower($_REQUEST['status']);
    switch(strtolower($_REQUEST['status'])) {
     case 'open':
        $results_type=__('Open Tickets');
     case 'closed':
        $results_type=__('Closed Tickets');
        break;
     case 'resolved':
        $results_type=__('Resolved Tickets');
        break;
     default:
        $status=''; //ignore
    }
} elseif($thisclient->getNumOpenTickets()) {
    $status='open'; //Defaulting to open
    $results_type=__('Open Tickets');
}

$sortOptions=array('id'=>'`number`', 'subject'=>'cdata.subject',
                    'status'=>'status.name', 'dept'=>'dept_name','date'=>'ticket.created');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
//Sorting options...
$order_by=$order=null;
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'date';
if($sort && $sortOptions[$sort])
    $order_by =$sortOptions[$sort];

$order_by=$order_by?$order_by:'ticket_created';
if($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])])
    $order=$orderWays[strtoupper($_REQUEST['order'])];

$order=$order?$order:'DESC';//MICOD: Cambiado de ASC a DESC para el listado de tickets por fecha del más actual al más antiguo.
if($order_by && strpos($order_by,','))
    $order_by=str_replace(','," $order,",$order_by);

$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';

$qselect='SELECT ticket.ticket_id,ticket.`number`,ticket.dept_id,isanswered, '
    .'dept.ispublic, cdata.subject,'
    .'dept_name, status.name as status, status.state, ticket.source, ticket.created ';

$qfrom='FROM '.TICKET_TABLE.' ticket '
      .' LEFT JOIN '.TICKET_STATUS_TABLE.' status
            ON (status.id = ticket.status_id) '
      .' LEFT JOIN '.TABLE_PREFIX.'ticket__cdata cdata ON (cdata.ticket_id = ticket.ticket_id)'
      .' LEFT JOIN '.DEPT_TABLE.' dept ON (ticket.dept_id=dept.dept_id) '
      .' LEFT JOIN '.TICKET_COLLABORATOR_TABLE.' collab
        ON (collab.ticket_id = ticket.ticket_id
                AND collab.user_id ='.$thisclient->getId().' )';

$qwhere = sprintf(' WHERE ( ticket.user_id=%d OR collab.user_id=%d )',
            $thisclient->getId(), $thisclient->getId());

//Antigua forma de filtrar los tickets de usuario por el Status

/*$states = array(
        'open' => 'open',
        'closed' => 'closed');
if($status && isset($states[$status])){
    $qwhere.=' AND status.state='.db_input($states[$status]);
}
echo"<pre>";var_dump($status, $states[$status], $qwhere);echo"</pre>";*/
//--------------------------------------------------------------

/*MICOD: Código PHP que anexa a la caja de búsqueda principal la filtro por número de tickets, correo y localizador*/
if($search):
    $qs += array('a' => $_REQUEST['a'], 't' => $_REQUEST['t']);
    //query
    if($searchTerm){
        $qs += array('query' => $searchTerm);
        $queryterm=db_real_escape($searchTerm,false);
        if (is_numeric($searchTerm)) {
            $qwhere.=" AND ticket.`number` LIKE '$queryterm%'";
        } elseif (strpos($searchTerm,'@') && Validator::is_email($searchTerm)) {
            $qwhere.=" AND email.address='$queryterm'";
        } else {
            $qwhere.=" AND UPPER (CAST( cdata.localizador AS CHAR( 100 ) CHARSET utf8 )) LIKE '%".strtoupper($searchTerm)."%'"; /*Billy 1/02/2016 Busca el localizador en el buscador general indiferentemente si es mayuscula o minuscula*/
        }
   }
   endif;
if ($_REQUEST['advsid'] && isset($_SESSION['adv_'.$_REQUEST['advsid']])) {
    $ticket_ids = implode(',', db_input($_SESSION['adv_'.$_REQUEST['advsid']]));
    $qs += array('advsid' => $_REQUEST['advsid']);

    $qwhere .= ' AND ticket.ticket_id IN ('.$ticket_ids.')';
    $order_by = 'FIELD(ticket.ticket_id, '.$ticket_ids.')';
    $order = ' ';
}
/*-----------------------------------------------------------------------------------------------------------------*/

$search=($_REQUEST['a']=='search' && $_REQUEST['q']);
if($search) {
    $qs += array('a' => $_REQUEST['a'], 'q' => $_REQUEST['q']);
    if(is_numeric($_REQUEST['q'])) {
        $qwhere.=" AND ticket.`number` LIKE '$queryterm%'";
    } else {//Deep search!
        $queryterm=db_real_escape($_REQUEST['q'],false); //escape the term ONLY...no quotes.
        $qwhere.=' AND ( '
                ." cdata.subject LIKE '%$queryterm%'"
                ." OR thread.body LIKE '%$queryterm%'"
                .' ) ';
        $deep_search=true;
        //Joins needed for search
        $qfrom.=' LEFT JOIN '.TICKET_THREAD_TABLE.' thread ON ('
               .'ticket.ticket_id=thread.ticket_id AND thread.thread_type IN ("M","R"))';
    }
}

if(isset($_GET["des"]) && $_GET["des"] != "")
    $qwhere .= " AND ticket.created >= '".$_GET["des"]." 00:00:00' AND ticket.created <= '".$_GET["has"]." 24:59:59'";
if(isset($_GET["loc"]) && $_GET["loc"] != "" )
    $qwhere .= " AND cast(cdata.localizador as char(100) charset utf8) LIKE '%".$_GET["loc"]."%'";

TicketForm::ensureDynamicDataView();


//more stuff...
$qselect.=' ,count(attach_id) as attachments ';
$qfrom.=' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON  ticket.ticket_id=attach.ticket_id ';
$qgroup=' GROUP BY ticket.ticket_id';

$more = "";
$nume = 0;

if(isset($_GET["est"]) and $_GET["est"]!=""){ //Billy 1/02/2016 Se agrego la condicion que el campo estatus fuese diferente de vacio ya que ahora la busqueda es con select//
    /*$i=0;
    $str="";
    foreach ($_GET['est'] as $selected) {
        if ($i > 0) {
            $str .= "','$selected";
        }else{
            $str .= "$selected";
        }
        $i++;
    }
    $more .= " AND status.state IN ('$str')";*/
$more .= " AND status.state IN ('".$_GET['est']."')"; //Billy 1/02/2016 Se guarda el valor del GET en la variable more//
}
if(isset($_GET["top"]) and $_GET["top"]!=""){ //Billy 1/02/2016 Se agrego la condicion que el campo top fuese diferente de vacio ya que ahora la busqueda es con select//
    /*$i=0;
    $str="";
    foreach ($_GET['top'] as $selected) {
        if ($i > 0) {
            $str .= "','$selected";
        }else{
            $str .= "$selected";
        }
        $i++;
    }
    $more .= " AND ticket.topic_id IN ('$str')";*/
    $more .= " AND ticket.topic_id IN ('".$_GET['top']."')"; //Billy 1/02/2016 Se guarda el valor del GET en la variable more//
}
if(isset($_GET["dep"]) and $_GET["dep"]!=""){  //Billy 1/02/2016 Se agrego la condicion que el campo dep fuese diferente de vacio ya que ahora la busqueda es con select//
    /*$i=0;
    $str="";
    foreach ($_GET['dep'] as $selected) {
        if ($i > 0) {
            $str .= "','$selected";
        }else{
            $str .= "$selected";
        }
        $i++;
    }
    $more .= " AND ticket.dept_id IN ('$str')";*/
    $more .= " AND ticket.dept_id IN ('".$_GET['dep']."')"; //Billy 1/02/2016 Se guarda el valor del GET en la variable more//
}
if(isset($_GET["sta"]) and $_GET["sta"]!=""){ //Billy 1/02/2016 Se agrego la condicion que el campo sta fuese diferente de vacio ya que ahora la busqueda es con select//
    /*$i=0;
    $str="";
    foreach ($_GET['sta'] as $selected) {
        if ($i > 0) {
            $str .= "','$selected";
        }else{
            $str .= "$selected";
        }
        $i++;
    }
    $more .= " AND cast(cdata.status_loc as char(100) charset utf8) IN ('$str')";*/
    $more .= " AND cast(cdata.status_loc as char(100) charset utf8) IN ('".$_GET['sta']."')"; //Billy 1/02/2016 Se guarda el valor del GET en la variable more//
}
if(isset($_GET["des"]) && $_GET["des"] != "")
    $more .= " AND ticket.created >= '".$_GET["des"]." 01:01:01'";
if(isset($_GET["has"]) && $_GET["has"] != "")
    $more .= " AND ticket.created <= '".$_GET["has"]." 23:59:59'";
if(isset($_GET["loc"]) && $_GET["loc"] != "") //se agrego que el localizador sea diferente de vacio para que muestre todo
    $more .= " AND cast(cdata.localizador as char(100) charset utf8) LIKE '%".$_GET["loc"]."%'";

$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;//Conteo de la página actual

/*MICOD: Guardar la variable $more para que el filtro de la sentencia no se
pierda con cada página que se avanza*/
if ($_REQUEST['clean'] == 1) {//Si la variable GET es 1 entonces...
    $_SESSION['more'] = "";//Vaciamos la seción PHP
}elseif (($_REQUEST['clean'] == NULL) && ($page == 1)) {//Si la variable GET es NULL y la página actual es 1 entonces...
    $_SESSION['more'] = $more;//Cargamos la sesión PHP con los filtros de búsqueda seleccionados para la sentencia SQL
}
/*------------------------------------------------------------*/

$total=db_count("SELECT COUNT(ticket.ticket_id) FROM ost_ticket ticket 
            LEFT JOIN ost_ticket_status status ON (status.id = ticket.status_id) LEFT JOIN ost_ticket__cdata cdata 
            ON (cdata.ticket_id = ticket.ticket_id) LEFT JOIN ost_department dept ON (ticket.dept_id=dept.dept_id) 
            LEFT JOIN ost_ticket_collaborator collab ON (collab.ticket_id = ticket.ticket_id AND collab.user_id = ".$thisclient->getId()." ) 
            LEFT JOIN ost_ticket_attachment attach ON ticket.ticket_id=attach.ticket_id WHERE ( ticket.user_id= ".$thisclient->getId()." OR collab.user_id= ".$thisclient->getId()." ) 
            AND cast(cdata.localizador as char(100) charset utf8) LIKE '%%'");//Aplicando la sesión PHP en la sentencia

$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);
$pageNav->setURL('tickets.php', $qs);
$query="$qselect $qfrom $qwhere $more $qgroup ORDER BY $order_by $order LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
//echo $_GET["sta"];
$res = db_query($query);
$showing=($res
 && db_num_rows($res))?$pageNav->showing():"";
if(!$results_type)
{
    $results_type=ucfirst($status).' Tickets';
}
$showing.=($status)?(' '.$results_type):' '.__('All Tickets');
if($search)
    $showing=__('Search Results').": $showing";

/*MICOD: Buscamos el número de registros para los tickets abiertos y cerrados y los mostramos al lado de los botones en el menú*/
$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting

    $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

$open = "SELECT ticket.ticket_id,ticket.`number`,ticket.dept_id,isanswered, dept.ispublic, cdata.subject,dept_name, status.name 
            as status, status.state, ticket.source, ticket.created ,count(attach_id) as attachments FROM ost_ticket ticket 
            LEFT JOIN ost_ticket_status status ON (status.id = ticket.status_id) LEFT JOIN ost_ticket__cdata cdata 
            ON (cdata.ticket_id = ticket.ticket_id) LEFT JOIN ost_department dept ON (ticket.dept_id=dept.dept_id) 
            LEFT JOIN ost_ticket_collaborator collab ON (collab.ticket_id = ticket.ticket_id AND collab.user_id = ".$thisclient->getId()." ) 
            LEFT JOIN ost_ticket_attachment attach ON ticket.ticket_id=attach.ticket_id WHERE ( ticket.user_id= ".$thisclient->getId()." OR collab.user_id= ".$thisclient->getId()." ) 
            AND status.state IN ('open') 
            GROUP BY ticket.ticket_id ORDER BY ticket.created ASC";
$result_open = $mysqli->query($open);
$n_abiertos = mysqli_num_rows($result_open);


$close = "SELECT ticket.ticket_id,ticket.`number`,ticket.dept_id,isanswered, dept.ispublic, cdata.subject,dept_name, status.name 
            as status, status.state, ticket.source, ticket.created ,count(attach_id) as attachments FROM ost_ticket ticket 
            LEFT JOIN ost_ticket_status status ON (status.id = ticket.status_id) LEFT JOIN ost_ticket__cdata cdata 
            ON (cdata.ticket_id = ticket.ticket_id) LEFT JOIN ost_department dept ON (ticket.dept_id=dept.dept_id) 
            LEFT JOIN ost_ticket_collaborator collab ON (collab.ticket_id = ticket.ticket_id AND collab.user_id = ".$thisclient->getId()." ) 
            LEFT JOIN ost_ticket_attachment attach ON ticket.ticket_id=attach.ticket_id WHERE ( ticket.user_id= ".$thisclient->getId()." OR collab.user_id= ".$thisclient->getId()." ) 
            AND status.state IN ('closed')
            GROUP BY ticket.ticket_id ORDER BY ticket.created ASC";
$result_close = $mysqli->query($close);
$n_cerrados = mysqli_num_rows($result_close);
/*----------------------------------------------------------------------------------------------------------------------------*/
?>
<!--MICOD: JQuery para animación de los botones seleccionados del menú-->
<script type="text/javascript">
        $(document).ready(function() {

            var get = "<?php echo $_REQUEST['clean']; ?>";
            if (get == 1) {
                localStorage['open'] = "";
                localStorage['closer'] = "";
            };

            $("#open").click(function(){
                localStorage['open'] = true;
                var open = localStorage['open'] || false;
                localStorage['closer'] = "";
            });

            $("#closer").click(function(){
                localStorage['closer'] = true;
                var closer = localStorage['closer'] || false;
                localStorage['open'] = "";
            });

            if (localStorage['open']) {
                $("#open").addClass("active tickets");
            }else if (localStorage['closer']) {
                $("#closer").addClass("active tickets");
            }

        });
    </script>
<!--//////////////////////////////////////////////////////////////////--> 
            <!--MICOD: Nuevo menú de opciones de tickets abiertos y cerrados-->   
            <ul id="nav2" class="flush-left" style="margin-top: -20px;">
                <li></li>
                <li><a class="tickets" id="open" href="tickets.php?est%5B%5D=open&des=&has=&loc=">Abiertos (<?=$n_abiertos?>)</a></li>
                <li><a class="tickets" id="closer" href="tickets.php?est%5B%5D=closed&des=&has=&loc=">Cerrados (<?=$n_cerrados?>)</a></li>
                <li></li>        
            </ul>
            <!--//////////////////////////////////////////////////////////////////--> 
            <br>
            <!--MICOD: Caja de texto de búsqueda avanzada--> 
            <div id='basic_search'>
                <form action="tickets.php" method="get">
                <?php csrf_token(); ?>
                <input type="hidden" name="a" value="search">
                <table>
                    <tr>
                        <td><input type="text" id="basic-ticket-search" name="query"
                        size=30 value="<?php echo Format::htmlchars($_REQUEST['query'],
                        true); ?>"
                            autocomplete="off" autocorrect="off" autocapitalize="off"></td>
                        <td><input type="submit" name="basic_search" value="<?php echo __('Search'); ?>"></td>
                    </tr>
                </table>
                </form>
            </div>
            <!--//////////////////////////////////////////--> 
            <br>

<h1><?php echo __('Tickets');?></h1>
<br>
<form action="tickets.php" method="get" id="ticketSearchForm" style="display:none;">
    <input type="hidden" name="a"  value="search">
    <input type="text" name="q" size="20" value="<?php echo Format::htmlchars($_REQUEST['q']); ?>">
    <select name="status">
        <option value="">&mdash; <?php echo __('Any Status');?> &mdash;</option>
        <option value="open"
            <?php echo ($status=='open') ? 'selected="selected"' : '';?>>
            <?php echo _P('ticket-status', 'Open');?> (<?php echo $thisclient->getNumOpenTickets(); ?>)</option>
        <?php
        if($thisclient->getNumClosedTickets()) {
            ?>
        <option value="closed"
            <?php echo ($status=='closed') ? 'selected="selected"' : '';?>>
            <?php echo __('Closed');?> (<?php echo $thisclient->getNumClosedTickets(); ?>)</option>
        <?php
        } ?>
    </select>
    <input type="submit" value="<?php echo __('Go');?>">
</form>


<!--////////////////////////////inicio 25/01/2016 Billy Modal para busqueda avanzada////////////////////////////////////////////-->

<link rel="stylesheet" href="/ostickets/upload/css/bootstrap.css">
  <script src="/ostickets/upload/js/jquery-1.12.0.js"></script>
  <script src="/ostickets/upload/js/bootstrap.min.js"></script>

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
    
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">

          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h3 class="modal-title">Búsqueda Avanzada</h3>
        </div>

        <!--Body del Modal-->
        <div class="modal-body">

        <form action="tickets.php" id="advancedsearch" method="get" style="display:none;"> 
         <table class="table">
            <tr>
            <th><b>Estado</b></th>
            <td>
                <select name="est"> <!--Billy 1/02/2016 Se convirtio en un select-->
                <option value="">Seleccione</option> <!--Billy 1/02/2016 Opcion seleccione del campo estado-->
                <?php

                $query = "  SELECT status.state,status.name
                FROM ost_ticket ticket 
                    LEFT JOIN ost_ticket_status status ON (status.id = ticket.status_id) 
                    LEFT JOIN ost_ticket__cdata cdata ON (cdata.ticket_id = ticket.ticket_id) 
                    LEFT JOIN ost_department dept ON (ticket.dept_id=dept.dept_id) 
                    LEFT JOIN ost_ticket_collaborator collab ON (collab.ticket_id = ticket.ticket_id AND collab.user_id =".$_SESSION["_auth"]["user"]["id"]." ) 
                    LEFT JOIN ost_ticket_attachment attach ON ticket.ticket_id=attach.ticket_id 
                WHERE ( ticket.user_id=".$_SESSION["_auth"]["user"]["id"]." OR collab.user_id=".$_SESSION["_auth"]["user"]["id"]." )  
                GROUP BY 
                    status.id 
                ORDER BY 
                    status.name ASC";

                $result = $mysqli->query($query);

                while($row = $result->fetch_array())
                 echo '      <option value="'.$row[0].'">'.$row[1].'</option>';

                ?>
                </select>
            </td>
            <th><b>Temas de Ayuda</b></th>
            <td>
                <select name="top"> <!--Billy 1/02/2016 Se convirtio en un select-->
                <option value="">Seleccione</option> <!--Billy 1/02/2016 Opcion seleccione del campo tema de ayuda-->
<?php

    $query = "  SELECT topic.topic_id,topic.topic
                FROM ost_ticket ticket 
                    LEFT JOIN ost_ticket_status status ON (status.id = ticket.status_id) 
                    LEFT JOIN ost_ticket__cdata cdata ON (cdata.ticket_id = ticket.ticket_id) 
                    LEFT JOIN ost_department dept ON (ticket.dept_id=dept.dept_id) 
                    LEFT JOIN ost_ticket_collaborator collab ON (collab.ticket_id = ticket.ticket_id AND collab.user_id =".$_SESSION["_auth"]["user"]["id"]." ) 
                    LEFT JOIN ost_ticket_attachment attach ON ticket.ticket_id=attach.ticket_id
                    LEFT JOIN ost_help_topic topic ON (ticket.topic_id=topic.topic_id)
                WHERE ( ticket.user_id=".$_SESSION["_auth"]["user"]["id"]." OR collab.user_id=".$_SESSION["_auth"]["user"]["id"]." ) 
                GROUP BY 
                    status.id 
                ORDER BY 
                    status.name ASC";

    $result = $mysqli->query($query);

    while($row = $result->fetch_array())
        echo '      <option value="'.$row[0].'">'.$row[1].'</option>';

?>
                </select>
            </td>
        </tr>
        <tr>
            <th><b>Departamento</b></th>
            <td>
                <select name="dep"> <!--Billy 1/02/2016 Se convirtio en un select-->
                <option value="">Seleccione</option> <!--Billy 1/02/2016 Opcion seleccione del campo departamento-->
<?php

    $query = "  SELECT department.dept_id,department.dept_name
                FROM ost_ticket ticket 
                    LEFT JOIN ost_ticket_status status ON (status.id = ticket.status_id) 
                    LEFT JOIN ost_ticket__cdata cdata ON (cdata.ticket_id = ticket.ticket_id) 
                    LEFT JOIN ost_department dept ON (ticket.dept_id=dept.dept_id) 
                    LEFT JOIN ost_ticket_collaborator collab ON (collab.ticket_id = ticket.ticket_id AND collab.user_id =".$_SESSION["_auth"]["user"]["id"]." ) 
                    LEFT JOIN ost_ticket_attachment attach ON ticket.ticket_id=attach.ticket_id
                    LEFT JOIN ost_department department ON (ticket.dept_id=department.dept_id)
                WHERE ( ticket.user_id=".$_SESSION["_auth"]["user"]["id"]." OR collab.user_id=".$_SESSION["_auth"]["user"]["id"]." )
                GROUP BY 
                    department.dept_id 
                ORDER BY 
                    department.dept_name ASC";

    $result = $mysqli->query($query);

    while($row = $result->fetch_array())
        echo '      <option value="'.$row[0].'">'.$row[1].'</option>';

?>
                </select>
            </td>
            <th><b>Status Localizador</b></th>
            <td>
                <select name="sta"> <!--Billy 1/02/2016 Se convirtio en un select-->
                <option value="">Seleccione</option> <!--Billy 1/02/2016 Opcion seleccione del campo status localizador-->
<?php

    $query = "  SELECT cast(cdata.status_loc as char(100) charset utf8)
                FROM ost_ticket ticket 
                    LEFT JOIN ost_ticket_status status ON (status.id = ticket.status_id) 
                    LEFT JOIN ost_ticket__cdata cdata ON (cdata.ticket_id = ticket.ticket_id) 
                    LEFT JOIN ost_department dept ON (ticket.dept_id=dept.dept_id) 
                    LEFT JOIN ost_ticket_collaborator collab ON (collab.ticket_id = ticket.ticket_id AND collab.user_id =".$_SESSION["_auth"]["user"]["id"]." ) 
                    LEFT JOIN ost_ticket_attachment attach ON ticket.ticket_id=attach.ticket_id
                WHERE ( ticket.user_id=".$_SESSION["_auth"]["user"]["id"]." OR collab.user_id=".$_SESSION["_auth"]["user"]["id"]." ) AND 
                    cast(cdata.status_loc as char(100) charset utf8) != ''
                GROUP BY 
                    cdata.status_loc
                ORDER BY 
                    cdata.status_loc ASC";

    $result = $mysqli->query($query);

    while($row = $result->fetch_array())
        echo '      <option value="'.$row[0].'">'.$row[0].'</option>';

?>
                </select>
            </td>
        </tr>
        <tr>
            <th><b>Desde</b></th>
            <td>
            <input type="text" id ="datepicker" name="des"> <!--Billy 1/02/2016 Input para el datepicker desde-->
                        </td>
            <th><b>Hasta</b></th>
            <td>
                <input type="text" id ="datepicker2" name="has"> <!--Billy 1/02/2016 Input para el datepicker hasta-->
            </td>
        </tr>
        <tr>
            <th><b>Localizador</b></th>
            <td>
                <input type="text" name="loc" maxlength="6" autocomplete="off" autocorrect="off" autocapitalize="off">
            </td>

            <th></th>
            <td >
            </td>

        </tr>
    </table> 
    <center><button type="submit" class="btn btn-default">Consultar</center>
</form>
        </div>
        
        <div class="modal-footer">
          <button type="button" class="btn btn-info" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
      <!--Fin del Body del Modal-->
  </div>
  </div>


<button type="button" class="btn btn-sm btn-link text" data-toggle="modal" data-target="#myModal" style="padding-left:10px; text-decoration:none" id="advanced-search">[Búsqueda Avanzada]</button>
<br>
<a class="refresh" href="tickets.php"><?php echo __('Refresh'); ?></a>


<!--///////////////////////////////////Fin 25/01/2016 Billy Modal para busqueda avanzada////////////////////////////////////////////////////////////-->



<script type="text/javascript">
    
/*Efecto para que muestre el formulario en el modal*/
        $("#advanced-search").click(function(){
            $("#advancedsearch").show("slow");
            
        });
/*Fin del efecto para que muestre el formulario en el modal*/


    /*
        $("#advanced-search2").click(function(){
            $("#advancedsearch").hide("slow");
            $("#advanced-search").show();
            $("#advanced-search2").hide();
        });*/
</script>


<!--Inicio Billy 26/01/2016 cambia el tamaño del contenedor en el cliente-->
<script type="text/javascript">
    $("#container").css("width","90%");
</script>
<!--Fin Billy 26/01/2016 cambia el tamaño del contenedor en el cliente-->


<!--Inicio Billy 27/01/2016 se agregaron los campos GDS y Pago a la tabla y se ancho la tabla-->
<table id="ticketTable" width="100%" border="0" cellspacing="0" cellpadding="0">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th nowrap>
                <a href="tickets.php?sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Ticket ID"><b><?php echo __('Ticket #');?></a>
            </th>
            <th width="120">
                <a href="tickets.php?sort=date&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Date"><b><?php echo __('Create Date');?></a>
            </th>
            <th width="100">
                <a href="tickets.php?sort=status&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Status"><b><?php echo __('Status');?></a>
            </th>
            <th width="320">
                <a href="tickets.php?sort=subj&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Subject"><b><?php echo __('Subject');?></a>
            </th>
            <th width="120">
                <a href="tickets.php?sort=dept&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Department"><b><?php echo __('Department');?></a>
            </th>

            <th width="120"><b>GDS</th>

            <th width="120"><b>Localizador</th>
            <th width="120"><b>Status</th>

            <th width="120"><b>Pago</th>

           <!--Fin Billy 27/01/2016 se agregaron los campos GDS y Pago a la tabla y se ancho la tabla-->
        </tr>

    </thead>
    <tbody>
    <?php
     $subject_field = TicketForm::objects()->one()->getField('subject');
     if($res && ($num=db_num_rows($res))) {
        $defaultDept=Dept::getDefaultDeptName(); //Default public dept.
        while ($row = db_fetch_array($res)) {
            $dept= $row['ispublic']? $row['dept_name'] : $defaultDept;
            $subject = Format::truncate($subject_field->display(
                $subject_field->to_php($row['subject']) ?: $row['subject']
            ), 40);
            if($row['attachments'])
                $subject.='  &nbsp;&nbsp;<span class="Icon file"></span>';

            $ticketNumber=$row['number'];
            if($row['isanswered'] && !strcasecmp($row['state'], 'open')) {
                $subject="<b>$subject</b>";
                $ticketNumber="<b>$ticketNumber</b>";
            }
            ?>
            <tr id="<?php echo $row['ticket_id']; ?>">
                <td >
                <a class="Icon <?php echo strtolower($row['source']); ?>Ticket" title="<?php echo $row['email']; ?>"
                    href="tickets.php?id=<?php echo $row['ticket_id']; ?>"><?php echo $ticketNumber; ?></a>
                </td>
                <td>&nbsp;<?php echo Format::db_date($row['created']); ?></td>
                <td>&nbsp;<?php echo $row['status']; ?></td>
                <td>
                    <a href="tickets.php?id=<?php echo $row['ticket_id']; ?>"><?php echo $subject; ?></a>
                </td>
                <td>&nbsp;<?php echo Format::truncate($dept,30); ?></td>
<?php


//Inicio Billy 27/01/2016 Query para traer de la base de datos el localizador, el estatus y el tipo de pago//////

$query2 = " SELECT 
                UPPER(CAST(cdata.localizador AS char(100) CHARACTER SET utf8)),
                CAST(cdata.status_loc AS char(100) CHARACTER SET utf8),
                fev.value
                FROM `ost_ticket__cdata` cdata 
                LEFT JOIN ost_form_entry fe ON (cdata.ticket_id = fe.object_id)
                LEFT JOIN ost_form_entry_values fev ON (fe.id = fev.entry_id)
            WHERE ticket_id = ".$row['ticket_id']." and fev.field_id = '37'
            GROUP BY ticket_id";

$result2 = $mysqli->query($query2);
$row2 = $result2->fetch_array();

$pago=explode(":",str_replace(array('"', "}"), array("", ""),$row2[2]));  //con la funcion explode separo en dos el valor del arreglo al encontrar : y con str_replace limpio el string para mostrar el tipo de pago

//Fin Billy 27/01/2016 Query para traer de la base de datos el localizador, el estatus y el tipo de pago//////

//Inicio Billy 27/01/2016 Query para traer de la base de datos el tipo de gds//////

$query3 = " SELECT 
                fev.value
                FROM `ost_ticket__cdata` cdata 
                LEFT JOIN ost_form_entry fe ON (cdata.ticket_id = fe.object_id)
                LEFT JOIN ost_form_entry_values fev ON (fe.id = fev.entry_id)
            WHERE ticket_id = ".$row['ticket_id']." and fev.field_id = '44'
            GROUP BY ticket_id";

$result3 = $mysqli->query($query3);
$row3= $result3->fetch_array();


$gds=explode(":",str_replace(array('"', "}"), array("", ""),$row3[0]));  //con la funcion explode separo en dos el valor del arreglo al encontrar : y con str_replace limpio el string para mostrar el gds

//Fin Billy 27/01/2016 Query para traer de la base de datos el tipo de gds//////

?>

<!--Inicio Billy 27/01/2016 lleno las columnas de la tabla con los datos traidos desde la base de datos-->

                <td>&nbsp;<?php echo $gds[1]; ?></td> <!--Lleno la columna gds con los datos traidos desde la base de datos-->
                <td>&nbsp;<?php echo strtoupper($row2[0]); ?></td> <!--Lleno la columna localizador con los datos traidos desde la base de datos-->
                <td>&nbsp;<?php echo $row2[1]; ?></td> <!--Lleno la columna estatus con los datos traidos desde la base de datos-->
                <td>&nbsp;<?php echo $pago[1]; ?></td> <!--Lleno la columna pago con los datos traidos desde la base de datos-->

<!--Fin Billy 27/01/2016 lleno las columnas de la tabla con los datos traidos desde la base de datos-->       

            </tr>
        <?php
        }

     } else {
         echo '<tr><td colspan="9">'.__('Your query did not match any records').'</td></tr>';
     }
    ?>
    </tbody>
</table>

<br>
<?php
if($res && $num>0) {

    if((($pageNav->getPage())-1) <= 0)
        $pagea = 1;
    else
        $pagea = ($pageNav->getPage())-1;

    if((($pageNav->getPage())+1) >= $pageNav->getNumPages())
        $pages = $pageNav->getNumPages();
    else
        $pages = ($pageNav->getPage())+1;

    $primero   = "tickets.php?sort=".$_GET["sort"]."&order=".$_GET["order"]."&p=1&des=".$_GET["des"]."&has=".$_GET["has"]."&loc=".$_GET["loc"];
    $anterior  = "tickets.php?sort=".$_GET["sort"]."&order=".$_GET["order"]."&p=$pagea&des=".$_GET["des"]."&has=".$_GET["has"]."&loc=".$_GET["loc"];
    $siguiente = "tickets.php?sort=".$_GET["sort"]."&order=".$_GET["order"]."&p=$pages&des=".$_GET["des"]."&has=".$_GET["has"]."&loc=".$_GET["loc"];
    $ultimo    = "tickets.php?sort=".$_GET["sort"]."&order=".$_GET["order"]."&p=".$pageNav->getNumPages()."&des=".$_GET["des"]."&has=".$_GET["has"]."&loc=".$_GET["loc"];

//Inicio Billy 25/01/2016 Paginador del cliente//
    echo '  <div style="text-align:center;">
                <a href="'.$primero.'"><span class="glyphicon glyphicon-backward"></span></a>&nbsp;
                <a href="'.$anterior.'"><span class="glyphicon glyphicon-chevron-left"></span></a>&nbsp;
                '.__('Page').''.$pageNav->getPageLinks().'&nbsp;
                <a href="'.$siguiente.'"><span class="glyphicon glyphicon-chevron-right"></span></a>&nbsp;
                <a href="'.$ultimo.'"><span class="glyphicon glyphicon-forward"></span></a>&nbsp;
            </div>';

            //Fin Billy 25/01/2016 paginador del cliente//
}

?>
  
<!--Inicio Billy 1/02/2016 Agrego las clases del datepicker-->

  <script src="/ostickets/upload/js/jquery-1.12.0.js"></script>
  <script src="/ostickets/upload/js/jquery-ui.js"></script>

<!--Fin Billy 1/02/2016 Agrego las clases del datepicker-->

<!--Inicio Billy 1/02/2016 Funciones para el datepicker-->
<script>

/*Inicio Billy 1/02/2016 Funcion para mostrar el datepicker*/
$(function() {
    $("#datepicker").datepicker();
    $( "#datepicker" ).datepicker('option', {dateFormat: 'yy-mm-dd'});
  });

$(function() {
    $("#datepicker2").datepicker();
    $( "#datepicker2" ).datepicker('option', {dateFormat: 'yy-mm-dd'});
  });

/*Fin Billy 1/02/2016 Funcion para mostrar el datepicker*/


/*Inicio Billy 1/02/2016 Funcion para cambiar el idioma del datepicker*/
$(function($){
    $.datepicker.regional['es'] = {
        closeText: 'Cerrar',
        prevText: '<Ant',
        nextText: 'Sig>',
        currentText: 'Hoy',
        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr', 'May','Jun','Jul','Ago','Sep', 'Oct','Nov','Dic'],
        dayNames: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        dayNamesShort: ['Dom','Lun','Mar','Mié','Juv','Vie','Sáb'],
        dayNamesMin: ['Do','Lu','Ma','Mi','Ju','Vi','Sá'],
        weekHeader: 'Sm',
        dateFormat: 'dd/mm/yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['es']);
});

/*Fin Billy 1/02/2016 Funcion para cambiar el idioma del datepicker*/

</script>
<!--Fin Billy 1/02/2016 Funciones para el datepicker-->