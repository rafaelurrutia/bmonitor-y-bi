$(function() {

	mapChart = new Highcharts.Map({

		chart : {
			renderTo : 'mapa'
		},
		title : {
			text : ''
		},
		credits : {
			enabled : false
		},
		mapNavigation : {
			enabled : false,
			buttonOptions : {
				verticalAlign : 'bottom'
			}
		},
		tooltip : {
			style : {
				color : '#FFFFFF',
				fontSize : '8px'
			},
			color : '#FFFFFF',
			backgroundColor : '#3d3d3d',
			borderColor : 'black',
			borderRadius : 10,
			borderWidth : 1,
			formatter : function() {
				var text = '<strong>State: </strong>' + this.point.name + '<br><strong>Available agents: </strong>' + this.point.agent_quantity_enable + '<br><strong>Not available agents: </strong>' + this.point.agent_quantity_disable + '<br><strong>% availability: </strong>' + this.point.available;

				return text;
			}
		},
		plotOptions : {
			series : {
				events : {
					click : function(e) {
						if (e.point.value == 1) {

							// #graficoDisponibilidadCalifornia, #graficoDisponibilidadOregon, #graficoDisponibilidadNewYork

							$("#tablaResumenSondasPorEstados, #tablaResumenSondasCalifornia, #tablaResumenSondasOregon, #tablaResumenSondasNewYork").hide();
							$("#DivButtonBack").show();
					
							var graficoWashington = new Highcharts.Chart(optionsWashington);
							$("#tablaResumenSondasWashington").show();
							$("#tituloGrafico").html(graficoDisponibilidadWashingtonTitulo);
							$("#tituloGrafico").css("background-color", e.point.color);
							

						} else if (e.point.value == 2) {

							$("#tablaResumenSondasPorEstados, #tablaResumenSondasWashington, #tablaResumenSondasOregon, #tablaResumenSondasNewYork").hide();
							$("#DivButtonBack").show();

							var graficoCalifornia = new Highcharts.Chart(optionsCalifornia);
							$("#tablaResumenSondasCalifornia").show();
							$("#tituloGrafico").html(graficoDisponibilidadCaliforniaTitulo);
							$("#tituloGrafico").css("background-color", e.point.color);

						} else if (e.point.value == 3) {

							$("#tablaResumenSondasPorEstados, #tablaResumenSondasWashington, #tablaResumenSondasCalifornia, #tablaResumenSondasNewYork").hide();
							$("#DivButtonBack").show();

							var graficoOregon = new Highcharts.Chart(optionsOregon);
							$("#tablaResumenSondasOregon").show();
							$("#tituloGrafico").html(graficoDisponibilidadOregonTitulo);
							$("#tituloGrafico").css("background-color", e.point.color);

						} else if (e.point.value == 33) {

							$("#tablaResumenSondasPorEstados, #tablaResumenSondasWashington, #tablaResumenSondasOregon, #tablaResumenSondasCalifornia").hide();
							$("#DivButtonBack").show();
							
							var graficoNewYork = new Highcharts.Chart(optionsNewYork);
							$("#tablaResumenSondasNewYork").show();
							$("#tituloGrafico").html(graficoDisponibilidadNewYorkTitulo);
							$("#tituloGrafico").css("background-color", e.point.color);

						}
					}
				}
			}
		},
		series : [{
			data : data,
			mapData : Highcharts.maps['countries/us/us-all'],
			joinBy : 'hc-key',
			name : 'Random data',
			states : {
				hover : {
					color : '#9C27B0'
				}
			},
			dataLabels : {
				useHTML : true,
				enabled : true,
				formatter : function(e) {
					if (this.point.agent_quantity_disable == "N/A") {
						return '<span style="color:black; font-size:6pt; ">' + this.point.name + '</span>';
					} else if(this.point.status==statusTextBad) {
						return '<span style="color:black; font-size:6pt; ">' + this.point.name + '</span><span style="color:white; font-size:6pt;"> (' + this.point.agent_quantity_disable + ')</span>';
					}else{
						return '<span style="color:black; font-size:6pt; ">' + this.point.name + '</span><span style="color:red; font-size:6pt;"> (' + this.point.agent_quantity_disable + ')</span>';
					}
				}
				//	format : '<span style="color:black"> {point.name} </span><span style="color:red"> ({point.agent_quantity_disable}) </span>'
			}
		}]

	});

	var legend = mapChart.legend;

	legend.group.hide();

});
