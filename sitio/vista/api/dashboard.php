<!DOCTYPE html>
<html>
	<head>
		<title>Baking Software</title>
		<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/layout.css">
		<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/index.css">
		<link rel="stylesheet" type="text/css" href="{url_base}sitio/css/themes/cupertino/jquery-ui.css"/>
		
		<style type="text/css">
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
		</style>
		
		<script type="text/javascript" src="{url_base}sitio/js/jquery-1.9.1.min.js"></script>
		<script type="text/javascript" src="{url_base}sitio/js/jquery-ui-1.10.3.custom.min.js"></script>
		<script type="text/javascript" src="{url_base}sitio/js/default.js"></script>
		<script type="text/javascript" src="http://code.highcharts.com/highcharts.js"></script>
		<script type="text/javascript">

			$(document).ready(function(){
				function getJsonCall(url) {
					var result = null;
					$.ajax({
						type : "GET",
						async : false,
						url : url
					}).done(function(data) {
						result = data;
					}).fail(function(jqXHR, textStatus, errorThrown) {
						console.log(jqXHR);
					});
				
					return result;
				}
				
				$(".portlet-m").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all").find(".portlet-header-m").addClass("ui-widget-header ui-corner-all").prepend("<span class='ui-icon ui-icon-minusthick portlet-toggle-m'></span>");
				$(".portlet-toggle-m").click(function() {
					var icon = $(this);
					icon.toggleClass("ui-icon-minusthick ui-icon-plusthick");
					icon.closest(".portlet-m").find(".portlet-content-m").toggle();
				});
				
				var chartColors = ["green", "red"];
				var getGrupos = [];
				var getSondasDisponibles = [];
				var getSondasNoDisponibles = [];
				var getIdGroup = [];
				var dateTime = new Date;
				
				$(".time").html(dateTime.getHours()+":"+dateTime.getMinutes());
				
				var getJsonValuesFromGraphics = getJsonCall('getDashboardGropsHosts');
				
				if (getJsonValuesFromGraphics.hasOwnProperty('data')) {	
					$.each(getJsonValuesFromGraphics.data, function(index, value) {
						getIdGroup.push(value.id_group);
						getGrupos.push(value.name);
						getSondasDisponibles.push(parseInt(value.available));
						getSondasNoDisponibles.push(parseInt(value.no_available));
					});
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
						color : chartColors[0]
					}, {
						name : '{NOT_AVAILABLE}',
						data : getSondasNoDisponibles,
						color : chartColors[1]
					}]
				});
			});
		</script>
	</head>
	<body>
		<div class="portlet-m">
		    <div class="portlet-header-m">{DASHBOARD_STATUS_AGENT_GROUP} (<span class="time"></span>)</div>
		    <div id="container" class="portlet-content-m" style="margin: 0 auto"></div>
		</div>
		<div class="portlet-m">
		    <div class="portlet-header-m">Smart Agents seems to be unavailable: {qn} (<span class="time"></span>)</div>
		    <div id="containerUHost" class="portlet-content-m" style="margin: 0 auto">{hn}</div>
		</div>
		<div class="portlet-m">
		    <div class="portlet-header-m">Smart Agents reported activity: {qo} (<span class="time"></span>)</div>
		    <div id="containerHost" class="portlet-content-m" style="margin: 0 auto">{ho}</div>
		</div>
	</body>
</html>