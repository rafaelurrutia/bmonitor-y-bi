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
 
$cmd->parametro->set('LOGS_FILE', 'logs_neutralidad');

$cmd->logs->info("[Neutralidad] ===============================================");
$cmd->logs->info("[Neutralidad] Starting Statistic Server");
$cmd->logs->info("[Neutralidad] Copyright Baking Software 2011");
$cmd->logs->info("[Neutralidad] Version 1.1.darkside");
$cmd->logs->info("[Neutralidad] ===============================================");

$active = $cmd->_crontab("neutralidad_crontab","start");

if($active) {
	$cmd->logs->info("[Neutralidad] Starting OK");
} else {
	$cmd->logs->info("[Neutralidad] Starting NOK");
	exit;
}

$d1=date("U");
$d0=$d1;

//$cmd->conexion->InicioTransaccion();

$cmd->conexion->query("update config set ldap_port=-1");

//$cmd->conexion->query("SET innodb_lock_wait_timeout = 500");

$qy="";

if (!isset($argv[1]))
{
	$m=date("n", strtotime("yesterday"));
	$y=date("Y", strtotime("yesterday"));
	if ($m < 10) $m = "0" . $m;
	$clock=$y . "/" . $m;
	$where=" clock between unix_timestamp('" . $y . "-" . $m . "-01') AND unix_timestamp(adddate('" . $y . "-" . $m . "-01', interval 1 month))-1 ";
	$grp="%Y/%m";
	$cmd->logs->info("[Neutralidad] Processing period ".$clock);
}

if (isset($argv[1]) && $argv[1] == "Q")
{
	$m=date("n", strtotime("yesterday"));
	
	switch(TRUE)
	{
		case (((int)$m == 1) || ((int)$m == 2) || ((int)$m == 3)):
			$qy="Q1";
			$m0="01";
			$m1="03";
		break;
		case (((int)$m == 4) || ((int)$m == 5) || ((int)$m == 6)):
			$qy="Q2";
			$m0="04";
	  		$m1="06";
		break;
		case (((int)$m == 7) || ((int)$m == 8) || ((int)$m == 9)):
			$qy="Q3";
			$m0="07";
			$m1="09";
		break;
		case (((int)$m == 10) || ((int)$m == 11) || ((int)$m == 12)):
			$qy="Q4";
			$m0="10";
			$m1="12";
		break;
	}
  	
	$y=date("Y", strtotime("yesterday"));
	$clock1=$y . "/" . $m0;
	$clock2=$y . "/" . $m1;
	$clock=$y . "/" . $qy;
	$where=" clock between unix_timestamp('" . $y . "-" . $m0 . "-01') AND unix_timestamp(adddate('" . $y . "-" . $m1 . "-01', interval 1 month))-1 ";
	$grp="%Y/" . $qy;
	$cmd->logs->info("[Neutralidad] Processing " . $qy);
}

if (isset($argv[1]) && $argv[1] == "REPO")
{
	if(is_numeric($argv[2]) && is_numeric($argv[3])) {
		$m=$argv[2];
		$y=$argv[3];
		if ($m < 10) $m = "0" . $m;
		$clock=$y . "/" . $m;
		$where=" clock between unix_timestamp('" . $y . "-" . $m . "-01') AND unix_timestamp(adddate('" . $y . "-" . $m . "-01', interval 1 month))-1 ";
		$grp="%Y/%m";
		$cmd->logs->info("[Neutralidad] Processing period ".$clock);
	} elseif (isset($argv[2]) && $argv[2] == "Q" && is_numeric($argv[3])) {
		
	switch($argv[3])
	{
		case 1:
			$qy="Q1";
			$m0="01";
			$m1="03";
		break;
		case 2:
			$qy="Q2";
			$m0="04";
	  		$m1="06";
		break;
		case 3:
			$qy="Q3";
			$m0="07";
			$m1="09";
		break;
		case 4:
			$qy="Q4";
			$m0="10";
			$m1="12";
		break;
		default:
			echo "Error Param";
			$cmd->_crontab("neutralidad_crontab","finish");
			exit;
	}		

	$y=$argv[4];
	
	$clock1=$y . "/" . $m0;
	$clock2=$y . "/" . $m1;
	$clock=$y . "/" . $qy;
	$where=" clock between unix_timestamp('" . $y . "-" . $m0 . "-01') AND unix_timestamp(adddate('" . $y . "-" . $m1 . "-01', interval 1 month))-1 ";
	$grp="%Y/" . $qy;
	$cmd->logs->info("[Neutralidad] Processing " . $qy." ".$y);
			
		
	} else {
		echo "Error Param";
		$cmd->_crontab("neutralidad_crontab","finish");
		exit;
	}
}

