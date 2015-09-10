<?php

require '/var/www/sites/qoe.baking.cl/core/startup.inc.php';

$fn="/var/www/sites/qoe.baking.cl/bi/logs/bi.log";   
$f = fopen($fn, 'r');
$data=fread($f, filesize($fn));
fclose($f);
$cmd->basic->mail("rodrigo@bsw.cl", "Soporte BSW", "soporte@bsw.cl", "QoE Smart Agent Information ", $data);

?>