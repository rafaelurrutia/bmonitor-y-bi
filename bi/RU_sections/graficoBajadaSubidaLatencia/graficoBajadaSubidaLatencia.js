var letrasMes = new Array();

letrasMes = ["january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"];

var fechaLine = new Array();
var fechaActual = new Date();
for ( i = 30; i >= 1; i--) {
	var dia = new Date(fechaActual.getTime() - ((24 * 60 * 60 * 1000) * i));
	fechaLine.push(dia.getDate() + " de " + letrasMes[dia.getMonth()]);
}

var dataSonda = new Array();
var dataSonda = [41, 53, 55, 58, 60, 55, 55, 55, 55, 52, 55, 57, 58, 60, 60, 60, 60, 62, 62, 62, 45, 45, 45, 46, 48, 60, 60, 66, 63, 65];

var optionsAgente = {
	chart : {
		renderTo : 'lineByAgent',
		defaultSeriesType : 'line'
	},
	title : {
		text : graficoDisponibilidadCaliforniaTitulo
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
	series : graficoDisponibilidadAgent
};
