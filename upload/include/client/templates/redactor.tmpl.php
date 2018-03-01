<div class="redactor_box no-pjax">
	<textarea style="width: 100%; display: none;" name="message" placeholder="" data-draft-namespace="ticket.client" 
				data-draft-object-id="0gh5c51n4v95" class="richtext draft draft-delete ifhtml" cols="21" rows="8" dir="ltr">
	</textarea>
</div>

<div id="filedropdzone" class="filedrop">
	<div class="files"></div>
	<div class="dropzone"><i class="icon-upload"></i>
	    Agregar archivos aquí o <a href="#" class="manual"> elegirlos </a>        
	    <input type="file" multiple="multiple" id="file-filedropdzone" style="display: none; width: 0px; height: 0px;" 
	    		accept="">
	</div>
</div>
<script type="text/javascript">
    $(function(){$('#filedropdzone .dropzone').filedropbox({
      url: 'ajax.php/form/upload/attach',
      link: $('#filedropdzone').find('a.manual'),
      paramname: 'upload[]',
      fallback_id: 'file-filedropdzone',
      allowedfileextensions: [],
      allowedfiletypes: [],
      maxfiles: 20,
      maxfilesize: 16,
      name: 'attach:21[]',
      files: []        });});
</script>
<link rel="stylesheet" type="text/css" href="/vhosts/ostickets/upload/css/filedrop.css">