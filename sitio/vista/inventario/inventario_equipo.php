<script type="text/javascript" src="{url_base}sitio/js/view.inventario_equipos.js"></script>
<script type="text/javascript">

    tableInventory = $("#tableInventory");
    
	$("#fmGrupo_inventario").change(function( ) {
		tableInventory.flexReload();
	});

    $("#fmFilter_inventario").change(function( ) {
        var status = $(this).val();
        tableInventory.flexOptions({qtype:'availability',query:status});
        tableInventory.flexReload();
    });
    
	tableInventory.flexigrid({
		url : '/inventario/getEquipoTable',
		dataType : 'json',
		title : '{TITLE_TABLE_INVENTORY}',
		colModel : [{
			display : '{AGENT}',
			name : 'host',
			width : '250',
			sortable : true,
			align : 'left'
		},{
            display : '{AGENT_CODE}',
            name : 'codigosonda',
            width : '118',
            sortable : true,
            align : 'center'
        }, {
			display : 'IP',
			name : 'ip_wan',
			width : '75',
			sortable : true,
			align : 'center'
		}, {
			display : '{AVAILABLE}',
			name : 'availability',
			width : '60',
			sortable : true,
			align : 'left'
		}, {
			display : 'LAN IP 1',
			name : 'ip_lan',
			width : '68',
			sortable : true,
			align : 'center'
		}, {
			display : 'MAC Lan',
			name : 'mac_lan',
			width : '103',
			sortable : true,
			align : 'center'
		}, {
			display : 'Plan',
			name : 'plan',
			width : '75',
			sortable : false,
			align : 'center'
		}, {
			display : 'MAC Wan',
			name : 'mac',
			width : '103',
			sortable : true,
			align : 'left'
		}, {
			display : 'Direccion',
			name : 'direccion',
			width : '200',
			sortable : false,
			align : 'left'
		}, {
			display : 'Status',
			name : 'status',
			hide : true
		}],
		usepager : true,
		useRp : true,
		rp : 30,
		height : 'auto',
		qtype: 'availability',
		query: '1',
		striped : false,
		onSubmit : function( ) {
			tableInventory.flexOptions({
				params : [{
					name : 'callId',
					value : 'tableInventory'
				},{
                    name : 'where',
                    value : 'none'
                }].concat($('#fmFilter').serializeArray())
			});
			return true;
		},
		onSuccess : function( ) {
			$("#tableInventory #editWifi").button();
			
			$("#tableInventory tr").each(function() {
		        var st = $(this).find("td:nth(9)").text();
		        console.log(st);
		        if (st == "1") {
		            $(this).attr("class",$(this).attr("class") == "erow" ? "darkgreen" : "lightgreen" );
		        } 
		        else if (st == "0") {
		           $(this).attr("class",$(this).attr("class") == "erow" ? "darkred" : "lightred" );
		        }
			});
			
			return true;
		}

	});

</script>
<form id="fmFilter">
	<div class="paneles">
		<fieldset>
			<div id="row">
				<label for="fmGrupo_inventario" accesskey="g">{GROUP}</label>
				<select name="fmGrupo_inventario" id="fmGrupo_inventario">
					{option_group}
				</select>
			</div>
            <div id="row">
                <label for="fmFilter_inventario" accesskey="g">{FILTER}</label>
                <select name="fmFilter_inventario" id="fmFilter_inventario">
                    <option value="1">{AVAILABLE}</option>
                    <option value="NULL">{NOT_AVAILABLE}</option>
                    <option value="">{NO_FILTER}</option>
                </select>
            </div>
            <!-- /*<div id="row" class="none">
            	<span class="pheader">{DISABLED}</span><div style="background: #ffe8e8" class="pcontent"></div>
           	</div>
            <div id="row" class="none">
            	<span class="pheader">{ENABLED}</span><div style="background: #f2fff9" class="pcontent"></div>
           	</div> 
           	*/ -->
		</fieldset>
	</div>
</form>
<div id="modal_edit_wifi" title="{EDIT_PARAMETERS}">
	{form_edit_wifi}
</div>
<table id="tableInventory" class="equipo"></table>