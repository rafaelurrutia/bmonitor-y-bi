<script type="text/javascript" src="{url_base}sitio/js/view.admin_firmware.js"></script>
<script type="text/javascript">
    table_firmware = $("#table_firmware");
    table_firmware.flexigrid({
        url: '/admin/getTableFirmware',
        title: 'Firmware',
        dataType: 'json',
        colModel : [
            {display: 'Date Updated', name : 'fecha_update'  , width : '110'  , sortable : false , align: 'left'},
            {display: 'Version', name : 'version'  , width : '100'  , sortable : false , align: 'left'},
            {display: 'Responsible', name : 'responsable'  , width : '100'  , sortable : false , align: 'left'},
            {display: 'Branch', name : 'branch'  , width : '43'  , sortable : false , align: 'left'}
        ],
        buttons : [
            {name: '{NEW}', bclass: 'add', onpress : toolbox_firmware},
            {name: '{UPDATE}', bclass: 'config', onpress : toolbox_firmware}
        ],   
        usepager: true,
        useRp: true,
        striped:false,
        rp: 15,
         showTableToggleBtn: true,
        resizable: true,
        onSubmit : function(){
           table_firmware.flexOptions({params: [{
                       name:'callId', 
                       value:'table_firmware'
            }].concat($('#table_firmwareFilter').serializeArray())});
            return true;
        },
        height: 'auto',
        onSuccess:  function(){
             $( "#toolbar #toolbarSet" ).buttonset();
        }
    });

	function toolbox_firmware(com, grid) {
		if (com == '{NEW}') {
			$( "#modal_firmware_new" ).dialog( "open" );
		} else if (com == '{UPDATE}') {
			$( "#modal_firmware_confirm" ).dialog( "open" );
		}
	}

</script>

<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>

<div id="modal_firmware_new" title="Install new Firmware">
	{form_new}
</div>

<div id="modal_firmware_confirm" title="Update all Agents?">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Are you really sure to update all Agents to last version?</p>
</div>

<table id="table_firmware" class="table_firmware"></table>