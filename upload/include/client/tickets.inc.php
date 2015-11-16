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
            $qwhere.=" AND cdata.`localizador` LIKE '$searchTerm%'";
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
if(isset($_GET["loc"]) && $_GET["loc"] != "")
    $qwhere .= " AND cast(cdata.localizador as char(100) charset utf8) LIKE '%".$_GET["loc"]."%'";

TicketForm::ensureDynamicDataView();


//more stuff...
$qselect.=' ,count(attach_id) as attachments ';
$qfrom.=' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON  ticket.ticket_id=attach.ticket_id ';
$qgroup=' GROUP BY ticket.ticket_id';

$more = "";
$nume = 0;

if(isset($_GET["est"])){
    $i=0;
    $str="";
    foreach ($_GET['est'] as $selected) {
        if ($i > 0) {
            $str .= "','$selected";
        }else{
            $str .= "$selected";
        }
        $i++;
    }
    $more .= " AND status.state IN ('$str')";
}

if(isset($_GET["top"])){
    $i=0;
    $str="";
    foreach ($_GET['top'] as $selected) {
        if ($i > 0) {
            $str .= "','$selected";
        }else{
            $str .= "$selected";
        }
        $i++;
    }
    $more .= " AND ticket.topic_id IN ('$str')";
}
if(isset($_GET["dep"])){
    $i=0;
    $str="";
    foreach ($_GET['dep'] as $selected) {
        if ($i > 0) {
            $str .= "','$selected";
        }else{
            $str .= "$selected";
        }
        $i++;
    }
    $more .= " AND ticket.dept_id IN ('$str')";
}
if(isset($_GET["sta"])){
    $i=0;
    $str="";
    foreach ($_GET['sta'] as $selected) {
        if ($i > 0) {
            $str .= "','$selected";
        }else{
            $str .= "$selected";
        }
        $i++;
    }
    $more .= " AND cast(cdata.status_loc as char(100) charset utf8) IN ('$str')";
}
if(isset($_GET["des"]) && $_GET["des"] != "")
    $more .= " AND ticket.created >= '".$_GET["des"]." 01:01:01'";
if(isset($_GET["has"]) && $_GET["has"] != "")
    $more .= " AND ticket.created <= '".$_GET["has"]." 23:59:59'";
if(isset($_GET["loc"]))
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

$total=db_count('SELECT count(DISTINCT ticket.ticket_id) '.$qfrom.' '.$qwhere. ' '.$_SESSION['more']);//Aplicando la sesión PHP en la sentencia

$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$qstr = '&amp;'. Http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);
$pageNav->setURL('tickets.php', $qs);
$query="$qselect $qfrom $qwhere $more $qgroup ORDER BY $order_by $order LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
//echo $_GET["sta"];

$res = db_query($query);
$showing=($res && db_num_rows($res))?$pageNav->showing():"";
if(!$results_type)
{
    $results_type=ucfirst($status).' Tickets';
}
$showing.=($status)?(' '.$results_type):' '.__('All Tickets');
if($search)
    $showing=__('Search Results').": $showing";

/*MICOD: Buscamos el número de registros para los tickets abiertos y cerrados y los mostramos al lado de los botones en el menú*/
$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting
    $mysqli = new mysqli("localhost", "osticket", "0571ck37", "osticket1911");
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
            <ul id="nav" class="flush-left" style="margin-top: -20px;">
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

<!--No tocar, dejarlo así--> 
<a style="padding-left:10px;cursor:pointer;" id="advanced-search">[Busqueda Avanzada]</a>
<a style="padding-left:10px;cursor:pointer; display: none;" id="advanced-search2">[Busqueda Avanzada]</a>
<br>

<a class="refresh" href="tickets.php"><?php echo __('Refresh'); ?></a>

<form action="tickets.php" id="advancedsearch" method="get" style="display:none;"> 
    <br>
    <table width="100%" style="border-collapse: separate; border-spacing:  3px;" id="search_advanced">
        <tr>
            <th style="text-align: right; width: 15%; border-bottom: 5px;">
                Estado:
            </th>
            <td style="width: 35%; border-spacing: 10px 20px; border-collapse: separate">
                <select name="est[]" multiple>
<?php

    $mysqli = new mysqli("localhost", "osticket", "0571ck37", "osticket1911");

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

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
            <th style="text-align: right; width: 15%;">
                Temas de Ayuda:
            </th>
            <td style="width: 35%;">
                <select name="top[]" multiple>
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
            <th style="text-align: right; width: 15%;">
                Departamento:
            </th>
            <td style="width: 35%;">
                <select name="dep[]" multiple>
