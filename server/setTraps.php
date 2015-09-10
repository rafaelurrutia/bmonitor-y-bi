<?php
require '../core/startup.inc.php';
/* 
** BSW
** Copyright (C) 2012-2012 BSW S.A.
**
** This modification allow the user to modify SEC data
**/

$cmd = new Control(true);


$cmd->parametro->remove('STDOUT');

$cmd->parametro->set('STDOUT', true);
 
$cmd->parametro->set('LOGS_FILE', 'logs_traps');

$active = $cmd->_crontab("traps_crontab","start");

if($active) {
	$cmd->logs->info("[traps] Starting OK");
} else {
	$cmd->logs->info("[traps] Starting NOK");
	exit;
}

$cmd->conexion->InicioTransaccion();




$cmd->conexion->commit();
$cmd->_crontab("traps_crontab","finish");
?>