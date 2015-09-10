<?php
$server = "";

if(isset($_GET["s"]) && $_GET["s"] != ""){
	$server = $_GET["s"];
}else{
	$server = $_SERVER["REMOTE_ADDR"];
}

$hops = 20;

if(isset($_GET["h"]) && $_GET["h"] != ""){
	$hops = $_GET["h"];
}

header("Content-Type: text/plain");
$init_time = time();

$resultado_inicial = shell_exec("traceroute -m $hops -w 1 $server");
$resultado = explode("\n", $resultado_inicial);
$resultado = array_diff($resultado, array(""));

$nulos = 0;
$loops = 0;
$max = 0;
$min = 0;
$avg = 0;
$sum = 0;
$desviacion = 0;
$varianza = 0;

$arreglo_loop = array();
$promedios = array();
$ip_unique = array();

for($i = 1; $i < count($resultado); $i++){
	preg_match_all("/\*/", $resultado[$i], $encontrados);
	
	if(count($encontrados[0]) >= 3){
		$nulos++;
	}else{
		preg_match("/\([0-9]{2,3}\.[0-9]{2,3}\.[0-9]{2,3}\.[0-9]{2,3}\)/", $resultado[$i], $ip);
		
		if(count($ip) > 0){
			$arreglo_loop[] = $ip[0];
		}
					
		preg_match_all("/[0-9]+([,\.][0-9]*)?\sms/", $resultado[$i], $tiempos);
					
		if(count($tiempos[0]) > 0){
			$arreglo_promedio_fila = array();
						
			foreach ($tiempos[0] as $key => $value) {
				preg_match("/[0-9]+([,\.][0-9]*)?/", $value, $numero);
				$arreglo_promedio_fila[] = $numero[0];
			}
						
			$suma_tiempos = array_sum($arreglo_promedio_fila);
			$prom_tiempos = $suma_tiempos / count($arreglo_promedio_fila);
			$prom_tiempos = round($prom_tiempos, 0);
			$promedios[] = $prom_tiempos;
		}
	}
}

if(count($arreglo_loop) > 1){
	$ip_unique = $arreglo_loop_unique = array_unique($arreglo_loop);
	$arreglo_loop_resumen = array_count_values($arreglo_loop);
			
	foreach($arreglo_loop_unique as $unico){
		if($arreglo_loop_resumen[$unico] > 1){
			$loops++;
		}
	}	
}

if(count($promedios) > 0){
	$max = max($promedios);
	$min = min($promedios);
	$avg = array_sum($promedios) / count($promedios);
	$sum = array_sum($promedios);
				
	foreach($promedios as $prom){
		$rango = pow($prom - $avg, 2);
		$varianza += $rango;
	}
				
	$varianza = round($varianza / count($promedios), 0);
	$desviacion = round(sqrt($varianza), 0);
}

$finish_time = time();
$time = $finish_time - $init_time;
			 
echo "num_hops=".(count($resultado) - 1).";\n";
echo "num_nulls=".$nulos.";\n";
echo "num_loops=".$loops.";\n";
echo "num_ip_diff=".count($ip_unique).";\n";
echo "time_max=$max;\n";
echo "time_min=$min;\n";
echo "avg_time=$avg;\n";
echo "variance=$varianza;\n";
echo "std_dvt=$desviacion;\n";
echo "time_execution=".intval(date("s", $time)).";\n";
echo "text=$resultado_inicial;\n";
?>