<?php
require '../core/startup.inc.php';
/*
 ** BSW
 ** Copyright (C) 2012-2013 BSW S.A.
 **
 ** This modification allow the user to modify SEC data
 **/

//Global Param:
$dateStart=date("U");
$var = (object) array(
    "logsFile" => "logs_qoe",
    "crontab" => "qos_crontab"
);

if (!isset($argv[1]))
{
    $domain = 'bmonitor.baking.cl';
} else {
    $domain = $argv[1];
}

$cmd = new Control(true,$domain);

$cmd->parametro->remove('STDOUT');
$cmd->parametro->remove('DEBUG');

$cmd->parametro->set('STDOUT', true);
$cmd->parametro->set('DEBUG', false);

$cmd->parametro->set('LOGS_FILE', $var->logsFile);

$cmd->logs->info("[QoS] ===============================================");
$cmd->logs->info("[QoS] Starting Statistic Server");
$cmd->logs->info("[QoS] Copyright Baking Software 2013");
$cmd->logs->info("[QoS] Version 2.1");
$cmd->logs->info("[QoS] ===============================================");

$active = $cmd->_crontab($var->crontab, "start");

if ($active) {
    $cmd->logs->info("[QoS] Starting OK");
} else {
    $cmd->logs->info("[QoS] Starting NOK");
    exit ;
}

//Set Param
$param = (object) array();

$param->year = date("Y", strtotime("yesterday"));
$param->month = date("n", strtotime("yesterday"));

//$param->month = 6;

if ($param->month < 10) {
    $param->month = "0" . $param->month;
}
$param->clock = $param->year . "/" . $param->month;

$param->where = " `clock` BETWEEN UNIX_TIMESTAMP('" . $param->year . "-" . $param->month . "-01') 
AND UNIX_TIMESTAMP(adddate('" . $param->year . "-" . $param->month . "-01', interval 1 month))-1 ";

$param->grp = "%Y/%m";

$cmd->logs->info("[QoS] Processing period " . $param->clock);

//GetDBValue

$getGroupIDResult = $cmd->conexion->queryFetch("SELECT HG.`groupid` FROM `bm_host_groups` HG WHERE HG.`type`='QoS'");

