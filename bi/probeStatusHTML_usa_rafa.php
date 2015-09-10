<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category Home
 * @package  Index
 * @author   Rodrigo Montes <rodrigo@bsw.cl>
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: $Id: index.phpl,v 1.9 2008-10-09 15:16:47 cweiske Exp $
 * @link     http://mnla.com
 */

 require_once 'api.php';
 ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="la" lang="la">
    <head>
    	<script>
    		var statusTextGood = "Good";
			var statusTextRegular = "Regular";
			var statusTextBad = "Bad";
				
			var statusPercentageGood = 100;
			var statusPercentageBad = 80;
			
			var statusColorGood = "#8BC34A";
			var statusColorRegular = "#FFA726";
			var statusColorBad = "#F44336";
					
			var letrasMes = new Array();
				letrasMes = ["january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"];	
							
			var mapChart;
			var optionsEstados;
			
			var graficoDisponibilidadPorEstados;
			var graficoDisponibilidadPorEstadosTitulo="% Global availability agents of the past 7 days by standard time zones";
			
			var graficoDisponibilidadWashingtonTitulo="% First 7 global availability agents of the past 7 days in Washington";
			var graficoDisponibilidadCaliforniaTitulo="% First 7 global availability agents of the past 7 days in California";
			var graficoDisponibilidadOregonTitulo="% First 7 global availability agents of the past 7 days in Oregon";
			var graficoDisponibilidadNewYorkTitulo="% First 7 global availability agents of the past 7 days in New York";
			
    	</script>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" type="image/x-icon" href="favicon.ico" />
        <title><?php echo printName(); ?></title>
        <link rel="stylesheet" type="text/css" href="theme/css/bootstrap.org.min.css"/>
        <link rel="stylesheet" type="text/css" href="theme/css/bootstrap-baking.css"/>
        <link rel="stylesheet" type="text/css" href="theme/css/font-awesome.min.css">
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <script src="theme/js/jquery-1.11.1.min.js"></script>
        <script src="theme/js/bootstrap.min.js"></script>
        <script src="http://code.highcharts.com/highcharts.js"></script>
		<script src="http://code.highcharts.com/highcharts-more.js"></script>
		<script src="http://code.highcharts.com/maps/modules/map.js"></script>
		<script src="http://code.highcharts.com/maps/modules/data.js"></script>
		<script src="http://code.highcharts.com/mapdata/custom/world.js"></script>
		<script src="http://code.highcharts.com/mapdata/countries/us/us-all.js"></script> 
		
		<script src="RU_sections/mapa/mapaData.js"></script>
		<script src="RU_sections/mapa/mapa.js"></script>
		
		<script src="RU_sections/graficoDisponibilidadPorEstados/graficoDisponibilidadPorEstadosData.js"></script>
		<script src="RU_sections/graficoDisponibilidadPorEstados/graficoDisponibilidadPorEstados.js"></script>
		
		<script src="RU_sections/graficoDisponibilidadWashington/graficoDisponibilidadWashingtonData.js"></script>
		<script src="RU_sections/graficoDisponibilidadWashington/graficoDisponibilidadWashington.js"></script>
	
		<script src="RU_sections/graficoDisponibilidadCalifornia/graficoDisponibilidadCaliforniaData.js"></script>
		<script src="RU_sections/graficoDisponibilidadCalifornia/graficoDisponibilidadCalifornia.js"></script>
		
		<script src="RU_sections/graficoDisponibilidadOregon/graficoDisponibilidadOregonData.js"></script>
		<script src="RU_sections/graficoDisponibilidadOregon/graficoDisponibilidadOregon.js"></script>	
		
		<script src="RU_sections/graficoDisponibilidadNewYork/graficoDisponibilidadNewYorkData.js"></script>
		<script src="RU_sections/graficoDisponibilidadNewYork/graficoDisponibilidadNewYork.js"></script>

		<script src="RU_sections/tablaResumenSondasPorEstados/tablaResumenSondasPorEstados.js"></script>
		<!--
		<script src="RU_sections/graficoBajadaSubidaLatencia/graficoBajadaSubidaLatenciaData.js"></script>
		<script src="RU_sections/graficoBajadaSubidaLatencia/graficoBajadaSubidaLatencia.js"></script>
		-->
		<link rel="stylesheet" type="text/css" href="RU_sections/informacionGlobal/informacionGlobal.css"/>
		<link rel="stylesheet" type="text/css" href="RU_sections/titulosMapaGrafico/titulosMapaGrafico.css"/>
		
		
		<link rel="stylesheet" type="text/css" href="RU_sections/mapa/mapa.css"/>
		<link rel="stylesheet" type="text/css" href="RU_sections/tablaResumenSondasPorEstados/tablaResumenSondasPorEstados.css"/>
		
		<link rel="stylesheet" type="text/css" href="RU_sections/tablaResumenSondasWashington/tablaResumenSondasWashington.css"/>
		<link rel="stylesheet" type="text/css" href="RU_sections/tablaResumenSondasCalifornia/tablaResumenSondasCalifornia.css"/>
		<link rel="stylesheet" type="text/css" href="RU_sections/tablaResumenSondasOregon/tablaResumenSondasOregon.css"/>
		<link rel="stylesheet" type="text/css" href="RU_sections/tablaResumenSondasNewYork/tablaResumenSondasNewYork.css"/>
		
		<link rel="stylesheet" type="text/css" href="RU_sections/graficoDisponibilidadPorEstados/graficoDisponibilidadPorEstados.css"/>
		
		<style>
			

