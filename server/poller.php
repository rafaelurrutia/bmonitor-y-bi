<?php
/* 
** BSW
** Copyright (C) 2012-2013 BSW S.A.
**
** This modification allow the user to modify SEC data
**/

date_default_timezone_set('America/Guayaquil'); 

$sitePath = realpath(dirname(__FILE__).  '/../' ) . "/";

require $sitePath.'core/startup.inc.php';

$cmd = new Control(true);

list($idItem,$idHost) = explode('_', $argv[2]);

$serial = $argv[1].'_'.$argv[2];

$cmd->parametro->set('LOGS_FILE', 'logs_poller8');

$memcache = new Memcached('poller_memcached');

$connect = $memcache->addServer('localhost', 11211);

if($connect) {
   $cmd->logs->info("[POLLER][$idHost][$idItem] Memcached OK"); 
} else {
   $cmd->logs->error("[POLLER][$idHost][$idItem] Memcached NOK");
   exit;
}

$data = (object)$memcache->get("poller_".$argv[2]);

if(!isset($idItem) || $idItem == '' ){
    $cmd->logs->error("Error al detectar el id: ",$argv[2]);
    liberando($memcache,$idHost,$idItem,$serial,"Error parametros invalidos",$cmd);
} elseif (!isset($data->idHost) || $data->idHost == '' ) {
    $cmd->logs->warning("[".$argv[3]."]Error al detectar el host $idHost en la memoria: ",$data);
    $cmd->logs->debug("[".$argv[3]."]Recoletando nuevamente la data del host con el id:",$idHost);
    
    $get_item_sql = "SELECT 
            I.`id_item` As idItem , 
            H.`id_host` AS idHost,  
            H.`dns`, 
            H.`host`,
            H.`ip_wan` As ipWan,
            I.`id_item` As idItem, 
            I.`description` ,
            I.`type_item` As typeItem,
            G.`feriados`,
            G.`horario`,
            I.`snmp_community` As snmpCommunity,
            I.`snmp_port` As snmpPort,
            I.`snmp_oid` As snmpOid, 
            G.`snmp_monitor`,
            unix_timestamp(now()) as `utime`,
            IF(PA.`uptime` IS NULL, unix_timestamp(DATE_FORMAT(NOW(),'%Y-%m-%d %k:%i:00')),PA.`uptime`) As uptime,
            IF(G.`delay_bsw` IS NULL or G.`delay_bsw` = 0,I.`delay`,G.`delay_bsw`) AS delay,
            DATE_ADD(PA.`fechahora`,INTERVAL  IF(G.`delay_bsw` IS NULL or G.`delay_bsw` = 0,I.`delay`,G.`delay_bsw`) SECOND) as 'nextcheck'
        FROM `bm_host` H
        LEFT JOIN `bm_host_groups` G ON G.`groupid`=H.`groupid`
        LEFT JOIN `bm_items_groups` IG ON IG.`groupid`=H.`groupid`
        LEFT JOIN `bm_items` I ON I.`id_item`=IG.`id_item`
        LEFT OUTER JOIN `bm_poller_active` PA ON (PA.`id_host`=H.`id_host` AND  PA.`id_item`=I.`id_item`)
        LEFT OUTER JOIN `bm_item_profile` IP ON (IP.`id_host`=H.`id_host` AND IP.`id_item`=IG.`id_item`) 
        WHERE 
                I.`id_item` = $idItem AND
                H.`id_host` = $idHost";
                
     

    $get_item_result = $cmd->conexion->queryFetch($get_item_sql);
    
    if($get_item_result){
                
        $data = (object)$get_item_result[0];
        $data->type = $argv[3];
    
        if(!isset($data->nextcheck) || $data->nextcheck == '') {
            liberando($memcache,$idHost,$idItem,$serial,"Error al obtener objeto en memoria",$cmd);
            exit;          
        }

    } else {
        liberando($memcache,$idHost,$idItem,$serial,"Error al obtener objeto en memoria",$cmd);
        exit; 
    }
}

$cmd->logs->info("[POLLER][$idHost][$idItem] Loading ".$argv[3]);


//Funciones

