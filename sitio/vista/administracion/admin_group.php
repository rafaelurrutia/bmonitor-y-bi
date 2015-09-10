<script type="text/javascript" src="{url_base}sitio/js/view.admin_groups.js"></script>
<script type="text/javascript">
jQuery(function($){
    var groupsTable = $("#grupos");
	groupsTable.flexigrid({
    	url : '/admin/getGroupTable',
    	title : '{GROUPS}',
    	dataType : 'json',
    	colModel : [{
        	display : 'ID',
        	name : 'id_group',
        	width : 30,
        	sortable : false,
        	align : 'center'
        },{
        	display : '{NAME}',
        	name : 'group',
        	width : 200,
        	sortable : false,
        	align : 'left'
        },{
        	display : '{DEFAULT}',
        	name : 'default',
        	width : 100,
        	sortable : false,
        	align : 'center'
    	},{
        	display : '{ACTIVE}',
        	name : 'active',
        	width : 90,
        	sortable : false,
        	align : 'center'
    	},{
        	display : '{OPTIONS}',
        	name : 'option',
        	width : 160,
        	sortable : false,
        	align : 'center'
        }],
    	{button}
    	usepager : true,
    	useRp : true,
    	rp : 30,
        width: 'auto',
        height: 'auto',
    	sortname : "id_group",
    	sortorder : "asc",
    	showTableToggleBtn : true,
    	resizable : false,
    	onSubmit : function( )
        {
        	groupsTable.flexOptions({ qtype:'group',query:$("#fmGroup").val()});
            return true;
        },
        singleSelect : false,
        onSuccess : function( )
    	{
            $("#grupos #toolbarSet").buttonset();
        }
	});
	
	$("#fmGroup").change(function(){
		groupsTable.flexReload();
	});
});

function dialogGroupsBox( com,grid )
{
    if ( com == "{NEW}" ) {
        $("#modalNewGroup").dialog("open");
    } else if ( com == "{DELETE}" ) {
        lengthSelect = $('.trSelected',grid).length;
        if ( lengthSelect > 0 ) {
            $("#groups-delete").dialog("open");
        }
    }
}
</script>
<style type="text/css">
	.multiselect {
		width: 580px;
		height: 200px;
	}
	.multiselectContainer {
		overflow: visible;
		width: 100%;
	}
</style>

<div id="modalNewGroup" title="{CREATE_GROUP}">
	{formNewGroups}
</div>

<div style="display:none" id="modalEditGroup"  title="{EDIT_GROUP}" ></div>

<div style="display:none" id="modalGroupsPerm"  title="{PERMISSIONS}" ></div>

<div id="groups-delete" title="{DELETE} {GROUP}?">
	<p>
		<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>{MSG_DELETE}
	</p>
	<p style="float:left; margin:4px 0px 0px 0px;" id="selected">
		{SELECTED}:
	</p>
</div>
<!-- /* No sirve para nada este filtro, se busca en muy pocos elementos.
<form id="gruposFilter">
    <div class="paneles">
        <fieldset>
            <div id="row" class="none">
                <label for="fmGroup" accesskey="g">{SEARCH}</label>
                <input name="fmGroup" id="fmGroup" />
            </div>
        </fieldset>
    </div>
</form>
*/-->
<table id="grupos"></table>