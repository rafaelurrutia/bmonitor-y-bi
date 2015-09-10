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
 
$cmd->parametro->set('LOGS_FILE', 'logs_export');

$cmd->logs->info("[Export] ===============================================");
$cmd->logs->info("[Export] Starting export");
$cmd->logs->info("[Export] Copyright Baking Software 2013");
$cmd->logs->info("[Export] Version 2.0");
$cmd->logs->info("[Export] ===============================================");

$active = $cmd->_crontab("export_crontab","start");

if($active) {
	$cmd->logs->info("[Export] Starting OK");
} else {
	$cmd->logs->info("[Export] Starting NOK");
	exit;
}


if (isset($argv[1]) && isset($argv[2]))
{
	$date1 = $argv[1];
	$date2 = $argv[2];
	
	//Validando fechas
	
	$date1_v = explode('-', $date1);
	
	if(!checkdate($date1_v[1], $date1_v[2], $date1_v[0])) {
		echo "\n Fecha invalida \n";
		$cmd->_crontab("export_crontab","finish");
		exit;
	}

	$date2_v = explode('-', $date2);
	
	if(!checkdate($date2_v[1], $date2_v[2], $date2_v[0])) {
		echo "\n Fecha invalida \n";
		$cmd->_crontab("export_crontab","finish");
		exit;
	}	
	
	$where=" `clock` BETWEEN UNIX_TIMESTAMP('" . $date1_v[0] . "-" . $date1_v[1] . "-". $date1_v[2]. "') AND UNIX_TIMESTAMP('" . $date2_v[0] . "-" . $date2_v[1] . "-". $date2_v[2]."')-1";

	$cmd->logs->info("[Export] Processing period ".$date1_v." TO ".$date2_v);
	
} else {
	echo "\n";
	echo "Param invalid : {SCRIPT} DATE1 DATE2 , ej export.php 2013-01-01 2013-01-10  \n  ";
	$cmd->_crontab("export_crontab","finish");
	exit;
}
