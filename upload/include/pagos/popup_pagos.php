<!DOCTYPE html>
<html>
<head>
	<title></title>
	<link rel="stylesheet" href="../../css/bootstrap.min.css">
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
				<label for="buspago">Seleccione un pago...</label>
				<input type="text" class="form-control" id="buspago" placeholder="Buscar Pago...">				
				<?php $emails 		=getOrgEmails($_REQUEST["id"]); ?>
				<?php $contactoid 	=getContactoPorEmails($emails); ?>				
				<?php $pagos  		=getPagosCrm($contactoid); ?>
				<?php echo "Contactoid: ".$contactoid; ?>
				<?php echo showResults($pagos,array("Concepto","Banco Receptor","Fecha","Ref","Monto")); ?>
			</div>
		</div>
  </div>
  
</form>

<script src="../../js/jquery-1.8.3.min.js"></script>
<script src="../../js/bootstrap.min.js"></script>

<script type="text/javascript">
	$("#tableResult tr").on("click", function(){	  	
        $.ajax({
            data:{id : this.id},
            type: "POST",
            url: 'getPagoCrm.php',
            success: function(response){                                                                  
              	window.opener.$("#tablapagos").html(window.opener.$("#tablapagos").html()+response);				
				$.ajax({
		            data:{},
		            type: "POST",
		            url: 'getTotalPagosCrm.php',
		            success: function(response){                                                                  
		              	window.opener.$("#totalpagos").html(response);
						window.close();
		            }
		        });	
            }
        });	
	});
</script>

</body>
</html>