function Percentil($id_item,$id_plan,$dt,$groupid,$where,$cmd)
{
	$per=array();
	$sql="SELECT value FROM bm_persentil_temp3 WHERE id_item = '" . $id_item . "' and id_plan='" . $id_plan . "' AND " . $where . " and groupid=" . $groupid . " and valid=1  order by value";
	$res = $cmd->conexion->queryFetch($sql);
	$nolock=$cmd->conexion->query("SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ");
	
	$cnt=count($res);

	if ($cnt >= 5)
	{
		// P5
		$n=($cnt*(5/100));
		if ($n != round($n))
		{
			$n=floor($n);
			$per[5] = ($res[$n]['value']+$res[$n+1]['value'])/2;
		}
		else {
			if ($res[$n]['value'] > 0) $per[5]=$res[$n]['value'];
		}
			
		// P80
		$n=($cnt*(80/100));
		if ($n != round($n))
		{
			$n=floor($n);
			$per[80] = ($res[$n]['value']+$res[$n+1]['value'])/2;
		}
		else {
			if ($res[$n]['value'] > 0) $per[80]=$res[$n]['value'];
		}
      		
		// P95
		$n=($cnt*(95/100));
		if ($n != round($n))
		{
			$n=floor($n)-1;
			$per[95] = ($res[$n]['value']+$res[$n+1]['value'])/2;
		}
		else {
			if ($res[$n]['value'] > 0) $per[95]=$res[$n]['value'];
		}

	}
	return $per;
}

// Grupos de neutralidad:

$get_group = $cmd->conexion->queryFetch("SELECT HG.`groupid` FROM `bm_host_groups` HG WHERE HG.`type`='NEUTRALIDAD'");

if(!$get_group) {
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: Get groupid ",NULL,'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
}

$get_group = $cmd->conexion->arrayToIN($get_group,'groupid');

// STAT1 Availability
$d1=date("U");

$host_neutralidad="CREATE TEMPORARY TABLE bm_tmp_1 AS SELECT H.`id_host`,H.`id_plan`,IG.`id_item`, I.`description`,H.`groupid`
FROM `bm_host` H 
LEFT JOIN `bm_items_groups` IG USING(`groupid`)
LEFT JOIN `bm_items` I ON I.`id_item`=IG.`id_item`
LEFT JOIN `bm_host_groups` G ON G.`groupid`=H.`groupid`
WHERE G.`type`='NEUTRALIDAD' AND H.`borrado`=0 AND I.`description` IN
('NACbandwidthdown.sh','NACbandwidthup.sh','LOCbandwidthdown.sh','LOCbandwidthup.sh','INTbandwidthdown.sh','INTbandwidthup.sh','NACping_avg.sh','LOCping_avg.sh','INTping_avg.sh','ip_renew.sh','INTping_std.sh','LOCping_std.sh','NACping_std.sh','Availability.bsw')";

$temporary_bm_tmp_1 = $cmd->conexion->query($host_neutralidad,false,'logs_neutralidad');

