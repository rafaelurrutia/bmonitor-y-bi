<script type="text/javascript" src="{url_base}sitio/js/view.admin_template.js"></script>
<script type="text/javascript">
    table_template = $("#table_template");
    table_template.flexigrid({
        url: '/admin/getTableTemplate',
        title: 'Templates',
        dataType: 'json',
        colModel : [
            {display: 'Name', name : 'name'  , width : '100'  , sortable : false , align: 'left'},
            {display: 'Options', name : 'opciones'  , width : '125'  , sortable : false , align: 'left'}
        ],
        buttons : [
            {name: '{NEW}', bclass: 'add', onpress : toolbox_template},
            {name: '{DELETE}', bclass: 'delete', onpress : toolbox_template}
        ], 
        usepager: true,
        useRp: true,
        striped:false,
        rp: 15,
        showTableToggleBtn: true,
        resizable: true,
        onSubmit : function(){
            table_template.flexOptions({params: [{
                       name:'callId', 
                       value:'table_template'
            }].concat($('#table_templateFilter').serializeArray())});
            return true;
        },
        height: 'auto',
        onSuccess:  function(){
             $( "#toolbar #toolbarSet" ).buttonset();
        }
    }); 
    
	function toolbox_template(com, grid) {
		if (com == '{NEW}') {
			$( "#modal_template_new" ).dialog( "open" );
		} else if (com == '{DELETE}') {
			lengthSelect = $('.trSelected', grid).length;
			if(lengthSelect > 0) {
				$( "#modal_template_delete" ).dialog( "open" );
			}
		}
	}

</script>
<style>
	.form_sonda .col2 {
		width: 80%;
	}
</style>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="modal_template_delete" title="Delete Template?">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}</p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">{SELECTED}: </p>
</div>

<div id="modal_template_new" title="Create new template">
	{form_new}
</div>

<div id="modal_template_edit" title="Edit or Clone Template"></div>

<table id="table_template" class="table_template"></table>