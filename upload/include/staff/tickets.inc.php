<!--Inicio Billy 25/01/2016-->

<link rel="stylesheet" href="/ostickets/upload/css/bootstrap.css">
  <script src="/ostickets/upload/css/bootstrap.min.js"></script>

<!--Fin Billy 25/01/2016-->

<script type="text/javascript">
    $("#container").css("width","90%");
</script>

<?php
if(!defined('OSTSCPINC') || !$thisstaff || !@$thisstaff->isStaff()) die('Access Denied');

$qs= array(); //Query string collector
if($_REQUEST['status']) { //Query string status has nothing to do with the real status used below; gets overloaded.
    $qs += array('status' => $_REQUEST['status']);
}
//See if this is a search
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

$showoverdue=$showanswered=false;
$staffId=0; //Nothing for now...TODO: Allow admin and manager to limit tickets to single staff level.
$showassigned= true; //show Assigned To column - defaults to true

//Get status we are actually going to use on the query...making sure it is clean!
$status=null;
switch(strtolower($_REQUEST['status'])){ //Status is overloaded
    case 'open':
        $status='open';
        $results_type=__('Open Tickets');
        break;
    case 'closed':
        $status='closed';
        $results_type=__('Closed Tickets');
        $showassigned=true; //closed by.
        break;
    case 'overdue':
        $status='open';
        $showoverdue=true;
        $results_type=__('Overdue Tickets');
        break;
    case 'assigned':
        $status='open';
        $staffId=$thisstaff->getId();
        $results_type=__('My Tickets');
        break;
    case 'answered':
        $status='open';
        $showanswered=true;
        $results_type=__('Answered Tickets');
        break;
    default:
        if (!$search && !isset($_REQUEST['advsid'])) {
            $_REQUEST['status']=$status='open';
            $results_type=__('Open Tickets');
        }
}

// Stash current queue view
$_SESSION['::Q'] = $_REQUEST['status'];

$qwhere ='';
/*
   STRICT DEPARTMENTS BASED PERMISSION!
   User can also see tickets assigned to them regardless of the ticket's dept.
*/

$depts=$thisstaff->getDepts();
$qwhere =' LEFT JOIN ost_ticket_thread thread ON (thread.ticket_id=ticket.ticket_id) WHERE ( '
        .'  ( ticket.staff_id='.db_input($thisstaff->getId())
        .' AND status.state="open") ';

if(!$thisstaff->showAssignedOnly())
    $qwhere.=' OR ticket.dept_id IN ('.($depts?implode(',', db_input($depts)):0).')';

if(($teams=$thisstaff->getTeams()) && count(array_filter($teams)))
    $qwhere.=' OR (ticket.team_id IN ('.implode(',', db_input(array_filter($teams)))
            .') AND status.state="open") ';

$qwhere .= ' )';

//STATUS to states
$states = array(
    'open' => array('open'),
    'closed' => array('closed'));

if($status && isset($states[$status])) {
    $qwhere.=' AND status.state IN (
                '.implode(',', db_input($states[$status])).' ) ';
}

if (isset($_REQUEST['uid']) && $_REQUEST['uid']) {
    $qwhere .= ' AND (ticket.user_id='.db_input($_REQUEST['uid'])
            .' OR collab.user_id='.db_input($_REQUEST['uid']).') ';
    $qs += array('uid' => $_REQUEST['uid']);
}

//Queues: Overloaded sub-statuses  - you've got to just have faith!
if($staffId && ($staffId==$thisstaff->getId())) { //My tickets
    $results_type=__('Assigned Tickets');
    $qwhere.=' AND ticket.staff_id='.db_input($staffId);
    $showassigned=false; //My tickets...already assigned to the staff.
}elseif($showoverdue) { //overdue
    $qwhere.=' AND ticket.isoverdue=1 ';
}elseif($showanswered) { ////Answered
    $qwhere.=' AND ticket.isanswered=1 ';
}elseif(!strcasecmp($status, 'open') && !$search) { //Open queue (on search OPEN means all open tickets - regardless of state).
    //Showing answered tickets on open queue??
    if(!$cfg->showAnsweredTickets())
        $qwhere.=' AND ticket.isanswered=0 ';

    /* Showing assigned tickets on open queue?
       Don't confuse it with show assigned To column -> F'it it's confusing - just trust me!
     */
    if(!($cfg->showAssignedTickets() || $thisstaff->showAssignedTickets())) {
        $qwhere.=' AND ticket.staff_id=0 '; //XXX: NOT factoring in team assignments - only staff assignments.
        $showassigned=false; //Not showing Assigned To column since assigned tickets are not part of open queue
    }
}

