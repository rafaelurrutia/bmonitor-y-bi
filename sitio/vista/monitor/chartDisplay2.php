<script type="text/javascript">

function sendComment(clock,idHost) {
	
	$('#loading').show();
	$('#imgLoading').show();
	
	var comment = $( "#comments"+clock ).val();
	
	var request = $.ajax({
		type : "POST",
		url : "/graphc/setCommentPoint",
		dataType : "json",
		data: { 
				idItem: "{idItem}", 
				idGroup: "{idGroup}",
				idHost: idHost,
				clock: clock,
				comment: comment
		}
	});

	request.fail(function( jqXHR, textStatus ) {
		
	});

	request.always(function( ) {
		$('#loading').hide();
		$('#imgLoading').hide();
	});
	
	request.done(function( data ) {
		
		
	})
}

function deletePoint(clock,idHost) {
	
	$('#loading').show();
	$('#imgLoading').show();
	
	var request = $.ajax({
		type : "POST",
		url : "/graphc/deletePoint",
		dataType : "json",
		data: { 
				idItem: "{idItem}", 
				idGroup: "{idGroup}",
				idHost: idHost,
				clock: clock
		}
	});

	request.fail(function( jqXHR, textStatus ) {
		
	});

	request.always(function( ) {
		$('#loading').hide();
		$('#imgLoading').hide();
	});
	
	request.done(function( data ) {
		
		
	})
}
	
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
		
	function bytes(bytes, label,type) {
		if (type !== 'bps') {
			if(type == 'ms'|| type == 'sec' || type == 'seg' ) {
				return Highcharts.numberFormat(bytes, 2, ',','.') +' '+ type;
			}  else {
				return Highcharts.numberFormat(bytes, 0, ',','.') +' '+ type;
			}	
		} 
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
        global: {
        	useUTC: false
    	}
	});

	var seriesOptions = [],
		seriesCounter = 0,
		seriesValid = 0,
		chartContainer = null;

	destroy();

	$('#loading').show();
	$('#imgLoading').show();

	//aqui el grafico simple trae la data desde /apps/controlador/graphc.php funcion getChartFilterData()...
	var request = $.ajax({
		type : "POST",
		url : "/graphc/getChartFilterData?dataGrouping={dataGroupingActive}&idFilter={idFilter}&idPlan={idPlan}&idItem={idItem}&idGroup={idGroup}&idHost={idHost}{limit}{separation}&callback=?",
		dataType : "jsonp"
	});

	request.fail(function( jqXHR, textStatus ) {
		
	});

	request.always(function( ) {
		$('#loading').hide();
		$('#imgLoading').hide();
	});
									    					
	request.done(function( data ) {
		console.log(data);
		//var chart_{container} = $('#container_graph_{container}').highcharts('StockChart', {   
		chartContainer = new Highcharts.StockChart({
	    	chart: {
	    	     renderTo: 'containerGraph',
		         zoomType: 'x',
		         defaultSeriesType: 'line',
		         animation: true,
		         spacingBottom: 50,
		         events: {
		         	redraw: function(event) {
		         		createLabel(chartContainer);
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
				inputEnabled: $('#containerGraph').width() > 480,
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
                   	}
                }
            },
		    xAxis: {
		    	type: 'datetime',
		    	//tickInterval: 600000,
				labels: {
            		enabled: true
        		},
        		ShowEmpty: true
		    },
			yAxis: {
		        labels: {
		            formatter: function() { return numberFormat(this.value, true,"{unit}"); }
		        },
                min: 0        
		    },
        	plotOptions: {
                series: {
                    cursor: 'pointer',
                    point: {
                        events: {
                            click: function (e) {
                            	if(!{dataGroupingActive}) {
									jQuery('#loading').show();
									jQuery('#imgLoading').show();
									
									var clock = this.x/1000;
									
									$( "#comments"+clock).remove();
									$( "#sendComment"+clock ).remove();
									$( "#delete"+clock ).remove();
									
									var request = $.ajax({
										type : "POST",
										url : "/graphc/getPoint",
										data: { 
												x: this.x, 
												y: this.y,
												idItem: "{idItem}",
												idHost: "{idHost}",
												name: this.series.name
										},
										dataType : "json"
									});
	
									request.done(function( msg ) {
										if(msg.status){
											hs.htmlExpand(null, {
			                                    align: 'right',
			                                    headingText: msg.name,
			                                    pageOrigin: {
													x: 100,
													y: 100
												},
			                                    maincontentText: msg.data,
			                                    width: 500
			                                });
										}
									});
														
									request.always(function() {
										$('#loading').hide();
										$('#imgLoading').hide();
									});
								}
                            }
                        }
                    },
                    marker: {
                        lineWidth: 1
                    }
                }
            },		    
		    credits: {
                enabled: false
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
				valueDecimals: 0,
				formatter: function(i) {
						//var s = '<b>'+ Highcharts.dateFormat('%A  %e de %B del %Y a las %H:%M:%S',this.x) +'</b>';
						
						var s = '<b>'+ Highcharts.dateFormat('%A, %B %e, %Y at %H:%M:%S',this.x) +'</b>';
						
						$.each(this.points, function(i, point) {                           
							s += '<br/><span style="color:'+point.series.color+'">'+point.series.name +'</span>: <b>'+  numberFormat(point.y, true,"{unit}") + '</b>';
						});
						return s;
				}
		    },
		    
		    series: data
		}, function(chartContainer){
            createLabel(chartContainer);
            
            setTimeout(function() {
       			 $('input', $('#' + chartContainer.options.chart.renderTo)).datepicker( {
       			 	 dateFormat: 'yy-mm-dd'
       			 })
    		}, 0)
        });      
	});

	function destroy() {
		chartContainer && chartContainer.destroy();
		chartContainer = null;
	}
	
    g_dataGrouping = {
            {dataGrouping}
    };
    
	function createLabel(charts) {
		//charts = chart_{container};

		if (typeof charts == "undefined") {
			return false;
		}
		
		try {
			ChartSeries = charts.series;

			var textNew = '';
			$( "#containerGraphDetail" ).html('<thead><tr><th class="ui-state-default">Detalle</th><th class="ui-state-default">Max</th><th class="ui-state-default">Min</th><th class="ui-state-default">Average</th></tr></thead>	');
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
						var unit = '';
					} else {
						var unit = unitSet;
					}
					
                   	textNew = '<tr><td class="ui-widget-content">'+serieLine.name+'</td><td class="ui-widget-content"> ' + numberFormat(max, true,unit) + '</td><td class="ui-widget-content">  ' + numberFormat(min, true,unit) +  '</td><td class="ui-widget-content"> ' + numberFormat(seriesAvg, true,unit)+ "</td></tr>";
					$( "#containerGraphDetail" ).append( textNew );
				}
				
			});
		
		} catch(err) {
			console.log("Error average");
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
<div id="containerGraph" style="height: 500px; min-width: 310px"></div>
<div class="ui-grid ui-widget">
<table style="margin: auto" id="containerGraphDetail" class="ui-grid-content ui-widget-content">
	
</table>
</div>