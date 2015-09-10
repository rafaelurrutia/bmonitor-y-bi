<!DOCTYPE html>
<html>
	<head>
		<title>BakingSoftware</title>
		<script type="text/javascript" src="http://code.jquery.com/jquery-1.11.2.min.js"></script>
		<script type="text/javascript" src="http://code.highcharts.com/highcharts.js"></script>
		<script type="text/javascript">
			$(document).ready(function(){
				var changeTime = "{url_from}";
				
				if(changeTime == "Last 36 Hours"){
					$("#changeTime").text("Last 7 Days");
				}else{
					$("#changeTime").text("Last 36 Hours");
				}
				
				$("#grafico").highcharts({
					title: {
						text: "{title}"
					},
					subtitle: {
						text: "{subtitle}"	
					},
					chart: {
						type: "spline"
					},
					credits: {
						enabled: false
					},
					xAxis: {
						type: "datetime",
						categories: [],
						labels: {
							rotation: -90
						}
					},
					yAxis: {
						title: {
							text: "Unidad: {unit}"
						}
					},
					tooltip: {
						valueSuffix: "{unit}"
					},
					plotOptions: {
						series: {
							lineWidth: 1
						}
					},
					series: {data}
				});
			});
			
			function cambiar(tiempo){
				if(tiempo == "Last 36 Hours"){
					window.location.href = "/api/biGraph/{url_item}/{url_plan}/{url_sonda}/Last 7 Days";	
				}else{
					window.location.href = "/api/biGraph/{url_item}/{url_plan}/{url_sonda}/Last 36 Hours";
				}
			}
		</script>
	</head>
	<body>
		<div id="grafico" style="min-width: 400px; min-height: 390px;"></div>
		<div style="min-width: 400px;"><span style="float: right; color: #000000; font-size: 12px; font-family: Arial, Helvetica, sans-serif; margin-right:15px;"><a id="changeTime" href="#" onclick="cambiar('{url_from}')">Last 7 days</a></span></div>
	</body>
</html>