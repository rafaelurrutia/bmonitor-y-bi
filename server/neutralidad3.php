<?php
require '../core/startup.inc.php';
/* 
** BSW
** Copyright (C) 2000-2005 BSW S.A.
**
** This modification allow the user to modify SEC data
**/

$cmd = new Control();

$cmd->parametro->remove('STDOUT');

$cmd->parametro->set('STDOUT', true);

$cmd->parametro->set('LOGS_FILE', 'logs_neutralidad');

$cmd->logs->debug("[Neutralidad] ===============================================");
$cmd->logs->debug("[Neutralidad] Starting Statistic Server");
$cmd->logs->debug("[Neutralidad] Copyright Baking Software 2011");
$cmd->logs->debug("[Neutralidad] Version 1.1.darkside");
$cmd->logs->debug("[Neutralidad] ===============================================");

$active = $cmd->_crontab("neutralidad_crontab","start");

if($active) {
	$cmd->logs->debug("[Neutralidad] Starting OK");
} else {
	$cmd->logs->debug("[Neutralidad] Starting NOK");
	exit;
}

$d1=date("U");
$d0=$d1;

$cmd->conexion->query("update config set ldap_port=-1");

$qy="";

if (!isset($argv[1]))
{
	$m=date("n", strtotime("yesterday"));
	$y=date("Y", strtotime("yesterday"));
	if ($m < 10) $m = "0" . $m;
	$clock=$y . "/" . $m;
	$where=" clock between unix_timestamp('" . $y . "-" . $m . "-01') AND unix_timestamp(adddate('" . $y . "-" . $m . "-01', interval 1 month))-1 ";
	$grp="%Y/%m";
	$cmd->logs->debug("[Neutralidad] Processing period ".$clock);
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
	$cmd->logs->debug("[Neutralidad] Processing " . $qy);
}

function Percentil($id_item,$id_plan,$dt,$groupid,$where,$cmd)
{
	$per=array();
	$sql="SELECT value FROM bm_persentil_temp3 WHERE id_item = '" . $id_item . "' and id_plan='" . $id_plan . "' AND " . $where . " and groupid=" . $groupid . " order by value";
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

$get_group = $cmd->conexion->arrayToIN($get_group,'groupid');


// STAT1 Availability
$d1=date("U");

$host_neutralidad="CREATE TEMPORARY TABLE bm_tmp_1 AS SELECT H.`id_host`,H.`id_plan`,IG.`id_item`, I.`description`,H.`groupid`
FROM `bm_host` H 
LEFT JOIN `bm_items_groups` IG USING(`groupid`)
LEFT JOIN `bm_items` I ON I.`id_item`=IG.`id_item`
LEFT JOIN `bm_host_groups` G ON G.`groupid`=H.`groupid`
WHERE G.`type`='NEUTRALIDAD'AND I.`description` IN
('NACbandwidthdown.sh','NACbandwidthup.sh','LOCbandwidthdown.sh','LOCbandwidthup.sh','INTbandwidthdown.sh','INTbandwidthup.sh','NACping_avg.sh','LOCping_avg.sh','INTping_avg.sh','ip_renew.sh','INTping_std.sh','LOCping_std.sh','NACping_std.sh','Availability.bsw')";

$cmd->conexion->query($host_neutralidad);

$cmd->conexion->query("create index xx_idx1 on bm_tmp_1(itemid)");

//Limpia stast 1

$cmd->conexion->query("DELETE FROM `bm_stat1` WHERE `clock`='" . $clock . "'");

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
	

	
	  
  