if(!$temporary_bm_tmp_1){
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: CREATE TEMPORARY TABLE bm_tmp_1",NULL,'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
}

//Limpia stast 1

$deleteCacheHistory_sql =  "DELETE FROM `bm_stat1` WHERE clock='" . $clock . "' ";

$deleteCacheHistory_prepare = $cmd->conexion->prepare($deleteCacheHistory_sql);

$deleteCacheHistory_result = $deleteCacheHistory_prepare->execute();

if(!$deleteCacheHistory_result) {
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: delete bm_stat1 ",NULL,'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
} else {
	$deleteCacheHistory_start = $deleteCacheHistory_prepare->rowCount();
	$cmd->logs->info("[Neutralidad] " . $deleteCacheHistory_start. "  records deleted from bm_stat1 table (start)",false,'logs_neutralidad');
}

$bm_stat1="INSERT INTO `bm_stat1` SELECT 
	bm_tmp_1.`groupid`, bm_tmp_1.`id_item` , bm_tmp_1.`description`, bm_tmp_1.`id_plan`, DATE_FORMAT(from_unixtime(`bm_q`.`clock`),'" . $grp . "') as clock,
	sum(if(`bm_q`.`value`>=1,1,0)) as exitosa, sum(if(`bm_q`.`value`=0,1,0)) as fallida, avg(if(`bm_q`.`value`>0,1,0))*100 as cumple
FROM bm_tmp_1 
LEFT JOIN  `bm_q` ON bm_tmp_1.`id_host`=`bm_q`.`id_host` AND bm_tmp_1.`id_item`=`bm_q`.`id_item`
WHERE 
	bm_tmp_1.`id_item` =1 AND". $where . "
GROUP BY
	bm_tmp_1.`groupid`,
	bm_tmp_1.`id_item`,
	bm_tmp_1.`id_plan`,
	DATE_FORMAT(from_unixtime(`bm_q`.`clock`),'" . $grp . "')";

$INSERT_bm_stat1 = $cmd->conexion->query($bm_stat1,false,'logs_neutralidad');

if(!$INSERT_bm_stat1) {
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: Insert bm_stat1 ",NULL,'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
}

$d=date("U")-$d1;

$cmd->logs->info("[Neutralidad] Processing statistics " . $clock . " 1/8 in " . $d . " seconds");

// STATP

$d1=date("U");

$statp_query="CREATE TEMPORARY TABLE bm_statp_temp1 AS SELECT
	bm_tmp_1.`groupid`, bm_tmp_1.`id_item` , bm_tmp_1.`description`, bm_tmp_1.`id_plan`,
	DATE_FORMAT(from_unixtime(`bm_q`.`clock`),'" . $grp . " %w %H') as clock,
	avg(`bm_q`.`value`) as value,
	stddev_samp(`bm_q`.`value`) as std_samp,
	stddev_pop(`bm_q`.`value`) as std_pop, 
	stddev_pop(`bm_q`.`value`) as max_std_pop, 
	stddev_samp(`bm_q`.`value`) as max_std_samp, 
	count(*) as cnt 
FROM bm_tmp_1 
LEFT JOIN `bm_q` ON bm_tmp_1.`id_host`=`bm_q`.`id_host` AND bm_tmp_1.`id_item`=`bm_q`.`id_item`
WHERE `bm_q`.`valid`=1 AND `bm_q`.`value` > 0  AND" . $where . " AND bm_tmp_1.`description` IN 
('NACbandwidthdown.sh','NACbandwidthup.sh','LOCbandwidthdown.sh','LOCbandwidthup.sh','INTbandwidthdown.sh','INTbandwidthup.sh','NACping_avg.sh','LOCping_avg.sh','INTping_avg.sh','ip_renew.sh','INTping_std.sh','LOCping_std.sh','NACping_std.sh')
GROUP BY
	bm_tmp_1.`groupid`,
	bm_tmp_1.`id_plan`,
	bm_tmp_1.`id_item`,
	DATE_FORMAT(from_unixtime(`bm_q`.`clock`),'" . $grp . " %w %H');";

