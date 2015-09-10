<script type="text/javascript" src="{url_base}sitio/js/view.config_screen.js"></script>
<script type="text/javascript">
	table_screen = $("#table_screen");
	table_screen.flexigrid({
		url : '/configScreen/getTableScreens',
		title : '{SCREENS}',
		dataType : 'json',
		colModel : [{
			display : 'ID',
			name : 'screenid',
			width : '50',
			sortable : true,
			align : 'left'
		}, {
			display : '{GROUP}',
			name : 'groupname',
			width : '120',
			sortable : true,
			align : 'left'
		}, {
			display : '{NAME}',
			name : 'name',
			width : '120',
			sortable : true,
			align : 'left'
		}, {
			display : '{DIMENSIONS_COLUMNSXROWS}',
			name : 'dimensions',
			width : '154',
			sortable : false,
			align : 'left'
		}, {
			display : '{OPTIONS}',
			name : 'option',
			width : '135',
			sortable : false,
			align : 'left'
		}],
		{button}
		usepager : true,
		useRp : true,
		striped : false,
		rp : 15,
		showTableToggleBtn : true,
		resizable : true,
		onSubmit : function( ) {
			table_screen.flexOptions({
				params : [{
					name : 'callId',
					value : 'table_screen'
				}].concat($('#tableScreenFilter').serializeArray())
			});
			return true;
		},
		height : 'auto',
		onSuccess : function( ) {
			$("#table_screen #toolbarSet").buttonset();
		}

	});

	function toolboxScreen( com, grid ) {
		if ( com == '{NEW}' ) {
			$("#modal_screen_form").dialog("open");
		} else if ( com == '{DELETE}' ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				$("#modal_screen_delete").dialog("open");
			}
		}
	}

</script>
<style type="text/css">
	.column {
		width: 350px;
		float: left;
		padding-bottom: 100px;
	}
	.portlet {
		margin: 0 1em 1em 0;
		padding: 0.3em;
	}
	.portlet-header {
		padding: 0.2em 0.3em;
		margin-bottom: 0.5em;
		position: relative;
	}
	.portlet-toggle {
		position: absolute;
		top: 50%;
		right: 0;
		margin-top: -8px;
	}
	.portlet-content {
		padding: 0.4em;
	}
	.portlet-placeholder {
		border: 1px dotted black;
		margin: 0 1em 1em 0;
		height: 50px;
	}
</style>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="modal_screen_form" title="{TITLE_NEW_SCREENS}">
	{form_new_screen}
</div>

<div id="modal_screen_delete" title="{TITLE_DELETE_SCREENS}">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
	</p>
	<p style="float:left; margin:4px 0px 0px 0px;" id="selected">
		{SELECTED}:
	</p>
</div>

<div id="modal_screen_edit" title="{TITLE_EDIT_SCREENS}"></div>
<div id="modal_screen_asig" title="{TITLE_ASSIG_SCREENS}"></div>

<div class="paneles">
	<form id="tableScreenFilter">
		<fieldset>
			<div id="row">
				<label for="groupid" accesskey="g">{GROUP}</label>
				<select name="groupid" id="groupid">
					{option_group}
				</select>
			</div>
		</fieldset>
	</form>
</div>

<table id="table_screen"></table>