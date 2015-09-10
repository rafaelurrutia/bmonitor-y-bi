<script type="text/javascript" src="{url_base}sitio/js/view.config_groups.js"></script>
<script type="text/javascript">
	table_groups = $("#table_groups");
	table_groups.flexigrid({
		url : '/config/getTableGroups',
		title : '{GROUPS}',
		dataType : 'json',
		colModel : [{
			display : '{GROUP}',
			name : 'name',
			width : '100',
			sortable : false,
			align : 'left'
		}, {
			display : '{TYPE} {GROUP}',
			name : 'type',
			width : '100',
			sortable : false,
			align : 'left'
		},  {  
			display : '', <!-- //'{OPTIONS}',//-->
			name : 'option',
			width : '145',
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
		height : 'auto',
		onSuccess : function( ) {
			$("#table_groups #toolbarSet").buttonset();
		}

	});

	function toolboxGroups( com, grid ) {
		if ( com == '{NEW}' ) {
			$("#modal_groups_new").dialog("open");
		} else if ( com == '{DELETE}' ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				$("#modal_groups_delete").dialog("open");
			}
		}
	}

</script>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="modal_groups_new" title="{CREATE_GROUP}">
	{form_new_group}
</div>

<div id="modal_groups_delete" title="{DELETE_GROUP}">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}</p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">{SELECTED}: </p>
</div>

<div id="modal_groups_edit" title="{EDIT_GROUP}"></div> 

<table id="table_groups" class="table_groups"></table>