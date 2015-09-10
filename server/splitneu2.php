<?php
require '../core/startup.inc.php';
/*
 ** BSW
 ** Copyright (C) 2000-2005 BSW S.A.
 **
 ** This modification allow the user to modify SEC data
 **/

$cmd = new Control(true,'bmonitor.baking.cl',true);

//$cmd->parametro->remove('STDOUT');
//$cmd->parametro->set('STDOUT', true);

$longopts = array(
    "debug::",
    "loadata::",
    "notdelete::",
    "deleteCycle::"
);

$param = FALSE;

$options = getopt("d::h::Q:", $longopts);

if (isset($options['d'])) {
    $cmd->parametro->remove('STDOUT');
    $cmd->parametro->set('STDOUT', true);
    echo "MODO DEBUG ON\n";
}

if (isset($options['h'])) {
    var_dump($options);
    exit ;
}

$cmd->logs->info("[SPLIT] ===============================================", false, 'logs_neutralidad');
$cmd->logs->info("[SPLIT] Starting selecion of data for NEUTRALIDAD", false, 'logs_neutralidad');
$cmd->logs->info("[SPLIT] Copyright Baking Software 2011", false, 'logs_neutralidad');
$cmd->logs->info("[SPLIT] Version 1.1.darkside", false, 'logs_neutralidad');
$cmd->logs->info("[SPLIT] ===============================================", false, 'logs_neutralidad');

$active = $cmd->_crontab("splitneu_crontab", "start", true);

if ($active) {
    $cmd->logs->info("[SPLIT] Starting OK", false, 'logs_neutralidad');
} else {
    $cmd->logs->info("[SPLIT] Starting NOK", false, 'logs_neutralidad');
    exit ;
}

$d1 = date("U");

$quarter = ceil(date("n", strtotime("yesterday")) / 3);

$m = '';

if (isset($options['Q']) && is_numeric($options['Q']) && ($options['Q'] > 0 && $options['Q'] < 5)) {
    $quarter = (int)$argv[2];
    echo "Ocupando Q" . $quarter . "\n";
    $param = TRUE;
    //Limpiando Datos
    $getHostItem = $cmd->conexion->query("TRUNCATE TABLE `bm_q`;");
}

switch($quarter) {
case 1 :
    $m = '01';
    break;
case 2 :
    $m = '03';
    break;
case 3 :
    $m = '06';
    break;
case 4 :
    $m = '09';
    break;
}

//$y=date("Y",time()-3600);
$y = date("Y", strtotime("yesterday"));

$dt = $y . "-" . $m . "-01";

$dt_utime = strtotime("$dt");

$cmd->logs->info("[SPLIT] Period " . $dt . " (Q" . $quarter . ")", false, 'logs_neutralidad');

