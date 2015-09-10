<script type="text/javascript" src="{url_base}sitio/js/view.inventario_equipos.js"></script>
<script type="text/javascript">

    tableAlert = $("#tableAlert");
    
	$("#fmGrupo_tableAlert").change(function( ) {
		tableAlert.flexOptions({ newp: 1 }).flexReload();
	});

	$("#fmStatus_tableAlert").change(function( ) {
		tableAlert.flexOptions({ newp: 1 }).flexReload();
	});
	
	$("#fmGroupBy_tableAlert").change(function( ) {
		tableAlert.flexOptions({ newp: 1 }).flexReload();
	});
    
	tableAlert.flexigrid({
		url : '/monitor/getAlertTable',
		dataType : 'json',
		title : '{LAST_ALERT}',
		colModel : [{
			display : '{AGENT}',
			name : 'name_host',
			sortable : true,
			align : 'left',
			width : '200'
		},{
            display : '{AGENT_CODE}',
            name : 'code_host',
            sortable : true,
            align : 'center',
            width : '100'
       },{
            display : '{INFO_TRAP}',
            name : 'type_trap',
            sortable : true,
            align : 'center',
            width : '200'
       },{
            display : 'Item',
            name : 'name_item',
            sortable : true,
            align : 'left',
            width : '250'
        },{
	    	display : '{MEASUREMENT_DATE}',
            name : 'datetime',
            sortable : true,
            align : 'left',
            width : '150'
		},{
			display : '{MEASUREMENT_VALUE}',
            name : 'lastvalue',
            sortable : false,
            align : 'left',
            width : '150'
		},{
			display : '{MEASUREMENT_UNIT}',
            name : 'unit',
            sortable : true,
            align : 'left',
            width : '150'
		},{
			display : '{MEASUREMENT_VALUE_PERC}',
            name : 'averagevalue',
            sortable : false,
            align : 'left',
            width : '150'
		},{
			display : '{THRESHOLD_NOMINAL_VALUE}',
            name : 'nominal_value',
            sortable : false,
            align : 'left',
            width : '150'
		},{
			display : '{THRESHOLD_WARNING_VALUE}',
            name : 'warning_value',
            sortable : false,
            align : 'left',
            width : '150'
		},{
			display : '{THRESHOLD_CRITICAL_VALUE}',
            name : 'critical_value',
            sortable : false,
            align : 'left',
            width : '150'
		}],
		usepager : true,
		useRp : true,
		rp : 30,
		height : 'auto',
		striped : false,
		onSubmit : function( ) {
			tableAlert.flexOptions({
				params : [{
					name : 'callId',
					value : 'tableAlert'
				},{
                    name : 'where',
                    value : 'none'
                }].concat($('#fmFilter').serializeArray())
			});
			return true;
		},
		onSuccess : function( ) {			
			$("#tableAlert tr").each(function() {
		        var fail = $(this).find("td:nth(2)").text().toLowerCase();
		        
		        if(fail.indexOf("critical") >= 0) {
		        	$(this).attr("class",$(this).attr("class") == "erow" ? "darkred" : "lightred" );
		        }
		        
		        if(fail.indexOf("warning") >= 0){
		        	$(this).attr("class",$(this).attr("class") == "erow" ? "darkyellow" : "lightyellow" );
		        }
		        
		        if(fail.indexOf("clear") >= 0) {	           
		            $(this).attr("class",$(this).attr("class") == "erow" ? "darkgreen" : "lightgreen" );
		        }
			});
			
			return true;
		}

	});
	
	setInterval(function(){
		tableAlert.flexReload();
	}, 10000);

</script>
<form id="fmFilter">
	<div class="paneles">
		<fieldset>
			<div id="row">
				<label for="fmGrupo_tableAlert" accesskey="g">{GROUP}</label>
				<select name="fmGrupo_tableAlert" id="fmGrupo_tableAlert">
					{option_group}
				</select>
			</div>
			<div id="row">
				<label for="fmStatus_tableAlert" accesskey="s">{STATUS}</label>
				<select name="fmStatus_tableAlert" id="fmStatus_tableAlert">
					<option value="0">{ALL}</option>
					<option value="1">{WARNING}</option>
					<option value="2">{CRITICAL}</option>
				</select>
			</div>
		</fieldset>
	</div>
</form>
<table id="tableAlert"></table>