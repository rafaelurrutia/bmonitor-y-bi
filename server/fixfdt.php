<?php
require '../core/startup.inc.php';
/* 
** BSW
** Copyright (C) 2012-2012 BSW S.A.
**
** This modification allow the user to modify SEC data
**/

$cmd = new Control(true);

/*
$cmd->parametro->remove('STDOUT');

$cmd->parametro->set('STDOUT', true);
* 
*/
 
$cmd->parametro->set('LOGS_FILE', 'logs_fdt');

$cmd->logs->info("[FIXFDT] ===============================================");
$cmd->logs->info("[FIXFDT] Starting Statistic Server");
$cmd->logs->info("[FIXFDT] Copyright Baking Software 2011");
$cmd->logs->info("[FIXFDT] Version 1.1.darkside");
$cmd->logs->info("[FIXFDT] ===============================================");

$active = $cmd->_crontab("fdt_fix_crontab","start");

if($active) {
	$cmd->logs->info("[FIXFDT] Starting OK");
} else {
	$cmd->logs->info("[FIXFDT] Starting NOK");
	exit;
}

$start=microtime(true);

if (!isset($argv[1])) {
	$dmenos=0;
} else {
	$dmenos=$argv[1];
}

$xdt=strftime("%A %Y-%m-%d",(strtotime("$dmenos days")));
$daysNow = strftime("%u",(strtotime("$dmenos days")));
$datestart=strftime("%Y-%m-%d 01:00:00",(strtotime("$dmenos days")));
$dateend=strftime("%Y-%m-%d 20:59:59",(strtotime("$dmenos days")));
$time=strtotime("$dmenos days");
$continue = TRUE;

if ($daysNow == 6) {
	$tests=6;
} else {
	$tests=11;
}

/*
if((int)$daysNow == 7) {
	$cmd->_crontab("fdt_fix_crontab","finish");
	exit;
}*/
 
$cmd->logs->info("[FIXFDT] Number of test per day: " .$tests);
$cmd->logs->info("[FIXFDT] Processing 1/2  $daysNow " . $xdt);

$host_neutralidad='SELECT H.`id_host`, H.`host`, HG.`groupid` , HG.`name`, `horario`,`feriados` FROM `bm_host`  H
LEFT JOIN `bm_host_groups` HG ON H.`groupid`=HG.`groupid`
WHERE H.`borrado` = 0 AND HG.`type`="ENLACES"';

$get_host_result = $cmd->conexion->queryFetch($host_neutralidad,false,'fdt_fix_crontab');

if(!$get_host_result){
	$cmd->logs->error("Error al generar fix de fdt, ",NULL,'logs_fdt');
	$cmd->logs->error("STATUS: NOK  DETAIL: CREATE TEMPORARY TABLE bm_tmp_1",NULL,'logs_fdt');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("fdt_fix_crontab","finish");
	exit;
}

//Seguro de mañana

$valid = $cmd->basic->cdateObj($get_host_result[0]['horario'],$get_host_result[0]['feriados'],$time);

if(($valid == TRUE && is_object($valid) == FALSE) && $dmenos == 0){
	$cmd->logs->error("Horario no permitido o no requiere medicion",NULL,'logs_fdt');
	$cmd->_crontab("fdt_fix_crontab","finish");
	exit;	
}elseif( $valid === FALSE ) {
	$cmd->logs->error("Horario no permitido o no requiere medicion",NULL,'logs_fdt');
	$cmd->_crontab("fdt_fix_crontab","finish");
	exit;	
}

$get_id_item_result =  $cmd->conexion->queryFetch('SELECT `id_item` FROM `bm_items` WHERE description in ("LOCbandwidthdownif.sh","LOCbandwidthupif.sh");');

$itemid_1 = $get_id_item_result[0]['id_item'];
$itemid_2 = $get_id_item_result[1]['id_item'];

$insert_values = array();

foreach ($get_host_result as $key => $host) {
	
	$groupid=$host["groupid"];
	$hostid=$host["id_host"];

	
	if ($daysNow <=5) {
		$q3 = 'SELECT horario, date("'.$datestart.'") as dt FROM bsw_h WHERE horario 
		NOT IN  
			( 
				SELECT HOUR(FROM_UNIXTIME(HI.`clock`)) AS HO FROM `bm_history` HI LEFT JOIN `bm_host` H ON H.`id_host`=HI.`id_host` 
					WHERE 
						HI.`clock` BETWEEN UNIX_TIMESTAMP("'.$datestart.'") AND UNIX_TIMESTAMP("'.$dateend.'") AND 
						HI.`id_item` = '.$itemid_1.' AND
						HI.`id_host` = '.$hostid.'
			)';
	} else {
		$q3 = 'SELECT horario, date("'.$datestart.'") as dt FROM bsw_h WHERE horario < 14 AND horario 
		NOT IN  
			( 
				SELECT HOUR(FROM_UNIXTIME(HI.`clock`)) AS HO FROM `bm_history` HI LEFT JOIN `bm_host` H ON H.`id_host`=HI.`id_host` 
					WHERE HI.`clock` BETWEEN UNIX_TIMESTAMP("'.$datestart.'") AND UNIX_TIMESTAMP("'.$dateend.'") AND 
					HI.`id_item` = '.$itemid_1.' AND
					HI.`id_host` = '.$hostid.'
			)';		
	}
	
	$horarios = $cmd->conexion->queryFetch($q3);
	
	if($horarios){
		foreach ($horarios as $hkey => $hvalue) {
			$h=$hvalue["horario"];
			$dt=$hvalue["dt"];
			
			//LOCbandwidthdownif.sh
			$insert_values[]="($itemid_1,$hostid,unix_timestamp('" . $dt . " " . $h . ":30:00'),0,10)";
			//LOCbandwidthupif.sh
			$insert_values[]="($itemid_2,$hostid,unix_timestamp('" . $dt . " " . $h . ":30:00'),0,10)";
			
		}
	}
}

if(count($insert_values) > 0) {
	$cmd->logs->info("[FIXFDT] insert:",count($insert_values));	
	$insert_sql = 'INSERT INTO `bm_history` (`id_item`, `id_host`, `clock`, `value`, `valid`) VALUES ';
	$insert_sql_final = $insert_sql.implode(',', $insert_values);
	$cmd->conexion->query($insert_sql_final);
} else {
	$cmd->logs->info("[FIXFDT] insert not found");
}

$duracion=microtime(true)-$start;
$cmd->_crontab("fdt_fix_crontab","finish");
$cmd->logs->info("[FIXFDT] done in " . $duracion . " ms");
?>
