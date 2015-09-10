<script type="text/javascript">

    lang = "{lang}";
    
    tableGraph = $("#table_graph");
    
    tableGraph.flexigrid({
        url: '/config/getTableGraph',
        title: '{CHARTS}',
        dataType: 'json',
        colModel : [
            {display: '{GROUP}', name : 'groupname'  , width : '100'  , sortable : false , align: 'left'},
            {display: '{NAME}', name : 'name'  , width : '240'  , sortable : true , align: 'left'},
            {display: '{CHART} {TYPE}', name : 'graphtype'  , width : '100'  , sortable : false , align: 'left'},
            {display: '{OPTIONS}', name : 'opciones'  , width : '75'  , sortable : false , align: 'left'}
        ],
        {button}    
        usepager: true,
        useRp: true,
        striped:false,
        rp: 15,
         showTableToggleBtn: true,
        resizable: true,
        onSubmit : function(){
            tableGraph.flexOptions({params: [{
                       name:'callId', 
                       value:'table_graph'
            }].concat($('#table_graphFilter').serializeArray())});
            return true;
        },
        height: 'auto',
        onSuccess:  function(){
             $( "#table_graph #toolbarSet" ).buttonset();
        }
    });

    $('#table_graphFilter #groupid').change(function() {
        tableGraph.flexReload();
    });
    	
	function gridFormat (){
		$( "#toolbar button" ).button();		
	}
	
	function toolboxGraph(com, grid) {
		if (com == '{NEW}') {
			$( "#modal_graph_form" ).dialog( "open" );
		} else if (com == '{DELETE}') {
			lengthSelect = $('.trSelected', grid).length;
			if(lengthSelect > 0) {
				$( "#modal_graph_delete" ).dialog( "open" );
			}
		}
	}
	
</script>
<script type="text/javascript" src="{url_base}sitio/js/view.config_graph.js"></script>
<style type="text/css">
   .multiselect  {
        width: 605px;
        height: 200px;
   } 
</style>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="modal_graph_form" title="{TITLE_NEW_CHART}">
	{form_new_graph}
</div>

<div id="modal_graph_delete" title="{TITLE_DELETE_CHART}">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}</p>
	<p style="float:left; margin:4px 0px 0px 0px;" id="selected">{SELECTED}: </p>
</div>

<div id="modal_graph_edit" title="{TITLE_EDIT_CHART}"></div>

<div class="paneles">
<form id="table_graphFilter">
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

<table id="table_graph" class="table_graph"></table>