$getHostItem_sql = "SELECT H.`id_host`,IP.`id_item`
	FROM `bm_item_profile` IP LEFT JOIN `bm_items` I USING(`id_item`) 
		LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host` 
		LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=H.`groupid`
WHERE I.`description` in ('NACBandwidthdown','NACBandwidthup','LOCBandwidthdown','LOCBandwidthup','INTBandwidthdown','INTBandwidthup','NACPINGavg','LOCPINGavg','INTPINGavg','IPLOGINRENEW','INTPINGstd','LOCPINGstd','NACPINGstd','INTPINGlost','LOCPINGlost','NACPINGlost','INTPINGstdjitter','LOCPINGjitter','NACPINGjitter','Availability.bsw') AND ( I.`description` = 'Availability.bsw' OR  HG.`type`='NEUTRALIDAD') ORDER BY H.`id_host`,IP.`id_item`;";

$getHostItem = $cmd->conexion->queryFetch($getHostItem_sql);

if ($getHostItem) {

    foreach ($getHostItem as $key => $host) {
        $hostIN[] = $host["id_host"];
        $itemsIN[] = $host["id_item"];
    }

    $hostIN = $cmd->conexion->arrayToIN($hostIN);
    $itemsIN = $cmd->conexion->arrayToIN($itemsIN);

    $getHostItem_total = count($getHostItem);

    $cmd->logs->info("[SPLIT] Period " . $dt_utime . " (Q" . $quarter . ")", false, 'logs_neutralidad');

    //Limpiando Datos no utilizados

    if (isset($options['deleteCycle'])) {
        
        $cmd->logs->info("[SPLIT] Start cycle deleted from bsw_q table (finish)", false, 'logs_neutralidad');

        $limit = 500000;
        $cycle = 40;

        $finished = false;
        $continue = true;
        $countRetry = 0;

        $getMinID = $cmd->conexion->queryFetch("SELECT min(`id_history`) As minHistory  FROM `bm_q`;");

        if ($getMinID) {
            $MinID = $getMinID[0]['minHistory'];

            $getMaxID = $cmd->conexion->queryFetch("SELECT max(`id_history`) As maxHistory  FROM `bm_q`;");

            if ($getMaxID) {
                $MaxID = $getMaxID[0]['maxHistory'];

                $cycleRange = ($MaxID - $MinID) / $cycle;

            } else {
                $continue = false;
            }

        } else {
            $continue = false;
        }

        if ($continue) {
            $idHistory = $MinID;
            while (!$finished) {
                $idHistory = $idHistory + $cycleRange;

                $getClockSelectSQL = "SELECT `id_history`, `clock` FROM `bm_q` WHERE `id_history` <= $idHistory  ORDER BY `id_history` DESC LIMIT 1 ;";

                $getClockSelect = $cmd->conexion->queryFetch($getClockSelectSQL);

                if ($getClockSelect) {
                    
                    $clockSelect = $getClockSelect[0]['clock'];
                    $idHistory = $getClockSelect[0]['id_history'];
                    
                    if($clockSelect <= $dt_utime) {
                            
                        $deleteCacheHistory_sql = "DELETE FROM `bm_q` WHERE `id_history` < $idHistory";
                        
                        $deleteCacheHistory = $cmd->conexion->numRow($deleteCacheHistory_sql);
                           
                        if($deleteCacheHistory !== false) {
                            $cmd->logs->info("[SPLIT] " . $deleteCacheHistory . "  records deleted from bsw_q table (complete) lower id $idHistory", false, 'logs_neutralidad');
                        }
                        
                    } else {
                        $deleteCacheHistory_sql = "DELETE FROM `bm_q` WHERE `id_history` < $idHistory AND `clock` < $dt_utime";
                        
                        $deleteCacheHistory = $cmd->conexion->numRow($deleteCacheHistory_sql);
                           
                        if($deleteCacheHistory !== false) {
                         
                            $cmd->logs->info("[SPLIT] " . $deleteCacheHistory . "  records deleted from bsw_q table (finish) lower id $idHistory", false, 'logs_neutralidad');
                            $countRetry++;
                        }
                    }
                    
                } else {
                    $countRetry++;
                }
                
                if($countRetry > 1) {
                    $finished = true;
                } 
            }
        }
    } elseif (!isset($options['notdelete'])) {
        
        $deleteCacheHistory_sql = "DELETE FROM bm_q WHERE clock < $dt_utime";

        $deleteCacheHistory_prepare = $cmd->conexion->prepare($deleteCacheHistory_sql);

        $deleteCacheHistory_prepare->execute();

        $deleteCacheHistory_start = $deleteCacheHistory_prepare->rowCount();

        $cmd->logs->info("[SPLIT] " . $deleteCacheHistory_start . "  records deleted from bsw_q table (start)", false, 'logs_neutralidad');

    }
    //Obtenido el ultimo history_id ingresado

    if ($param == FALSE) {
        $idStartHistory_sql = "SELECT MAX(`id_history`) as id_history FROM `bm_q` ORDER BY `id_history` DESC LIMIT 1";
        $idStartHistory = $cmd->conexion->queryFetch($idStartHistory_sql);
        $getID = (int)$idStartHistory[0]['id_history'];
        if ($getID == '') {
            $getID = 0;
        }
    } else {
        $getID = 0;
    }

    $cmd->logs->info("[SPLIT] ID 1 entregado: " . $getID, false, 'logs_neutralidad');

    $n = 1;
    $suma = 0;

    if ($getID === 0) {
        $getID_sql = "SELECT MIN(`id_history`) as `id_history`  FROM `bm_history`  WHERE  `id_item`=1 AND `id_host` IN $hostIN   AND `clock` >= $dt_utime LIMIT 1";
        $getID_result = $cmd->conexion->queryFetch($getID_sql, 'logs_neutralidad');

        if ($getID_result) {
            $getID = $getID_result[0]['id_history'];
        } else {
            $cmd->_crontab("splitneu_crontab", "finish");
            exit ;
        }
    }
	
	if(!is_numeric($getID)){
		$getID = 0;
	}

    $cmd->logs->info("[SPLIT] Insertando history desde $getID", false, 'logs_neutralidad');

    //Ejecutando query:

    if (isset($options['loadata'])) {
        $cmd->logs->info("Ocupando Load DATA ", NULL, 'logs_neutralidad');

        if (file_exists('/tmp/neutralidadSPLIT.csv')) {
            try {
                unlink('/tmp/neutralidadSPLIT.csv');
            } catch (Exception $e) {
                $cmd->logs->error("[SPLIT] Error al borrar archivo  /tmp/neutralidadSPLIT.csv", $e->getMessage(), 'logs_neutralidad');
            }
        }

        $select_sql = "SELECT * FROM `bm_history` WHERE `id_history` > $getID AND `id_host` IN $hostIN AND `id_item` IN $itemsIN INTO OUTFILE '/tmp/neutralidadSPLIT.csv'";

        $valid = $cmd->conexion->query($select_sql);

        if ($valid) {
            $insert_file = "LOAD DATA INFILE '/tmp/neutralidadSPLIT.csv' INTO TABLE `bm_q`";
            $insertHistory = $cmd->conexion->numRow($insert_file);
        } else {
            $insertHistory = false;
        }

    } else {
        $insertHistory_sql = "INSERT IGNORE INTO `bm_q` SELECT * FROM `bm_history` WHERE `id_history` > $getID AND `id_item` IN $itemsIN AND `id_host` IN $hostIN";
        $insertHistory = $cmd->conexion->numRow($insertHistory_sql);
    }

    if ($insertHistory !== false) {
        $cmd->logs->info("[SPLIT] 100%  " . $insertHistory . " records added", false, 'logs_neutralidad');
    } else {
        $cmd->logs->error("Error al ejecutar el script splitneu.php");
        $insertHistory = 0;
    }

    $d = date("U") - $d1;
    $cmd->logs->info("[SPLIT] done in " . $d . " seconds " . $insertHistory . " records added", false, 'logs_neutralidad');
}
if (file_exists('/tmp/neutralidadSPLIT.csv')) {
    try {
        unlink('/tmp/neutralidadSPLIT.csv');
    } catch (Exception $e) {
        $cmd->logs->error("[SPLIT] Error al borrar archivo  /tmp/neutralidadSPLIT.csv", $e->getMessage(), 'logs_neutralidad');
    }
}
$cmd->_crontab("splitneu_crontab", "finish");
?>

