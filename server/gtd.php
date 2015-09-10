<?php
require '../core/startup.inc.php';
/* 
** BSW
** Copyright (C) 2000-2005 BSW S.A.
**
** This modification allow the user to modify SEC data
**/

$cmd = new Control();

//$cmd->parametro->remove('STDOUT');
//$cmd->parametro->set('STDOUT', true);

$cmd->logs->debug("[Neutralidad-1] ===============================================",false,'logs_neutralidad');
$cmd->logs->debug("[Neutralidad-1] Starting Neutralidad-1 STATS",false,'logs_neutralidad');
$cmd->logs->debug("[Neutralidad-1] Copyright Baking Software 2011",false,'logs_neutralidad');
$cmd->logs->debug("[Neutralidad-1] Version 1.1.darkside",false,'logs_neutralidad');
$cmd->logs->debug("[Neutralidad-1] ===============================================",false,'logs_neutralidad');

$active = $cmd->_crontab("neutralidad_subtel","start");

if($active) {
	$cmd->logs->debug("[Neutralidad-1] Starting OK",false,'logs_neutralidad');
} else {
	$cmd->logs->debug("[Neutralidad-1] Starting NOK",false,'logs_neutralidad');
	exit;
}

$d1=date("U");

$cmd->_crontab("neutralidad_subtel","finish");

// Grupos de neutralidad:

$get_group = $cmd->conexion->queryFetch("SELECT HG.`groupid` FROM `bm_host_groups` HG WHERE HG.`type`='NEUTRALIDAD'");

$get_group = $cmd->conexion->arrayToIN($get_group,'groupid');

//Funciones

