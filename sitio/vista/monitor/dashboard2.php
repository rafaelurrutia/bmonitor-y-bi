<style type="text/css">
	.groupsColumn {
		text-align: center
	}
	.column-m {
		width: 925px;
		text-align: left;
		margin: 0 auto;
	}
	.portlet-m {
		margin: 0 1em 1em 0;
		padding: 0.3em;
	}
	.portlet-header-m {
		padding: 0.2em 0.3em;
		position: relative;
	}

	.portlet-toggle-m {
		position: absolute;
		top: 50%;
		right: 0;
		margin-top: -8px;
	}

	.portlet-content-m {
		padding: 0px;
		margin-top: 9px;
	}

	.portlet-content-m table {
		width: 100%;
	}

	.portlet-placeholder {
		border: 1px dotted black;
		margin: 0 1em 1em 0;
		height: 100px;
	}

	.ui-tooltip {
		max-width: 650px;
	}
	
	.cellPadding{
		padding:0px 5px;
	}
	
	table.cellPadding tr td{ padding:0px 10px; }

</style>
<script type="text/javascript" language="JavaScript">
	$(function() {

		$(".column-m").sortable({
			connectWith : ".column-m",
			handle : ".portlet-header-m",
			cancel : ".portlet-toggle-m",
			placeholder : "portlet-placeholder ui-corner-all"
		});

		$(".portlet-m").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all").find(".portlet-header-m").addClass("ui-widget-header ui-corner-all").prepend("<span class='ui-icon ui-icon-minusthick portlet-toggle-m'></span>");

		$(".portlet-toggle-m").click(function() {
			var icon = $(this);
			icon.toggleClass("ui-icon-minusthick ui-icon-plusthick");
			icon.closest(".portlet-m").find(".portlet-content-m").toggle();
		});

		$(document).tooltip({
			items : ".tooltips",
			content : function() {
				var element = $(this);

				$.ajax({
					async : false,
					cache : true,
					url : "/monitor/getDashboardTips/" + element.attr('id'),
					dataType : "json",
					success : function(data) {
						table = data.table;
					}
				});
				return table;
			}
		});
		/*
		 setInterval(function() {
		 $.ajax({
		 url : "/monitor/getDashboardReload",
		 type : "GET",
		 data : {
		 refresh : true
		 },
		 dataType : "json",
		 error : function() {
		 return false;
		 },
		 success : function(data) {
		 $("#table_status_system").html(data.table_status_system);
		 $("#table_status_bsw").html(data.table_status_bsw);
		 $("#table_problem_list").html(data.table_problem_list);
		 }
		 });
		 }, 8000);
		 */
	}); 
</script>