$CREATE_TEMPORARY_bm_statp_temp1 = $cmd->conexion->query($statp_query,false,'logs_neutralidad');

if(!$CREATE_TEMPORARY_bm_statp_temp1) {
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: Create bm_statp_temp1 ",NULL,'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
}


$deleteCacheHistory_sql =  "DELETE FROM `bm_statp` WHERE clock='" . $clock . "' ";

$deleteCacheHistory_prepare = $cmd->conexion->prepare($deleteCacheHistory_sql);

$deleteCacheHistory_result = $deleteCacheHistory_prepare->execute();

if(!$deleteCacheHistory_result) {
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: delete bm_statp ",NULL,'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
} else {
	$deleteCacheHistory_start = $deleteCacheHistory_prepare->rowCount();
	$cmd->logs->info("[Neutralidad] " . $deleteCacheHistory_start. "  records deleted from bm_statp table (start)",false,'logs_neutralidad');
}

// bsw_statp_temp1 = bm_statp_temp1

$d=date("U")-$d1;

$cmd->logs->info("[Neutralidad] Processing statistics " . $clock . " 2/8 in " . $d . " seconds");


$d1=date("U");

//Get Pesos 

$getPesosSum = $cmd->conexion->queryFetch("SELECT sum(value) as Suma FROM bsw_pesos WHERE groupid in $get_group and clock='$clock';");

$getPesosSum = $getPesosSum[0]['Suma'];

if(isset($getPesosSum) && is_numeric($getPesosSum)) {
	
	$cmd->logs->debug("Get suma de los pesos $getPesosSum");
	
	$bm_statp_temp2_query="INSERT INTO `bm_statp` SELECT bm_statp_temp1.`groupid`, bm_statp_temp1.`id_plan`, bm_statp_temp1.`id_item`, bm_statp_temp1.`description`,
	substr(bm_statp_temp1.clock,1,7) as clock,substr(bm_statp_temp1.clock,9,1) as nweek,substr(bm_statp_temp1.clock,11) as nhour,
	bm_statp_temp1.`value`,
	std_samp,std_pop,
	bm_statp_temp1.value*bsw_pesos.value/($getPesosSum) as wvalue,
	bm_statp_temp1.std_samp*bsw_pesos.value/($getPesosSum) as wstd_samp, 
	bm_statp_temp1.std_pop*bsw_pesos.value/($getPesosSum) as wstd_pop,
	cnt,max_std_samp as std_samp_per_h,max_std_pop as std_pop_per_h,
	bsw_pesos.value/($getPesosSum) as w		     
	FROM  bm_statp_temp1 , bsw_pesos
	WHERE concat(bsw_pesos.clock,' ',dia,' ',hora)=bm_statp_temp1.clock AND bsw_pesos.groupid in $get_group AND bsw_pesos.clock='$clock'";
	
	$INSERT_bm_statp = $cmd->conexion->query($bm_statp_temp2_query,false,'logs_neutralidad');
	
	if(!$INSERT_bm_stat1) {
		$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
		$cmd->logs->error("STATUS: NOK  DETAIL: Insert bm_statp ",NULL,'logs_neutralidad');
		//$cmd->conexion->rollBack();
		$cmd->_crontab("neutralidad_crontab","finish");
		exit;
	}
}

$d=date("U")-$d1;
$cmd->logs->info("[Neutralidad] Processing statistics " . $clock . " 3/8 in " . $d . " seconds");

// STAT

$deleteCacheHistory_sql =  "DELETE FROM `bm_stat` WHERE clock='" . $clock . "' ";

$deleteCacheHistory_prepare = $cmd->conexion->prepare($deleteCacheHistory_sql);

$deleteCacheHistory_result = $deleteCacheHistory_prepare->execute();

