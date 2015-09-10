<script type="text/javascript" src="{url_base}sitio/js/view.config_profiles.js"></script>
<script type="text/javascript">
	{table}

	function toolboxProfiles(com, grid) {
		if (com == 'Nuevo') {
			$("#modalNewCategory").dialog("open");
		} else if (com == 'Borrar') {
			lengthSelect = $('.trSelected', grid).length;
			if (lengthSelect > 0) {
				$("#modalProfilesDelete").dialog("open");
			}
		}
	}   
</script>

<div id="loading"></div>
<div id="imgLoading">
    <img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="modalNewCategory" title="Crear nueva categoria">
    {form_new}
</div>

<div id="modalProfilesDelete" title="Borrar Categoria(s)?">
    <p>
        <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>The category(ies) selected will be permanently deleted. ¿Are you sure?
    </p>
</div>

<div id="modalEditValue" title="Editar valores del perfil"></div>
<div id="modalEditCategory" title="Editar categorias"></div>

<table id="tableProfile"></table>