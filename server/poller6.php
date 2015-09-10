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

$controllerPath = APPS_CONTROL . "poller6" . '.php';

if(is_file($controllerPath))
	require $controllerPath;
else {
	header("HTTP/1.0 400");
	echo "Error poller6 not found";
	exit;
}

$poller = new poller6();

// Poller inicial

$hijos = $poller->index();

$totalHijos = count($hijos);
		
if($hijos){

	foreach ($hijos as $key => $hijo) {
		$pid = pcntl_fork();
		
		$i = $key+1;
		
        if (!$pid) {
			$poller->process($hijo["id"], $hijo["param"]);
			exit($i);
        }		
	}

	while (pcntl_waitpid(0, $status) != -1) {
		$status = pcntl_wexitstatus($status);
		$cmd->logs->debug("[POLLER] Hilo numero $status , finalizado",NULL,'logs_poller');
	}
}


// Poller Retry

$hijos = $poller->retry();

$totalHijos = count($hijos);
		
if($hijos){

	foreach ($hijos as $key => $hijo) {
		$pid = pcntl_fork();
		
		$i = $key+1;
		
        if (!$pid) {
			$poller->process($hijo["id"], $hijo["param"]);
			exit($i);
        }		
	}

	while (pcntl_waitpid(0, $status) != -1) {
		$status = pcntl_wexitstatus($status);
		$cmd->logs->debug("[POLLER] Hilo numero $status , finalizado",NULL,'logs_poller');
	}
}
?>