if(!$deleteCacheHistory_result) {
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: delete bm_stat ",NULL,'logs_neutralidad');
//	$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
} else {
	$deleteCacheHistory_start = $deleteCacheHistory_prepare->rowCount();
	$cmd->logs->info("[Neutralidad] " . $deleteCacheHistory_start. "  records deleted from bm_stat table (start)",false,'logs_neutralidad');
}

$d1=date("U");
 
$q="SELECT SP.`groupid`,SP.`id_plan`,SP.`id_item`,SP.`description`,SP.`clock`,sum(cnt) as cnt,sum(wvalue) as wvalue,stddev_pop(value) as std_pop,stddev_samp(value) as std_samp
FROM bm_statp SP
WHERE `clock`='$clock'
GROUP BY SP.`groupid`,SP.`id_plan`,SP.`id_item`,SP.`clock`";
$data = $cmd->conexion->queryFetch($q,'logs_neutralidad');

foreach ($data as $key => $value) {
	$groupid=$value["groupid"];
	$id_plan=$value["id_plan"];
	$wwvalue=$value["wvalue"];
	$cnt=$value["cnt"];
	$std_pop=$value["std_pop"];
	$std_samp=$value["std_samp"];
	$description=$value["description"];
	$id_item=$value["id_item"];
	$q1="SELECT  SP.`value`,SP.`wvalue`,SP.`w` FROM `bm_statp` SP WHERE SP.`id_plan`=$id_plan AND SP.`groupid`=$groupid AND SP.`id_item`=$id_item AND SP.`clock`='$clock'";
	$run=$cmd->conexion->queryFetch($q1,'logs_neutralidad');
	$r1=count($run);
    $std=0;
    $sum=0;	
	foreach ($run as $keyR => $valueR) {
      $wvalue=$valueR["wvalue"];
      $w=$valueR["w"];
      $sum=$sum+$wvalue;
      $std=$std+$w*($wvalue-$wwvalue)*($wvalue-$wwvalue);		
	}
    if ($r1-1 > 0 ) {
		$std=sqrt($std)/($r1-1);
    } else {
    	$std=0;
    }
    $insert_sql = sprintf('INSERT INTO `bm_stat` values( %d , %d, "%s" , %d , "%s" , "%d" , "%d" ,"%d" , "%d" ) ' ,
							$groupid,
							$id_plan,
							$clock,
							$id_item,
							$description,
							$sum,
							$std,
							$std_samp,
							$cnt
							);
							
	$cmd->conexion->query($insert_sql,false,'logs_neutralidad');
}

$d=date("U")-$d1;
$cmd->logs->info("[Neutralidad] Processing statistics " . $clock . " 4/8 in " . $d . " seconds");

// STAT3

$d1=date("U");

$q="CREATE TEMPORARY TABLE bm_stat_temp3 AS SELECT
	bm_tmp_1.`groupid`, bm_tmp_1.`id_item` , bm_tmp_1.`description`, bm_tmp_1.`id_plan`,
	DATE_FORMAT(from_unixtime(clock),'$grp') as clock,
	round(avg(value),2) as vavg,
	round(min(value),2) as vmin,
	round(max(value),2) as vmax,
	round(stddev_samp(value),2) as std_samp,
	round(stddev(value),2) as std,
	sum(if(valid=1 AND value > 0,1,0)) as exitosa,
	sum(if(valid=0 OR value = 0,1,0)) as fallida,
	round(stddev_samp(value)*1.96/sqrt(count(*)),2) as intervalo,
	round(2*stddev_samp(value)/sqrt(count(*)),2) as error,
	0 as p5,
	0 as p95,
	1 as valid,
	0 as p80 
