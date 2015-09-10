<!DOCTYPE html>
<html class="loading">

	<head>
		<title>Techwizard</title>
		<script src="http://code.jquery.com/jquery-latest.min.js"></script>

		<script src="http://code.highcharts.com/highcharts.js"></script>
		<script src="http://code.highcharts.com/highcharts-more.js"></script>
		<script src="http://code.highcharts.com/maps/modules/map.js"></script>
		<script src="http://code.highcharts.com/maps/modules/data.js"></script>
		<script src="http://code.highcharts.com/mapdata/custom/world.js"></script>
		<script src="http://code.highcharts.com/mapdata/countries/us/us-all.js"></script>

		<style>
			#container {
				height: 500px;
				min-width: 310px;
				max-width: 800px;
				margin: 0 auto;
			}
			.loading {
				margin-top: 10em;
				text-align: center;
				color: gray;
			}
		</style>
		<script>
			var lineChart;
			var mapChart;

			var lineData = [{
				name : 'Tokyo',
				data : [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6]
			}, {
				name : 'New York',
				data : [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]
			}, {
				name : 'Berlin',
				data : [-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0]
			}, {
				name : 'London',
				data : [3.9, 4.2, 5.7, 8.5, 11.9, 15.2, 17.0, 16.6, 14.2, 10.3, 6.6, 4.8]
			}];

			var lineData1 = [{
				name : 'Sonda 1',
				data : [9, 6.9, 9, 14, 1, 21, 25.2, 26, 23, 13, 9, 9.6]
			}, {
				name : 'Sonda 2',
				data : [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 8.6, 2.5]
			}, {
				name : 'Sonda 3',
				data : [-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0]
			}];

		</script>

		<script>
			$(function() {

				lineChart = new Highcharts.Chart({

					chart : {
						type : 'line',
						renderTo : 'container'
					},

					title : {
						text : 'Monthly Average Temperature',
						x : -20 //center
					},
					subtitle : {
						text : 'Source: WorldClimate.com',
						x : -20
					},
					xAxis : {
						categories : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
					},
					yAxis : {
						title : {
							text : 'Temperature (°C)'
						},
						plotLines : [{
							value : 0,
							width : 1,
							color : '#808080'
						}]
					},
					tooltip : {
						valueSuffix : '°C'
					},
					legend : {
						layout : 'vertical',
						align : 'right',
						verticalAlign : 'middle',
						borderWidth : 0
					},
					series : lineData
				});

				$.getJSON('http://www.highcharts.com/samples/data/jsonp.php?filename=us-population-density.json&callback=?', function(data) {

					// Make codes uppercase to match the map data
					$.each(data, function() {
						this.code = this.code.toUpperCase();
					});

					// Instanciate the map
					//		$('#container2').highcharts('Map', {

					mapChart = new Highcharts.Map({

						chart : {
							renderTo : 'container2',
							borderWidth : 1
						},

						title : {
							text : 'US population density (/km²)'
						},

						legend : {
							layout : 'horizontal',
							borderWidth : 0,
							backgroundColor : 'rgba(255,255,255,0.85)',
							floating : true,
							verticalAlign : 'top',
							y : 25
						},

						mapNavigation : {
							enabled : true
						},

						colorAxis : {
							min : 1,
							type : 'logarithmic',
							minColor : '#EEEEFF',
							maxColor : '#000022',
							stops : [[0, '#EFEFFF'], [0.67, '#4444FF'], [1, '#000022']]
						},

						series : [{
							animation : {
								duration : 1000
							},
							data : data,
							mapData : Highcharts.maps['countries/us/us-all'],
							joinBy : ['postal-code', 'code'],
							dataLabels : {
								enabled : true,
								color : 'white',
								format : '{point.code}'
							},
							name : 'Population density',
							tooltip : {
								pointFormat : '{point.code}: {point.value}/km²'
							}
						}]
					});

				});

				$("button[id='reload']").on("click", function() {
					var lineChart1 = $('#container').highcharts();

					lineChart1.setTitle({
						text : "hola"
					});
					lineChart1.series[0].setData(lineData1);
//					lineChart1.legend.allItems[0].update({name:'aaa'});
					lineChart1.legend.allItems[0].update({name:'aaa'});
					lineChart1.legend.allItems[1].update({name:'aa'});
					lineChart1.legend.allItems[2].update({name:'a'});
				});

				$("button[id='readFile']").on("click", function() {
				//	readTextFile("http://192.168.100.8/hostinfo/000C292A3CEA.txt");
					readTextFile("hostinfo/000C292A3CEA.txt");
				});

				function readTextFile(file) {
					var rawFile = new XMLHttpRequest();
					rawFile.open("GET", file, false);
					rawFile.onreadystatechange = function() {
						if (rawFile.readyState === 4) {
							if (rawFile.status === 200 || rawFile.status == 0) {
								var allText = rawFile.responseText;
								alert(allText);
							}
						}
					}
					rawFile.send(null);
				}

			});

		</script>

	</head>

	<body>

		<div id="container" style="height: 400px; max-width: 400px; margin: 0 auto"></div>
		<div id="container2" style="height: 400px; max-width: 400px; margin: 0 auto"></div>
		<button id="reload">
			click me
		</button>
		<button id="readFile">
			read txt
		</button>
	</body>
</html>
