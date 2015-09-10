<script language="JavaScript">

jQuery(function() {
	
	function bytes(bytes, label,type) {
		if (type !== 'bps') return bytes;
		if (bytes == 0) return '';
	    var s = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB'];
	    var e = Math.floor(Math.log(bytes)/Math.log(1024));
	    var value = ((bytes/Math.pow(1024, Math.floor(e))).toFixed(2));
	    e = (e<0) ? (-e) : e;
	    if (label) value += ' ' + s[e];
	    return value;
	}
	
	Highcharts.setOptions({
		lang: {
             months: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            // weekdays: ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'],
             weekdays: ['Domingo','Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
             rangeSelectorFrom: 'Inicio',
             rangeSelectorTo: 'Fin'
        },
        global: {
        	useUTC: false
    	}
	});

	var seriesOptions_{graphid}_{id_host}_{graph_type} = [],
		seriesCounter_{graphid}_{id_host}_{graph_type} = 0,
		seriesValid_{graphid}_{id_host}_{graph_type} = 0,
		names_{graphid}_{id_host}_{graph_type} = [{series}];


	$.each(names_{graphid}_{id_host}_{graph_type}, function(i, name_{graphid}_{id_host}_{graph_type}) {
		/*
		$.ajax({
				url: "/graphc/getGraph",
				type: "GET",
				data: {idgroups:{groupid}, graphid:{graphid}, id_host:{id_host} , name: name_{graphid}_{id_host}_{graph_type}.toLowerCase()},
				dataType: "json",
				error: function() {
					jQuery('#loading').hide();
	  				jQuery('#imgLoading').hide();
					return false;
				},
				success: function(data) {
					if(data.status) {
						$.ajax({
							url: "/graphc/getGraph_display",
							type: "GET",
							dataType: "jsonp",
							data: {graphid:{graphid}, id_host:{id_host}, name: name_{graphid}_{id_host}_{graph_type}.toLowerCase()},
							success: function(registros_{graphid}_{id_host}_{graph_type}) {
								
								seriesOptions_{graphid}_{id_host}_{graph_type}[seriesValid_{graphid}_{id_host}_{graph_type}] = {
									name: name_{graphid}_{id_host}_{graph_type},
									data: registros_{graphid}_{id_host}_{graph_type}
								};
								seriesValid_{graphid}_{id_host}_{graph_type}++;
								
							}
						});
						seriesCounter_{graphid}_{id_host}_{graph_type}++;
					}

					if (seriesCounter_{graphid}_{id_host}_{graph_type} > 0) {
						 setTimeout(function(){
						 	 jQuery('#loading').hide();
							 jQuery('#imgLoading').hide();
	                 		 createChart_{graphid}_{id_host}_{graph_type}();
	           			 }, 1000);
					}				
				
				}
		});
		*/
		alert("InicoEnvia");
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
		});
				
						
		/*
		$.getJSON('/graphc/getGraph?idgroups={groupid}&graphid={graphid}&id_host={id_host}&callback=?&name='+ name.toLowerCase(),	function(data) {

			seriesOptions[i] = {
				name: name,
				data: data
			};

			seriesCounter++;

			if (seriesCounter == names.length) {
				createChart();
			}
		});*/
	});

	function destroy_{graphid}_{id_host}_{graph_type}() {
		chart_{graphid}_{id_host}_{graph_type} && chart_{graphid}_{id_host}_{graph_type}.destroy();
		chart_{graphid}_{id_host}_{graph_type} = null;
	}
	
	function createChart_{graphid}_{id_host}_{graph_type}() {

		chart_{graphid}_{id_host}_{graph_type} = new Highcharts.StockChart({
	    	chart: {
		         renderTo: 'container_graph_{graphid}_{id_host}_{graph_type}',
		         zoomType: 'x'
		    },

			rangeSelector: {
		        buttons: [{
		            type: 'day',
		            count: 1,
		            text: '1d'
		        }, {
		            type: 'week',
		            count: 1,
		            text: '1w'
		        }, {
		            type: 'month',
		            count: 1,
		            text: '1m'
		        }, {
		            type: 'month',
		            count: 3,
		            text: '3m'
		        }, {
		            type: 'year',
		            count: 1,
		            text: '1y'
		        }, {
		            type: 'all',
		            text: 'All'
		        }],
		        selected: 0
		    },
		    xAxis: {
		    	type: 'datetime',
				labels: {
            		enabled: false
        		}
		    },
			yAxis: {
		        labels: {
		            formatter: function() { return bytes(this.value, true,"{unit}"); }
		        }
		    },
 			title : {
				text : '{title}'
			},
   
		    tooltip: {
		    	pointFormat: '<span style="color:{series.color}">{series.name}</span>: <b>{point.y}</b> ({unit})<br/>',
				enabled: true,
				shared: true,
				valueDecimals: 0
		    },
		    
		    series: seriesOptions_{graphid}_{id_host}_{graph_type}
		}, function(chart){
            setTimeout(function(){
                $('input.highcharts-range-selector').datepicker()
            },0)
        });

	} 
});
</script>
<div id="container_graph_{graphid}_{id_host}_{graph_type}" style="height: 300px; min-width: 500px">
	
</div>​