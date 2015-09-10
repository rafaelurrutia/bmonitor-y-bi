var graficoDisponibilidadOregon = [{
	"name" : 'Seattle',
	"data" : [41, 60, 83, 61, 45, 26, 87],
	"lineWidth" : 0.5,
	"color":statusColorGood
}, {
	"name" : 'Spokane',
	"data" : [51, 52, 33, 51, 55, 66, 77],
	"lineWidth" : 0.5,
	"color":statusColorGood
}, {
	"name" : 'Tacoma',
	"data" : [51, 42, 54, 55, 45, 56, 57],
	"lineWidth" : 0.5,
	"color":statusColorGood
}, {
	"name" : 'Vancouver',
	"data" : [31, 42, 53, 53, 35, 56, 57],
	"lineWidth" : 0.5,
	"color":statusColorGood
}, {
	"name" : 'Bellevue',
	"data" : [76, 72, 52, 33, 55, 56, 57],
	"lineWidth" : 0.5,
	"color":statusColorGood
}, {
	"name" : 'Everett',
	"data" : [45, 32, 73, 38, 55, 56, 57],
	"lineWidth" : 0.5,
	"color":statusColorGood
}];

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
		text : graficoDisponibilidadOregonTitulo
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
