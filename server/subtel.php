<?php
require '../core/startup.inc.php';
/* 
** BSW
** Copyright (C) 2000-2013 BSW S.A.
**
** This modification allow the user to modify SEC data
**/

$cmd = new Control(true,'bmonitor.baking.cl',true);

$options = getopt("d::Q:");

/*
$cmd->parametro->remove('STDOUT');
$cmd->parametro->set('STDOUT', true);
*/

if (isset($options['d'])) {
	$cmd->parametro->remove('STDOUT');
	$cmd->parametro->set('STDOUT', true);
	echo "MODO DEBUG ON\n";	
}

$cmd->logs->info("[Neutralidad-2] ===============================================",false,'logs_neutralidad');
$cmd->logs->info("[Neutralidad-2] Starting Neutralidad-1 Subtel",false,'logs_neutralidad');
$cmd->logs->info("[Neutralidad-2] Copyright Baking Software 2012",false,'logs_neutralidad');
$cmd->logs->info("[Neutralidad-2] Version 2.1",false,'logs_neutralidad');
$cmd->logs->info("[Neutralidad-2] ===============================================",false,'logs_neutralidad');

$active = $cmd->_crontab("neutralidad_subtel","start");

if($active) {
	$cmd->logs->info("[Neutralidad-1] Starting OK",false,'logs_neutralidad');
} else {
	$cmd->logs->info("[Neutralidad-1] Starting NOK",false,'logs_neutralidad');
	exit;
}

$d1=date("U");

$cmd->_crontab("neutralidad_subtel","finish");

// Grupos de neutralidad:

$get_group = $cmd->conexion->queryFetch("SELECT HG.`groupid` FROM `bm_host_groups` HG WHERE HG.`type`='NEUTRALIDAD'");

$get_group = $cmd->conexion->arrayToIN($get_group,'groupid');


//Parametros




//Funciones

function CreateVel($vel,$cmd)
{
	$valid_colum_sql = $cmd->conexion->queryFetch("SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
           WHERE TABLE_NAME='bm_neutralidad' AND column_name='$vel'");
	
	if(!$valid_colum_sql || (count($valid_colum_sql) == 0)) {
		$cmd->conexion->query("ALTER TABLE `bm_neutralidad` ADD `" . $vel . "` DOUBLE(15,3)  NULL  DEFAULT NULL;");
	}
}

function vel_correct($id_plan,$plan,$vel,$clock,$desc,$id_item,$cmd)
{
	$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $vel*1 . ',2) WHERE `clock`="' . $clock . '" AND `id_item`='.$id_item);
}

function validRound($value)
{
	$val_r = ($value != "") ? "'" . doubleval($value) . "'" : "0"; 
	return $val_r;
}