function filter($description, $dns, $valor , $cmd)
{
    switch ($description) {
        case 'Availability.bsw':
            if(preg_match("/$dns/i",$valor)){
                $cmd->logs->debug("Filtro  encontrado, se tranforma valor inicial en 1, $dns ->  $valor");
                return '1';
            } else {
                $cmd->logs->debug("Filtro  encontrado, se tranforma valor inicial en 0, $dns ->  $valor");
                return '0';
            }
        break;

        case 'Availability-ForTrap.bsw':
            if(preg_match("/$dns/i",$valor)){
                $cmd->logs->debug("Filtro  encontrado, se tranforma valor inicial en 1, $dns ->  $valor");
                return '1';
            } else {
                $cmd->logs->debug("Filtro  encontrado, se tranforma valor inicial en 0, $dns ->  $valor");
                return '0';
            }
        break;
            
        default:
            return false;
        break;
    }   
}


//Iniciando carga

switch ($data->typeItem) {
    case 'float':
        $tableHistory="bm_history";
    break;
    
    case 'string':
        $tableHistory="bm_history_str";
    break;
    
    case 'text':
        $tableHistory="bm_history_text";
    break;
    
    case 'log':
        $tableHistory="bm_history_uint";
    break;
                    
    default:
        $tableHistory="bm_history";
    break;
}

$version = $cmd->parametro->get("SNMP_VERSION",'2c');

if($data->type === 'start') {
    $timeout = $cmd->parametro->get("SNMP_TIMEOUT",1000000);
    $retry = $cmd->parametro->get("SNMP_RETRY",3);   
} else {
    $timeout = $cmd->parametro->get("SNMP_TIMEOUT_RETRY",1000000);
    $retry = $cmd->parametro->get("SNMP_RETRY_RETRY",3);       
}

$ipDestiny = $data->ipWan.":".$data->snmpPort;
$continue  = true;

//Validacion de Horario
$c_schedule = $cmd->basic->cdateObj($data->horario, $data->feriados);