//Search?? Somebody...get me some coffee
$deep_search=false;
$order_by=$order=null;
if($search):
    $qs += array('a' => $_REQUEST['a'], 't' => $_REQUEST['t']);
    //query
    if($searchTerm){
        $qs += array('query' => $searchTerm);
        $queryterm=db_real_escape($searchTerm,false); //escape the term ONLY...no quotes.

//Anthony Inicio 04/01/2015
        $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
        /* check connection */
        if (mysqli_connect_errno()) {
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        $aguja = explode(" ",$queryterm);
        $pajar = "";
        for($i=0;$i<=strlen($aguja)+1;$i++){
            if($i == 0)
                $pajar .= " body LIKE '%".$aguja[$i]."%'";
            else
                $pajar .= " AND body LIKE '%".$aguja[$i]."%'";
        }
        $comentario_sql =  "SELECT id
                            FROM `ost_ticket_thread` 
                            WHERE ".$pajar;

        $comentario_res = $mysqli->query($comentario_sql);
        $comentario_row = mysqli_num_rows($comentario_res);
//Anthony Final 04/01/2015

        if (is_numeric($searchTerm)) {
            $qwhere.=" AND ticket.`number` LIKE '$queryterm%'";
        } elseif (strpos($searchTerm,'@') && Validator::is_email($searchTerm)) {
            //pulling all tricks!
            # XXX: What about searching for email addresses in the body of
            #      the thread message
            $qwhere.=" AND email.address='$queryterm'";

//Anthony Inicio 04/01/2015
        } elseif ($comentario_row > 0) {
            $aguja = explode(" ",$queryterm);
            $pajar = "";
            for($i=0;$i<=strlen($aguja)+1;$i++){
                if($i == 0)
                    $pajar .= " AND thread.body LIKE '%".$aguja[$i]."%'";
                else
                    $pajar .= " AND thread.body LIKE '%".$aguja[$i]."%'";
            }
            $qwhere.=$pajar;
//Anthony Final 04/01/2015

        } else {//Deep search!
            //This sucks..mass scan! search anything that moves!
            require_once(INCLUDE_DIR.'ajax.tickets.php');
            $tickets = TicketsAjaxApi::_search(array('query'=>$queryterm));
            
            if (count($tickets)) {
                $ticket_ids = implode(',',db_input($tickets));
                $qwhere .= ' AND ticket.ticket_id IN ('.$ticket_ids.')';
                $order_by = 'FIELD(ticket.ticket_id, '.$ticket_ids.')';
                $order = ' ';
            }
            else
                // No hits -- there should be an empty list of results
                $qwhere .= ' AND false';
        }
   }

endif;

//Original
// if ($_REQUEST['advsid'] && isset($_SESSION['adv_'.$_REQUEST['advsid']])) {
//     $ticket_ids = implode(',', db_input($_SESSION['adv_'.$_REQUEST['advsid']]));
//     $qs += array('advsid' => $_REQUEST['advsid']);

//     $qwhere .= ' AND ticket.ticket_id IN ('.$ticket_ids.') ';
//     // Thanks, http://stackoverflow.com/a/1631794
//     $order_by = 'FIELD(ticket.ticket_id, '.$ticket_ids.')';
//     $order = ' ';
// }
//Fin Original

//Inicio 17/02/2016 Nueva forma de mostrar el resultado de la consulta de la busqueda avanzada
if ($_REQUEST['advsid']) {
    $mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    $result_tkt = $mysqli->query($_SESSION["consulta"]);
    while($filas_tkt = $result_tkt->fetch_array())
        $ticket_ids .= $filas_tkt[0].",";
    $ticket_ids = substr($ticket_ids, 0,strlen($ticket_ids)-1);

    $qwhere .= ' AND ticket.ticket_id IN ('.$ticket_ids.') ';
   
    // Thanks, http://stackoverflow.com/a/1631794
    $order_by = 'FIELD(ticket.ticket_id, '.$ticket_ids.')';
    $order = ' ';
}
//Fin 17/02/2016 Nueva forma de mostrar el resultado de la consulta de la busqueda avanzada

$qwhere .= ' GROUP BY thread.ticket_id ';

$sortOptions=array('date'=>'effective_date','ID'=>'ticket.`number`*1',
    'pri'=>'pri.priority_urgency','name'=>'user.name','subj'=>'cdata.subject',
    'status'=>'status.name','assignee'=>'assigned','staff'=>'staff',
    'dept'=>'dept.dept_name');

$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');

//Sorting options...
$queue = isset($_REQUEST['status'])?strtolower($_REQUEST['status']):$status;
if($_REQUEST['sort'] && $sortOptions[$_REQUEST['sort']])
    $order_by =$sortOptions[$_REQUEST['sort']];
elseif($sortOptions[$_SESSION[$queue.'_tickets']['sort']]) {
    $_REQUEST['sort'] = $_SESSION[$queue.'_tickets']['sort'];
    $_REQUEST['order'] = $_SESSION[$queue.'_tickets']['order'];

    $order_by = $sortOptions[$_SESSION[$queue.'_tickets']['sort']];
    $order = $_SESSION[$queue.'_tickets']['order'];
}

if($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])])
    $order=$orderWays[strtoupper($_REQUEST['order'])];

//Save sort order for sticky sorting.
if($_REQUEST['sort'] && $queue) {
    $_SESSION[$queue.'_tickets']['sort'] = $_REQUEST['sort'];
    $_SESSION[$queue.'_tickets']['order'] = $_REQUEST['order'];
}