if ($getGroupIDResult) {
    foreach ($getGroupIDResult as $key => $value) {
        $groupID[] = $value['groupid'];
    }
} else {
    $cmd->logs->error("[QoS] Error al obtener el id de los grupos, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$createTable = "CREATE TEMPORARY TABLE  IF NOT EXISTS `tmp_hostid_itemid` (
  `id_tmp` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_host` int(11) unsigned NOT NULL DEFAULT '0',
  `id_plan` int(11) unsigned NOT NULL,
  `groupid` int(11) unsigned NOT NULL,
  `id_item` int(11) unsigned NOT NULL,
  `type` enum('personalized','download','upload') NOT NULL DEFAULT 'personalized',
  `order` enum('DESC','ASC') NOT NULL DEFAULT 'DESC',
  `nominal` decimal(11,2) DEFAULT NULL,
  `warning` int(11) DEFAULT NULL,
  `critical` int(11) DEFAULT NULL,
  `nacD` int(11) unsigned NOT NULL,
  `nacU` int(11) unsigned NOT NULL,
  `location` varchar(255) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_tmp`),
  KEY `id_host` (`id_host`),
  KEY `id_item` (`id_item`)
);";

$temporaryTableCreate = $cmd->conexion->query($createTable);

if (!$temporaryTableCreate) {
    $cmd->logs->error("[QoS] Error create temporary table, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$getHostValid = $cmd->conexion->query("INSERT INTO `tmp_hostid_itemid` SELECT null,H.`id_host`,H.`id_plan`,H.`groupid`,IG.`id_item`, TH.`type`, IF( TH.`nominal` > TH.`warning`,'DESC','ASC') as 'order' , TH.`nominal`,TH.`warning` ,TH.`critical`, PL.`nacD`,PL.`nacU`, GROUP_CONCAT(HD.`value` SEPARATOR '|') AS location
FROM `bm_host` H 
LEFT JOIN `bm_items_groups` IG ON IG.`groupid` = H.`groupid`
LEFT JOIN `bm_plan` PL ON PL.`id_plan`=H.`id_plan`
    INNER JOIN `bm_threshold` TH ON IG.`id_item`=TH.`id_item`
LEFT OUTER JOIN `bm_host_detalle` HD ON HD.`id_host`=H.`id_host`
WHERE
 H.`borrado`=0  
AND H.`id_plan` > 0
AND TH.`report` = 'true'
AND HD.`id_feature` IN (77,78,79)");

if (!$getHostValid) {
    $cmd->logs->error("[QoS] Error al obtener las sondas, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

//Get var  y cosas
$cmd->basic->timeStart();

$getVar = "CREATE TEMPORARY TABLE tmp_statsQoS AS SELECT 
    THI.`groupid`, THI.`id_item` as 'idItem' , THI.`id_plan` as 'idPlan',
    DATE_FORMAT(from_unixtime(clock),'" . $param->grp . "') as 'DATE',
    round(avg(IF(`valid` = 1,`value`,NULL)),2) as AVG,
    round(min(IF(`valid` = 1,`value`,NULL)),2) as MIN,
    round(max(IF(`valid` = 1,`value`,NULL)),2) as MAX,
    round(stddev_samp(IF(`valid` = 1,`value`,NULL)),2) as STD_SAMP,
    round(stddev(IF(`valid` = 1,`value`,NULL)),2) as STD,
    case THI.`type` 
        when 'personalized' then 
                IF(THI.nominal > THI.warning, 
                        SUM(IF(H.`value` >  THI.warning AND H.`value` <=  THI.nominal ,1,0)) , 
                        SUM(IF(H.`value` <  THI.warning AND H.`value` >=  THI.nominal ,1,0)))   
        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.nominal/100) AND H.`value` >  (`nacD`*THI.warning/100) ,1,0))
        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.nominal/100) AND H.`value` >  (`nacU`*THI.warning/100) ,1,0))
    end as nominal,
    case THI.`type` 
        when 'personalized' then 
                IF(THI.nominal > THI.warning, 
                        SUM(IF(H.`value` >  THI.nominal ,1,0)) , 
                        SUM(IF(H.`value` <  THI.nominal ,1,0)))   
        when 'download' then SUM(IF(H.`value` >  (`nacD`*THI.nominal/100),1,0))
        when 'upload' then SUM(IF(H.`value` >  (`nacU`*THI.nominal/100),1,0))
    end as nominalD,
    case THI.`type` 
        when 'personalized' then 
                IF(THI.nominal > THI.warning, 
                        SUM(IF(H.`value` <=  THI.warning AND H.`value` >  THI.critical ,1,0)) , 
                        SUM(IF(H.`value` >=  THI.warning AND H.`value` <  THI.critical ,1,0)))   
        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.warning/100) && H.`value` <  (`nacD`*THI.critical/100),1,0))
        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.warning/100) && H.`value` <  (`nacU`*THI.critical/100),1,0))
    end as warning,
    case THI.`type` 
        when 'personalized' then 
                IF(THI.nominal > THI.warning, 
                        SUM(IF(H.`value` <=  THI.critical,1,0)) , 
                        SUM(IF(H.`value` >=  THI.critical,1,0)))  
        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.critical/100),1,0))
        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.critical/100),1,0))
    end as critical,
    sum(if(valid=1,1,0)) as EXITOSA,
    sum(if(valid=0,1,0)) as FALLIDA,
    round(stddev_samp(IF(`valid` = 1,`value`,NULL))*1.96/sqrt(SUM(IF(`valid` = 1,1,0))),2) as INTERVALO,
    round(2*stddev_samp(IF(`valid` = 1,`value`,NULL))/sqrt(SUM(IF(`valid` = 1,1,0))),2) as ERROR,
    0 as PERCENTIL_5,
    0 as PERCENTIL_95,
    0 as PERCENTIL_80 
FROM tmp_hostid_itemid THI
LEFT JOIN `bm_history` H ON THI.`id_host`=H.`id_host` AND THI.`id_item`=H.`id_item`
WHERE 
    H.`valid`=1 AND 
    H.`value` > 0  AND
    $param->where
GROUP BY
    THI.`groupid`,
    THI.`id_item`,
    THI.`id_plan`,
    `DATE`";

$getVarValid = $cmd->conexion->query($getVar);

