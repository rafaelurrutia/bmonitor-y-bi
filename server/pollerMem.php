<?php
$sitePath = realpath(dirname(__FILE__).  '/../' ) . "/";
require $sitePath.'core/startup.inc.php';
/* 
** BSW
** Copyright (C) 2012-2012 BSW S.A.
**
** This modification allow the user to modify SEC data
**/

$cmd = new Control(true);

$cmd->parametro->set('LOGS_FILE', 'logs_poller8');

$cmd->parametro->remove('STDOUT');

$cmd->parametro->set('STDOUT', false);


$cmd->logs->info("[Poller] ===============================================");
$cmd->logs->info("[Poller] Starting Statistic Server");
$cmd->logs->info("[Poller] Copyright Baking Software 2013");
$cmd->logs->info("[Poller] Version 1");
$cmd->logs->info("[Poller] ===============================================");


$memcache = new Memcached;

$memcache->addServer('localhost', 11211) or exit ('Server Error: memcached down.');
    
$serial = $argv[1].'_'.$argv[2];

$result['result'] = 'mono'.$serial;

$result['finished'] = true;

$cmd->logs->info("Guardando con la serial: ",$serial);

sleep(10);

$memcache->add($serial,$result,1);

if($memcache->getResultCode() == Memcached::RES_SUCCESS){
    echo "OK";
} else {
    echo "NOK";
}
?>