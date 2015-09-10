<script type="text/javascript" src="{url_base}sitio/js/view.admin_user.js"></script>
<script type="text/javascript">
jQuery(function($){
    users = $("#usuarios");	
	users.flexigrid({
		url: '{url_base}admin/getUserTable',
		title: '{USERS}',
		dataType: 'json',
		colModel : [
			{display: 'ID', name : 'id_user', width : 30, sortable : true, align: 'center'},
			{display: '{NAME}', name : 'name', width : 200, sortable : true, align: 'left'},
			{display: '{GROUP}', name : 'group', width : 100, sortable : true, align: 'center'},
			{display: '{ADMINISTRATOR}', name : 'is_admin', width : 100, sortable : true, align: 'center'},
			{display: '{STATUS}', name : 'active', width : 50, sortable : false, align: 'center'},
			{display: '{OPTIONS}', name : 'option', width : 220, sortable : false, align: 'center'}
		],
		{button}
        usepager: false,
        sortname: "id_user",
		sortorder: "asc",
        showTableToggleBtn: false,
        resizable: true,
        width: 'auto',
        height: 'auto',
		onSubmit : function(){
        	users.flexOptions({params: [{name:'callId', value:'usuarios'}].concat($('#usuariosFilter').serializeArray())});
       		return true;
    	},
   		onSuccess:  function(){
        	 $( "#usuarios #toolbarSet" ).buttonset();
        }
	});
	
	$("#fmUser").change(function(){
		users.flexReload();
	});

	$("#toGroup").change(function(){
		users.flexReload();
	});
	
});

function dialogUserBox(com, grid) {
	if (com == '{NEW}') {
		$( "#modal_user_new" ).dialog( "open" );
	} else if (com == '{DELETE}') {
		lengthSelect = $('.trSelected', grid).length;
		if(lengthSelect > 0) {
			$( "#modal_user_delete" ).dialog( "open" );
		}
	}
}

</script>
<style>
	.form_class select { margin-bottom:12px; width:55%; padding: .4em; float: right }
	.form_class label, .form_class input { display:block;   }
	.form_class fieldset label { float: left; }
	.form_class input.text { margin-bottom:12px; width:55%; padding: .4em;  float: right;}
	#groupRadio {
		float: right;
		width:55%;
	}
	.form_class fieldset {
		  border-color: #000;
		  border-width: 1px;
		  border-style: solid;
		  padding: 10px;        /* padding in fieldset support spotty in IE */
		  margin: 0;
	}
	.form_class h1 { font-size: 1.2em; margin: .6em 0; }
	.ui-dialog .ui-state-error { padding: .3em; }
	.validateTips { border: 1px solid transparent; padding: 0.3em; }
	
	#passwordStrength
	{
		height:10px;
		display:block;
		float:left;
	}
	
	.strength0
	{
		width:250px;
		background:#cccccc;
	}
	
	.strength1
	{
		width:50px;
		background:#ff0000;
	}
	
	.strength2
	{
		width:100px;	
		background:#ff5f5f;
	}
	
	.strength3
	{
		width:150px;
		background:#56e500;
	}
	
	.strength4
	{
		background:#4dcd00;
		width:200px;
	}
	
	.strength5
	{
		background:#399800;
		width:250px;
	}
</style>

<div id="modal_user_new" title="{TITLE_NEW_USER}">
	{admin_user_form}
</div>

<div id="modal_user_delete" title="{DELETE} {USER}?">
    <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}</p>
    <p style="float:left; margin:4px 0px 0px 0px;" id="selected">{SELECTED}: <span id="usersdel"></span></p>
</div>

<div id="modal_user_edit" title="{EDIT_REGISTRY}">

</div>

<div id="modal_user_edit_pass" title="{EDIT_PASSWORD}">
    {admin_user_form_pass}
</div>

<!--/*<form id="usuariosFilter">
    <div class="paneles">
        <fieldset>
            <div id="row" class="none">
                <label for="fmUser" accesskey="u">{USER}</label>
                <input name="fmUser" id="fmUser" />
            </div>
            <div id="row" class="none">
                <label for="toGroup" accesskey="u">{GROUP}</label>
                <input name="toGroup" id="toGroup" />
            </div>
        </fieldset>
    </div>
</form>
*/-->
<table id="usuarios" class="usuarios"></table>