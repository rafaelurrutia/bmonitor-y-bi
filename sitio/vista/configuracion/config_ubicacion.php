<script type="text/javascript" src="{url_base}sitio/js/view.config_ubicacion.js"></script>

<script type="text/javascript">
    table_ubicacion = $(".table_ubicacion");
	table_ubicacion.flexigrid({
		url : '/config/getTableUbicaciones',
		title : 'Ubicación',
		dataType : 'json',
		colModel : [{
			display : '{NAME}',
			name : 'name',
			width : '289',
			sortable : true,
			align : 'left'
		},{
			display : 'Region',
			name : 'region',
			width : '181',
			sortable : true,
			align : 'left'
		},{
			display : '{STATE_PROVINCE}',
			name : 'state',
			width : '105',
			sortable : true,
			align : 'left'
		},{
			display : '{CITY}',
			name : 'city',
			width : '83',
			sortable : true,
			align : 'left'
		}, {
			display : '{OPTIONS}',
			name : 'opciones',
			width : '90',
			sortable : false,
			align : 'center'
		}],
		buttons : [{
			name : '{NEW}',
			bclass : 'add',
			onpress : toolboxUbicacion
		}, {
			name : '{DELETE}',
			bclass : 'delete',
			onpress : toolboxUbicacion
		}],
		searchitems : [{
			display : 'Region',
			name : 'REGION_NOMBRE',
			isdefault : false
		}, {
			display : 'Provincia',
			name : 'PROVINCIA_NOMBRE',
			isdefault : false
		}, {
			display : 'Comuna',
			name : 'COMUNA_NOMBRE',
			isdefault : false
		}, {
			display : 'Código localidad',
			name : 'CO.rdb',
			isdefault : false
		}, {
			display : '{NAME}',
			name : 'CO.establecimiento',
			isdefault : false
		}],
		usepager : true,
		useRp : true,
		striped : false,
		rp : 15,
		showTableToggleBtn : true,
		resizable : true,
		onSubmit : function( ) {
			table_ubicacion.flexOptions({
				params : [{
					name : 'callId',
					value : 'table_ubicacion'
				}].concat($('#table_ubicacionFilter').serializeArray())
			});
			return true;
		},
		height : 'auto',
		onSuccess : function( ) {
			$("#table_ubicacion #toolbarSet").buttonset();
		}

	});

	function gridFormat( ) {
		$("#toolbar button").button();
	}

	function toolboxUbicacion( com, grid ) {
		if ( com == '{NEW}' ) {
			$("#modal_ubicacion_new").dialog("open");
		} else if ( com == '{DELETE}' ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				$("#modal_ubicacion_delete").dialog("open");
			}
		}
	}

</script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="modal_ubicacion_new" title="Crear nuevo registro">
	{form_new_ubicacion}
</div>

<div id="modal_ubicacion_delete" title="Borrar Graph(es)?">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>El o los registros(s) seleccionado(s) se borrará(n). ¿Estás seguro?
	</p>
</div>

<div id="modal_ubicacion_edit" title="Editar ubicacion"></div>

<table id="table_ubicacion" class="table_ubicacion"></table>