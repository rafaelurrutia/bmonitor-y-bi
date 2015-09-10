<?php
require '../core/startup.inc.php';
/* 
** BSW
** Copyright (C) 2000-2012 BSW S.A.
**
** This modification allow the user to modify SEC data
**/

$controllerPath = APPS_CONTROL . 'admin_import.php';

require $controllerPath;

$cmd = new Control();

$cmd->parametro->remove('STDOUT');

$cmd->parametro->set('STDOUT', true);

$cmd->parametro->set('LOGS_FILE', 'logs_import');

$admin_import = new admin_import();

$admin_import->import();
?>