function fillVel($id_plan,$plan,$clock,$desc,$id_item,$get_group,$cmd)
{
	$from="Con peso";
    $fal=0;
    $db_qos =$cmd->conexion->queryFetch("SELECT * FROM `bm_stat2` WHERE `valid`=0 AND `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
    
    if($db_qos) {
	    foreach ($db_qos as $key => $qos_data) {
			$fal = $qos_data["fallida"]*1;
	    }   	
    }
    $db_qos =$cmd->conexion->queryFetch("SELECT * FROM `bm_stat2` WHERE `valid`=1 AND `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
    
    if($db_qos) {
	    foreach ($db_qos as $key => $qos_data) {
	    	$p95=validRound($qos_data["p95"]);
			$p5=validRound($qos_data["p5"]);
			$vavg=validRound($qos_data["vavg"]);
			$std_samp=validRound($qos_data["std_samp"]);
			
			$query = sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=3+%d;",$plan,$p95,$clock,$id_item).
			sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=4+%d;",$plan,$p5,$clock,$id_item).
			sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=5+%d;",$plan,$vavg,$clock,$id_item).
			sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=6+%d;",$plan,$std_samp,$clock,$id_item);
						
			$cmd->conexion->query($query,false,'logs_neutralidad');
						
			//$cmd->conexion->query(sprintf("UPDATE `bm_neutralidad` SET `%s`=%d WHERE `clock`='%s' AND `id_item`=3+%d",$plan,$p95,$clock,$id_item),false,'logs_neutralidad');
			//$cmd->conexion->query(sprintf("UPDATE `bm_neutralidad` SET `%s`=%d WHERE `clock`='%s' AND `id_item`=4+%d",$plan,$p5,$clock,$id_item),false,'logs_neutralidad');
			//$cmd->conexion->query(sprintf("UPDATE `bm_neutralidad` SET `%s`=%d WHERE `clock`='%s' AND `id_item`=5+%d",$plan,$vavg,$clock,$id_item),false,'logs_neutralidad');
			//$cmd->conexion->query(sprintf("UPDATE `bm_neutralidad` SET `%s`=%d WHERE `clock`='%s' AND `id_item`=6+%d",$plan,$std_samp,$clock,$id_item),false,'logs_neutralidad');
			//$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $fal/($qos_data["exitosa"]+$fal) . ',2) WHERE `clock`="' . $clock . '" AND `id_item`='.$id_item);
	    }   	
    }

    if ($from == "Con peso")
    {
		$db_qos =$cmd->conexion->queryFetch("SELECT * FROM `bm_stat` WHERE `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
		
		if($db_qos) {
			foreach ($db_qos as $key => $qos_data) {
				$vavg=validRound($qos_data["avg"]);
				$std_samp=validRound($qos_data["std1"]);
			
				//$query = sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=5+%d;",$plan,$vavg,$clock,$id_item).
				//sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=6+%d;",$plan,$std_samp,$clock,$id_item);
						
				//$cmd->conexion->query($query);
				if($vavg > 0){
					$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $vavg*1 . ',2) WHERE clock="' . $clock . '" AND `id_item`=5+'.$id_item);
				}
				
				if($std_samp > 0){
					$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $std_samp*1 . ',2) WHERE clock="' . $clock . '" AND `id_item`=6+'.$id_item);
				}
				
				
			}
		}
    }
}

function fillPing($id_plan,$plan,$clock,$desc,$id_item,$get_group,$cmd)
{
	$from="Con peso";
   
	$db_qos =$cmd->conexion->queryFetch("SELECT * FROM `bm_stat2` WHERE `valid`=1 AND `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
	
	if($db_qos) {
		foreach ($db_qos as $key => $qos_data) {
			$query = 'UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["vavg"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=13+'.$id_item.";";
			$cmd->conexion->query($query);
		}
    }
	if ($from == "Con peso")
	{
		$db_qos =$cmd->conexion->queryFetch("SELECT * FROM `bm_stat` WHERE `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
		
		if($db_qos) {
			foreach ($db_qos as $key => $qos_data) {
				$avg = (int)round($qos_data["avg"]*1/2);
				if($avg > 0) {
					$query = 'UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["avg"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=13+'.$id_item.";";
					$cmd->conexion->query($query);
				}
			}
		}
	}
}

function fillPingS($id_plan,$plan,$clock,$desc,$id_item,$get_group,$cmd)
{
	$from="Con peso";
   
	$db_qos =$cmd->conexion->queryFetch("SELECT * FROM `bm_stat2` WHERE `valid`=1 AND `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
	
	if($db_qos) {
		foreach ($db_qos as $key => $qos_data) {
			$query = 'UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["std_samp"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=14+'.$id_item.";";
			$cmd->conexion->query($query);
		}
    }
	if ($from == "Con peso")
	{
		$db_qos =$cmd->conexion->queryFetch("SELECT * FROM `bm_stat` WHERE `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
		
		if($db_qos) {
			foreach ($db_qos as $key => $qos_data) {
				$avg = (int)round($qos_data["std1"]*1/2);
				if($avg > 0) {
					$query = 'UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["std1"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=14+'.$id_item.";";
					$cmd->conexion->query($query);
				}
			}
		}
	}
}

//Calculando Parametros

if(isset($options['Q']) && is_numeric($options['Q']) && ($options['Q'] > 0 && $options['Q'] < 5)){
	$quarter = $options['Q'];
	echo "\n Ocupando el Q $quarter\n";
} else {
	$quarter=ceil(date("n",  strtotime("yesterday"))/3);	
    echo "\n Ocupando el Q $quarter\n";
}

$year=date("Y",strtotime("yesterday"));

switch($quarter)
{
	case 1:
		$clocks = array($year.'/Q1',$year.'/01',$year.'/02',$year.'/03');
	break;
	case 2:
		$clocks = array($year.'/Q2',$year.'/04',$year.'/05',$year.'/06');
	break;
	case 3:
		$clocks = array($year.'/Q3',$year.'/07',$year.'/08',$year.'/09');
	break;
	case 4:
		$clocks = array($year.'/Q4',$year.'/10',$year.'/11',$year.'/12');
	break;
}

//Mediciones
foreach ($clocks as $keyClock => $clock) {
    
    $cmd->logs->info("[Neutralidad-2] Creando reporte $clock",false,'logs_neutralidad');  

	//Limpiar Tabla Neutralidad
	$cmd->conexion->query("DELETE FROM `bm_neutralidad` WHERE clock='$clock'");
	
	$cmd->conexion->query('INSERT INTO `bm_neutralidad` (`id_item`,`clock`) SELECT `id_item`,"' . $clock . '" FROM `bm_neutralidad_item`');
	
	$get_plantes_sql="SELECT P.`id_plan`, P.`plan`, P.`nacD`, P.`locD`, P.`intD`, P.`nacU`, P.`locU`, P.`intU`
FROM `bm_plan` P 
	LEFT JOIN `bm_plan_groups` PG USING(`id_plan`) 
	LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=PG.`groupid`
WHERE HG.`type`='NEUTRALIDAD';";

	$get_plantes = $cmd->conexion->queryFetch($get_plantes_sql);
	
	foreach ($get_plantes as $keyPlan => $qos_data) {
		
		$plan=str_replace("-","",$qos_data["plan"]);
		CreateVel($plan,$cmd);
		
		vel_correct($qos_data['id_plan'],$plan,$qos_data['nacD'],$clock,'NACBandwidthdown',102,$cmd);
		vel_correct($qos_data['id_plan'],$plan,$qos_data['locD'],$clock,'LOCBandwidthdown',202,$cmd);
		vel_correct($qos_data['id_plan'],$plan,$qos_data['intD'],$clock,'INTBandwidthdown',302,$cmd);

		vel_correct($qos_data['id_plan'],$plan,$qos_data['nacU'],$clock,'NACBandwidthup',107,$cmd);
		vel_correct($qos_data['id_plan'],$plan,$qos_data['locU'],$clock,'LOCBandwidthup',207,$cmd);
		vel_correct($qos_data['id_plan'],$plan,$qos_data['intU'],$clock,'INTBandwidthup',307,$cmd);
		
		fillVel($qos_data['id_plan'],$plan,$clock,'NACBandwidthdown',100,$get_group,$cmd);
		fillVel($qos_data['id_plan'],$plan,$clock,'LOCBandwidthdown',200,$get_group,$cmd);
		fillVel($qos_data['id_plan'],$plan,$clock,'INTBandwidthdown',300,$get_group,$cmd);
		
		fillVel($qos_data['id_plan'],$plan,$clock,'NACBandwidthup',105,$get_group,$cmd);
		fillVel($qos_data['id_plan'],$plan,$clock,'LOCBandwidthup',205,$get_group,$cmd);
		fillVel($qos_data['id_plan'],$plan,$clock,'INTBandwidthup',305,$get_group,$cmd);
		
		fillPing($qos_data['id_plan'],$plan,$clock,'NACPINGavg',100,$get_group,$cmd);
		fillPing($qos_data['id_plan'],$plan,$clock,'LOCPINGavg',200,$get_group,$cmd);
		fillPing($qos_data['id_plan'],$plan,$clock,'INTPINGavg',300,$get_group,$cmd);
		
		fillPingS($qos_data['id_plan'],$plan,$clock,'NACPINGstd',100,$get_group,$cmd);
		fillPingS($qos_data['id_plan'],$plan,$clock,'LOCPINGstd',200,$get_group,$cmd);
		fillPingS($qos_data['id_plan'],$plan,$clock,'INTPINGstd',300,$get_group,$cmd);
	}

	//Generando CSV

	$datos = array();
	$head = array();
	
	$head[] = '';
	foreach ($get_plantes as  $planes) {
		$head[] = $planes['plan'];
	}
	
	$datos[] = $head;
	
	$get_subtel_neutralidad_sql="SELECT * FROM `bm_neutralidad` 
									JOIN `bm_neutralidad_item` USING(`id_item`) 
								WHERE `clock`='$clock'
								ORDER BY id_item;";

	$get_subtel_neutralidad = $cmd->conexion->queryFetch($get_subtel_neutralidad_sql);
	
	if($get_subtel_neutralidad) {
		$count = 1;
		foreach ($get_subtel_neutralidad as $key => $value) {
			$nombreColumna ="columna_".$count;
			${$nombreColumna}=array();
			
			${$nombreColumna}[] = $value['item'];
					
			for ($i=0; $i < count($get_plantes); $i++) {
				$plan=str_replace("-","",$get_plantes[$i]["plan"]); 
				if(isset($value[$plan])) {
					${$nombreColumna}[] = $value[$plan];
				} else {
					${$nombreColumna}[] = "";
				}
				
			}
			$datos[] = ${$nombreColumna};
			$count++;
		}
	
	} else {
		$get_item_sql = "SELECT * FROM `bm_neutralidad_item` ORDER BY id_item;";
		$get_item = $cmd->conexion->queryFetch($get_item_sql);
		foreach ($get_item as $value) {
			$columna.$key=array();
			$columna.$key[] = $value['item'];
			for ($i=0; $i < count($get_plantes); $i++) {
				$columna.$key[] = '';
			}
			$datos[] = $columna.$key;
		}
	}
	
	$clock_file =  str_replace("/", "_", $clock);
    
	$fp = fopen( SITE_PATH . '/upload/subtel.'.$clock_file.'.csv', 'w');
	
	if($fp && (count($datos) > 0)) {
		foreach ($datos as $fields) {
		    if($fields != ''){
		        $createcsv = fputcsv($fp, $fields);
		    }
		}
	}
	
	fclose($fp);
}

/// SIN PESOS
$SUBTEL_SOURCE_INSERT_FILE = $cmd->parametro->get('SUBTEL_SOURCE_INSERT_FILE',FALSE);

if($SUBTEL_SOURCE_INSERT_FILE) {
	$command = 'mysql'
	        . ' --host=' . $cmd->parametro->get('HostBD')
	        . ' --user=' . $cmd->parametro->get('UserBD')
	        . " --password='" . $cmd->parametro->get('PassBD') . "'"
	        . ' --database=' . $cmd->parametro->get('NameBD')
	        . ' --execute="SOURCE ' . SITE_PATH.'/upload';
			
	$output1 = shell_exec($command . '/subtel.sql"');
}
?>
