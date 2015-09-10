<script type="text/javascript">

jQuery(function() {

	var unitSet = "{unit}";

	$.array = function( array,method,value ) {
		
		if ( method == 'put' ) {
			return array.push(value);
		} else if ( method == 'delete' ) {

			var pos = array.indexOf(value);

			if ( pos > -1 ) {
				array.splice(pos,1);
				return array;
			} else {
				return false;
			}

		} else if ( method == 'get' ) {
			var pos = array.indexOf(value);

			if ( pos > -1 ) {
				return true;
			} else {
				return false;
			}

		} else {
			return false;
		}
	};

	function numberFormat(number, label,type) {
		
		type = typeof type !== 'undefined' ? type : false;
		
		var format = ['bytes','bits','byte','bit','Bps','bps',false];
		
		var valid = $.array(format,'get',type);
		
		if(!valid){
			if(type == 'ms'|| type == 'sec' || type == 'seg' ) {
				return Highcharts.numberFormat(number, 2, ',','.') +' '+ type;
			}  else {
				if(number < 100){
					return Highcharts.numberFormat(number, 2, ',','.') +' '+ type;
				} else {
					return Highcharts.numberFormat(number, 0, ',','.') +' '+ type;
				}
				
			}
		}
		
		if (number == 0) return 0;
		
		if(type == 'bytes' || type == 'byte') {
			var s = ['B', 'kB', 'MB', 'GB', 'TB', 'PB'];
		} else if (type.toLowerCase() == 'bps') {
			var s = ['bps', 'kbps', 'Mbps', 'Gbps', 'Tbps', 'Pbps'];
		} else if (type == 'bits' || type == 'bit') {
			var s = ['bps', 'kb', 'Mb', 'Gb', 'Tb', 'Pb'];
		}
		
		var e = Math.floor(Math.log(number)/Math.log(1000));
	    var value = ((number/Math.pow(1000, Math.floor(e))).toFixed(2));
	    e = (e<0) ? (-e) : e;
	    if (isNaN(parseFloat(value))) {
	    	if(isNaN(parseFloat(number))) {
	    		return "No Value";
	    	} else {
	    		return number;
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
		chart_{container} = null;

	destroy();
	
	$('#loading').show();
	$('#imgLoading').show();
	
	var request = $.ajax({
		type : "POST",
		url : "/graphc/getChartData?idGraph={idGraph}&idGroup={idGroup}&idHost={idHost}{limit}{separation}&callback=?",
		dataType : "jsonp"
	});

	request.fail(function( jqXHR, textStatus ) {
		
	});

	request.always(function( ) {
		$('#loading').hide();
		$('#imgLoading').hide();
	});
		
	request.done(function( data ) {
		var chart_{container} = new Highcharts.StockChart({
	    	chart: {
		         renderTo: 'container_graph_{container}',
		         zoomType: 'x',
		         defaultSeriesType: 'line',
		         animation: true,
		         events: {
		         	redraw: function(event) { 
		         		createLabel(chart_{container});
		         	}
		         }
		    },
		    scrollbar: {
				barBackgroundColor: 'gray',
				barBorderRadius: 7,
				barBorderWidth: 0,
				buttonBackgroundColor: 'gray',
				buttonBorderWidth: 0,
				buttonBorderRadius: 7,
				trackBackgroundColor: 'none',
				trackBorderWidth: 1,
				trackBorderRadius: 8,
				trackBorderColor: '#CCC'
		    },
            rangeSelector: {
            	inputEnabled: $('#container_graph_{container}').width() > 480,
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
                selected: 1
            },
            plotOptions: {
                enabled: true,
                series: {
                    dataGrouping: {
                      {dataGrouping}
                   	},
                   	cursor: 'pointer',
                    point: {
                        events: {
                            click: function (e) {
                                hs.htmlExpand(null, {
                                    pageOrigin: {
                                        x: e.pageX,
                                        y: e.pageY
                                    },
                                    headingText: this.series.name,
                                    maincontentText: "Comment Point Graph",
                                    width: 200
                                });
                            }
                        }
                    },
                    marker: {
                        lineWidth: 1
                    }
                }
            },
		    xAxis: {
		    	type: 'datetime'
		    },
			yAxis: {
		        labels: {
		            formatter: function() { 
		                return numberFormat(this.value, true,"{unit}"); 
		            }
		        },
		        min: 0  
		    },
		    legend: {
		    	enabled: true,
				labelFormatter: function() {
		           return this.name;
		        }
		    },
		    title: {
				text: '{title}'
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
							s += '<br/><span style="color:'+point.series.color+'">'+point.series.name+'</span>: <b>'+  numberFormat(point.y, true,"{unit}") + '</b>';
						});
						return s;
				}
		    },
		    
		    series: data
		}, function(chart_{container}){
            createLabel(chart_{container});
            
            setTimeout(function() {
       			 $('input', $('#' + chart_{container}.options.chart.renderTo)).datepicker( {
       			 	 dateFormat: 'yy-mm-dd'
       			 })
    		}, 0)
        });		
	});	
				
	function destroy() {
		chart_{container} && chart_{container}.destroy();
		chart_{container} = null;
	}
	
	function createLabel(charts) {
		
		if (typeof charts == "undefined") {
			return false;
		}
		try {
			ChartSeries = charts.series;
	
			var textNew = '';
			$( "#containerGraphGroupsDetail" ).html('<thead><tr><th class="ui-state-default">Detalle</th><th class="ui-state-default">Max</th><th class="ui-state-default">Min</th><th class="ui-state-default">Average</th></tr></thead>');
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

					if (typeof unitSet == "undefined") {
						var unit = '{unit}';
					} else {
						var unit = unitSet;
					}

					textNew = '<tr><td class="ui-widget-content">'+serieLine.name+'</td><td class="ui-widget-content">' + numberFormat(max, true,unit) + '</td><td class="ui-widget-content">' + numberFormat(min, true,unit) +  '</td><td class="ui-widget-content">' + numberFormat(seriesAvg, true,unit)+ "</td></tr>";
					$( "#containerGraphGroupsDetail" ).append( textNew );
				}
				
			});
		
		}catch(err) {
			console.log(err);
		}
		
	};
	
	
	
	Array.max = function( array ){
		return Math.max.apply( Math, array );
	};
    
	Array.min = function( array ){
		return Math.min.apply( Math, array );
	};
});
</script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
<div id="container_graph_{container}" style="height: 470px; min-width: 600px">
	
</div>
<div class="ui-grid ui-widget">
<table style="margin: auto" id="containerGraphGroupsDetail" class="ui-grid-content ui-widget-content">
	
</table>
</div>