//Set default sort by columns.
if(!$order_by ) {
    if($showanswered)
        $order_by='ticket.lastresponse, ticket.created'; //No priority sorting for answered tickets.
    elseif(!strcasecmp($status,'closed'))
        $order_by='ticket.closed, ticket.created'; //No priority sorting for closed tickets.
    elseif($showoverdue) //priority> duedate > age in ASC order.
        $order_by='pri.priority_urgency ASC, ISNULL(ticket.duedate) ASC, ticket.duedate ASC, effective_date ASC, ticket.created';
    else //XXX: Add due date here?? No -
        $order_by='pri.priority_urgency ASC, effective_date DESC, ticket.created';
}

$order=$order?$order:'DESC';
if($order_by && strpos($order_by,',') && $order)
    $order_by=preg_replace('/(?<!ASC|DESC),/', " $order,", $order_by);

$sort=$_REQUEST['sort']?strtolower($_REQUEST['sort']):'pri.priority_urgency'; //Urgency is not on display table.
$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';

if($_GET['limit'])
    $qs += array('limit' => $_GET['limit']);

$qselect ='SELECT ticket.ticket_id,tlock.lock_id,ticket.`number`,ticket.dept_id,ticket.staff_id,ticket.team_id '
    .' ,user.name'
    .' ,email.address as email, dept.dept_name, status.state '
         .' ,status.name as status,ticket.source,ticket.isoverdue,ticket.isanswered,ticket.created ';

$qfrom=' FROM '.TICKET_TABLE.' ticket '.
       ' LEFT JOIN '.TICKET_STATUS_TABLE. ' status
            ON (status.id = ticket.status_id) '.
       ' LEFT JOIN '.USER_TABLE.' user ON user.id = ticket.user_id'.
       ' LEFT JOIN '.USER_EMAIL_TABLE.' email ON user.id = email.user_id'.
       ' LEFT JOIN '.DEPT_TABLE.' dept ON ticket.dept_id=dept.dept_id ';

if ($_REQUEST['uid'])
    $qfrom.=' LEFT JOIN '.TICKET_COLLABORATOR_TABLE.' collab
        ON (ticket.ticket_id = collab.ticket_id )';


$sjoin='';

if($search && $deep_search) {
    $sjoin.=' LEFT JOIN '.TICKET_THREAD_TABLE.' thread ON (ticket.ticket_id=thread.ticket_id )';
}

//get ticket count based on the query so far..
$total=db_count("SELECT count(DISTINCT ticket.ticket_id) $qfrom $sjoin ".substr($qwhere,0,stripos($qwhere, "GROUP BY thread.ticket_id")));
//pagenate
$pagelimit=($_GET['limit'] && is_numeric($_GET['limit']))?$_GET['limit']:PAGE_LIMIT;
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,$pagelimit);
$qstr = '&amp;'.http::build_query($qs);
$qs += array('sort' => $_REQUEST['sort'], 'order' => $_REQUEST['order']);
$pageNav->setURL('tickets.php', $qs);

//ADD attachment,priorities, lock and other crap
$qselect.=' ,IF(ticket.duedate IS NULL,IF(sla.id IS NULL, NULL, DATE_ADD(ticket.created, INTERVAL sla.grace_period HOUR)), ticket.duedate) as duedate '
         .' ,CAST(GREATEST(IFNULL(ticket.lastmessage, 0), IFNULL(ticket.closed, 0), IFNULL(ticket.reopened, 0), ticket.created) as datetime) as effective_date '
         .' ,ticket.created as ticket_created, CONCAT_WS(" ", staff.firstname, staff.lastname) as staff, team.name as team '
         .' ,IF(staff.staff_id IS NULL,team.name,CONCAT_WS(" ", staff.lastname, staff.firstname)) as assigned '
         .' ,IF(ptopic.topic_pid IS NULL, topic.topic, CONCAT_WS(" / ", ptopic.topic, topic.topic)) as helptopic '
         .' ,cdata.priority as priority_id, cdata.subject, pri.priority_desc, pri.priority_color';

$qfrom.=' LEFT JOIN '.TICKET_LOCK_TABLE.' tlock ON (ticket.ticket_id=tlock.ticket_id AND tlock.expire>NOW()
               AND tlock.staff_id!='.db_input($thisstaff->getId()).') '
       .' LEFT JOIN '.STAFF_TABLE.' staff ON (ticket.staff_id=staff.staff_id) '
       .' LEFT JOIN '.TEAM_TABLE.' team ON (ticket.team_id=team.team_id) '
       .' LEFT JOIN '.SLA_TABLE.' sla ON (ticket.sla_id=sla.id AND sla.isactive=1) '
       .' LEFT JOIN '.TOPIC_TABLE.' topic ON (ticket.topic_id=topic.topic_id) '
       .' LEFT JOIN '.TOPIC_TABLE.' ptopic ON (ptopic.topic_id=topic.topic_pid) '
       .' LEFT JOIN '.TABLE_PREFIX.'ticket__cdata cdata ON (cdata.ticket_id = ticket.ticket_id) '
       .' LEFT JOIN '.PRIORITY_TABLE.' pri ON (pri.priority_id = cdata.priority)';

TicketForm::ensureDynamicDataView();

