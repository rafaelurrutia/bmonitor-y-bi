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
	    		return "No Value";
	    	} else {
	    		return bytes;
	    	}
     	}
	    if (label) value += ' ' + s[e];
	    return value;
	}

    Highcharts.setOptions({
        global: {
            useUTC: false
        }
    });
    
	var seriesOptions = [],
		seriesCounter = 0,
		seriesValid = 0,
		chart_{idGraph}_{idHost}_{graph_type} = null;

	destroy();	
	
	//aqui el grafico Agrupado trae la data desde /apps/controlador/graphc.php funcion getChartData()...  
	$.getJSON('/graphc/getChartData?idGraph={idGraph}&idGroup={idGroup}&idHost={idHost}{limit}{separation}&callback=?',	function(data) {
		seriesOptions = data;
		createChart();
	});
	function destroy() {
		chart_{idGraph}_{idHost}_{graph_type} && chart_{idGraph}_{idHost}_{graph_type}.destroy();
		chart_{idGraph}_{idHost}_{graph_type} = null;
	}
	
	function createChart() {

		var chart_{idGraph}_{idHost}_{graph_type} = new Highcharts.StockChart({
	    	chart: {
		         renderTo: 'container_graph_{idGraph}_{idHost}_{graph_type}',
		         zoomType: 'x',
		         defaultSeriesType: 'line',
		         animation: true,
		         events: {
		         	redraw: function(event) { 
		         		createLabel(chart_{idGraph}_{idHost}_{graph_type});
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
                    text: '1W'
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
                    text: '1Y'
                }, {
                    type: 'all',
                    text: 'All'
                }],
                selected: 0
            },
            navigator: {
                enabled: true,
                series: {
                   dataGrouping: {
                      {dataGrouping}
                   }
                }
            },
		    xAxis: {
		    	type: 'datetime'
		    },
			yAxis: {
		        labels: {
		            formatter: function() { return bytes(this.value, true,"{unit}"); }
		        }     
		    },
		    legend: {
		    	enabled: true,
		        layout: 'vertical',
		        backgroundColor: '#FFFFFF',
		        verticalAlign: 'bottom',
		        align: 'center',
		        borderWidth: 0,
		        width: 600,
		        itemWidth: 300,
		        labelFormatter: function() {
		            return this.name;
		        }
		    },
		    tooltip: {
		    	//pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({unit})<br/>',
				enabled: true,
				shared: true,
				valueDecimals: 2,
				formatter: function(i) {
						//var s = '<b>'+ Highcharts.dateFormat('%A  %e de %B del %Y a las %H:%M:%S',this.x) +'</b>';
						var s = '<b>'+ Highcharts.dateFormat('%A, %B %e, %Y at %H:%M:%S',this.x) +'</b>';
						$.each(this.points, function(i, point) {
							s += '<br/><span style="color:'+point.series.color+'">'+point.series.name +'</span>: <b>'+  bytes(point.y, true,"{unit}") + '</b>';
						});
						return s;
				}
		    },
		    
		    series: seriesOptions
		}, function(chart_{idGraph}_{idHost}_{graph_type}){
            createLabel(chart_{idGraph}_{idHost}_{graph_type});
            
            setTimeout(function() {
       			 $('input', $('#' + chart_{idGraph}_{idHost}_{graph_type}.options.chart.renderTo)).datepicker( {
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
							datos[count] = serieLine.processedYData[a];
						
							count++;
		     			}
						
					}
					
					seriesAvg = (totalLine / count);
					
					max = Array.max(datos);
					min = Array.min(datos);
				
					textNew = serieLine.name+' Max: ' + bytes(max, true,"{unit}") + ' - Min:  ' + bytes(min, true,"{unit}") +  ' - Average: ' + bytes(seriesAvg, true,"{unit}");
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

<div id="container_graph_{idGraph}_{idHost}_{graph_type}" style="height: 470px; min-width: 600px">
	
</div>