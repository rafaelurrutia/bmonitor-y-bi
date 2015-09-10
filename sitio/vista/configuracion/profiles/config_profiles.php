<script type="text/javascript" src="/sitio/js/view.config_profiles2.js"></script>
<script type="text/javascript">
	var profile = $("#tableProfile").flexigrid({
		url : '/profiles2/getTable',
		title : '{PROFILE}',
		dataType : 'json',
		colModel : [{
			display : '{NAME}',
			name : 'name',
			width : '100',
			sortable : false,
			align : 'left'
		}, {
			display : '{GROUP}',
			name : 'grupo',
			width : '100',
			sortable : false,
			align : 'left'
		}, {
			display : '{ACTION}',
			name : 'action',
			width : '513',
			sortable : false,
			align : 'left'
		}],
		{button}
		usepager : true,
		useRp : true,
		striped : false,
		rp : 15,
		showTableToggleBtn : true,
		resizable : false,
		singleSelect: true,
		height : 'auto',
		onSuccess : function( ) {
			$( "#tableProfile #cloneProfile" ).button({
				text: true,
				icons: {
					primary: "ui-icon-copy"
				}
		    });
			$( "#tableProfile #scheduleProfile" ).button({
				text: true,
				icons: {
					primary: "ui-icon-clock"
				}
		    });

			$( "#tableProfile #sequenceProfile" ).button({
				text: true,
				icons: {
					primary: "ui-icon-transferthick-e-w"
				}
		    });
		    
			$( "#tableProfile #categoriesProfile" ).button({
				text: true,
				icons: {
					primary: "ui-icon-script"
				}
		    });
		    	    		    
			$( "#tableProfile #paramProfile" ).button({
				text: false,
				icons: {
					primary: "ui-icon-wrench"
				}
		    });		    

			$( "#tableProfile #structureProfile" ).button({
				text: false,
				icons: {
					primary: "ui-icon-gear"
				}
		    });	

			$( "#tableProfile #deleteProfile" ).button({
				text: false,
				icons: {
					primary: "ui-icon ui-icon-trash"
				}
		    });	
		    
		    $( "#tableProfile #exportProfile" ).button({
				text: false,
				icons: {
					primary: "ui-icon ui-icon-arrowstop-1-n"
				}
		    });	
		    	    
		    $( "#tableProfile #importProfile" ).button({
				text: false,
				icons: {
					primary: "ui-icon ui-icon-arrowstop-1-s"
				}
		    });		     
		}
	});

	function toolboxProfiles( com, grid ) {
		if ( com == '{NEW}' ) {
			modalProfileNew.dialog("open");
		} else if ( com == '{DELETE}' ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				modalProfileDelete.dialog("open");
			}
		} else if ( com == '{EXPORT}' ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				$.exportProfile();
			}
		} else if ( com == '{IMPORT}' ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				$.importProfile();
			}
		}
	}
</script>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="modalProfileNew" title="{NEW} {PROFILE}">
	{formNewProfile}
</div>

<div id="modalProfileDelete" title="{DELETE} {PROFILE}(s)?">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
	</p>
	<p style="float:left; margin:4px 0px 0px 0px;" id="selected">
		{SELECTED}:
	</p>
</div>

<div id="modalStructureNew" title="{NEW} {CATEGORY}">
	{formStructureNew}
</div>

<div id="modalStructureDelete" title="{DELETE} {CATEGORY}(ies)?">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
	</p>
	<p style="float:left; margin:4px 0px 0px 0px;" id="selected">
		{SELECTED}:
	</p>
</div>

<div id="modalStructureItemNewParam" title="{NEW} {PARAMETERS}">
	{formNewItemParam}
</div>

<div id="modalStructureItemNewMonitor" title="{NEW} Monitor">
	{formNewItemMonitor}
</div>

<div id="modalStructureItemDelete" title="{DELETE} Item?">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
	</p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">
        {SELECTED}:
    </p>
</div>

<div id="modalStructureItemEdit" title="{EDIT} Item?"></div>

<div id="modalProfileClone" title="{CLONE} {PROFILE}?">
	{modalProfileClone}
</div>

<div id="modalParametersProfileNew" title="{PARAMETERS} {PROFILE}?">
    {modalParametersProfileNew}
</div>

<div id="modalProfileExport" title="{PROFILE} {EXPORT}?">
    {modalProfileExport}
</div>

<div id="modalProfileImport" title="{PROFILE} {IMPORT}?">
    {modalProfileImport}
</div>

<div id="modalParametersProfileEdit" title="{PARAMETERS} {PROFILE}?">
</div>

<div id="modalParametersProfileDelete" title="{DELETE} Item?">
    <p>
        <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
    </p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">
        Selected:
    </p>
</div>

<div id="modalDeleteCategory" title="{DELETE} Item?">
    <p>
        <span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
    </p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">
        {SELECTED}:
    </p>
</div>

<div id="dialogGenerator">

</div>

<div id="tableProfile">
	<!-- Tabla de perfiles -->
</div>

<div id="containerProfile">

</div>