$query="$qselect $qfrom $qwhere ORDER BY $order_by $order LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
$hash = md5($query);
$_SESSION['search_'.$hash] = $query;
//QUERRY QUE LISTA LOS RESULTADOS
$res = db_query($query);
$showing=db_num_rows($res)? ' &mdash; '.$pageNav->showing():"";
if(!$results_type)
    $results_type = sprintf(__('%s Tickets' /* %s will be a status such as 'open' */),
        mb_convert_case($status, MB_CASE_TITLE));

if($search)
    $results_type.= ' ('.__('Search Results').')';

$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting..

// Fetch the results
$results = array();
while ($row = db_fetch_array($res)) {
    $results[$row['ticket_id']] = $row;
}

// Fetch attachment and thread entry counts
if ($results) {
    $counts_sql = 'SELECT ticket.ticket_id, coalesce(attach.count, 0) as attachments, '
        .'coalesce(thread.count, 0) as thread_count, coalesce(collab.count, 0) as collaborators '
        .'FROM '.TICKET_TABLE.' ticket '
        .'left join (select count(attach.attach_id) as count, ticket_id from '.TICKET_ATTACHMENT_TABLE
            .' attach group by attach.ticket_id) as attach on (attach.ticket_id = ticket.ticket_id) '
        .'left join (select count(thread.id) as count, ticket_id from '.TICKET_THREAD_TABLE
            .' thread group by thread.ticket_id) as thread on (thread.ticket_id = ticket.ticket_id) '
        .'left join (select count(collab.id) as count, ticket_id from '.TICKET_COLLABORATOR_TABLE
            .' collab group by collab.ticket_id) as collab on (collab.ticket_id = ticket.ticket_id) '
         .' WHERE ticket.ticket_id IN ('.implode(',', db_input(array_keys($results))).');';
    $ids_res = db_query($counts_sql);
    while ($row = db_fetch_array($ids_res)) {
        $results[$row['ticket_id']] += $row;
    }
}

//YOU BREAK IT YOU FIX IT.
?>
<!-- SEARCH FORM START -->
<div id='basic_search'>
    <form action="tickets.php" method="get">
    <?php csrf_token(); ?>
    <input type="hidden" name="a" value="search">
    <table>
        <tr>
            <td>
                <input type="text" id="basic-ticket-search" name="query"
            size=30 value="<?php echo Format::htmlchars($_REQUEST['query'],
            true); ?>"
                autocomplete="off" autocorrect="off" autocapitalize="off"></td>
            <td><input type="submit" name="basic_search" class="button" value="<?php echo __('Search'); ?>"></td>
            <td>&nbsp;&nbsp;<a href="#" id="go-advanced">[<?php echo __('advanced'); ?>]</a>&nbsp;<i class="help-tip icon-question-sign" href="#advanced"></i></td>
        </tr>
    </table>
    </form>
</div>
<!-- SEARCH FORM END -->
<div class="clear"></div>
<div style="margin-bottom:20px; padding-top:10px;">
<div>
        <div class="pull-left flush-left">
            <h2><a href="<?php echo Format::htmlchars($_SERVER['REQUEST_URI']); ?>"
                title="<?php echo __('Refresh'); ?>"><i class="icon-refresh"></i> <?php echo
                $results_type.$showing; ?></a></h2>
        </div>
        <div class="pull-right flush-right">

            <?php
            if ($thisstaff->canDeleteTickets()) { ?>
            <a id="tickets-delete" class="action-button pull-right tickets-action"
                href="#tickets/status/delete"><i
            class="icon-trash"></i> <?php echo __('Delete'); ?></a>
            <?php
            } ?>
            <?php
            if ($thisstaff->canManageTickets()) {
                echo TicketStatus::status_options();
            }
            ?>
        </div>
</div>


