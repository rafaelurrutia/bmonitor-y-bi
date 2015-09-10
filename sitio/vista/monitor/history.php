<script language="JavaScript">
jQuery(function() {
	
	Highcharts.setOptions({
		lang: {
             months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            // weekdays: ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'],
             weekdays: ['Domingo','Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
             rangeSelectorFrom: 'Des de',
             rangeSelectorTo: 'Fin'
        },
        global: {
        	useUTC: false
    	}
	});

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
		if (type !== 'bps') return bytes;
		if (bytes == 0) return '';
	    var s = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB'];
	    var e = Math.floor(Math.log(bytes)/Math.log(1024));
	    var value = ((bytes/Math.pow(1024, Math.floor(e))).toFixed(2));
	    e = (e<0) ? (-e) : e;
	    if (isNaN(parseFloat(value))) {
			return bytes;
     	}
	    if (label) value += ' ' + s[e];
	    return value;
	}

	var seriesOptions_{idItem}_{host} = [],
		seriesCounter_{idItem}_{host} = 0,
		chart_{idItem}_{host} = null,
		names_{idItem}_{host} = ['{title}'];

	$.each(names_{idItem}_{host}, function(i, name_{idItem}_{host}) {
		$.getJSON('/history/getGraph?idItem={idItem}&host={host}&callback=?&name='+ name_{idItem}_{host}.toLowerCase(),	function(data) {

			seriesOptions_{idItem}_{host}[i] = {
				name: name_{idItem}_{host},
				data: data
			};
		    
			// As we're loading the data asynchronously, we don't know what order it will arrive. So
			// we keep a counter and create the chart when all the data is loaded.
			seriesCounter_{idItem}_{host}++;

			if (seriesCounter_{idItem}_{host} == names_{idItem}_{host}.length) {
				destroy_{idItem}_{host}();
				createChart_{idItem}_{host}();
			}
		});
	});

	function destroy_{idItem}_{host}() {
		chart_{idItem}_{host} && chart_{idItem}_{host}.destroy();
		chart_{idItem}_{host} = null;
	}
	
	function createChart_{idItem}_{host}() {

		var chart_{idItem}_{host} = new Highcharts.StockChart({
		    chart: {
		        renderTo: 'container_graph_{idItem}_{host}',
		        defaultSeriesType: 'line',
				animation: false,
		        zoomType: 'x',
		  		animation: false,
		        spacingBottom: 60,
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
		            formatter: function() { return numberFormat(this.value, true,"{unit}"); }
		        },
		        ShowEmpty: true
		    },
		    legend: {
		    	enabled: true,
		        layout: 'vertical',
		        backgroundColor: '#FFFFFF',
		        floating: true,
		        align: 'left',
		        y: 55,
		        width: 435,
		        labelFormatter: function() {
		            return this.name;
		        }
		    },
			navigator: {
           		top: 255
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
		    tooltip: {
		    	//pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({unit})<br/>',
				enabled: true,
				shared: true,
				valueDecimals: 0,
				formatter: function(i) {
						var s = '<b>'+ Highcharts.dateFormat('%A  %e de %B del %Y a las %H:%M:%S',this.x) +'</b>';
						$.each(this.points, function(i, point) {
							s += '<br/><span style="color:'+point.series.color+'">'+point.series.name +'</span>: <b>'+  numberFormat(point.y, true,"{unit}") + '</b>';
						});
						return s;
				}
		    },
		    title : {
				text : '{title}'
			},  
		    series: seriesOptions_{idItem}_{host}
		});
        
        
           // Set the datepicker's date format
	}


/*
	$.getJSON('http://bmonitor.baking.cl:3362/history/getGraph?idItem={idItem}&host={host}&callback=?', function(data) {
		// Create the chart
		chart = new Highcharts.StockChart({
			chart : {
				renderTo : 'container_graph'
			},

			rangeSelector : {
				selected : 1
			},
			xAxis: {
				categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
				'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
			},
			title : {
				text : '{title}'
			},
			
			series : [{
				name : "Hola",
				data : data,
				tooltip: {
					valueDecimals: 2
				}
			}]
		});
	});
*/
	  
});
</script>
<div id="container_graph_{idItem}_{host}" style="height: 370px; min-width: 600px"></div>