<?php

    $mysqli = new mysqli("localhost", "osticket", "0571ck37", "osticket1911");

    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

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
            <th style="text-align: right; width: 15%;">
                Status Localizador:
            </th>
            <td style="width: 35%;">
                <select name="sta[]" multiple>
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
            <th style="text-align: right;">
                Desde:
            </th>
            <td>
                <input type="date" name="des">
            </td>
            <th style="text-align: right;">
                Hasta:
            </th>
            <td width="25%">
                <input type="date" name="has">
            </td>
        </tr>
        <tr>
            <th style="text-align: right;">
                Localizador:
            </th>
            <td >
                <input type="text" name="loc" maxlength="6">
            </td>
            <td style="text-align: right;" colspan="4">
                <input type="submit" value="Consultar">
            </td>
        </tr>
    </table>
    
    <br><br>
</form>

<script type="text/javascript">
    
    if ($("#advancedsearch").is(":hidden")){
        $("#advanced-search").click(function(){
            $("#advancedsearch").show("slow");
            $("#advanced-search").hide();
            $("#advanced-search2").show();
        });
    }
        $("#advanced-search2").click(function(){
            $("#advancedsearch").hide("slow");
            $("#advanced-search").show();
            $("#advanced-search2").hide();
        });
</script>

<table id="ticketTable" width="800" border="0" cellspacing="0" cellpadding="0">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th nowrap>
                <a href="tickets.php?sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Ticket ID"><?php echo __('Ticket #');?></a>
            </th>
            <th width="120">
                <a href="tickets.php?sort=date&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Date"><?php echo __('Create Date');?></a>
            </th>
            <th width="100">
                <a href="tickets.php?sort=status&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Status"><?php echo __('Status');?></a>
            </th>
            <th width="320">
                <a href="tickets.php?sort=subj&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Subject"><?php echo __('Subject');?></a>
            </th>
            <th width="120">
                <a href="tickets.php?sort=dept&order=<?php echo $negorder; ?><?php echo $qstr; ?>" title="Sort By Department"><?php echo __('Department');?></a>
            </th>
            <th width="120">
                Localizador
            </th>
            <th width="120">
                Status
            </th>
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
                <td>
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

$mysqli = new mysqli("localhost", "osticket", "0571ck37", "osticket1911");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$query2 = " SELECT 
                CAST(localizador AS char(100) CHARACTER SET utf8),
                CAST(status_loc AS char(100) CHARACTER SET utf8) 
            FROM `ost_ticket__cdata` 
            WHERE ticket_id = ".$row['ticket_id'];
$result2 = $mysqli->query($query2);
$row2 = $result2->fetch_array();

?>
                <td>&nbsp;<?php echo strtoupper($row2[0]); ?></td>
                <td>&nbsp;<?php echo $row2[1]; ?></td>
            </tr>
        <?php
        }

     } else {
         echo '<tr><td colspan="6">'.__('Your query did not match any records').'</td></tr>';
     }
    ?>
    </tbody>
</table>
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

    echo '  <div>
                &nbsp;'.__('Page').': 
                <a href="'.$primero.'">Primero</a>&nbsp;
                <a href="'.$anterior.'">Anterior</a>&nbsp;
                '.$pageNav->getPageLinks().'&nbsp;
                <a href="'.$siguiente.'">Siguiente</a>&nbsp;
                <a href="'.$ultimo.'">Ultimo</a>&nbsp;
            </div>';
}

?>

