<?php
require '../core/startup.inc.php';
/*
 ** BSW
 ** Copyright (C) 2012-2013 BSW S.A.
 **
 ** This modification allow the user to modify SEC data
 **/

$cmd = new Control(true);

/*
 $cmd->parametro->remove('STDOUT');

 $cmd->parametro->set('STDOUT', true);
 */

$cmd -> parametro -> set('LOGS_FILE', 'logs_export');

$cmd -> logs -> info("[Export] ===============================================");
$cmd -> logs -> info("[Export] Starting export");
$cmd -> logs -> info("[Export] Copyright Baking Software 2013");
$cmd -> logs -> info("[Export] Version 2.0");
$cmd -> logs -> info("[Export] ===============================================");

$active = $cmd -> _crontab("export_crontab", "start");

if ($active) {
    $cmd -> logs -> info("[Export] Starting OK");
} else {
    $cmd -> logs -> info("[Export] Starting NOK");
    exit ;
}

if (isset($argv[1]) && isset($argv[2])) {
    $date1 = $argv[1];
    $date2 = $argv[2];

    //Validando fechas

    $date1_v = explode('-', $date1);

    if (!checkdate($date1_v[1], $date1_v[2], $date1_v[0])) {
        echo "\n Fecha invalida \n";
        $cmd -> _crontab("export_crontab", "finish");
        exit ;
    }

    $date2_v = explode('-', $date2);

    if (!checkdate($date2_v[1], $date2_v[2], $date2_v[0])) {
        echo "\n Fecha invalida \n";
        $cmd -> _crontab("export_crontab", "finish");
        exit ;
    }

    $where = " `clock` BETWEEN UNIX_TIMESTAMP('" . $date1_v[0] . "-" . $date1_v[1] . "-" . $date1_v[2] . "') AND UNIX_TIMESTAMP('" . $date2_v[0] . "-" . $date2_v[1] . "-" . $date2_v[2] . "')-1";

    $cmd -> logs -> info("[Export] Processing period " . $date1 . " TO " . $date2);

} else {
    echo "\n";
    echo "Param invalid : {SCRIPT} DATE1 DATE2 , ej export.php 2013-01-01 2013-01-10  \n  ";
    $cmd -> _crontab("export_crontab", "finish");
    exit ;
}

$m = date("n", strtotime("yesterday"));

switch(TRUE)
{
    case (((int)$m == 1) || ((int)$m == 2) || ((int)$m == 3)) :
        $qy = "Q1";
        $m0 = "01";
        $m1 = "03";
        break;
    case (((int)$m == 4) || ((int)$m == 5) || ((int)$m == 6)) :
        $qy = "Q2";
        $m0 = "04";
        $m1 = "06";
        break;
    case (((int)$m == 7) || ((int)$m == 8) || ((int)$m == 9)) :
        $qy = "Q3";
        $m0 = "07";
        $m1 = "09";
        break;
    case (((int)$m == 10) || ((int)$m == 11) || ((int)$m == 12)) :
        $qy = "Q4";
        $m0 = "10";
        $m1 = "12";
        break;
}

//Selected period
$period_start = strtotime(date('Y') . '-' . $m0 . "-01");

$period_1 = strtotime($date1);
$period_2 = strtotime($date2);

if ($period_1 < $period_2) {
    if ($period_1 >= $period_start) {
        $table = 'bm_q';
    } else {
        $table = 'bm_history';
    }
} elseif ($period_1 > $period_2) {
    if ($period_2 >= $period_start) {
        $table = 'bm_q';
    } else {
        $table = 'bm_history';
    }
}

//Set param

$cmd -> conexion -> query("SET SESSION group_concat_max_len = 10000");

//Get host
$getHostID = $cmd -> conexion -> queryFetch('SELECT GROUP_CONCAT(`id_host`) as HostID FROM `bm_host` WHERE `borrado` = 0');

if (!$getHostID) {
    echo "Error al obtener el listado de sondas activas";
    $cmd -> _crontab("export_crontab", "finish");
} else {
    $HostID = $getHostID[0]['HostID'];
}

$path = SITE_PATH . "upload/export_" . $date1 . "_" . $date2 . ".csv";

if (file_exists($path)) {
    unlink($path);
}

$get_export_sql = 'SELECT HG.`name` AS Grupo ,  H.`host` AS Sonda, P.`plan` AS Plan, IT.`description`, FROM_UNIXTIME(HI.`clock`,"%d-%m-%Y %H:%i:%s") as fecha, HI.`value`, HI.`valid`
FROM `' . $table . '` HI
	LEFT JOIN `bm_host`  H ON HI.`id_host`=H.`id_host`
	LEFT JOIN `bm_items` IT ON HI.`id_item`=IT.`id_item`
	LEFT JOIN `bm_plan` P ON P.`id_plan`=H.`id_plan`
	LEFT JOIN `bm_host_groups` HG ON H.`groupid`=HG.`groupid`
WHERE
	HI.`id_item` IN (1,10,14,141,27,32,37,41,48,54,58,62,69,75) AND
	HI.`id_host`  IN (' . $HostID . ') AND ' . $where . " INTO OUTFILE '$path' FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '\"' LINES TERMINATED BY '\n'";

$valid = $cmd -> conexion -> query($get_export_sql);

if ($valid) {
    echo $path . "\n";
} else {
    echo "Error al generar archivo, analizar logs \n";
}

$cmd -> _crontab("export_crontab", "finish");
?>