<script type="text/javascript" src="{url_base}sitio/js/view.config_monitores.js"></script>
<script type="text/javascript">
	$("#fmGrupoMonitores").change(function(){
		$("#monitores").flexOptions({params: [{name:'callId', value:'monitores'}].concat($('#fmFilterMonitor').serializeArray())}).flexReload();
	});

	$("#monitores").flexigrid({
		url: '{url_base}config/getMonitores',
		dataType: 'json',
		title: '{TITLE_TABLE_MONITORS}',
		colModel : [
		    {display: 'ID', name : 'id_item', width : 30, sortable : true, align: 'left'},
			{display: '{DESCRIPTIVE_NAME}', name : 'descriptionLong', width : 300, sortable : true, align: 'left'},
			{display: 'Monitor', name : 'description', width : 100, sortable : true, align: 'left'},
			{display: '{REFRESH_EVERY}', name : 'delay', width : 100, sortable : true, align: 'left'},
			{display: '{HISTORICAL_DAYS}', name : 'history', width : 90, sortable : true, align: 'left'},
			{display: '{TYPE}', name : 'type_poller', width : 65, sortable : true, align: 'left'},
			{display: '{STATUS}', name : 'status', width : 45, sortable : false, align: 'left'},
			{display: '{OPTIONS}', name : 'option', width : 148, sortable : false, align: 'left'}
		],
		{button}
		usepager: true,
        sortname: "id_item",
		sortorder: "asc",
        showTableToggleBtn: true,
        resizable: true,
        height:'auto',
        striped:false,
		useRp: true,
		rp: 15,
		onSubmit : function(){
        	$('#monitores').flexOptions({params: [{name:'callId', value:'monitores'}].concat($('#fmFilterMonitor').serializeArray())});
       		return true;
    	},onSuccess:  function(){
        	 $( "#toolbar #toolbarSet" ).buttonset();
        }
    
	});
	
	function toolboxMonitor(com, grid) {
		if (com == '{NEW}') {
			$( "#modal_monitor_new" ).dialog( "open" );
		} else if (com == '{DELETE}') {
			lengthSelect = $('.trSelected', grid).length;
			if(lengthSelect > 0) {
				$( "#modal_monitor_delete" ).dialog( "open" );
			}
		}
	}
</script>
<div id="modal_monitor_new" title="{TITLE_NEW_MONITORS}">
	{form_new_monitor}
</div>

<div id="modal_monitor_delete" title="{TITLE_DELETE_MONITORS}">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}</p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">{SELECTED}: </p>    
</div>

<div id="modal_monitor_edit" title="{TITLE_EDIT_MONITORS}">
</div>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div class="paneles">
<form id="fmFilterMonitor">
    <fieldset>
        <div id="row">
            <label for="fmGrupoMonitores" accesskey="g">{GROUP}</label>
            <select name="fmGrupoMonitores" id="fmGrupoMonitores">
                {option_group}
            </select>
        </div>
    </fieldset>
</form>
</div>

<table id="monitores"></table>