FROM bm_tmp_1 
LEFT JOIN `bm_q` ON bm_tmp_1.`id_host`=`bm_q`.`id_host` AND bm_tmp_1.`id_item`=`bm_q`.`id_item`
WHERE `bm_q`.`valid`=1 AND `bm_q`.`value` > 0 AND" . $where . " AND bm_tmp_1.`description` IN 
('NACbandwidthdown.sh','NACbandwidthup.sh','LOCbandwidthdown.sh','LOCbandwidthup.sh','INTbandwidthdown.sh','INTbandwidthup.sh','NACping_avg.sh','LOCping_avg.sh','INTping_avg.sh','ip_renew.sh','INTping_std.sh','LOCping_std.sh','NACping_std.sh')
GROUP BY
	bm_tmp_1.`groupid`,
	bm_tmp_1.`id_item`,
	bm_tmp_1.`id_plan`,
	DATE_FORMAT(from_unixtime(`bm_q`.`clock`),'$grp');";
	
$CREATE_TEMPORARY_bm_stat_temp3 =  $cmd->conexion->query($q,false,'logs_neutralidad');

if(!$CREATE_TEMPORARY_bm_stat_temp3) {
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: CREATE TEMPORARY TABLE bm_stat_temp3 ",NULL,'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
}

$d=date("U")-$d1;
$cmd->logs->info("[Neutralidad] Processing statistics " . $clock . " 5/8 in " . $d . " seconds");

$d1=date("U");

$q="INSERT INTO bm_stat_temp3 SELECT
	bm_tmp_1.`groupid`, bm_tmp_1.`id_item` , bm_tmp_1.`description`, bm_tmp_1.`id_plan`,
	DATE_FORMAT(from_unixtime(clock),'$grp') as clock,
	round(avg(value),2) as vavg,
	round(min(value),2) as vmin,
	round(max(value),2) as vmax,
	round(stddev_samp(value),2) as std_samp,
	round(stddev(value),2) as std,
	sum(if(valid=1,1,0)) as exitosa,
	sum(if(valid=0,1,0)) as fallida,
	round(stddev_samp(value)*1.96/sqrt(count(*)),2) as intervalo,
	round(2*stddev_samp(value)/sqrt(count(*)),2) as error,
	0 as p5,
	0 as p95,
	0 as valid,
	0 as p80 
FROM bm_tmp_1 
LEFT JOIN `bm_q` ON bm_tmp_1.`id_host`=`bm_q`.`id_host` AND bm_tmp_1.`id_item`=`bm_q`.`id_item`
WHERE  `bm_q`.`value` > 0 AND" . $where . " AND bm_tmp_1.`description` IN 
('NACbandwidthdown.sh','NACbandwidthup.sh','LOCbandwidthdown.sh','LOCbandwidthup.sh','INTbandwidthdown.sh','INTbandwidthup.sh','NACping_avg.sh','LOCping_avg.sh','INTping_avg.sh','ip_renew.sh','INTping_std.sh','LOCping_std.sh','NACping_std.sh')
GROUP BY
	bm_tmp_1.`groupid`,
	bm_tmp_1.`id_item`,
	bm_tmp_1.`id_plan`,
	DATE_FORMAT(from_unixtime(`bm_q`.`clock`),'$grp');";
	
$INSERT_bm_stat_temp3 = $cmd->conexion->query($q);

if(!$INSERT_bm_stat_temp3) {
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: Insert bm_stat_temp3 ",NULL,'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
}

$d=date("U")-$d1;
$cmd->logs->info("[Neutralidad] Processing statistics " . $clock . " 6/8 in " . $d . " seconds");

$d11=date("U");

$tmp_table_persentil = "CREATE TEMPORARY TABLE bm_persentil_temp3 SELECT `bm_q`.`clock`, T1.`id_item`,T1.`description`,T1.`id_plan`,T1.`groupid`,`bm_q`.`value`
FROM bm_tmp_1 T1
LEFT JOIN  `bm_q` ON T1.`id_host`=`bm_q`.`id_host` AND T1.`id_item`=`bm_q`.`id_item`
WHERE $where
ORDER BY `bm_q`.`value`;";

