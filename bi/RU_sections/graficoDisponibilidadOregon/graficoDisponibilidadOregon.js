// fecha sondas por region
var fechaLineAgent = new Array();
var fechaActual = new Date();
for ( i = 7; i >= 1; i--) {
	var dia = new Date(fechaActual.getTime() - ((24 * 60 * 60 * 1000) * i));
	fechaLineAgent.push(dia.getDate() + " of " + letrasMes[dia.getMonth()]);
}

var optionsOregon = {
	chart : {
		renderTo : 'graficoDisponibilidadPorEstados',
		defaultSeriesType : 'line'
	},
	title : {
		text : ""
	},
	credits : {
		enabled : false
	},
	xAxis : {
		categories : fechaLineAgent,
		labels : {
			style : {
				color : '#1A237E',
				fontSize : '9px'
			}
		}
	},
	yAxis : {
		title : {
			text : 'Media'
		},
		labels : {
			style : {
				color : '#1A237E',
				fontSize : '9px'
			}
		},
		plotLines : [{
			value : 0,
			width : 1,
			color : '#808080'
		}]
	},
	legend : {
		align : 'center',
		verticalAlign : 'top',
		floating : true,
		x : 0,
		y : 30,
		itemStyle : {
			color : '#1A237E',
			fontSize : '8px'
		}
	},
	tooltip : {
		style : {
			color : '#FFFFFF'
		},
		backgroundColor : '#3d3d3d',
		borderColor : 'black',
		borderRadius : 10,
		borderWidth : 1
	},
	series : graficoDisponibilidadOregon
};