if (!$getVarValid) {
    $cmd->logs->error("[QoS] Error al calcular statsQoS, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$duration = $cmd->basic->timeEnd();
$cmd->logs->info("[QoS] Temporary table tmp_statsQoS created in $duration  seconds", NULL);

//Calculando percentil

function calPercentil($id_item, $id_plan, $dt, $groupid, $where, $cmd)
{
        
    $PERCENTILE_TYPE_REPORT_QOE = $cmd->parametro->get('PERCENTILE_TYPE_REPORT_QOE',0);
    
    if($PERCENTILE_TYPE_REPORT_QOE == 0) {
        $percentileType = 'INC';
    } else {
        $percentileType = 'EXC';
    }
    
    $PERCENTILE_REPORT_QOE = json_decode($cmd->parametro->get('PERCENTILE_REPORT_QOE','[]')); 
    
    $per = array();
    $sql = "SELECT value FROM tmp_percentil WHERE id_item = '" . $id_item . "' and id_plan='" . $id_plan . "' AND " . $where . " and groupid=" . $groupid . " order by value";
    $res = $cmd->conexion->queryFetch($sql);
    if ($res) {

        foreach ($PERCENTILE_REPORT_QOE as $key => $value) {
            $per[$value] = $cmd->basic->getPercentil($res, $value, $percentileType, TRUE, 'value');
        }

        return $per;
    } else {
        return false;
    }

}

$cmd->basic->timeStart();
 
$getValuePercentil = "CREATE TEMPORARY TABLE tmp_percentil SELECT H.`clock`, TMP.`id_item`,TMP.`id_plan`,TMP.`groupid`,H.`value`
FROM tmp_hostid_itemid TMP
LEFT JOIN  `bm_history` H  ON TMP.`id_host`=H.`id_host` AND TMP.`id_item`=H.`id_item`
WHERE $param->where
ORDER BY H.`value`;";

$getValuePercentilValid = $cmd->conexion->query($getValuePercentil);

if (!$getValuePercentilValid) {
    $cmd->logs->error("[QoS] Error al obtener los datos del history, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$duration = $cmd->basic->timeEnd();
$cmd->logs->info("[QoS] Temporary table tmp_percentil created in $duration  seconds", NULL);

$getPlanPercentilSQL = "SELECT * FROM `tmp_statsQoS` WHERE `DATE`='" . $param->clock . "' ORDER BY `idItem`,`idPlan`,`groupid`";

$getPlanPercentil = $cmd->conexion->queryFetch($getPlanPercentilSQL);

if (!$getPlanPercentil) {
    $cmd->logs->error("[QoS] Error al obtener los planes a calcular, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$countN = count($getPlanPercentil);
$startN = 1;

foreach ($getPlanPercentil as $key => $value) {
    $cmd->basic->timeStart();

    $groupid = $value["groupid"];
    $id_plan = $value["idPlan"];
    $clock = $value["clock"];
    $id_item = $value["idItem"];

    $percentil = calPercentil($id_item, $id_plan, $clock, $groupid, $param->where, $cmd);
    if (!isset($percentil[5])) $percentil[5] = 0;      
    if (!isset($percentil[80])) $percentil[80] = 0;      
    if (!isset($percentil[95])) $percentil[95] = 0;
    $updatePercentil="UPDATE tmp_statsQoS set PERCENTIL_5=" . $percentil[5] . ",PERCENTIL_95=" . $percentil[95] . ",PERCENTIL_80=" . $percentil[80] . " WHERE groupid=" . $groupid .
        " AND DATE='" . $param->clock . "'" .
        " AND idPlan='" . $id_plan . "'" .
        " AND idItem='" . $id_item . "'";
    $cmd->conexion->query($updatePercentil);

    $duration = $cmd->basic->timeEnd();
    $cmd->logs->info("[QoS] Processing statistics " . $clock . " [" . $groupid . "] " . $id_plan . "(" . ($startN) . " of " . $countN . ") in " . $duration . " seconds");
    $startN++;
}

$getPlanPercentilSQL = "SELECT * FROM `tmp_statsQoS` WHERE `DATE`='" . $param->clock . "' ORDER BY `idItem`,`idPlan`,`groupid`";

$getPlanPercentil = $cmd->conexion->queryFetch($getPlanPercentilSQL);

if (!$getPlanPercentil) {
    $cmd->logs->error("[QoS] Error al obtener los planes a calcular, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
} 

//Limpiando datos anteriores

$deleteClockDataSQL = "DELETE FROM bm_inform WHERE clock='" . $param->clock . "'";

$deleteClockData = $cmd->conexion->numRow($deleteClockDataSQL);

if ($deleteClockData === false) {
    $cmd->logs->error("[QoS] Error al borrar datos correspondientes a la fecha $clock", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
} 

$insertInform = "INSERT INTO `bm_inform` (`clock`, `groupid`, `idPlan`, `idItem`, `format`, `type`, `value`, `location`) VALUES ";

foreach ($getPlanPercentil as $key => $value) {
    
    $varResult = $value;
    $idPlan = $varResult['idPlan'];
    $idItem = $varResult['idItem'];
    $groupid = $varResult['groupid'];
    unset($varResult['idPlan']);
    unset($varResult['idItem']);
    unset($varResult['groupid']);
    unset($varResult['clock']);
    
    foreach ($varResult as $type => $valueParam) {
        $values[] = "('$param->clock', $groupid, $idPlan, $idItem, 'decimal', '$type', '$valueParam', 0)";
    }   
}

$insertInform = $cmd->conexion->query($insertInform.join(',', $values));

if (!$insertInform) {
    $cmd->logs->error("[QoS] Error al guardar resultado, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
} 

$duracion=date("U")-$dateStart;
$cmd->logs->info("[QoS] Statistics completed in " . $duracion . " seconds for period " . $param->clock);

$cmd->_crontab($var->crontab, "finish");