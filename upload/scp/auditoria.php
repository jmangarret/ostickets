<!--Inicio Billy 22/02/2016-->

<link rel="stylesheet" href="/upload/css/bootstrap.css">
<script src="/upload/css/bootstrap.min.js"></script>

<!--Fin Billy 23/01/2016-->

<?php

	require('admin.inc.php');
	require(STAFFINC_DIR.'header.inc.php');

	$mysqli = new mysqli(DBHOST, DBUSER, DBPASS, DBNAME);
    /* check connection */
    if (mysqli_connect_errno()) {
        printf("Connect failed: %s\n", mysqli_connect_error());
        exit();
    }

//Inicio Billy 22/02/2016 Query para traer la cantidad total de registros de la tabla de auditoriavy se creo el objeto de la clase Pagenate
	$query = "	SELECT *
				FROM ost_auditoria_limite_credito";
	$result = $mysqli->query($query);
	$rowcount = mysqli_num_rows($result);

	$pageNav=new Pagenate($rowcount, $_GET['p'], PAGE_LIMIT);

//Fin Billy 23/02/2016 Query para traer la cantidad total de registros de la tabla de auditoriavy se creo el objeto de la clase Pagenate


    $query = "	SELECT CONCAT( firstname,  ' ', lastname ) , c.name, a.total, a.disponible, a.date
				FROM ost_auditoria_limite_credito a
				INNER JOIN ost_staff b ON a.staff_id = b.staff_id
				INNER JOIN ost_organization c ON a.org_id = c.id 
				ORDER BY a.date DESC LIMIT " .$pageNav->getStart().",".$pageNav->getLimit(); //se agrego el limite para paginar los resultados
	$result = $mysqli->query($query);
	$rowcount = mysqli_num_rows($result);  //la variable $rowcount contiene el numero de registros obtenidos a partir de la sentencia sql

	while($row = $result->fetch_array())
    	$rows[] = $row;

?>

<script type="text/javascript">
	$("#nav .inactive:eq(5) ul").remove();
	$("#nav .inactive:eq(5)").addClass("active");
	$("#nav .inactive:eq(5)").removeClass("inactive");

	$("#sub_nav").append('<li><a class="users active" href="auditoria.php" title="" id="subnav0">L&iacute;mite de Cr&eacute;dito</a></li>');
</script>

<div class="pull-left" style="width:700px;padding-top:5px;padding-bottom:2px;">
    <h2>L&iacute;mite de Cr&eacute;dito:</h2>
</div>

<?php csrf_token(); ?>

<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
	<caption style="padding: 5px;">Mostrando <?=$rowcount?> resultados</caption>
	<thead>
		<tr>
			<th style="padding: 4px 4px;">Agente</th>
			<th style="padding: 4px 4px;">Organizaci&oacute;n</th>
			<th style="padding: 4px 4px;">L&iacute;mite de Cr&eacute;dito Total</th>
			<th style="padding: 4px 4px;">L&iacute;mite de Cr&eacute;dito Disponible</th>
			<th style="padding: 4px 4px;">Fecha</th>
		</tr>
	</thead>
	<tbody>

	</tbody>

<?php
$count = 0;
foreach($rows as $row)
{ ?>
    <tr>
        <td style="padding: 4px 4px;"><?=$row[0]?></td>
        <td style="padding: 4px 4px;"><?=$row[1]?></td>
        <td style="padding: 4px 4px;"><?=$row[2]?></td>
        <td style="padding: 4px 4px;"><?=$row[3]?></td>
        <td style="padding: 4px 4px;"><?=date("d-m-Y H:i:s",strtotime($row[4]))?></td>
    </tr>
<?php
$count++;
}

/* free result set */
$result->close();

/* close connection */
$mysqli->close();

?>
                </tbody><tfoot>
     <tr>
        <td colspan="6">
            Mostrando <?=$rowcount?> resultados
        </td>
     </tr>
    </tfoot>
</table>
<br>
<?php

//Inicio Billy 22/02/2016 Paginador de la auditoria//
    
    if((($pageNav->getPage())-1) <= 0)
        $pagea = 1;
    else
        $pagea = ($pageNav->getPage())-1;

    if((($pageNav->getPage())+1) >= $pageNav->getNumPages())
        $pages = $pageNav->getNumPages();
    else
        $pages = ($pageNav->getPage())+1;

    $primero   = "auditoria.php?p=1";
    $anterior  = "auditoria.php?p=$pagea";
    $siguiente = "auditoria.php?p=$pages"; 
    $ultimo    = "auditoria.php?p=".$pageNav->getNumPages(); 


    echo '  <div style="text-align:center;">
                <a href="'.$primero.'"><span class="glyphicon glyphicon-backward"></span></a>&nbsp;
                <a href="'.$anterior.'"><span class="glyphicon glyphicon-chevron-left"></span></a>&nbsp;
                '.__('Page').''.$pageNav->getPageLinks().'&nbsp;
                <a href="'.$siguiente.'"><span class="glyphicon glyphicon-chevron-right"></span></a>&nbsp;
                <a href="'.$ultimo.'"><span class="glyphicon glyphicon-forward"></span></a>&nbsp;</div>';

//Fin Billy 23/02/2016 paginador de la auditoria//


	while($row = $result->fetch_array()){

	}

	include(STAFFINC_DIR.'footer.inc.php');

?>

<script type="text/javascript">
	
</script>