.loading {
    margin-top: 10em;
    text-align: center;
    color: gray;
}

.textoSonda{
	margin-top: 10px;
	padding: 4 4 4 4px;
	border-style: solid;
	border-color: #2196F3;
	border-width: 1px;
	background-color: #FFFFFF;
	width:98%;
	margin-left:15px;
}

#lineByAgent {
	width: 98%;
	height: 500px;
	border-style: solid;
	border-color: #2196F3;
	border-width: 1px;
	background-color: #FFFFFF;
	margin-left:15px;
}

		</style>
      
        <script>
       
			$(function() {
				
				$("#DivButtonBack").hide();
				
			    try {
				Highcharts.setOptions({
					global : {
						useUTC : false
					}
				});
			}  catch(err) {
			    
			}
			
				
			$("#buttonBack").click(function() {
				
 				$("#tablaResumenSondasWashington").hide();
 				$("#tablaResumenSondasCalifornia").hide();
 				$("#tablaResumenSondasOregon").hide();
 				$("#tablaResumenSondasNewYork").hide();
 				
 				$("#tablaResumenSondasPorEstados").show();
 	
 				var grafico = new Highcharts.Chart(optionsEstados);
 				$("#tituloGrafico").html(graficoDisponibilidadPorEstadosTitulo);
 				
 				$("#DivButtonBack").hide();
 				
 				$("#detailAgent").hide();
 				
 				window.top.location.href = "#"; 
 				
 				$("#tituloGrafico").css("background-color","#2196F3");
 						
			});

		    $(".click tr").click(function() {
		    	
		    	
					$("#detailAgent").show();
					
					$container = $('#lineByAgent'); 
		            $chartAgent = $('#lineByAgent').highcharts(); 
	                $chartAgent.setSize($container.width(), $chartAgent.chartHeight, doAnimation = true);	
	                
	              
	                
	              
			    });
			    
			    $(".click tr").mouseover(function() {
					$('body').css('cursor', 'pointer'); 
			    });
			    
			    $(".click tr").mouseleave(function() {
					$('body').css('cursor', 'default'); 
			    });
			
			var letrasMes = new Array();
		
			letrasMes = ["january", "february", "march", "april", "may", "june", "july", "august", "september", "october", "november", "december"];	
		
			var fechaLine1 = new Array();
			var fechaActual = new Date();
			for ( i = 30; i >= 1; i--) {
				var dia = new Date(fechaActual.getTime() - ((24 * 60 * 60 * 1000) * i));
				fechaLine1.push(dia.getDate() + " of " + letrasMes[dia.getMonth()]);
			}
			
			var dataSonda = new Array();
			var dataSonda=[41, 53, 55, 58, 60, 55, 55, 55, 55, 52, 55, 57, 58, 60, 60, 60, 60, 62, 62, 62, 45, 45, 45, 46, 48, 60, 60, 66, 63, 65];

			$('#lineByAgent').highcharts({
		        title: {
		            text: 'Download, upload and latency graphic last 30 days',
		            x: -20 //center
		        },
		        credits : {
					enabled : false
				},
				xAxis : {
					categories : fechaLine1
				},
				yAxis : {
					title : {
						text : 'kbps'
					},
					plotLines : [{
						value : 0,
						width : 1,
						color : '#808080'
					}]
				},
		        tooltip: {
		            valueSuffix: '°C'
		        },
		        legend: {
		            layout: 'vertical',
		            align: 'right',
		            verticalAlign: 'middle',
		            borderWidth: 0
		        },
		        series: [{
		            name: 'Download',
		            data: [7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6,7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6, 25.2, 26.5, 23.3, 18.3, 13.9, 9.6],
		            lineWidth : 0.5,
					color : statusColorBad
		        }, {
		            name: 'Upload',
		            data: [-0.2, 0.8, 5.7, 11.3, 17.0, 22.0, 24.8, 24.1, 20.1, 14.1, 18.6, 2.5, 25.2, 26.5, 28.3, 36.3, 34.9, 7.6, 15.2, 26.5, 13.3, 18.3, 16.9, 19.6, 15.2, 16.5, 32.3, 38.3, 23.9, 9.6],
		            lineWidth : 0.5,
					color : statusColorGood
		        }, {
		            name: 'Latency',
		            data: [-0.9, 0.6, 3.5, 8.4, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0, 3.9, 1.0, 18.3, 13.9, 9.6,7.0, 6.9, 9.5, 14.5, 18.2, 21.5, 13.5, 17.0, 18.6, 17.9, 14.3, 9.0],
		            lineWidth : 0.5,
					color : statusColorRegular
		        }]
		    });
			
			});

        </script>
        
 
    </head>
    <body>
        <div id="loading"></div>
        <div id="imgLoading">
            <img widht="32" height="32" src="" alt="loading" title="loading" />        
        </div>

 <?php
 require_once "HTMLNavigationBar.php";
