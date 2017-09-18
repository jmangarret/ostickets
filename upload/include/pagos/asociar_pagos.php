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
    <label for="satelite">Satelite/Freelance:</label>
    <input type="text" class="form-control" id="satelite">
  </div>
  <div class="form-group">
		<ul class="nav nav-tabs">
			<li class="active"><a data-toggle="tab" href="#tab1">Localizadores</a></li>
			<li><a data-toggle="tab" href="#tab2">Pagos</a></li>
		</ul>
		<div class="tab-content">
			<div id="tab1" class="tab-pane fade in active">		    
				<label for="loc">Seleccione los localizadores...</label>
				<input type="text" class="form-control" id="loc" placeholder="Buscar Localizador...">
				<?php $locs  =getLocalizadores(15728); ?>
				<?php echo showResults($locs,array("Localizador","Fecha","GDS","Total")); ?>
			</div>
			<div id="tab2" class="tab-pane fade">
				<label for="pago">Seleccione los pagos...</label>
				<input type="text" class="form-control" id="pago" placeholder="Buscar Pago...">
				<?php $pagos  =getPagos(13052); ?>
				<?php echo showResults($pagos,array("Banco Receptor","Fecha","Ref","Monto")); ?>
			</div>
		</div>
  </div>
  <button type="submit" class="btn btn-default">Submit</button>
</form>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

</body>
</html>