<div class="clear" style="margin-bottom:10px;"></div>
<form action="tickets.php" method="POST" name='tickets' id="tickets">
<?php csrf_token(); ?>
 <input type="hidden" name="a" value="mass_process" >
 <input type="hidden" name="do" id="action" value="" >
 <input type="hidden" name="status" value="<?php echo
 Format::htmlchars($_REQUEST['status'], true); ?>" >

 <table class="list" border="0" cellspacing="1" cellpadding="2" width="100%">
    <thead>
        <tr>
            <?php if($thisstaff->canManageTickets()) { ?>
            <th width="8px">&nbsp;</th>
            <?php } ?>
            <th width="70">
                <a <?php echo $id_sort; ?> href="tickets.php?sort=ID&order=<?php echo $negorder; ?><?php echo $qstr; ?>"
                    title="<?php echo sprintf(__('Sort by %s %s'), __('Ticket ID'), __($negorder)); ?>"><?php echo __('Ticket'); ?></a></th>
            <th width="70">
                <a  <?php echo $date_sort; ?> href="tickets.php?sort=date&order=<?php echo $negorder; ?><?php echo $qstr; ?>"
                    title="<?php echo sprintf(__('Sort by %s %s'), __('Date'), __($negorder)); ?>"><?php echo __('Date'); ?></a></th>
            <th width="280">
                 <a <?php echo $subj_sort; ?> href="tickets.php?sort=subj&order=<?php echo $negorder; ?><?php echo $qstr; ?>"
                    title="<?php echo sprintf(__('Sort by %s %s'), __('Subject'), __($negorder)); ?>"><?php echo __('Subject'); ?></a></th>
            <th width="170">
                <a <?php echo $name_sort; ?> href="tickets.php?sort=name&order=<?php echo $negorder; ?><?php echo $qstr; ?>"
                     title="<?php echo sprintf(__('Sort by %s %s'), __('Name'), __($negorder)); ?>"><?php echo __('From');?></a></th>
            <?php
            //if($search && !$status) { ?>
                <th width="60">
                    <a <?php echo $status_sort; ?> href="tickets.php?sort=status&order=<?php echo $negorder; ?><?php echo $qstr; ?>"
                        title="<?php echo sprintf(__('Sort by %s %s'), __('Status'), __($negorder)); ?>"><?php echo __('Status');?></a></th>
            <?php
            //} else { ?>
                <th width="60" <?php echo $pri_sort;?>>
                    <a <?php echo $pri_sort; ?> href="tickets.php?sort=pri&order=<?php echo $negorder; ?><?php echo $qstr; ?>"
                        title="<?php echo sprintf(__('Sort by %s %s'), __('Priority'), __($negorder)); ?>"><?php echo __('Priority');?></a></th>
            <?php
            //}
            ?>
            <th width="60">
                    <a <?php echo $status_sort; ?> href=""
                        title="<?php echo sprintf(__('Sort by %s %s'), "Organización", __($negorder)); ?>">Organización</a></th>
            <?php
            

            if($showassigned ) {
                //Closed by
                if(!strcasecmp($status,'closed')) { ?>
                    <th width="150">
                        <a <?php echo $staff_sort; ?> href="tickets.php?sort=staff&order=<?php echo $negorder; ?><?php echo $qstr; ?>"
                            title="<?php echo sprintf(__('Sort by %s %s'), __("Closing Agent's Name"), __($negorder)); ?>"><?php echo __('Closed By'); ?></a></th>
                <?php
                } else { //assigned to ?>
                    <th width="150">
                        <a <?php echo $assignee_sort; ?> href="tickets.php?sort=assignee&order=<?php echo $negorder; ?><?php echo $qstr; ?>"
                            title="<?php echo sprintf(__('Sort by %s %s'), __('Assignee'), __($negorder)); ?>"><?php echo __('Assigned To'); ?></a></th>
                <?php
                }
            } else { ?>
                <th width="150">
                    <a <?php echo $dept_sort; ?> href="tickets.php?sort=dept&order=<?php echo $negorder;?><?php echo $qstr; ?>"
                        title="<?php echo sprintf(__('Sort by %s %s'), __('Department'), __($negorder)); ?>"><?php echo __('Department');?></a></th>
            <?php
            } ?>

            <!--Inicio Billy 27/01/2016 Se agrego la celda gds a la tabla-->
           <th style="color: #184E81;padding: 3px;">GDS</th>
            <!--Fin Billy 27/01/2016 Se agrego la celda gds a la tabla-->


            <th style="color: #184E81;padding: 3px;">
                Localizador
            </th>

            <th style="color: #184E81;padding: 3px;">
                <!-- <a <?php echo $status_sort; ?> href=""
                title="<?php echo sprintf(__('Sort by %s %s'), "Finalizado", __($negorder)); ?>">Finalizado</a> -->
                Status_Loc
            </th>


            <!--Inicio Billy 27/01/2016 Se agrego la celda pago a la tabla-->
           <th style="color: #184E81;padding: 3px;">Pago</th>
            <!--Fin Billy 27/01/2016 Se agrego la celda pago a la tabla-->


            <!--Inicio Billy 26/01/2016 Se ancho la celda del tiempo para que se aprecie mejor-->
            <th style="color: #184E81;padding: 3px;" width="125">Tiempo</th>
            <!--Fin Billy 26/01/2016 Se ancho la celda del tiempo para que se aprecie mejor-->

        </tr>
     </thead>
     <tbody>
        <?php
        // Setup Subject field for display
        $subject_field = TicketForm::objects()->one()->getField('subject');
        $class = "row1";
        $total=0;
        if($res && ($num=count($results))):
            $ids=($errors && $_POST['tids'] && is_array($_POST['tids']))?$_POST['tids']:null;
            foreach ($results as $row) {
                $tag=$row['staff_id']?'assigned':'openticket';
                $flag=null;
                if($row['lock_id'])
                    $flag='locked';
                elseif($row['isoverdue'])
                    $flag='overdue';

                $lc='';
                if($showassigned) {
                    if($row['staff_id'])
                        $lc=sprintf('<span class="Icon staffAssigned">%s</span>',Format::truncate($row['staff'],40));
                    elseif($row['team_id'])
                        $lc=sprintf('<span class="Icon teamAssigned">%s</span>',Format::truncate($row['team'],40));
                    else
                        $lc=' ';
                }else{
                    $lc=Format::truncate($row['dept_name'],40);
                }
                $tid=$row['number'];

                $subject = Format::truncate($subject_field->display(
                    $subject_field->to_php($row['subject']) ?: $row['subject']
                ), 40);
                $threadcount=$row['thread_count'];
                if(!strcasecmp($row['state'],'open') && !$row['isanswered'] && !$row['lock_id']) {
                    $tid=sprintf('<b>%s</b>',$tid);
                }
                /*INICIO
                Anthony Parisi
                2016-02-03
                Las siguientes lineas de código definiran el color de fondo de las filas de acuerdo al crierio del tipo de solicitud.
                Rojo para: Cambios, Cancelar Itinerario y Anular Aereo.
                Rosa para: Emitir Localizador.
                */
                $color_rojo = "Cancelar itinerario,Anular Aereo,Cambios";
                $color_rosa = "Emitir localizador,Reemision";
                if(strpos($color_rojo,$subject)!==false) $color_tr2 = 'style="background-color:Crimson;"';
                elseif(strpos($color_rosa,$subject)!==false) $color_tr2 = "style='background-color:LightGreen;'";
                else $color_tr2 ="";
                /*FIN*/
                ?>
            <tr id="<?php echo $row['ticket_id']; ?>">
                <?php if($thisstaff->canManageTickets()) {

                    $sel=false;
                    if($ids && in_array($row['ticket_id'], $ids))
                        $sel=true;
                    ?>
                <td align="center" class="nohover" <?=$color_tr2?>>
                    <input class="ckb" type="checkbox" name="tids[]"
                        value="<?php echo $row['ticket_id']; ?>" <?php echo $sel?'checked="checked"':''; ?>>
                </td>
                <?php } ?>
<?php

$mysqli = new mysqli("localhost", "osticket", "0571ck37", "osticket1911");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

$query2 = " SELECT *
            FROM `ost_ticket` 
            WHERE ticket_id = ".$row['ticket_id'];

$result2 = $mysqli->query($query2);
$row2 = $result2->fetch_array();
$color = "";

if($row2[4] == 1){

    if($row2[20] > $row2[19])
        $color = "color: #259A00;";

}

/*MICOD: Sentencia para listar las organizaciones en el listado de tickets*/
$query3 = "SELECT ost_organization.id, ost_organization.name FROM ost_organization 
            WHERE ost_organization.id IN (SELECT DISTINCT ost_user.org_id FROM ost_user WHERE ost_user.id 
            IN (SELECT DISTINCT ost_ticket.user_id FROM ost_ticket 
            WHERE ost_ticket.ticket_id = ".$row['ticket_id']."))";
$result3 = $mysqli->query($query3);
$organizacion = $result3->fetch_array();
/*----------------------------------------------------------------*/

?>
                <td title="<?php echo $row['email']; ?>" <?=$color_tr?>>
                  <a style="<?=$color?>" class="Icon <?php echo strtolower($row['source']); ?>Ticket ticketPreview"
                    title="<?php echo __('Preview Ticket'); ?>"
                    href="tickets.php?id=<?php echo $row['ticket_id']; ?>"><?php echo $tid; ?></a></td>
                <td align="center" <?=$color_tr?>><?php echo Format::db_datetime($row['effective_date']); ?></td>
                <td <?=$color_tr?>><a <?php if ($flag) { ?> class="Icon <?php echo $flag; ?>Ticket" title="<?php echo ucfirst($flag); ?> Ticket" <?php } ?>
                    href="tickets.php?id=<?php echo $row['ticket_id']; ?>"><?php echo $subject; ?></a>
                     <?php
                        if ($threadcount>1)
                            echo "<small>($threadcount)</small>&nbsp;".'<i
                                class="icon-fixed-width icon-comments-alt"></i>&nbsp;';
                        if ($row['collaborators'])
                            echo '<i class="icon-fixed-width icon-group faded"></i>&nbsp;';
                        if ($row['attachments'])
                            echo '<i class="icon-fixed-width icon-paperclip"></i>&nbsp;';
                    ?>
                </td>
                <td <?=$color_tr?>>&nbsp;<?php echo Format::htmlchars(
                        Format::truncate($row['name'], 22, strpos($row['name'], '@'))); ?>&nbsp;</td>
                <?php
                //if($search && !$status){
                    $displaystatus=ucfirst($row['status']);
                    if(!strcasecmp($row['state'],'open'))
                        $displaystatus="<b>$displaystatus</b>";
                    echo "<td $color_tr>$displaystatus</td>";
                //} else { ?>
                <td class="nohover" align="center" <?php echo ($color_tr!="")?$color_tr:'style="background-color:'.$row['priority_color'].';\"'; ?>>
                    <?php echo $row['priority_desc']; ?></td>
                <?php
                //}
                ?>
                <!--MICOD: Impresión de las organizaciones en el listado-->
                <td <?=$color_tr?>>&nbsp;<?=$organizacion[1]?></td>
                <!--/////////////////////////////////////////////-->
                <td <?=$color_tr?>>&nbsp;<?php echo $lc; ?></td>
<?php


$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

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


//print_r($query2);
/*MICOD------------------------------------------------------------------------------------
Éste código consulta las fechas de creación y cierre de un ticket, calcula el tiempo que
duró abierto el ticket y lo renderiza en la tabla de tickets*/
// $query_time = "SELECT created, closed FROM ost_ticket WHERE ticket_id = ".$row['ticket_id'];
// $result_time = $mysqli->query($query_time);
// $row_time = $result_time->fetch_array();

// $year_created = substr($row_time['created'], 0,4);
// $month_created = substr($row_time['created'], 5,2);
// $year_closed = substr($row_time['closed'], 0,4);
// $month_closed = substr($row_time['closed'], 5,2);

// if(isset($row_time['closed'])){
//     $query_timedif = " SELECT created, closed, SEC_TO_TIME(TIMESTAMPDIFF(SECOND, closed, created)) HORAS 
//                     FROM ost_ticket WHERE ticket_id = ".$row['ticket_id']." 
//                     AND YEAR(created) = ".$year_created." AND MONTH(created) = ".$month_created." 
//                     AND YEAR(closed) = ".$year_closed." AND MONTH(closed) = ".$month_closed;
                    
//     $result_timedif = $mysqli->query($query_timedif);
//     $row_timedif = $result_timedif->fetch_array();

//     $hora = substr($row_timedif['HORAS'], 1,2);
//     $minuto = substr($row_timedif['HORAS'], 4,2);
//     $segundo = substr($row_timedif['HORAS'], 7,2);

//     $respuesta = 0;
//     if ($segundo != "00") {
//         $respuesta = 1;
//     }if ($minuto != "00") {
//         $respuesta = 2;
//     }if ($hora != "00") {
//         $respuesta = 3;
//     }
//     switch ($respuesta) {
//         case '1':
//             $duracion = "0m";
//             break;
//         case '2':
//             $duracion = $minuto."m";
//             break;
//         case '3':
//             if ($hora == "01") {
//                 $duracion = $hora."h";
//             }else{
//                 $duracion = $hora."h";
//             }
//             break;
//         default:
//             $duracion = "Activo";
//             break;
//     }
// }else{
//     $duracion = "Activo";
// }
/*MICOD------------------------------------------------------------------------------------*/

if(isset($row_time['closed']))
    $query_timedif = "  SELECT created, closed FROM ost_ticket WHERE ticket_id = ".$row['ticket_id'];
else
    $query_timedif = "  SELECT created, NOW() FROM ost_ticket WHERE ticket_id = ".$row['ticket_id'];

$result_timedif = $mysqli->query($query_timedif);
$row_timedif = $result_timedif->fetch_array();

?>


<!--Inicio Billy 27/01/2016 lleno las columnas de la tabla con los datos traidos desde la base de datos-->
                <td <?=$color_tr?>>&nbsp;<?=$gds[1];?></td> <!--Lleno la columna gds con los datos traidos desde la base de datos-->
                <td <?=$color_tr?>>&nbsp;<?=$row2[0]?></td> <!--Lleno la columna localizador con los datos traidos desde la base de datos-->
                <td <?=$color_tr?>>&nbsp;<?=$row2[1]?></td> <!--Lleno la columna estatus con los datos traidos desde la base de datos-->
                <td <?=$color_tr?>>&nbsp;<?=$pago[1];?></td> <!--Lleno la columna tipo de pago con los datos traidos desde la base de datos-->
<!--Fin Billy 27/01/2016 lleno las columnas de la tabla con los datos traidos desde la base de datos-->

                <td <?=$color_tr?>>&nbsp;<?php 


                    $fecha1 = new DateTime($row_timedif[0]);
                    $fecha2 = new DateTime($row_timedif[1]);
                    $fecha = $fecha1->diff($fecha2);

                    if($fecha->y > 0)      printf('%dA, %dM, %dd, %dh, %dm', $fecha->y, $fecha->m, $fecha->d, $fecha->h, $fecha->i);
                    else if($fecha->m > 0) printf('%dM, %dd, %dh, %dm', $fecha->m, $fecha->d, $fecha->h, $fecha->i);
                    else if($fecha->d > 0) printf('%dd, %dh, %dm', $fecha->d, $fecha->h, $fecha->i);
                    else if($fecha->h > 0) printf('%dh, %dm', $fecha->h, $fecha->i);
                    else if($fecha->i > 0) printf('%dm', $fecha->i);
                    else echo ('0m');

                ?></td><!--MICOD: nueva columna-->
                
            </tr>
            <?php
            } //end of while.
        else: //not tickets found!! set fetch error.
            $ferror=__('There are no tickets matching your criteria.');
        endif; ?>
    </tbody>
    <tfoot>
     <tr>
        <td colspan="14"> <!--Se agrego mas celdas al coslpan-->
            <?php if($res && $num && $thisstaff->canManageTickets()){ ?>
            <?php echo __('Select');?>:&nbsp;
            <a id="selectAll" href="#ckb"><?php echo __('All');?></a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb"><?php echo __('None');?></a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb"><?php echo __('Toggle');?></a>&nbsp;&nbsp;
            <?php }else{
                echo '<i>';
                echo $ferror?Format::htmlchars($ferror):__('Query returned 0 results.');
                echo '</i>';
            } ?>
        </td>
     </tr>
    </tfoot>
    </table>

    <!--////////////////////Inicio Billy 25/01/2016 Paginador Administrador/////////////////////////////////////////////-->

    <br>
    <?php
    if ($num>0) { //if we actually had any tickets returned.

if((($pageNav->getPage())-1) <= 0)
        $pagea = 1;
    else
        $pagea = ($pageNav->getPage())-1;

    if((($pageNav->getPage())+1) >= $pageNav->getNumPages())
        $pages = $pageNav->getNumPages();
    else
        $pages = ($pageNav->getPage())+1;

    if(isset($_GET["advsid"]))
        $advsid="&advsid=" . $_GET["advsid"];

        $primero   = "tickets.php?status=".$_GET["status"]."&sort=".$_GET["sort"]."&order=".$_GET["order"]."&p=1&des=".$_GET["des"]."&has=".$_GET["has"]."&loc=".$_GET["loc"]."$advsid"; //Billy 17/02/2016 Se agrego al paginero el estatus y el query del resultado de la busqueda avanzada
        $anterior  = "tickets.php?status=".$_GET["status"]."&sort=".$_GET["sort"]."&order=".$_GET["order"]."&p=$pagea&des=".$_GET["des"]."&has=".$_GET["has"]."&loc=".$_GET["loc"]."$advsid"; //Billy 17/02/2016 Se agrego al paginero el estatus y el query del resultado de la busqueda avanzada
        $siguiente = "tickets.php?status=".$_GET["status"]."&sort=".$_GET["sort"]."&order=".$_GET["order"]."&p=$pages&des=".$_GET["des"]."&has=".$_GET["has"]."&loc=".$_GET["loc"]."$advsid"; //Billy 17/02/2016 Se agrego al paginero el estatus y el query del resultado de la busqueda avanzada
        $ultimo    = "tickets.php?status=".$_GET["status"]."&sort=".$_GET["sort"]."&order=".$_GET["order"]."&p=".$pageNav->getNumPages()."&des=".$_GET["des"]."&has=".$_GET["has"]."&loc=".$_GET["loc"]."$advsid"; //Billy 17/02/2016 Se agrego al paginero el estatus y el query del resultado de la busqueda avanzada

        echo '<div style="text-align:center;">
        <a href="'.$primero.'"><span class="glyphicon glyphicon-backward"></span></a>&nbsp;
        <a href="'.$anterior.'"><span class="glyphicon glyphicon-chevron-left"></span></a>&nbsp;
        &nbsp;'.__('Page').''.$pageNav->getPageLinks().'&nbsp;
        <a href="'.$siguiente.'"><span class="glyphicon glyphicon-chevron-right"></span></a>&nbsp;
        <a href="'.$ultimo.'"><span class="glyphicon glyphicon-forward"></span></a>&nbsp;';
        echo sprintf('<a class="export-csv no-pjax" href="?%s">%s</a>',
                Http::build_query(array(
                        'a' => 'export', 'h' => $hash,
                        'status' => $_REQUEST['status'])),
                __('Export'));
        echo '&nbsp;<i class="help-tip icon-question-sign" href="#export"></i></div>';
    } 

    ?>

    <!--///////////////////////////////////Fin Billy 25/01/2016 Paginador Administrador//////////////////////////////////////////////////////////-->

    </form>
