<script type="text/javascript" src="{url_base}sitio/js/view.admin_parameter.js"></script>
<script type="text/javascript">
    table_parameter = $("#table_parameter");
    table_parameter.flexigrid({
        url: '/admin/getTableParameter',
        title: 'Parameter',
        dataType: 'json',
        colModel : [
            {display: '{NAME}', name : 'nombre', width : 300, sortable : false, align: 'left'},
            {display: '{DESCRIPTION}', name : 'descripcion', width : 300, sortable : false, align: 'left'},
            {display: '{OPTIONS}', name : 'options', width : 300, sortable : false, align: 'left'}
        ],
        buttons: [
        	{name: '{NEW}', bclass: 'add', onpress : toolbox_parameter},
            {name: '{DELETE}', bclass: 'delete', onpress : toolbox_parameter}
        ],      
        usepager: true,
        useRp: true,
        striped:false,
        rp: 15,
        showTableToggleBtn: true,
        resizable: true,
        onSubmit : function(){
            table_parameter.flexOptions({params: [{
                       name:'callId', 
                       value:'table_parameter'
            }].concat($('#table_parameterFilter').serializeArray())});
            return true;
        },
        height: 'auto',
        onSuccess:  function(){
             $( "#table_parameter #toolbarSet" ).buttonset();
        }
    }); 
    
	function toolbox_parameter(com, grid) {
		if (com == '{NEW}') {
			$( "#modal_parameter_new" ).dialog( "open" );
		} else if (com == '{DELETE}') {
			$( "#modal_parameter_confirm" ).dialog( "open" );
		}
	}

</script>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div style="display: none" id="modal_parameter_new" title="Create new parameter">
	{form_new}
</div>

<div style="display:none" id="modal_parameter_edit"  title="Edit parameter" ></div>

<div style="display: none" id="modal_parameter_confirm" title="Delete parameter">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you really sure to delete parameter?</p>
</div>

<table id="table_parameter" class="table_parameter"></table>