?>
<div id="wrapper">
    <div id="sidebar-wrapper" class="col-md-2">
        <div class="menu" id="sidebar">
            <?php
            require_once "HTMLSidebar.php";
            ?>
        </div>
    </div>
    <div id="main-wrapper" class="col-md-10 pull-right">
		<a href="#one"></a>
		<div class="row">
          
			<?php include 'RU_sections/informacionGlobal/informacionGlobal.php'; ?>
		
		</div> 
		<div class="row">

       	<?php 
    		include 'RU_sections/titulosMapaGrafico/titulosMapaGrafico.php'; 			
    	?>
            
            </div> 
		<div class="row">
                <div id="mapa" class="col-md-6" align="center">
                	
                </div>
               
                <div id="graficoDisponibilidadPorEstados" class="col-md-6" align="center">
                	
                </div>
            
            </div> 
            <div class="row">
               
                <div class="col-md-12">
                	<?php 
                	include 'RU_sections/tablaResumenSondasPorEstados/tablaResumenSondasPorEstados.php'; 
                	
					include 'RU_sections/tablaResumenSondasWashington/tablaResumenSondasWashington.php';
					include 'RU_sections/tablaResumenSondasCalifornia/tablaResumenSondasCalifornia.php';
					include 'RU_sections/tablaResumenSondasOregon/tablaResumenSondasOregon.php';
					include 'RU_sections/tablaResumenSondasNewYork/tablaResumenSondasNewYork.php';
					
                	?>
                </div>
               
            </div>
            
            <div class="row" id="detailAgent" style="display: none;">
               
                <div class="col-md-12">
                	<div class="principale textoSonda" style="background-color:#2196F3;  ">
					<div id="cabecera1" style="background-color:#2196F3; ">
				
						<div align="left">
							<strong>Agent information:</strong><br /> <br /> 
							<span>OS: </span>							<span>Microsoft Windows 7 Starter</span><br />
							<span>Processor: </span>					<span>Intel(R) Core(TM) i3-4010U CPU @ 1.70GHz</span><br />
							<span>Network card: </span>					<span>[00:0C:29:2A:3C:EA] Conexión de red Intel(R) PRO/1000 MT</span><br />
							<span>Graphics card: </span>				<span>LogMeIn Mirror Driver;VNC Mirror Driver;VMware SVGA 3D;</span><br />
							<span>Resolution: </span>					<span>1024 x 768</span>
							<br>
						</div>
				
					</div>
	
					</div>
	
					<div class="graficosSonda" id="lineByAgent" >
					
					</div>

                	
                </div>
               
            </div>   
            
            <div class="row">
            	
            	<div id="DivButtonBack" class="col-md-12" align="center" style="display:block; margin-top: 10px;">
            		          	
					<a id="buttonBack" class="button btn btn-info btn-xs">Back</a>
			
            	</div>
            	
            </div>      
        <footer class="footer">
            <center>
                <p>
                    <small>© Baking Software SpA</small>
                </p>
            </center>
        </footer>
    </div>
</div>
</body>
</html>