<script type="text/javascript">
    tableFeature = $("#tableFeature");
    tableFeature.flexigrid({
        url: '/admin/getTableFeature',
        title: 'Feature',
        dataType: 'json',
        colModel : [
        	{display: 'ID', name : 'id_feature', width : 32, sortable : false, align: 'left'},
            {display: '{NAME}', name : 'feature', width : 126, sortable : false, align: 'left'},
            {display: '{DISPLAY}', name : 'display', width : 129, sortable : false, align: 'left'},
            {display: '{SELECT_BY_DEFAULT}', name : 'default_value', width : 138, sortable : false, align: 'left'},
            {display: '{GROUP}', name : 'type_feature', width : 135, sortable : false, align: 'left'},
            {display: 'Orden', name : 'orden', width : 39, sortable : false, align: 'left'},
            {display: '{OPTIONS}', name : 'option', width : 80, sortable : false, align: 'left'}
        ],      
        usepager: true,
        useRp: true,
        striped:false,
        rp: 15,
        showTableToggleBtn: true,
        buttons : [
            {name: '{NEW}', bclass: 'add', onpress : toolboxFeature},
            {name: '{DELETE}', bclass: 'delete', onpress : toolboxFeature}
        ],  
        resizable: true,
        height: 'auto',
        onSuccess:  function(){
             $( "#tableFeature #toolbarSet" ).buttonset();
        }
    }); 
    
	function toolboxFeature(com, grid) {
		if (com == '{NEW}') {
			$( "#modalFeatureNew" ).dialog( "open" );
		} else if (com == '{DELETE}') {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				$( "#modalFeatureDelete" ).dialog( "open" );
			}	
		}
	}
</script>
<script type="text/javascript" src="{url_base}sitio/js/view.admin_feature.js"></script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div style="display: none" id="modalFeatureEdit" title="Edit Feature">

</div>
<div style="display: none" id="modalFeatureNew" title="New Feature">
	{formNew}
</div>

<div id="modalFeatureDelete" title="{MSG_DELETE}">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
	</p>
	<p style="float:left; margin:4px 0px 0px 0px;" id="selected">
		{SELECTED}:
	</p>
</div>

<table id="tableFeature"></table>