function CreateVel($vel,$cmd)
{
	$valid_colum_sql = $cmd->conexion->queryFetch("SELECT * FROM `bm_neutralidad` LIMIT 1");
	
	if(!isset($valid_colum_sql[0][$vel])) {
		$cmd->conexion->query("ALTER TABLE `bm_neutralidad` ADD `" . $vel . "` DOUBLE(15,3)  NULL  DEFAULT NULL;");
	}
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
			$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["p95"]*1 . ',2) WHERE `clock`="' . $clock . '" AND `id_item`=3+'.$id_item);
			$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["p5"]*1 . ',2) WHERE `clock`="' . $clock . '" AND `id_item`=4+'.$id_item);
			$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["vavg"]*1 . ',2) WHERE `clock`="' . $clock . '" AND `id_item`=5+'.$id_item);
			$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["std_samp"]*1 . ',2) WHERE `clock`="' . $clock . '" AND `id_item`=6+'.$id_item);
			//$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $fal/($qos_data["exitosa"]+$fal) . ',2) WHERE `clock`="' . $clock . '" AND `id_item`='.$id_item);
	    }   	
    }

    if ($from == "Con peso")
    {
		$db_qos =$cmd->conexion->queryFetch("SELECT * FROM `bm_stat` WHERE `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
		
		if($db_qos) {
			foreach ($db_qos as $key => $qos_data) {
				$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["avg"]*1 . ',2) WHERE clock="' . $clock . '" AND `id_item`=5+'.$id_item);
				$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["std1"]*1 . ',2) WHERE clock="' . $clock . '" AND `id_item`=6+'.$id_item);
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
			$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["vavg"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=13+'.$id_item);
		}
    }
	if ($from == "Con peso")
	{
		$db_qos =$cmd->conexion->queryFetch("SELECT * FROM `bm_stat` WHERE `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
		
		if($db_qos) {
			foreach ($db_qos as $key => $qos_data) {
				$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["avg"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=13+'.$id_item);
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
			$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["std_samp"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=14+'.$id_item);
		}
    }
	if ($from == "Con peso")
	{
		$db_qos =$cmd->conexion->queryFetch("SELECT * FROM `bm_stat` WHERE `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
		
		if($db_qos) {
			foreach ($db_qos as $key => $qos_data) {
				$cmd->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["std1"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=14+'.$id_item);
			}
		}
	}
}

//Mediciones

$result = $cmd->conexion->queryFetch("SELECT clock,count(*) FROM bm_stat2 WHERE clock is not null GROUP BY clock ORDER BY clock;");

foreach ($result as $keyClock => $value) {
		
	$clock=$value["clock"];
	
	$clock_file =  str_replace("/", "_", $clock);
	
	//Limpiar Tabla Neutralidad
	$cmd->conexion->query("DELETE FROM `bm_neutralidad` WHERE clock='$clock'");
	
	$cmd->conexion->query('INSERT INTO `bm_neutralidad` (`id_item`,`clock`) SELECT `id_item`,"' . $clock . '" FROM `bm_neutralidad_item`');
	
	$get_plantes_sql="SELECT P.`id_plan`, P.`plan`
FROM `bm_plan` P 
	LEFT JOIN `bm_plan_groups` PG USING(`id_plan`) 
	LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=PG.`groupid`
WHERE HG.`type`='NEUTRALIDAD';";

	$get_plantes = $cmd->conexion->queryFetch($get_plantes_sql);
	
	foreach ($get_plantes as $keyPlan => $qos_data) {
		
		$plan=str_replace("-","",$qos_data["plan"]);
		CreateVel($plan,$cmd);
		
		vel_correct($qos_data['id_plan'],$qos_data['nacD'],$clock,'NACbandwidthdown.sh',102,$cmd);
		vel_correct($qos_data['id_plan'],$qos_data['locD'],$clock,'LOCbandwidthdown.sh',202,$cmd);
		vel_correct($qos_data['id_plan'],$qos_data['intD'],$clock,'INTbandwidthdown.sh',302,$cmd);

		vel_correct($qos_data['id_plan'],$qos_data['nacU'],$clock,'NACbandwidthup.sh',107,$cmd);
		vel_correct($qos_data['id_plan'],$qos_data['locU'],$clock,'LOCbandwidthup.sh',207,$cmd);
		vel_correct($qos_data['id_plan'],$qos_data['intU'],$clock,'INTbandwidthup.sh',307,$cmd);
		
		fillVel($qos_data['id_plan'],$plan,$clock,'NACbandwidthdown.sh',100,$get_group,$cmd);
		fillVel($qos_data['id_plan'],$plan,$clock,'LOCbandwidthdown.sh',200,$get_group,$cmd);
		fillVel($qos_data['id_plan'],$plan,$clock,'INTbandwidthdown.sh',300,$get_group,$cmd);
		
		fillVel($qos_data['id_plan'],$plan,$clock,'NACbandwidthup.sh',104,$get_group,$cmd);
		fillVel($qos_data['id_plan'],$plan,$clock,'LOCbandwidthup.sh',204,$get_group,$cmd);
		fillVel($qos_data['id_plan'],$plan,$clock,'INTbandwidthup.sh',304,$get_group,$cmd);
		
		fillPing($qos_data['id_plan'],$plan,$clock,'NACping_avg.sh',100,$get_group,$cmd);
		fillPing($qos_data['id_plan'],$plan,$clock,'LOCping_avg.sh',200,$get_group,$cmd);
		fillPing($qos_data['id_plan'],$plan,$clock,'INTping_avg.sh',300,$get_group,$cmd);
		
		fillPingS($qos_data['id_plan'],$plan,$clock,'NACping_std.sh',100,$get_group,$cmd);
		fillPingS($qos_data['id_plan'],$plan,$clock,'LOCping_std.sh',200,$get_group,$cmd);
		fillPingS($qos_data['id_plan'],$plan,$clock,'INTping_std.sh',300,$get_group,$cmd);
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
				${$nombreColumna}[] = $value[$plan];
			}
			$datos[] = ${$nombreColumna};
			$count++;
		}
	
	} else {
		$get_item_sql = "SELECT * FROM `bm_neutralidad_item` ORDER BY id_item;";
		$get_item = $this->conexion->queryFetch($get_item_sql);
		foreach ($get_item as $value) {
			$columna.$key=array();
			$columna.$key[] = $value['item'];
			for ($i=0; $i < count($get_plantes); $i++) {
				$columna.$key[] = '';
			}
			$datos[] = $columna.$key;
		}

	}
	
	$fp = fopen(site_path.'/upload/subtel.'.$clock_file.'.csv', 'w');
	
	foreach ($datos as $fields) {
    	fputcsv($fp, $fields);
	}
	
	fclose($fp); 
}
?>
