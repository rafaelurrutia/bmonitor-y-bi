<script type="text/javascript">
	jQuery(function($) {
		ultimafechaFilter = new $.formVar('ultimafechaFilter');

		ultimafechaFilter.get('fmGrupo').change(function() {
			$.post("monitor/ultimafecha", {
				id : $(this).val()
			}, function(data) {
				$("#fmEquipo").html(data)
			})
		});

		ultimafechaFilter.get('fmEquipo').change(function() {
			$('#graph_ultimafecha').html('');
			$("#cerrarGraph").hide();
			$(".flexigrid").show();
			ultimafecha.flexReload();
		});

		ultimafechaFilter.get('fmSearch').keyup(function() {
			ultimafecha.flexReload();
			$('#ultimafecha').flexOptions({
					newp: 1
			});
		});

		ultimafechaFilter.get('fmFilter').change(function() {
			ultimafecha.flexReload();
			$('#ultimafecha').flexOptions({
					newp: 1
			});
		});		

		ultimafecha = $("#ultimafecha").flexigrid({
			url : '/tablas/ultimafecha/',
			title : '{LAST_DATE}',
			dataType : 'json',
			colModel : [{
				display : '{NAME}',
				name : 'descriptionLong',
				width : '350',
				sortable : true,
				align : 'left'
			}, {
				display : '{MEASUREMENT_DATE}',
				name : 'lastclock',
				width : '120',
				sortable : true,
				align : 'left'
			}, {
				display : '{LAST_VALUE}',
				name : 'lastvalue',
				width : '65',
				sortable : false,
				align : 'center'
			}, {
				display : '{CHANGE_FROM_PREVIOUS_VALUE}',
				name : 'prevvaluediff',
				width : '200',
				sortable : false,
				align : 'center'
			}, {
				display : '{HISTORICAL_DAYS}',
				name : 'historical',
				width : '92',
				sortable : false,
				align : 'center'
			}],
			sortname : "descriptionLong",
			sortorder : "asc",
			usepager : true,
			useRp : true,
			striped : false,
			rp : 15,
			resizable : true,
			onSubmit : function() {
				$('#ultimafecha').flexOptions({
					params : [{
						name : 'callId',
						value : 'ultimafecha'
					}].concat($('#ultimafechaFilter').serializeArray())
				});
				return true;
			},
			height : 'auto',
			onSuccess : function() {
				$("#toolbar #toolbarSet").buttonset();
			}
		});

		$("#cerrarGraph").button();
	});
	
	function graph_display(iditem, idhost, groupid) {
		try {
			$(".flexigrid").hide();
			$("#cerrarGraph").show();
		} catch(a) {

		}
		$.post("graphc/getChartsFilter", {
			"monitorid" : iditem,
			"limit" : 3,
			"dataGrouping" : 0,
			"hostid" : idhost,
			"groupid" : groupid,
			"filterid" : 1,
			"planid" : 0,
			"container" : 'ultimafecha'
		}, function(data) {
			$("#graph_ultimafecha").html(data)
		})
	}

	function table_display(iditem, idhost, type) {
		try {
			$(".flexigrid").hide();
			$("#cerrarGraph").show();
		} catch(a) {

		}
		$('#graph_ultimafecha').load('history/table?idItem=' + iditem + '&host=' + idhost + '&type=' + type);
	}

	function graph_close() {
		var hostid = $('#fmEquipo').val();
		$('#graph_ultimafecha').html('');
		$("#cerrarGraph").hide();
		$(".flexigrid").show();
	}

</script>
<div class="paneles">
	<form id="ultimafechaFilter">
		<fieldset>
			<div id="row">
				<label for="fmGrupo" accesskey="g">{GROUP}</label>
				<select class="formControl" title="Group of agents" name="fmGrupo" id="fmGrupo">
					{select_group}
				</select>
			</div>
			<div id="row">
				<label for="fmEquipo" accesskey="g">{AGENT}</label>
				<select class="formControl" title="Select an agent" name="fmEquipo" id="fmEquipo">
					{select_equipo}
				</select>
			</div>
			<div id="row">
				<label for="fmFilter" accesskey="g">{FILTER}</label>
				<select class="formControl" title="Select an filter" name="fmFilter" id="fmFilter">
					<option value="0">{ALL}</option>
					<option value="1">{MONITORS_NO_DATA}</option>	
					<option value="2">{MONITORS_DATA}</option>					
				</select>
			</div>
			<div id="row" class="none">
				<label for="fmSearch" accesskey="g">{SEARCH}</label>
				<input name="fmSearch" id="fmSearch" />
			</div>
			<input type="button" style="display: none" value="{CLOSE} {CHART}" name="cerrarGraph" id="cerrarGraph" onClick="graph_close()">
		</fieldset>
	</form>
</div>
<div id="graph_ultimafecha"></div>
<table id="ultimafecha" style="display: none"></table>