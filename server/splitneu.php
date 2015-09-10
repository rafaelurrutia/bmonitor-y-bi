<?php
require '/var/www/site/bmonitor25/core/startup.inc.php';
/* 
** BSW
** Copyright (C) 2000-2005 BSW S.A. OK
**
** This modification allow the user to modify SEC data 
** Actualizado al 02/2015 usado actualmente 
**/

$cmd = new Control(true,'bmonitor.iblau.cl',true); //cada vez que se coloque un nuevo dominio hay que cambiar aqui

$cmd->parametro->remove('STDOUT');
$cmd->parametro->set('STDOUT', true);

//$cmd->logs->info("[SPLIT] ===============================================",false,'logs_neutralidad');
echo "\n [SPLIT] ===============================================";
//$cmd->logs->info("[SPLIT] Starting selecion of data for NEUTRALIDAD",false,'logs_neutralidad');
echo "\n [SPLIT] Starting selecion of data for NEUTRALIDAD";
//$cmd->logs->info("[SPLIT] Copyright Baking Software 2015",false,'logs_neutralidad');
echo "\n [SPLIT] Copyright Baking Software 2015";
//$cmd->logs->info("[SPLIT] Version 2.5.",false,'logs_neutralidad');
echo "\n [SPLIT] Version 2.5";
//$cmd->logs->info("[SPLIT] ===============================================",false,'logs_neutralidad');
echo "\n [SPLIT] ===============================================";

$active = $cmd->_crontab("splitneu_crontab","start");

if($active) {
	//$cmd->logs->info("[SPLIT] Starting OK",false,'logs_neutralidad');
	echo "\n [SPLIT] Starting OK";
} else {
	//$cmd->logs->info("[SPLIT] Starting NOK",false,'logs_neutralidad');
	echo "\n [SPLIT] Starting NOK";
	exit;
}

$d1=date("U");

$quarter=ceil(date("n",  strtotime("yesterday"))/3);
//$cmd->logs->error("quarter: $quarter");<--- sacar despues es para prueba solamente

$m='';

if ($quarter==1) $m='01';
if ($quarter==2) $m='03';
if ($quarter==3) $m='06';
if ($quarter==4) $m='09';

$y=date("Y",time()-3600);
$dt=$y . "-" . $m . "-01";
$dt_utime = strtotime("$dt");

