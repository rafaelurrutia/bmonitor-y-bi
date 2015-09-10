<script type="text/javascript">
    {sampleTags}
    
    var filterAgents = new $.formVar( 'sondasFilter' );
    
	jQuery(function( $ ) {        
		var agent = $("#sondas");
		//Tabla
		agent.flexigrid({
		url: '{url_base}config/getSondas',
		title: '{AGENTS}',
		dataType: 'json',
		colModel : [
    		{display: '{AGENT}', name : 'host' , width : '230'  , sortable : true, align: 'left'},
    		{display: '{AGENT_CODE}', name : 'codigosonda' , width : '118'  , sortable : true, align: 'left'},
    		{display: 'DNS', name : 'dns' , width : '118'  , sortable : true, align: 'left'},
    		{display: 'IP', name : 'ip_wan' , width : '90'  , sortable : true, align: 'left'},
    		{display: '{GROUP}', name : 'grupo' , width : '100'  , sortable : true, align: 'left'},
    		{display: '{STATUS}', name : 'estado' , width : '40'  , sortable : false, align: 'left'},
    		{display: '{AVAILABILITY}', name : 'availability' , width : '75'  , sortable : false, align: 'left'},
    		{display: '{OPTIONS}', name : 'opciones' , width : '290'  , sortable : true, align: 'left'}
		],
		{button}
		usepager: false,
		useRp: true,
		rp: 1000,
		height:'auto',
		rpOptions: [10, 20, 30, 40, 50,'All'],
		striped:false,
		sortname: "id_host",
		sortorder: "asc",
		showTableToggleBtn: true,
		resizable: true,
		onSubmit : function() {
		    var status = filterAgents.get('filter').val();
			agent.flexOptions({
				qtype : 'availability',
                query : status,
                params : [{
					name : 'callId',
					value : 'sondas'
				}, {
					name : 'where',
					value : 'none'
				}].concat(filterAgents.form.serializeArray())
			});
			return true;
		}, onSuccess:  function() {
			$("#sondas #toolbarSet").buttonset();
		}
	});
	
    filterAgents.get('filter').change(function( ) {
		agent.flexOptions({
					newp: 1
		}).flexReload();
	});

	//Filtros de la Tabla

	filterAgents.get('groupid').change(function( ) {
		agent.flexOptions({
					newp: 1
		}).flexReload();
	});
	});
	
	var selected = false;

	function toolboxSonda( com, grid ) {
		if ( com == '{NEW}' ) {
			$("#modal_sonda_form").dialog("open");
			$("#tabs_form_new_sonda").tabs("option", "active", 0);
		} else if ( com == '{DELETE}' ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				$("#sondas-delete").dialog("open");
			}
		} else if ( com == '{CONFIGURATION}' ) {
			lengthSelect = $('.trSelected', grid).length;
			if ( lengthSelect > 0 ) {
				$("#sonda-config").dialog("open");
			} else {
				alert(language.SELECT_A_AGENT);
			}
		} else if ( com == '{UPDATE}' ) {
			$("#sonda-update").dialog("open");
		} else if ( com == '{ALL}' ) {
			
			if(selected == false){	
				 selected = true;
			} else {
				 selected = false;
			}
			
			if(selected == true) {
				$('.bDiv tbody tr', grid).toggleClass('trSelected');
			} else {
				$('.bDiv tbody tr', grid).removeClass('trSelected');
			}
		}
	}

</script>
<script type="text/javascript" src="{url_base}sitio/js/view.config_equipos.js"></script>
<div id="modal_sonda_form" title="{TITLE_NEW_AGENT}">
	{form_new_sonda}
</div>

<div id="sondas-delete" title="{TITLE_DELETE_AGENT}">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
	</p>
	<p style="float:left; margin:4px 0px 0px 0px;" id="selected">
		{SELECTED}:
	</p>
</div>

<div id="sonda-config" title="{TITLE_CONFIG_AGENT}?">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_CONFIG}
	</p>
</div>

<div id="sonda-update" title="{TITLE_UPGRADE_AGENT}?">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_UPGRADE}
	</p>
</div>

<div id="modal_sonda_edit" title="{TITLE_EDIT_AGENT}"></div>

<div id="modal_sonda_trigger" title="{TITLE_TRIGGER_AGENT}">
	{form_trigger_sonda} 
</div>


<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<form id="sondasFilter">
	<div class="paneles">
		<fieldset>
			<div id="row">
				<label for="groupid" accesskey="g">{GROUP}</label>
				<select name="groupid" id="groupid">
					{option_group}
				</select>
			</div>
			<div id="row">
				<label for="filter" accesskey="g">{FILTER}</label>
				<select name="filter" id="filter">
				    <option value="">{NO_FILTER}</option>
					<option value="1">{AVAILABLE}</option>
					<option value="NULL">{NOT_AVAILABLE}</option>				
				</select>
			</div>
		</fieldset>
	</div>
</form>
<table id="sondas" class="sondas"></table>