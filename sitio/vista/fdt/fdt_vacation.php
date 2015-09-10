<!-- 
	 <script type="text/javascript" src="{url_base}sitio/js/ui.selectmenu.js"></script> -->
	 
<script type="text/javascript" src="{url_base}sitio/js/view.fdt_vacation.js"></script>
<script type="text/javascript">

	{table_vacation_active}

	{table_vacation}
	
	
	function toolboxVacation(com, grid) {
		if (com == 'Nuevo') {
			$( "#modal_vacation_new" ).dialog( "open" );
		} else if (com == 'Borrar') {
			lengthSelect = $('.trSelected', grid).length;
			if(lengthSelect > 0) {
				$( "#modal_vacation_delete" ).dialog( "open" );
			}
		}
	}
	
</script>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="modal_vacation_new" title="Asignar Licencias">
	{form_new_vacation}
</div>

<div id="modal_vacation_delete" title="Borrar Licencias?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>El o los campo(s) seleccionado(s) se borrará(n). ¿Estás seguro?</p>
</div>

<div id="modal_vacation_edit" title="Editar Licencias"></div>

<div id='container_table'> 
	<table class="table_vacation_active"></table>
	<table class="table_vacation"></table>
</div>