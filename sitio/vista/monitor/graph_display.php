<script language="JavaScript">

jQuery(function() {
	
	function bytes(bytes, label,type) {
		if (type !== 'bps') return Highcharts.numberFormat(bytes, 0, ',','.') +' '+ type ;
		if (bytes == 0) return 0;
	    var s = ['bytes', 'Kbps', 'Mbps', 'Gbps', 'Tbps', 'Pbps'];
	    var e = Math.floor(Math.log(bytes)/Math.log(1024));
	    var value = ((bytes/Math.pow(1024, Math.floor(e))).toFixed(2));
	    e = (e<0) ? (-e) : e;
	    if (isNaN(parseFloat(value))) {
	    	if(isNaN(parseFloat(bytes))) {
	    		return "Sin Valor";
	    	} else {
	    		return bytes;
	    	}
     	}
	    if (label) value += ' ' + s[e];
	    return value;
	}
	
	Highcharts.setOptions({
		lang: {
             months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            // weekdays: ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'],
             weekdays: ['Domingo','Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
             shortMonths : ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
             rangeSelectorFrom: 'Inicio',
             rangeSelectorTo: 'Fin'
        },
        global: {
        	useUTC: false
    	}
	});

	var seriesOptions = [],
		seriesCounter = 0,
		seriesValid = 0,
		chart_{graphid}_{id_host}_{graph_type} = null,
		names = [{series}];

	$.each(names, function(i, name) {

		/*
		$.getJSON('/graphc/getGraph?idgroups={groupid}&graphid={graphid}&id_host={id_host}&name='+ name_{graphid}_{id_host}_{graph_type}.toLowerCase(),	function(data) {
			alert("Mono");
			seriesOptions_{graphid}_{id_host}_{graph_type}[i] = {
				name: name_{graphid}_{id_host}_{graph_type},
				data: data
			};
		    
			// As we're loading the data asynchronously, we don't know what order it will arrive. So
			// we keep a counter and create the chart when all the data is loaded.
			seriesCounter_{graphid}_{id_host}_{graph_type}++;
			alert("HOLA"+names_{graphid}_{id_host}_{graph_type}.length);
			alert("CHAO"+seriesCounter_{graphid}_{id_host}_{graph_type});
			if (seriesCounter_{graphid}_{id_host}_{graph_type} == names_{graphid}_{id_host}_{graph_type}.length) {
				alert("Ok");
				//destroy_{graphid}_{id_host}_{graph_type}();
				createChart_{graphid}_{id_host}_{graph_type}();
			}
		});*/
							
		
		$.getJSON('/graphc/getGraph?idgroups={groupid}&graphid={graphid}&id_host={id_host}&limit={limit}&callback=?&name='+ name.toLowerCase(),	function(data) {

			seriesOptions[i] = {
				name: name,
				data: data
			};

            seriesOptions[i] = {
                name: item.description,
                data: data[item.id],
                marker : {
                    enabled : true,
                    radius : 3
                },
                shadow : true,
                tooltip : {
                    valueDecimals : 2
                },
                dataGrouping : {
                    units : [[
                        'week',
                        [1,2,3]
                    ], [
                        'month', 
                        [1, 3, 6, 12]
                    ],[
                        'day',
                        [1]
                    ],[
                        'hour',
                        [1]
                    ]]
                }
            };
                
			seriesCounter++;

			if (seriesCounter == names.length) {
				destroy();
				createChart();
			}
		});
	});

	function destroy() {
		chart_{graphid}_{id_host}_{graph_type} && chart_{graphid}_{id_host}_{graph_type}.destroy();
		chart_{graphid}_{id_host}_{graph_type} = null;
	}
	
	function createChart() {

		var chart_{graphid}_{id_host}_{graph_type} = new Highcharts.StockChart({
	    	chart: {
		         renderTo: 'container_graph_{graphid}_{id_host}_{graph_type}',
		         zoomType: 'x',
		         defaultSeriesType: 'line',
		         animation: false,
		         spacingBottom: 60,
		         events: {
		         	redraw: function(event) { 
		         		createLabel(chart_{graphid}_{id_host}_{graph_type});
		         	}
		         }
		    },
			rangeSelector: {
		        buttons: [{
		            type: 'day',
		            count: 1,
		            text: '1D'
		        }, {
		            type: 'week',
		            count: 1,
		            text: '1S'
		        }, {
		            type: 'month',
		            count: 1,
		            text: '1M'
		        }, {
		            type: 'month',
		            count: 3,
		            text: '3M'
		        }, {
		            type: 'year',
		            count: 1,
		            text: '1A'
		        }, {
		            type: 'all',
		            text: 'Todo'
		        }],
		        selected: 0
		    },
		    xAxis: {
		    	type: 'datetime',
		    	//tickInterval: 600000,
				labels: {
            		enabled: false
        		},
        		ShowEmpty: true
		    },
 
			yAxis: {
		        labels: {
		            formatter: function() { return bytes(this.value, true,"{unit}"); }
		        },
		        ShowEmpty: true,
		        min: -10,
                tickInterval: 1
		    },
		    legend: {
		    	enabled: true,
		        layout: 'vertical',
		        floating: true,
		        align: 'center',
		        verticalAlign: 'bottom',
		        x: -100,
		        borderWidth: 0,
		        width: 450,
		        labelFormatter: function() {
		            return this.name;
		        }
		    },
		    tooltip: {
		    	//pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({unit})<br/>',
				enabled: true,
				shared: true,
				valueDecimals: 0,
				formatter: function(i) {
						var s = '<b>'+ Highcharts.dateFormat('%A  %e de %B del %Y a las %H:%M:%S',this.x) +'</b>';
						$.each(this.points, function(i, point) {
							s += '<br/><span style="color:'+point.series.color+'">'+point.series.name +'</span>: <b>'+  bytes(point.y, true,"{unit}") + '</b>';
						});
						return s;
				}
		    },
		    
		    series: seriesOptions
		}, function(chart_{graphid}_{id_host}_{graph_type}){
            createLabel(chart_{graphid}_{id_host}_{graph_type});
            
            setTimeout(function() {
       			 $('input', $('#' + chart_{graphid}_{id_host}_{graph_type}.options.chart.renderTo)).datepicker( {
       			 	 dateFormat: 'yy-mm-dd'
       			 })
    		}, 0)
        });

		function createLabel(charts) {
			
			if (typeof charts == "undefined") {
				return false;
			}

			ChartSeries = charts.series;

			var textNew = '';

			$.each(ChartSeries, function(i, serieLine) {
				
				if( serieLine.name != 'Navigator') {

					var min = 0;
					var max = 0;
					var count = 0;
					var datos = new Array();
					var totalLine = 0;
					
					for (a = 0; a < serieLine.processedYData.length; a++) {
						
						if (!isNaN(parseFloat(serieLine.processedYData[a]))) {
							totalLine += serieLine.processedYData[a];
							datos[a] = serieLine.processedYData[a];
							count++;
		     			}
						
					}
					
					seriesAvg = (totalLine / count);
					
					max = Array.max(datos);
					min = Array.min(datos);
				
					textNew = serieLine.name+' Max: ' + bytes(max, true,"{unit}") + ' - Min:  ' + bytes(min, true,"{unit}") +  ' - Promedio: ' + bytes(seriesAvg, true,"{unit}");
					$('tspan:contains("'+serieLine.name+'")').text(textNew);
				
				}
				
			});
			
		};
	
	}
	
	Array.max = function( array ){
		return Math.max.apply( Math, array );
	};
    
	Array.min = function( array ){
		return Math.min.apply( Math, array );
	};
});
</script>

<div id="container_graph_{graphid}_{id_host}_{graph_type}" style="height: 470px; min-width: 600px">
	
</div>