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
$cmd->logs->info("[QoS] Version 3");
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

$createTable = "CREATE TEMPORARY TABLE `tmp_hostid_itemid` (
  `id_tmp` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_host` int(11) unsigned NOT NULL DEFAULT '0',
  `id_plan` int(11) unsigned NOT NULL,
  `groupid` int(11) unsigned NOT NULL,
  `id_item` int(11) unsigned NOT NULL,
  `type` enum('personalized','download','upload') NOT NULL DEFAULT 'personalized',
  `order` enum('DESC','ASC') NOT NULL DEFAULT 'DESC',
  `nominal` int(11) DEFAULT NULL,
  `warning` int(11) DEFAULT NULL,
  `critical` int(11) DEFAULT NULL,
  `nacD` int(11) unsigned NOT NULL,
  `nacU` int(11) unsigned NOT NULL,
  `location` varchar(255)  DEFAULT '0',
  PRIMARY KEY (`id_tmp`),
  KEY `IDIH` (`id_item`,`id_host`)
);";

$temporaryTableCreate = $cmd->conexion->query($createTable);

$cmd->conexion->query("UPDATE `tmp_hostid_itemid` SET `location` = '0|0' WHERE `location`  IS NULL;");

if (!$temporaryTableCreate) {
    $cmd->logs->error("[QoS] Error create temporary table, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

//New Method 

$getLocationFilter = $cmd->conexion->queryFetch("SELECT H.`id_host`, H.`host` ,  GROUP_CONCAT(CONCAT(HD.`id_feature`,':', HD.`value`) ORDER BY `id_feature`) as 'location'
FROM `bm_host` H 
LEFT OUTER JOIN `bm_host_detalle` HD ON H.`id_host`=HD.`id_host`
WHERE H.`borrado` = 0  AND HD.`id_feature` IN (78,79,82,3)
GROUP BY H.`id_host`;");

if (!$getLocationFilter) {
    $cmd->logs->error("[QoS] Error get location value, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$method = 2;
$resultLocation = array();
foreach ($getLocationFilter as $key => $value) {
    $location = explode(',', $value['location']);
    switch ($method) {
        //separate host name
        case 1:
            $hostname = preg_replace('[\s+]',"", $value['host']);
            $hostname = trim($hostname);
            $resultLocation[] = "(".$value['id_host'].",'0|".$hostname."')";
            break;
        //separate for location
        case 2:
            $locationList = array(78 => 0, 79 => 0, 82 => '');
            foreach ($location as $keyLocation => $valueLocation) {
                list($idFeauture,$valueFeature) = explode(':',$valueLocation);
                if($valueFeature != '' && !is_null($valueFeature)) {
                    $locationList[$idFeauture] = $valueFeature;
                }
            }
            
            if($locationList[82] != '' && $locationList[79] > 0)  {
                $resultLocation[] = "(".$value['id_host'].",'".$locationList[79]."|".$locationList[82]."')";
            } elseif ($locationList[82] != '' && $locationList[78] > 0) {
                $resultLocation[] = "(".$value['id_host'].",'".$locationList[78]."|".$locationList[82]."')";
            } elseif ( $locationList[82] != '') {
                $resultLocation[] = "(".$value['id_host'].",'0|".$locationList[82]."')";
            } else {
                $resultLocation[] = "(".$value['id_host'].",'".$locationList[78]."|".$locationList[79]."')";
            }

            break;
        //Error method
        default:
            $cmd->logs->error("[QoS] Error method separate, ", NULL);
            $cmd->_crontab($var->crontab, "finish");
            exit ;            
            break;
    }  
}

$resultLocation = array_unique($resultLocation);

//Create Temporary table location:

$createTableLocationTemporarySQL = "CREATE TEMPORARY TABLE `tmp_locationHost` (
  `id_host` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `location` varchar(244) DEFAULT NULL,
  PRIMARY KEY (`id_host`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

$createTableLocationTemporaryRESULT = $cmd->conexion->query($createTableLocationTemporarySQL);

if (!$createTableLocationTemporaryRESULT) {
    $cmd->logs->error("[QoS] Error create temporary location table, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$insertTableLocationTemporarySQL = "INSERT INTO `tmp_locationHost` (`id_host`, `location`)
VALUES ".join(',', $resultLocation);

var_dump($insertTableLocationTemporarySQL);

$insertTableLocationTemporaryRESULT = $cmd->conexion->query($insertTableLocationTemporarySQL);

  
$getHostValid = $cmd->conexion->query("INSERT INTO `tmp_hostid_itemid` 
SELECT null,H.`id_host`,H.`id_plan`,H.`groupid`,IG.`id_item`, TH.`type`, 
    IF( TH.`nominal` > TH.`warning`,'DESC','ASC') as 'order' , 
    IF(TH.`nominal` = -1 OR TH.`nominal` = -2, 130,TH.`nominal` ) AS `nominal`,TH.`warning` ,TH.`critical`, 
    ( PL.`nacD` * 1024 ) as 'nacD',
    (PL.`nacU`*1024) as 'nacU',
    LH.`location`
FROM `bm_host` H 
LEFT JOIN `bm_items_groups` IG ON IG.`groupid` = H.`groupid`
LEFT JOIN `bm_plan` PL ON PL.`id_plan`=H.`id_plan`
    INNER JOIN `bm_threshold` TH ON IG.`id_item`=TH.`id_item`
    LEFT OUTER JOIN `tmp_locationHost` LH ON LH.`id_host`=H.`id_host`
WHERE
    H.`borrado`=0  AND 
    H.`id_plan` > 0 AND 
    TH.`report` = 'true' 
GROUP BY `groupid`,`id_host`,`id_item`");

if (!$getHostValid) {
    $cmd->logs->error("[QoS] Error al obtener las sondas, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$getHostIDResult = $cmd->conexion->queryFetch(" SELECT  DISTINCT `id_host` FROM `tmp_hostid_itemid`");

if ($getHostIDResult) {
    foreach ($getHostIDResult as $key => $value) {
        $hostID[] = $value['id_host'];
    }
} else {
    $cmd->logs->error("[QoS] Error al obtener el id de los host , ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}


$getItemIDResult = $cmd->conexion->queryFetch("SELECT  DISTINCT `id_item` FROM `tmp_hostid_itemid`");

if ($getItemIDResult) {
    foreach ($getItemIDResult as $key => $value) {
        $itemID[] = $value['id_item'];
    }
} else {
    $cmd->logs->error("[QoS] Error al obtener el id de los items, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$PERCENTILE_REPORT_QOE = json_decode($cmd->parametro->get('PERCENTILE_REPORT_QOE','[]'));

if(count($PERCENTILE_REPORT_QOE) > 1){
    foreach ($PERCENTILE_REPORT_QOE as $key => $value) {
    	$PERCENTILE[] = '0 as PERCENTIL_'.$value;
    }   
    $PERCENTILE = join(',', $PERCENTILE);
} else {
    $PERCENTILE = '0 as PERCENTIL_5,
    0 as PERCENTIL_95,
    0 as PERCENTIL_80';
}

//Get var  y cosas
$cmd->basic->timeStart();

$getVarLocation = "CREATE TEMPORARY TABLE tmp_statsQoS AS SELECT 
    THI.`groupid`, THI.`id_item` as 'idItem' , THI.`id_plan` as 'idPlan', IF(THI.`location` IS NULL,'0|0',THI.`location`) as 'location' , 
    MIN(H.`id_history`) As 'minHistory',
    DATE_FORMAT(from_unixtime(clock),'" . $param->grp . "') as 'DATE',
    round(avg(IF(`valid` = 1,`value`,NULL)),2) as AVG,
    round(min(IF(`valid` = 1,`value`,NULL)),2) as MIN,
    round(max(IF(`valid` = 1,`value`,NULL)),2) as MAX,
    round(stddev_samp(IF(`valid` = 1,`value`,NULL)),2) as STD_SAMP,
    round(stddev(IF(`valid` = 1,`value`,NULL)),2) as STD,
    (`nacD`*THI.nominal/100) as 'DOWNLOAD',
    (`nacU`*THI.nominal/100) as 'UPLOAD',
    case THI.`type` 
        when 'personalized' then 
                IF(THI.nominal > THI.warning, 
                        SUM(IF(H.`value` <=  THI.nominal AND H.`value` >  THI.warning ,1,0)) , 
                        SUM(IF(H.`value` >=  THI.nominal AND H.`value` <  THI.warning ,1,0)))   
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
        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.warning/100) && H.`value` >  (`nacD`*THI.critical/100),1,0))
        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.warning/100) && H.`value` >  (`nacU`*THI.critical/100),1,0))
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
    $PERCENTILE
FROM tmp_hostid_itemid THI
LEFT JOIN `bm_history` H ON THI.`id_host`=H.`id_host` AND THI.`id_item`=H.`id_item`
WHERE
    H.`id_item` IN (".join(',', $itemID).") AND
    H.`id_host` IN (".join(',', $hostID).") AND
    $param->where AND
    H.`valid`=1 AND 
    H.`value` > 0  AND
    THI.`location` IS NOT NULL  
GROUP BY
    THI.`groupid`,
    THI.`id_item`,
    THI.`id_plan`,
    THI.`location`,
    `DATE`";

$getVarValidLocation = $cmd->conexion->query($getVarLocation);

if (!$getVarValidLocation) {
    $cmd->logs->error("[QoS] Error al calcular Location statsQoS, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$getVar = "INSERT INTO `tmp_statsQoS` SELECT 
    THI.`groupid`, THI.`id_item` as 'idItem' , THI.`id_plan` as 'idPlan', 0 as `location`,
    MIN(H.`id_history`) As 'minHistory',
    DATE_FORMAT(from_unixtime(clock),'" . $param->grp . "') as 'DATE',
    round(avg(IF(`valid` = 1,`value`,NULL)),2) as AVG,
    round(min(IF(`valid` = 1,`value`,NULL)),2) as MIN,
    round(max(IF(`valid` = 1,`value`,NULL)),2) as MAX,
    round(stddev_samp(IF(`valid` = 1,`value`,NULL)),2) as STD_SAMP,
    round(stddev(IF(`valid` = 1,`value`,NULL)),2) as STD,
    (`nacD`*THI.nominal/100) as 'DOWNLOAD',
    (`nacU`*THI.nominal/100) as 'UPLOAD',
    case THI.`type` 
        when 'personalized' then 
                IF(THI.nominal > THI.warning, 
                        SUM(IF(H.`value` <=  THI.nominal AND H.`value` >  THI.warning ,1,0)) , 
                        SUM(IF(H.`value` >=  THI.nominal AND H.`value` <  THI.warning ,1,0))) 
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
        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.warning/100) && H.`value` >  (`nacD`*THI.critical/100),1,0))
        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.warning/100) && H.`value` >  (`nacU`*THI.critical/100),1,0))
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
    $PERCENTILE 
FROM tmp_hostid_itemid THI
LEFT JOIN `bm_history` H ON THI.`id_host`=H.`id_host` AND THI.`id_item`=H.`id_item`
WHERE
    H.`id_item` IN (".join(',', $itemID).") AND
    H.`id_host` IN (".join(',', $hostID).") AND
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

$getMinHistory = $cmd->conexion->queryFetch("SELECT MIN(minHistory) as 'minHistory' FROM tmp_statsQoS;");

if (!$getMinHistory ||  !is_numeric($getMinHistory[0]['minHistory']) || $getMinHistory[0]['minHistory'] < 1) {
    $cmd->logs->error("[QoS] Error get min id History, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
} else {
    $minHistory = $getMinHistory[0]['minHistory'];
}

//Calculando percentil

function calPercentil($id_item, $id_plan, $groupid, $location, $where, $cmd)
{
        
    $PERCENTILE_TYPE_REPORT_QOE = $cmd->parametro->get('PERCENTILE_TYPE_REPORT_QOE',0);
    
    if($PERCENTILE_TYPE_REPORT_QOE == 0) {
        $percentileType = 'INC';
    } else {
        $percentileType = 'EXC';
    }
    
    $PERCENTILE_REPORT_QOE = json_decode($cmd->parametro->get('PERCENTILE_REPORT_QOE','[]')); 
    
    $per = array();
    
    if(strlen(trim($location))  < 2){
        $sql = "SELECT value FROM tmp_percentil WHERE id_item = '" . $id_item . "' AND id_plan='" . $id_plan . "' AND " . $where . " AND groupid=" . $groupid . " order by value";
    } else {
        
        $sql = "SELECT value FROM tmp_percentil WHERE id_item = '" . $id_item . "' AND id_plan='" . $id_plan . "' AND " . $where . " AND groupid=" . $groupid . " AND location='" . $location . "' order by value";
    }
    
    $res = $cmd->conexion->queryFetch($sql);
    
    if ($res) {

        foreach ($PERCENTILE_REPORT_QOE as $key => $value) {
            $perValue = $cmd->basic->getPercentil($res, $value, $percentileType, TRUE, 'value');
            if($perValue !== FALSE) {
                $per[$value] = $perValue;
            } else {
                $per[$value] = 0;
            }
        }
       // $cmd->logs->info("[QoS] calculate percentil for location: $location and item:$id_item ", $per);
        return $per;
    } else {
        return false;
    }

}

$cmd->basic->timeStart();
 
$getValuePercentil = "CREATE TEMPORARY TABLE tmp_percentil SELECT H.`clock`,  TMP.`groupid`, TMP.`id_plan`, TMP.`id_item`, TMP.`location`,H.`value`
FROM tmp_hostid_itemid TMP
LEFT JOIN  `bm_history` H  ON TMP.`id_host`=H.`id_host` AND TMP.`id_item`=H.`id_item`
WHERE H.`id_history` >= $minHistory AND  $param->where
ORDER BY H.`value`;";

$getValuePercentilValid = $cmd->conexion->query($getValuePercentil);

if (!$getValuePercentilValid) {
    $cmd->logs->error("[QoS] Error al obtener los datos del history, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
}

$duration = $cmd->basic->timeEnd();
$cmd->logs->info("[QoS] Temporary table tmp_percentil created in $duration  seconds", NULL);

$getPlanPercentilSQL = "SELECT * FROM `tmp_statsQoS` WHERE `DATE`='" . $param->clock . "' ORDER BY `groupid`,`idPlan`,`idItem`";

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
    $idPlan = $value["idPlan"];
    $iditem = $value["idItem"];
    $location = $value["location"];

    $percentil = calPercentil($iditem, $idPlan, $groupid,$location, $param->where, $cmd);
    
    $PercentilArray = array();
    
    foreach ($percentil as $pkey => $pvalue) {
        $PercentilArray[] = "`PERCENTIL_$pkey`='".$pvalue."'";
    }
    
    $updatePercentil="UPDATE tmp_statsQoS SET ".join(',', $PercentilArray)." WHERE groupid=" . $groupid .
        " AND DATE='" . $param->clock . "'" .
        " AND idPlan='" . $idPlan . "'" .
        " AND idItem='$iditem'" .
        " AND location='$location'";
  
    $cmd->conexion->query($updatePercentil);

    $duration = $cmd->basic->timeEnd();
    $cmd->logs->info("[QoS] Processing statistics " . $param->clock  . " [" . $groupid . "] " . $idPlan . "(" . ($startN) . " of " . $countN . ") in " . $duration . " seconds");
    $startN++;
}

$getPlanPercentilSQL = "SELECT * FROM `tmp_statsQoS` WHERE `DATE`='" . $param->clock . "' ORDER BY `groupid`,`idPlan`,`idItem`";

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
    $location = $varResult["location"];
    unset($varResult['idPlan']);
    unset($varResult['idItem']);
    unset($varResult['groupid']);
    unset($varResult['clock']);
    unset($varResult['location']);
    foreach ($varResult as $type => $valueParam) {
        $values[] = "('$param->clock', $groupid, $idPlan, $idItem, 'decimal', '$type', '$valueParam','$location')";
    }   
}

$insertInform = $cmd->conexion->query($insertInform.join(',', $values),false,'logs_qoe');

if (!$insertInform) {
    $cmd->logs->error("[QoS] Error al guardar resultado, ", NULL);
    $cmd->_crontab($var->crontab, "finish");
    exit ;
} 

$duracion=date("U")-$dateStart;
$cmd->logs->info("[QoS] Statistics completed in " . $duracion . " seconds for period " . $param->clock);

$cmd->_crontab($var->crontab, "finish");