<!-- codigo generado por rafael urrutia -->
<script type="text/javascript" language="JavaScript">
	function getJsonCall(url) {

		var result = null;
		$.ajax({
			type : "GET",
			async : false,
			url : url
		}).done(function(data) {
			result = data;
		}).fail(function(jqXHR, textStatus, errorThrown) {
			failAjax(jqXHR);
		});
		return result;

	}

	$(function() {

		var chartColors = ["green", "red"];

	
		var getGrupos = [];
		var getSondasDisponibles = [];
		var getSondasNoDisponibles = [];
		var getIdGroup = [];
		var dateTime = new Date;
		
		// script para publicar la hora en cada titulo
		$(".time").html(dateTime.getHours()+":"+dateTime.getMinutes());
		
		// script utilizado para construir grafico
		var getJsonValuesFromGraphics = getJsonCall('monitor/nEstadoSistema');
		
		if (getJsonValuesFromGraphics.hasOwnProperty('data')) {	
																			
			$.each(getJsonValuesFromGraphics.data, function(index, value) {
				getIdGroup.push(value.id_group);
				getGrupos.push(value.name);
				getSondasDisponibles.push(parseInt(value.available));
				getSondasNoDisponibles.push(parseInt(value.no_available));
			});
			
		}	
		
		// script para construir tabla informacion del servidor en donde da a conocer procesos, espacio en disco duro y memoria
		getJsonValues = getJsonCall('monitor/ru_informacionServidor').data[0];
		$('#generarTablaProcesos').html('<tr class="rowA"><td class="ui-grid-content">' + getJsonValues.server + '</td><td class="ui-grid-content">' + getJsonValues.promLoad1Min + '</td><td class="ui-grid-content">' + getJsonValues.promLoad5Min + '</td><td class="ui-grid-content">' + getJsonValues.promLoad15Min + '</td></tr>');
		$('#generarTablaDisco').html('<tr class="rowA"><td class="ui-grid-content">' + getJsonValues.server + '</td><td class="ui-grid-content">' + getJsonValues.espacioTotal + '</td><td class="ui-grid-content">' + getJsonValues.espacioOcupado + '</td><td class="ui-grid-content">' + getJsonValues.espacioDisponible + '</td></tr>');
		$('#generarTablaMemoria').html('<tr class="rowA"><td class="ui-grid-content">' + getJsonValues.memFree + '</td><td class="ui-grid-content">' + getJsonValues.memTotal + '</td><td class="ui-grid-content">' + getJsonValues.buffers + '</td><td class="ui-grid-content">' + getJsonValues.cached + '</td><td class="ui-grid-content">' + getJsonValues.swapCached + '</td><td class="ui-grid-content">' + getJsonValues.swapTotal + '</td><td class="ui-grid-content">' + getJsonValues.swapFree + '</td></tr>');

		// script para construir tabla estado de los nodos
		getJsonValues = getJsonCall('monitor/statusNodos');
		$.each(getJsonValues, function(index, value) {
			var cssClass = (index % 2 == 0) ? 'rowA' : 'rowB';
			var colorEstado = (value.estado === 'active') ? 'green' : 'red';
			$('#generarTablaEstadoDeLosNodos').append('<tr class="' + cssClass + '"><td class="ui-grid-content">' + value.nodo + '</td><td class="ui-grid-content"><span class="ui-icon ui-icon-bullet" style="color:' + colorEstado + '"></span></td><td class="ui-grid-content">' + value.ultima_prueba + '</td><td class="ui-grid-content">' + value.tipo + '</td></tr>');
		});
		
		getJsonValues = getJsonCall('monitor/estadoBsw').data[0];
		
		$('#generarTablaEstadoDeBsw tr:eq(0) td:eq(1)').text(getJsonValues.sondasState);
		$('#generarTablaEstadoDeBsw tr:eq(0) td:eq(2)').text(getJsonValues.sondasDetails);
		
		$('#generarTablaEstadoDeBsw tr:eq(1) td:eq(1)').text(getJsonValues.monitoresState);
		$('#generarTablaEstadoDeBsw tr:eq(1) td:eq(2)').text(getJsonValues.monitoresDetails);
		
		$('#generarTablaEstadoDeBsw tr:eq(2) td:eq(1)').text(getJsonValues.usuariosState);
		$('#generarTablaEstadoDeBsw tr:eq(2) td:eq(2)').text(getJsonValues.usuariosDetails);
		
		getJsonValues = getJsonCall('monitor/ultimos20problemas');
		
		if (getJsonValues.hasOwnProperty('data')) {	
																			
			var htmlRowBuild;
			$.each(getJsonValues.data, function(index, value) {
				var cssClass = (index % 2 == 0) ? 'rowA' : 'rowB';
				htmlRowBuild+='<tr class="' + cssClass + '"><td class="ui-grid-content">' + value.name_group + '</td><td class="ui-grid-content">' + value.name_sonda + '</td><td class="ui-grid-content">' + value.problem + '</td><td class="ui-grid-content">' + value.last_conection + '</td><td class="ui-grid-content">' + value.code_sonda + '</td></tr>';
			});
			$('#generarTablaUltimos20Problemas').html(htmlRowBuild);
			
		}else{
			var messageEmptyFields="No hay datos disponibles"
			htmlRowBuild+='<tr align="center" colspan="6"><td class="ui-grid-content">' + messageEmptyFields + '</td></tr>';
			$('#generarTablaUltimos20Problemas').html(htmlRowBuild);
		}	
		
		
		
		
		$('#container').highcharts({
			chart : {
				type : 'column',
				backgroundColor : null
			},
			title : {
				text : ''
			},
			exporting : {
				enabled : false
			},
			xAxis : {
				categories : getGrupos
			},
			yAxis : {
				min : 0,
				title : {
					text : 'Sondas'
				},
				stackLabels : {
					enabled : true,
					style : {
						fontWeight : 'bold',
						color : (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
					}
				}
			},
			legend : {
				align : 'right',
				x : -70,
				verticalAlign : 'top',
				y : 20,
				floating : true,
				backgroundColor : (Highcharts.theme && Highcharts.theme.background2) || 'white',
				borderColor : '#CCC',
				borderWidth : 1,
				shadow : false
			},
			tooltip : {
				/*
				 style : {
				 color : '#FFFFFF'
				 },
				 backgroundColor : '#3d3d3d',
				 borderColor : 'black',
				 borderRadius : 10,
				 borderWidth : 1,
				 */
				useHTML : true,
				formatter : function() {
					if (getSondasNoDisponibles[this.point.x] != 0) {

						var indice = this.point.x;

						var htmlTableHead = '<div class="ui-grid ui-widget ui-widget-content ui-corner-all"> <table class="cellPadding ui-grid-content ui-widget-content"> <tr> <th style="min-width: 170px!important;" class="ui-state-default">Sonda</th> <th class="ui-state-default">Codigo de Agente</th> <th class="ui-state-default">Última conexion SONDA OK</th>  </tr>';
						var htmlTableContent = "";
						var htmlTableFooter = '</table></div>';
						var classRow = "rowA";
						
						$.each(getJsonValuesFromGraphics.data[indice].details, function(ind, val) {
							classRow = (ind % 2 == 0) ? 'rowA' : 'rowB';
							htmlTableContent += '<tr class="' + classRow + '"><td class="ui-grid-content">' + val.host + '</td><td class="ui-grid-content">' + val.agent_code + '</td><td class="ui-grid-content">' + val.lastcheck + '</td></tr>';
						});

						return (htmlTableHead + htmlTableContent + htmlTableFooter);
					} else {
						return false;
					}
				}
			},
			plotOptions : {
				column : {
					stacking : 'normal',
					dataLabels : {
						enabled : true,
						formatter : function() {
							if (this.y > 0)
								return this.y;
						},
						color : (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white',
						style : {
							textShadow : '0 0 3px black, 0 0 3px black'
						}
					}
				}
			},
			series : [{
				name : '{AVAILABLE}',
				data : getSondasDisponibles,
				color : chartColors[0],

			}, {
				name : '{NOT_AVAILABLE}',
				data : getSondasNoDisponibles,
				color : chartColors[1],

			}]
		});

	}); 
</script>
<!-- fin codigo generado por rafael urrutia -->

<div class="groupsColumn">
	<div class="column-m">

		<!-- start section get server information -->
		<div class="portlet-m">
			<div class="portlet-header-m">
				{INFORMATION_SERVER} (<span class="time"></span>) 
			</div>
			<div class="portlet-content-m">
				<div id="table_get_server_information" class="ui-grid ui-widget ui-widget-content ui-corner-all">
					<table class="ui-grid-content ui-widget-content">
						<thead>
							<tr>
								<th class="ui-state-default">{SERVER}</th>
								<th class="ui-state-default">{AVERAGE_CPU_1}</th>
								<th class="ui-state-default">{AVERAGE_CPU_5}</th>
								<th class="ui-state-default">{AVERAGE_CPU_15}</th>
							</tr>
						</thead>
						<tbody id="generarTablaProcesos"></tbody>
					</table>

					<table class="ui-grid-content ui-widget-content">
						<tr>
							<th class="ui-state-default">{SERVER}</th>
							<th class="ui-state-default">{TOTAL_SPACE_DISK}</th>
							<th class="ui-state-default">{USED_SPACE_DISK}</th>
							<th class="ui-state-default">{AVAILABLE_SPACE_DISK}</th>
						</tr>
						<tbody id="generarTablaDisco"></tbody>

					</table>

					<table class="ui-grid-content ui-widget-content">
						<tr>
							<th class="ui-state-default">{TOTAL_MEMORY}</th>
							<th class="ui-state-default">{FREE_MEMORY}</th>
							<th class="ui-state-default">{BUFFER_MEMORY}</th>
							<th class="ui-state-default">{CACHED_MEMORY}</th>
							<th class="ui-state-default">{CACHED_SWAP_MEMORY}</th>
							<th class="ui-state-default">{TOTAL_SWAP_MEMORY}</th>
							<th class="ui-state-default">{FREE_SWAP_MEMORY}</th>
						</tr>
						<tbody id="generarTablaMemoria"></tbody>

					</table>

				</div>
			</div>
		</div>
		<!-- end section get server information -->

		<!-- start section "Estado de los nodos" -->
		<div class="portlet-m">
			<div class="portlet-header-m">
				{DASHBOARD_TITLE_STATUS_LIST} (<span class="time"></span>)
			</div>
			<div class="portlet-content-m">
				<div id="table_nodos_list" class="ui-grid ui-widget ui-widget-content ui-corner-all">
					<table class="ui-grid-content ui-widget-content">
						<thead>
							<tr>
								<th class="ui-state-default">{NODE}</th>
								<th class="ui-state-default">{STATUS}</th>
								<th class="ui-state-default">{LAST_TEST}</th>
								<th class="ui-state-default">{TYPE}</th>
							</tr>
						</thead>
						<tbody id="generarTablaEstadoDeLosNodos"></tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- end section "Estado de los nodos" -->

		<!-- start section "Estado de BSW" -->
		<div class="portlet-m">
			<div class="portlet-header-m">
				{DASHBOARD_TITLE_STATUS_BSW} (<span class="time"></span>)
			</div>
			<div class="portlet-content-m">
				<div id="table_status_bsw" class="ui-grid ui-widget ui-widget-content ui-corner-all">
					<table class="ui-grid-content ui-widget-content">
						<thead>
							<tr>
								<th class="ui-state-default">{PARAMETROS}</th>
								<th class="ui-state-default">{STATUS}</th>
								<th class="ui-state-default">{DETAILS}</th>
							</tr>
						</thead>
						<tbody id="generarTablaEstadoDeBsw">
							<tr class="rowA">
								<td class="ui-grid-content">{S_NUMBER_OF_HOSTS}</td>
								<td class="ui-grid-content"></td>
								<td class="ui-grid-content"></td>
							</tr>
							<tr class="rowB">
								<td class="ui-grid-content">{S_NUMBER_OF_ITEMS}</td>
								<td class="ui-grid-content"></td>
								<td class="ui-grid-content"></td>
							</tr>
							<tr class="rowA">
								<td class="ui-grid-content">{S_NUMBER_OF_USERS}</td>
								<td class="ui-grid-content"></td>
								<td class="ui-grid-content"></td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- end section "Estado de BSW" -->
		
		<!-- start section "Estado de las sondas por grupos (disponibles/no disponibles)" -->	 	
		<div class="portlet-m">
		    <div class="portlet-header-m">{DASHBOARD_STATUS_AGENT_GROUP} (<span class="time"></span>)</div>
		    <div id="container" class="portlet-content-m" style="margin: 0 auto">		        
		    </div>
		</div>
		<!-- end section "grafico sondas" -->
		
		<!-- start section "ultimos 20 problemas" -->		
		<div class="portlet-m">
		    <div class="portlet-header-m">{LAST_20_TROUBLES} (<span class="time"></span>)</div>
		    <div class="portlet-content-m">
		        <div id="table_problem_list" class="ui-grid ui-widget ui-widget-content ui-corner-all">
		            <table class="ui-grid-content ui-widget-content">
		                <thead>
		                    <tr>
		                        <th style="min-width: 100px!important;" class="ui-state-default">{GROUP}</th>
		                        <th style="min-width: 140px!important;" class="ui-state-default">{AGENT}</th>
		                        <th class="ui-state-default">{PROBLEM}</th>
		                        <th class="ui-state-default">{LAST_CONNECTION}</th>
		                        <th class="ui-state-default">{AGENT_CODE}</th>
		                    </tr>
		                </thead>
		                <tbody id="generarTablaUltimos20Problemas"></tbody>     
		            </table>
		        </div>
		    </div>
		</div>
		<!-- end section "ultimos 20 problemas" -->
		
	</div>

</div>

<div class="groupsColumn">
	{portlet}
	<!--
	<div class="column-m">
	<div class="portlet-m" style="">
	<div class="portlet-header-m">
	Estado de las sondas por grupos (Disponibles/No disponibles) ()
	</div>
	<div id="container" class="portlet-content-m" style="margin: 0 auto">

	</div>
	</div>
	</div>
	-->
</div>