//$cmd->logs->info("[SPLIT] Period " . $dt . " (Q". $quarter . ")",false,'logs_neutralidad');
echo "\n [SPLIT] Period " . $dt . " (Q". $quarter . ")";
//BGS015
//$getHostItem_sql = "SELECT H.`id_host`,IP.`id_item`
//	FROM `bm_item_profile` IP LEFT JOIN `bm_items` I USING(`id_item`) 
//		LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host` 
//		LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=H.`groupid`
//WHERE I.`description` in ('NACBandwidthdown','NACBandwidthup','LOCBandwidthdown','LOCBandwidthup','INTBandwidthdown',
//'INTBandwidthup','NACPINGavg','LOCPINGavg','INTPINGavg','IPLOGINRENEW','INTPINGstd','LOCPINGstd','NACPINGstd',
//'INTPINGlost','LOCPINGlost','NACPINGlost','INTPINGstdjitter','LOCPINGjitter','NACPINGjitter','Availability.bsw') 
//AND ( I.`description` = 'Availability.bsw' OR  HG.`type`='NEUTRALIDAD') ORDER BY H.`id_host`,IP.`id_item`;"; 
$getHostItem_sql = "SELECT H.`id_host`,IP.`id_item`
	FROM `bm_item_profile` IP LEFT JOIN `bm_items` I USING(`id_item`) 
		LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host` 
		LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=H.`groupid`
WHERE I.`descriptionLong` in ('Bandwidth - down - NAC','Bandwidth - up - NAC','Bandwidth - down - LOC','Bandwidth - up - LOC',
	'Bandwidth - down - INT', 'Bandwidth - up - INT', 'Ping - avg - NAC','Ping - avg - LOC', 'Ping - avg - INT',
'Login - renew - IP','Ping - std - INT','Ping - std - LOC', 'Ping - std - NAC', 'Ping - lost - INT', 'Ping - lost - LOC',
'Ping - lost - NAC','Ping - jitter - INT','Ping - jitter - LOC', 'Ping - jitter - NAC','Availability.bsw') 
AND ( HG.`type`='NEUTRALIDAD' OR  HG.`type`='QoS') ORDER BY H.`id_host`,IP.`id_item`;";

$getHostItem = $cmd->conexion->queryFetch($getHostItem_sql);
//$cmd->logs->error("ahora cuales son los host item");
//foreach ($getHostItem as $key => $value) {
//	foreach ($value as $key2 => $value2) {
//		$cmd->logs->error("$key2: $value2");
//	}
//}

if($getHostItem) {
	
	foreach ($getHostItem as $key => $host) {
		$hostIN[] = $host["id_host"];
		$itemsIN[] = $host["id_item"];
	}
	
	$hostIN = $cmd->conexion->arrayToIN($hostIN);
	$itemsIN = $cmd->conexion->arrayToIN($itemsIN);

	$getHostItem_total = count($getHostItem);
	
	//$cmd->logs->info("[SPLIT] Period " . $dt_utime . " (Q". $quarter . ")",false,'logs_neutralidad');
	echo "\n [SPLIT] Period Period " . $dt_utime . " (Q". $quarter . ")";
	//Limpiando Datos no utilizados
	
	$deleteCacheHistory_sql =  "DELETE FROM bm_q WHERE clock < $dt_utime";
	//$cmd->logs->error("se elimina data no utilizada: $deleteCacheHistory_sql");/////<---- sacar despues
	$deleteCacheHistory_prepare = $cmd->conexion->prepare($deleteCacheHistory_sql);
	
	$deleteCacheHistory_prepare->execute();
	
	$deleteCacheHistory_start = $deleteCacheHistory_prepare->rowCount();
	
	//$cmd->logs->info("[SPLIT] " . $deleteCacheHistory_start. "  records deleted from bsw_q table (start)",false,'logs_neutralidad');
	echo "\n [SPLIT] " . $deleteCacheHistory_start. "  records deleted from bsw_q table (start)";
	
	//Obtenido el ultimo history_id ingresado
	
	$idStartHistory_sql = "SELECT MAX(`id_history`) as id_history FROM `bm_q` ORDER BY `id_history` DESC LIMIT 1";
	$idStartHistory = $cmd->conexion->queryFetch($idStartHistory_sql);
	$getID = (int)$idStartHistory[0]['id_history'];
	if($getID == '') {
		$getID = 0;
	}
	
	$n=1;
	$suma=0;
	
	if($getID === 0) {
		$getID_sql = "SELECT MIN(`id_history`) as `id_history`  FROM `bm_history`  WHERE  `id_item`=1 AND `id_host` IN $hostIN   AND `clock` >= $dt_utime LIMIT 1";
		//$cmd->logs->error("si getID === 0: $getID_sql");/////<---- sacar despues
		$getID_result = $cmd->conexion->queryFetch($getID_sql,'logs_neutralidad');
		
		if($getID_result) {			
			$getID = $getID_result[0]['id_history'];
		} else {
			//$this->logs->error("nada que hacer se cancelo todo:");/////<---- sacar despues
			$cmd->_crontab("splitneu_crontab","finish");
			exit;
		}
	}
	
	if(!is_numeric($getID)){
		$getID = 0;
	}
	
	
	//$cmd->logs->info("[SPLIT] Insertando history desde $getID",false,'logs_neutralidad'); 
	echo "\n [SPLIT] Insertando history desde $getID";
	$insertHistory_sql = "INSERT INTO `bm_q` SELECT DISTINCT * FROM `bm_history` WHERE `id_history` >= ? AND `id_host` = ? AND `id_item`= ?";
	//$cmd->logs->error("SQL de insert bm_q: $insertHistory_sql");/////<---- sacar despues  
		
	$insertHistory_prepare = $cmd->conexion->prepare($insertHistory_sql);
	
	foreach ($getHostItem as $key => $host) {
		
		$hostid=$host["id_host"];
		$itemid=$host["id_item"];
    	//$cmd->logs->error("host $hostid y item: $itemid");/////<---- sacar despues  
		$insertHistory_logs = "INSERT INTO `bm_q` SELECT DISTINCT * FROM `bm_history` WHERE `id_history` >= $getID AND `id_host` = $hostid AND `id_item`= $itemid";
		//$cmd->logs->error("SQL2 de insert bm_q: $insertHistory_logs");/////<---- sacar despues
		$cmd->logs->debug("[SPLIT]  Ejecutando query: ", $insertHistory_logs, 'logs_neutralidad');
		$valid = $insertHistory_prepare->execute(array($getID, $hostid, $itemid));
		
		if($valid) {
			$insertHistory = $insertHistory_prepare->rowCount();
			//$cmd->logs->error("count: $insertHistory");/////<---- sacar despues
			//$cmd->logs->info("[SPLIT] " . number_format($n/$getHostItem_total*100,2) . "%  " . $insertHistory . " records added from item " . $itemid . " hostid " . $hostid ,false,'logs_neutralidad');
			echo "\n [SPLIT] " . number_format($n/$getHostItem_total*100,2) . "%  " . $insertHistory . " records added from item " . $itemid . " hostid " . $hostid;
			$suma=$suma+$insertHistory;
		} else {
			//$cmd->logs->error("[SPLIT] " . number_format($n/$getHostItem_total*100,2) . "%  " . $insertHistory . " records added from item " . $itemid . " hostid " . $hostid ,false,'logs_neutralidad');
			echo "\n [SPLIT] " . number_format($n/$getHostItem_total*100,2) . "%  " . $insertHistory . " records added from item " . $itemid . " hostid " . $hostid;
		}
		$n++;
	}
	
	$d=date("U")-$d1;
	//$cmd->logs->info("[SPLIT] done in " . $d . " seconds " . $suma . " records added",false,'logs_neutralidad');
	echo "\n [SPLIT] done in " . $d . " seconds " . $suma . " records added"; 
}
//$cmd->logs->error("EJECUTE SPLITNEU");/////<---- sacar despues
$cmd->_crontab("splitneu_crontab","finish");
?>