<div class="dialog" style="display:none;" id="advanced-search">
    <h3><?php echo __('Advanced Ticket Search');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <form action="tickets.php" method="post" id="search" name="search">
        <input type="hidden" name="a" value="search">
        <fieldset class="query">
            <input type="input" id="query" name="query" size="20" placeholder="<?php echo __('Keywords') . ' &mdash; ' . __('Optional'); ?>">
        </fieldset>
        <fieldset class="span6">
            <label for="statusId"><?php echo __('Statuses');?>:</label>
            <select id="statusId" name="statusId">
                 <option value="">&mdash; <?php echo __('Any Status');?> &mdash;</option>
                <?php
                foreach (TicketStatusList::getStatuses(
                            array('states' => array('open', 'closed'))) as $s) {
                    echo sprintf('<option data-state="%s" value="%d">%s</option>',
                            $s->getState(), $s->getId(), __($s->getName()));
                }
                ?>
            </select>
        </fieldset>
        <fieldset class="span6">
            <label for="deptId"><?php echo __('Departments');?>:</label>
            <select id="deptId" name="deptId">
                <option value="">&mdash; <?php echo __('All Departments');?> &mdash;</option>
                <?php
                if(($mydepts = $thisstaff->getDepts()) && ($depts=Dept::getDepartments())) {
                    foreach($depts as $id =>$name) {
                        if(!in_array($id, $mydepts)) continue;
                        echo sprintf('<option value="%d">%s</option>', $id, $name);
                    }
                }
                ?>
            </select>
        </fieldset>
        <fieldset class="span6">
            <label for="flag"><?php echo __('Flags');?>:</label>
            <select id="flag" name="flag">
                 <option value="">&mdash; <?php echo __('Any Flags');?> &mdash;</option>
                 <?php
                 if (!$cfg->showAnsweredTickets()) { ?>
                 <option data-state="open" value="answered"><?php echo __('Answered');?></option>
                 <?php
                 } ?>
                 <option data-state="open" value="overdue"><?php echo __('Overdue');?></option>
            </select>
        </fieldset>
        <fieldset class="owner span6">
            <label for="assignee"><?php echo __('Assigned To');?>:</label>
            <select id="assignee" name="assignee">
                <option value="">&mdash; <?php echo __('Anyone');?> &mdash;</option>
                <option value="s0">&mdash; <?php echo __('Unassigned');?> &mdash;</option>
                <option value="s<?php echo $thisstaff->getId(); ?>"><?php echo __('Me');?></option>
                <?php
                if(($users=Staff::getStaffMembers())) {
                    echo '<OPTGROUP label="'.sprintf(__('Agents (%d)'),count($users)-1).'">';
                    foreach($users as $id => $name) {
                        if ($id == $thisstaff->getId())
                            continue;
                        $k="s$id";
                        echo sprintf('<option value="%s">%s</option>', $k, $name);
                    }
                    echo '</OPTGROUP>';
                }

                if(($teams=Team::getTeams())) {
                    echo '<OPTGROUP label="'.__('Teams').' ('.count($teams).')">';
                    foreach($teams as $id => $name) {
                        $k="t$id";
                        echo sprintf('<option value="%s">%s</option>', $k, $name);
                    }
                    echo '</OPTGROUP>';
                }
                ?>
            </select>
        </fieldset>
        <fieldset class="span6">
            <label for="topicId"><?php echo __('Help Topics');?>:</label>
            <select id="topicId" name="topicId">
                <option value="" selected >&mdash; <?php echo __('All Help Topics');?> &mdash;</option>
                <?php
                if($topics=Topic::getHelpTopics()) {
                    foreach($topics as $id =>$name)
                        echo sprintf('<option value="%d" >%s</option>', $id, $name);
                }
                ?>
            </select>
        </fieldset>
        <fieldset class="owner span6">
            <label for="staffId"><?php echo __('Closed By');?>:</label>
            <select id="staffId" name="staffId">
                <option value="0">&mdash; <?php echo __('Anyone');?> &mdash;</option>
                <option value="<?php echo $thisstaff->getId(); ?>"><?php echo __('Me');?></option>
                <?php
                if(($users=Staff::getStaffMembers())) {
                    foreach($users as $id => $name)
                        echo sprintf('<option value="%d">%s</option>', $id, $name);
                }
                ?>
            </select>
        </fieldset>
        <fieldset class="date_range">
            <label><?php echo __('Date Range').' &mdash; '.__('Create Date');?>:</label>
            <input class="dp" type="input" size="20" name="startDate">
            <span class="between"><?php echo __('TO');?></span>
            <input class="dp" type="input" size="20" name="endDate">
        </fieldset>
        <?php
        $tform = TicketForm::objects()->one();
        echo $tform->getForm()->getMedia();
        foreach ($tform->getInstance()->getFields() as $f) {
            if (!$f->hasData())
                continue;
            elseif (!$f->getImpl()->hasSpecialSearch())
                continue;
            ?><fieldset class="span6">
            <label><?php echo $f->getLabel(); ?>:</label><div><?php
                     $f->render('search'); ?></div>
            </fieldset>
        <?php } ?>
        <hr/>
        <div id="result-count" class="clear"></div>
        <p>
            <span class="buttons pull-right">
                <input type="submit" value="<?php echo __('Search');?>">
            </span>
            <span class="buttons pull-left">
                <input type="reset" value="<?php echo __('Reset');?>">
                <input type="button" value="<?php echo __('Cancel');?>" class="close">
            </span>
            <span class="spinner">
                <img src="./images/ajax-loader.gif" width="16" height="16">
            </span>
        </p>
    </form>
</div>