if (($c_schedule == FALSE) || (is_object($c_schedule))) {

    if (isset($c_schedule->nexcheck)) {
        $data->nextcheck = $c_schedule->nexcheck;
    }

    $update_profile = "INSERT INTO `bm_item_profile` 
                            ( `id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, 
                                    `nextcheck`, `lastclock`, `lastvalue`, `prevvalue`, `error`, `status`)
                        VALUES (".
                            $data->idItem."," . 
                            $data->idHost.", '".
                            $data->snmpOid."', ".
                            $data->snmpPort.", '".
                            $data->snmpCommunity."', '".$data->nextcheck."' , UNIX_TIMESTAMP(), 0, 0, 'Fuera de horario', 'error') 
                            ON DUPLICATE KEY UPDATE `error`='Fuera de horario' ,  
                                                    `status`='error' ,
                                                    `nextcheck` = '".$data->nextcheck."' , 
                                                    `lastclock` = ".$data->utime." ,
                                                    `prevvalue`=`lastvalue`,`lastvalue`=0";
                                                    
    $cmd->conexion->query($update_profile);

    liberando($memcache,$idHost,$idItem,$serial,"Fuera de horario",$cmd);
}


//Validacion de IP
if (($data->ipWan === '0.0.0.0') || ($data->ipWan === '1.1.1.1') || ($data->ipWan === '1.0.0.0')) {
    $update_profile = "INSERT INTO `bm_item_profile` 
                            ( `id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, 
                                    `nextcheck`, `lastclock`, `lastvalue`, `prevvalue`, `error`, `status`)
                        VALUES (".
                            $data->idItem."," . 
                            $data->idHost.", '".
                            $data->snmpOid."', ".
                            $data->snmpPort.", '".
                            $data->snmpCommunity."',  '".$data->nextcheck."' , UNIX_TIMESTAMP(), 0, 0, 'IP invalid', 'error') 
                            ON DUPLICATE KEY UPDATE `error`='IP invalid' ,  
                                                    `status`='error' ,
                                                    `nextcheck` = '".$data->nextcheck."' , 
                                                    `lastclock` = ".$data->utime." ,
                                                    `prevvalue`=`lastvalue`,`lastvalue`=0";

    $cmd->conexion->query($update_profile);
    
    $insert_query="/* BSW - Poller NOK */  INSERT INTO $tableHistory 
                        (`id_item`,`id_host`,`clock`,`value`,`valid`) 
                        VALUES ( $data->idItem,$data->idHost,$data->utime, 0 ,0)";
    $cmd->conexion->query($insert_query);

    //Status Host
    if ((int)$data->idItem === 1) {
        $set_status_host_sql = "/* BSW */ UPDATE `bm_host` SET `availability` = '0' WHERE `id_host` = '$data->idHost';";
        $cmd->conexion->query($set_status_host_sql);
    }

    liberando($memcache,$idHost,$idItem,$serial,"Ip incorrecta",$cmd);

}


if (function_exists('snmp2_get') && ($continue === true)) {
    $timeStart = microtime(true);
    $resultSnmp = @snmp2_get($ipDestiny,$data->snmpCommunity,$data->snmpOid,$timeout,$retry);
    $timeEnd = microtime(true);
    $time = $timeEnd - $timeStart;
    if($resultSnmp){
        //Todo OK por el momento 
        $cmd->logs->debug("La consulta snmp al host: [$data->host] mediante la ip: [$ipDestiny] y el objetid: [$data->snmpOid] , tiempo: $time  , respondio: ",$resultSnmp);

         //validaciones

        if (strpos($resultSnmp, 'uci') !== false) {
            $cmd->logs->error("La consulta snmp -> host: [$data->host] ip: [$ipDestiny] objetid: [$data->snmpOid] respondio con Error: ", $resultSnmp);
            $valorSnmp = 'UCI : Entry not found';
            $tipoValorSnmp = 'STRING';
        } elseif (strpos($resultSnmp, 'OID') !== false) {
            $cmd->logs->error("La consulta snmp -> host: [$data->host] ip: [$ipDestiny] objetid: [$data->snmpOid] respondio con Error: ", $resultSnmp);
            $valorSnmp = 'No Such Object available on this agent at this OID';
            $tipoValorSnmp = 'STRING';
        } else {
            list($tipoValorSnmp, $valorSnmp) = explode(": ", $resultSnmp);
        }

        if (($tipoValorSnmp == 'STRING') && (!preg_match('/"/i', $valorSnmp))) {
            $valorSnmp = '"' . $valorSnmp . '"';
        }

        $filtroObligatorio = false;
        if (($tipoValorSnmp == 'STRING') && ($data->typeItem == 'float')) {
            $cmd->logs->warning("Revisar: Se esperaba un valor float y se recibio un string $valorSnmp", NULL, 'logs_poller8');
            $filtroObligatorio = true;
        }

        $cmd->logs->debug("Buscando filtro especial", NULL, 'logs_poller8');

        $filterStatus = filter($data->description, $data->dns, $valorSnmp ,$cmd);

        if ($filterStatus !== false) {
            $valorSnmp = $filterStatus;
        }

        if (($filterStatus == false) && ($filtroObligatorio)) {
            $cmd->logs->error("Revisar: Se esperaba un valor float y se recibio un string y no tiene filtro", NULL, 'logs_poller8');
            liberando($memcache,$idHost,$idItem,$serial,"Valor incorrecto y sin filtro",$cmd);
        }
        
        error_log(print_r($data,true));
        
        $query = "/* BSW - Poller OK */ INSERT INTO `$tableHistory` (`id_item`,`id_host`,`clock`,`value`,`valid`) values ( $data->idItem,$data->idHost,$data->utime, $valorSnmp ,1)";
        
        $cmd->conexion->query($query);
        
        if (empty($data->lastvalue)) {
            $data->lastvalue = "0";
        }

        if (((int)$data->idItem === 1) && ($valorSnmp == 1)) {
            $check_ok = ', `check_ok` = NOW()';
        } else {
            $check_ok = '';
        }

        if (is_numeric($valorSnmp)) {
            $valorSnmp = 'abs(' . $valorSnmp . ')';
        }
        
        $update_profile = "INSERT INTO `bm_item_profile` 
                                ( `id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, 
                                        `nextcheck`, `lastclock`, `lastvalue`, `prevvalue`, `error`, `status`)
                            VALUES (".
                                $data->idItem."," . 
                                $data->idHost.", '".
                                $data->snmpOid."', ".
                                $data->snmpPort.", '".
                                $data->snmpCommunity."', '".$data->nextcheck."' , UNIX_TIMESTAMP(), $valorSnmp, 0, '', 'ok') 
                                ON DUPLICATE KEY UPDATE `error`='' ,  
                                                        `status`='ok' $check_ok , 
                                                        `nextcheck` = '".$data->nextcheck."' , 
                                                        `lastclock` = ".$data->utime." ,
                                                        `prevvalue`=`lastvalue`,`lastvalue`=$valorSnmp";
                                                            
        $result = $cmd->conexion->query($update_profile);

        //Status Host
        if ((int)$data->idItem === 1) {
            $set_status_host_sql = "/* BSW */ UPDATE `bm_host` SET `availability` = '1' WHERE `id_host` = '$data->idHost';";
            $cmd->conexion->query($set_status_host_sql);
        }

        // Liberando poller
        liberando($memcache,$idHost,$idItem,$serial,"Fin",$cmd);
    } else {
        
        $cmd->logs->warning("[POLLER][$data->host][$data->idItem] Timeout snmp ip: [$ipDestiny] y el objetid: [$data->snmpOid] ");
        
        if($data->type === 'start') { 
            $update_profile = "INSERT INTO `bm_item_profile` 
                                    ( `id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, 
                                            `nextcheck`, `lastclock`, `lastvalue`, `prevvalue`, `error`, `status`)
                                VALUES (".
                                    $data->idItem."," . 
                                    $data->idHost.", '".
                                    $data->snmpOid."', ".
                                    $data->snmpPort.", '".
                                    $data->snmpCommunity."', NOW() , UNIX_TIMESTAMP(), 0, 0, 'Router not found (retrying)', 'retry') 
                                    ON DUPLICATE KEY UPDATE `error`='Timout snmp' ,  
                                                            `status`='retry' ,
                                                            `nextcheck` = NOW() , 
                                                            `lastclock` = ".$data->utime." ,
                                                            `prevvalue`=`lastvalue`,`lastvalue`=0";
            $cmd->conexion->query($update_profile);
        } else {
                        //Status Host
            if ((int)$data->snmp_monitor === 'true') {
                $set_status_host_sql = "/* BSW */ UPDATE `bm_host` SET `availability` = '0' WHERE `id_host` = '$data->idHost';";
                $cmd->conexion->query($set_status_host_sql);
            }
              
            $update_profile = "INSERT INTO `bm_item_profile` 
                                    ( `id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, 
                                            `nextcheck`, `lastclock`, `lastvalue`, `prevvalue`, `error`, `status`)
                                VALUES (".
                                    $data->idItem."," . 
                                    $data->idHost.", '".
                                    $data->snmpOid."', ".
                                    $data->snmpPort.", '".
                                    $data->snmpCommunity."', NOW() , UNIX_TIMESTAMP(), 0, 0, 'Router not found', 'error') 
                                    ON DUPLICATE KEY UPDATE `error`='Timout snmp' ,  
                                                            `status`='error' ,
                                                            `nextcheck` = DATE_ADD(NOW(), INTERVAL ".$data->delay." SECOND ) , 
                                                            `lastclock` = ".$data->utime." ,
                                                            `prevvalue`=`lastvalue`,`lastvalue`=0";
            $cmd->conexion->query($update_profile);
            $insert_query="/* BSW - Poller NOK */  INSERT INTO $tableHistory 
                                (`id_item`,`id_host`,`clock`,`value`,`valid`) 
                                VALUES ( $data->idItem,$data->idHost,$data->utime, 0 ,0)";
            $cmd->conexion->query($insert_query);
        }
    }
}


// Liberando item

function liberando($memcache,$idHost,$idItem,$serial,$txt,$cmd)
{
	$delete_lock_sql = "DELETE FROM `bm_poller_active` WHERE `id_host`=$idHost AND `id_item` = $idItem";
    $cmd->conexion->query($delete_lock_sql);
    
    $result['result'] = $txt;
    $result['finished'] = true;
    $memcache->add($serial,$result,2);
    
    if($memcache->getResultCode() == Memcached::RES_SUCCESS){
        echo "OK";
        $cmd->logs->debug("Liberacion OK", NULL, 'logs_poller8');
    } else {
        echo "NOK";
        $cmd->logs->error("Liberacion NOK: ", $memcache->getResultCode(), 'logs_poller8');
    }
    exit;
}

liberando($memcache,$idHost,$idItem,$serial,"Fin",$cmd);
?>