$cmd->conexion->query($tmp_table_persentil);

$db_qos="SELECT * FROM bm_stat_temp3 WHERE valid=1 and groupid in $get_group and clock='" . $clock . "' order by id_item,id_plan,groupid";

$result_db_qos = $cmd->conexion->queryFetch($db_qos,'logs_neutralidad');

if(!$result_db_qos) {
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  QUERY:",$db_qos,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: Get bm_stat_temp3: ",$cmd->conexion->errorInfo(),'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
}

$n1=count($result_db_qos);
$start=1;
foreach ($result_db_qos as $key => $value) {
    $d1=date("U");
    
	$groupid=$value["groupid"];
	$id_plan=$value["id_plan"];
	$clock=$value["clock"];
	$id_item=$value["id_item"];
	$description=$value["description"];
	$p=percentil($id_item,$id_plan,$clock,$groupid,$where,$cmd);
	if (!isset($p[5])) $p[5] = 0;      
    if (!isset($p[80])) $p[80] = 0;      
    if (!isset($p[95])) $p[95] = 0;
	$q="UPDATE bm_stat_temp3 set p5=" . $p[5] . ",p95=" . $p[95] . ",p80=" . $p[80] . " WHERE groupid=" . $groupid .
		" AND clock='" . $clock . "'" .
		" AND id_plan='" . $id_plan . "'" .
		" AND valid=1" .
		" AND id_item='" . $id_item . "'";
	$cmd->conexion->query($q);
	$d=date("U")-$d1;
	$cmd->logs->info("[Neutralidad] Processing statistics " . $clock . " [" . $groupid . "] " . $id_plan . "-" . $description . "(" . ($start) . " of " . $n1 . ") in " . $d . " seconds");
	$start++;
}

$deleteCacheHistory_sql =  "DELETE FROM bm_stat2 WHERE clock='" . $clock . "' ";

$deleteCacheHistory_prepare = $cmd->conexion->prepare($deleteCacheHistory_sql);

$deleteCacheHistory_result = $deleteCacheHistory_prepare->execute();

if(!$deleteCacheHistory_result) {
	$cmd->logs->error("Error al generar informe de neutralidad: ",$deleteCacheHistory_prepare->errorInfo(),'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: delete bm_stat2 ",NULL,'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
} else {
	$deleteCacheHistory_start = $deleteCacheHistory_prepare->rowCount();
	$cmd->logs->debug("Query:",$deleteCacheHistory_sql,'logs_neutralidad');
	$cmd->logs->info("[Neutralidad] " . $deleteCacheHistory_start. "  records deleted from bm_stat2 table (start)",false,'logs_neutralidad');
}

$db_qos=$cmd->conexion->query("INSERT into bm_stat2 SELECT * FROM bm_stat_temp3",false,'logs_neutralidad');

if(!$db_qos) {
	$cmd->logs->error("Error al generar informe de neutralidad, ",NULL,'logs_neutralidad');
	$cmd->logs->error("STATUS: NOK  DETAIL: Insert bm_stat2 ",NULL,'logs_neutralidad');
	//$cmd->conexion->rollBack();
	$cmd->_crontab("neutralidad_crontab","finish");
	exit;
}

$d=date("U")-$d11;
$cmd->logs->info("[Neutralidad] Processing statistics " . $clock . " 7/8 in " . $d . " seconds");
$d=date("U")-$d0;
$cmd->logs->info("[Neutralidad] Statistics completed in " . $d . " seconds for period " . $clock);

if (strpos($clock,"Q") >0){
	$cmd->conexion->query("update config set ldap_bind_dn='" . date("r") . "'");	
} else {
	$cmd->conexion->query("update config set ldap_base_dn='" . date("r") . "',ldap_port=" . $d);
}
//$cmd->conexion->commit();
$cmd->_crontab("neutralidad_crontab","finish");
?>