</div>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3><?php echo __('Please Confirm');?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="mark_overdue-confirm">
        <?php echo __('Are you sure you want to flag the selected tickets as <font color="red"><b>overdue</b></font>?');?>
    </p>
    <div><?php echo __('Please confirm to continue.');?></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons pull-left">
            <input type="button" value="<?php echo __('No, Cancel');?>" class="close">
        </span>
        <span class="buttons pull-right">
            <input type="button" value="<?php echo __('Yes, Do it!');?>" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>

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

        <!--Nuevo campor de Organización-->
        <fieldset class="span6">
            <label for="orgId">Organizaci&oacute;n:</label>
            <select id="orgId" name="orgId">
                <option value="">&mdash; Todas las organizaciones &mdash;</option>
                <?php
                if($org=Dept::getOrganitations()) {
                    foreach($org as $id =>$name) {
                        echo sprintf('<option value="%d">%s</option>', $id, $name);
                    }
                }
                ?>
            </select>
        </fieldset>
        <!--////////////////////////////-->

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

        <!--Inicio 17/02/2016 Agregar campo Status Localizador en la busqueda avanzada-->
        <fieldset class="span6">
            <label for="statloc">Status Localizador:</label>
            <select id="statloc" name="statloc">
                <option value="">&mdash; Cualquiera &mdash;</option>
                <option value="Anulado">Anulado</option>
                <option value="Emitido">Emitido</option>
                <option value="Pendiente">Pendiente</option>
                <option value="Itinerario Cancelado">Itinerario Cancelado</option>
                <option value="Localizador no Valido">Localizador no Valido</option>
                <option value="Reembolsado">Reembolsado</option>
                <option value="Reemitido">Reemitido</option>
                <option value="Ticket Sellado">Ticket Sellado</option>
            </select>
        </fieldset>
        <!--Fin 17/02/2016 Agregar campo Status Localizador en la busqueda avanzada-->

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

