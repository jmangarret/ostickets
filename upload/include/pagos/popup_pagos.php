<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<style type="text/css">
	.resaltar:hover{ background-color:#FEFE4C; cursor:pointer}
	</style>
</head>
<body>
<?php
include("../funciones/commons.php");
include("../funciones/crm.functions.php");
include("../funciones/pagos.functions.php");
?>
<form>
  <div class="form-group">
		<div class="tab-content">
			<div id="tab1" class="tab-pane fade in active">		    
				<label for="buspago">Seleccione un pago...</label> 13052
				<input type="text" class="form-control" id="buspago" placeholder="Buscar Pago...">				
				<?php $emails 		=getOrgEmails($_REQUEST["id"]); ?>
				<?php $contactoid 	=getContactoPorEmails($emails); ?>				
				<?php $pagos  		=getPagosCrm($contactoid); ?>
				<?php echo showResults($pagos,array("Concepto","Banco Receptor","Fecha","Ref","Monto")); ?>
			</div>
		</div>
  </div>
  
</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script type="text/javascript">
	$("#tableResult tr").on("click", function(){
			var c1=$(this).html().split("<td>").join(" ");
			var c2=c1.split("</td>").join(" - ");
			window.opener.document.getElementById("pago").value=c2.trim();
		});

	</script>

</body>
</html>

