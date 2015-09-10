<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  cli.poller
 * @author   Carlos Lazcano <carlos@bsw.cl>
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: 1: index.phpl,v 1.0 2012-11-22 14:06:47 cweiske Exp $
 * @link     http://baking.cl
 */
header('Content-type: text/html; charset=UTF-8');

$SITE_PATH = realpath(dirname(__FILE__) . '/../') . "/";

require $SITE_PATH . 'core/startup.inc.php';

/**
 *  Class Poller
 */

/**
 *
 */
class Poller {
	
	private $dbNameCore = 'bsw_bi';
	private $delay_box = array();

	function __construct($cmd) {
		$this->conexion = $cmd->conexion;
		$this->logs = $cmd->logs;
		$this->basic = $cmd->basic;
		$this->parametro = $cmd->parametro;
		$this->language = $cmd->language;
	}

	public function validStart() {
		$file = $_SERVER["SCRIPT_NAME"];
		$status = shell_exec('ps -weafe | grep "' . $file . '"  | grep -v  grep | wc -l');

		if ($status > 1) {
			$this->logs->error('Start NOK', null, 'logs_poller');
			exit ;
		} else {
			$this->logs->info('Start OK', null, 'logs_poller');
		}
	}

	public function start() {
		$servers = $this->getServer();

		if ($servers !== false) {
			foreach ($servers as $key => $server) {

				$this->setTimeZone($server['timezone']);

				$this->logs->info("Start poller server name: ", $server['name'], 'logs_poller');

				$change = $this->conexion->changeDB($server['dbName']);

				if ($change == false) {
					$this->logs->error("Change Database", $change, 'logs_poller');
					continue;
				}

				///Cargar Funciones
				$this->startPoller((object)$server);
				//$this->alertMonitor((object)$server);
			}

		}
	}

	private function setTimeZone($timezone) {
		date_default_timezone_set($timezone);
		ini_set('date.timezone', $timezone);

		$now = new DateTime();
		$mins = $now->getOffset() / 60;

		$sgn = ($mins < 0 ? -1 : 1);
		$mins = abs($mins);
		$hrs = floor($mins / 60);
		$mins -= $hrs * 60;

		$offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);

		$this->conexion->query("SET time_zone = '$offset';");
	}

	// Function Get Server

	public function getServer() {
		$getServerSQL = 'SELECT `idServer`, `name`, `domain`, `dbName`, `timezone`, `mailAlert` FROM `' . $this->dbNameCore . '`.`bi_server` WHERE `active` = \'true\';';
		$getServerRESULT = $this->conexion->queryFetch($getServerSQL);

		if ($getServerRESULT) {
			return $getServerRESULT;
		} else {
			return false;
		}
	}

	private function getDelay($delay) {

		if (isset($this->delay_box[$delay])) {
			return (object)$this->delay_box[$delay];
		}

		$time = time();
		$time_f = substr($time, 0, -1) . "0";

		$fecha = date('Y-m-d H:i:s');
		$nextcheck = strtotime("+$delay second", strtotime($fecha));
		$nextcheck = date('Y-m-d H:i:00', $nextcheck);

		$logs = "START";	
		$crontab = 'index';

		$get_delay_crontab_sql = "SELECT COUNT(`id_poller` ) AS Total , `uptime`, `nextcheck` FROM `bm_poller` WHERE `delay` = $delay AND NOW() < `nextcheck` LIMIT 1";

		$get_delay_crontab_result = $this->conexion->queryFetch($get_delay_crontab_sql, 'logs_poller');

		if ($get_delay_crontab_result) {
			$row = (object)$get_delay_crontab_result[0];

			if (($row->Total > 0) && (is_numeric($row->uptime))) {
				$this->delay_box[$delay]['uptime'] = $row->uptime;
				$this->delay_box[$delay]['nextcheck'] = $row->nextcheck;
				return (object)$this->delay_box[$delay];
			} else {

				$insert_delay_sql = "INSERT INTO `bm_poller` (`uptime`, `delay`, `item_poller`, `fecha_update`, `startcheck`, `nextcheck`)
										VALUES ( $time_f , $delay , 0, NOW(), NOW(), '$nextcheck') ON DUPLICATE KEY UPDATE `uptime` = $time_f, `fecha_update` = NOW(), `startcheck` = NOW(), `nextcheck` = '$nextcheck' ";
				$insert_delay_result = $this->conexion->query($insert_delay_sql, false, 'logs_poller');

				if ($insert_delay_result) {
					$this->delay_box[$delay]['uptime'] = $time_f;
					$this->delay_box[$delay]['nextcheck'] = $nextcheck;

					return (object)$this->delay_box[$delay];
				} else {
					$this->logs->error("[POLLER_$logs] Error al obtener delay_unix", NULL, 'logs_poller');
					exit ;
				}
			}
		} else {
			$this->logs->error("[POLLER_$logs] Error al obtener delay_unix", NULL, 'logs_poller');
			exit ;
		}
	}

	public function AgentDisabled() {
		$getAgent = "SELECT  profile.`id_item_profile`, host.`host`, host.`id_host`, host.`dns`, 
        		item.`id_item`, IF( HG.`delay_bsw` > 0 ,  HG.`delay_bsw`,item.`delay` ) as delay,
        			item.`type_item`, item.`description` , host.`ip_wan` , item.`snmp_oid`, 
        			item.`snmp_community`, item.`snmp_port`,  HG.`horario`, HG.`feriados`, HG.`snmp_monitor`
			FROM `bm_host` host
			LEFT JOIN `bm_item_profile` profile USING(`id_host`)
			LEFT JOIN  `bm_items` item ON profile.`id_item`=item.`id_item`
			LEFT JOIN  `bm_items_groups` IG ON (IG.`id_item`=item.`id_item` AND host.`groupid`=IG.`groupid` )
			LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=host.`groupid`
			WHERE host.`borrado`=0 AND IG.`status`= 1 AND host.`status` = 0 AND item.`id_item` = 1  AND profile.`nextcheck`  < NOW()  AND item.`type_poller` = 'snmp'";

		$getAgentResult = $this->conexion->queryFetch($getAgent, 'logs_poller');

		if ($getAgentResult) {
			foreach ($getAgentResult as $key => $host) {

				$delay_unix = $this->getDelay($host['delay']);

				$post['type_poller'] = 'index';
				$post['pipe'] = $key;

				$post['uptime'] = $delay_unix->uptime;
				$post['nextcheck'] = $delay_unix->nextcheck;
				$post['host'] = $host['host'];
				$post['id_host'] = $host['id_host'];
				$post['dns'] = $host['dns'];
				$post['id_item'] = $host['id_item'];
				$post['type_item'] = $host['type_item'];
				$post['description'] = $host['description'];
				$post['ip_wan'] = $host['ip_wan'];

				$post['snmpMonitor'] = 'false';

				$post['snmp_oid'] = $host['snmp_oid'];
				$post['snmp_community'] = $host['snmp_community'];
				$post['snmp_port'] = $host['snmp_port'];

				$post['horario'] = $host['horario'];
				$post['feriados'] = $host['feriados'];

				$this->process($host['id_item_profile'], $post);

			}
		}
	}

	public function startPoller($server) {

		$this->AgentDisabled();

		$getHostDateSQL = "SELECT  profile.`id_item_profile`, host.`host`, host.`id_host`, host.`dns`, 
        		item.`id_item`, IF( HG.`delay_bsw` > 0 ,  HG.`delay_bsw`,item.`delay` ) as delay,
        			item.`type_item`, item.`description` , host.`ip_wan` , item.`snmp_oid`, 
        			item.`snmp_community`, item.`snmp_port`,  HG.`horario`, HG.`feriados`, HG.`snmp_monitor`
			FROM `bm_host` host
			LEFT JOIN `bm_item_profile` profile USING(`id_host`)
			LEFT JOIN  `bm_items` item ON profile.`id_item`=item.`id_item`
			LEFT JOIN  `bm_items_groups` IG ON (IG.`id_item`=item.`id_item` AND host.`groupid`=IG.`groupid` )
			LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=host.`groupid`
			WHERE host.`borrado`=0 AND IG.`status`= 1 AND host.`status` = 1 AND profile.`nextcheck`  < NOW()  AND item.`type_poller` = 'snmp' ";

		$getHostDateRESULT = $this->conexion->queryFetch($getHostDateSQL, 'logs_poller');

		if ($getHostDateRESULT) {

			foreach ($getHostDateRESULT as $key => $host) {

				$delay_unix = $this->getDelay($host['delay']);

				$post['type_poller'] = 'index';
				$post['pipe'] = $key;

				$post['uptime'] = $delay_unix->uptime;
				$post['nextcheck'] = $delay_unix->nextcheck;
				$post['host'] = $host['host'];
				$post['id_host'] = $host['id_host'];
				$post['dns'] = $host['dns'];
				$post['id_item'] = $host['id_item'];
				$post['type_item'] = $host['type_item'];
				$post['description'] = $host['description'];
				$post['ip_wan'] = $host['ip_wan'];

				$post['snmpMonitor'] = $host['snmp_monitor'];

				$post['snmp_oid'] = $host['snmp_oid'];
				$post['snmp_community'] = $host['snmp_community'];
				$post['snmp_port'] = $host['snmp_port'];

				$post['horario'] = $host['horario'];
				$post['feriados'] = $host['feriados'];

				$this->process($host['id_item_profile'], $post);

			}

		} else {
			return false;
		}

		return true;
	}

	function filter($description, $dns, $valor, $cmd) {
		switch ($description) {
			case 'Availability.bsw' :
				if (preg_match("/$dns/i", $valor)) {
					$this->logs->debug("Filtro  encontrado, se tranforma valor inicial en 1, $dns ->  $valor");
					return '1';
				} else {
					$this->logs->debug("Filtro  encontrado, se tranforma valor inicial en 0, $dns ->  $valor");
					return '0';
				}
				break;

			case 'Availability-ForTrap.bsw' :
				if (preg_match("/$dns/i", $valor)) {
					$this->logs->debug("Filtro  encontrado, se tranforma valor inicial en 1, $dns ->  $valor");
					return '1';
				} else {
					$this->logs->debug("Filtro  encontrado, se tranforma valor inicial en 0, $dns ->  $valor");
					return '0';
				}
				break;

			default :
				return false;
				break;
		}
	}

	public function process($id_item, $post) {

		$getParam = (object)$post;

		$poler_log_text = '[POLLER_START]';
		$status = 'process';
		$timeout = 1000000;
		$retry = 1;
		$version = '2c';

		if (!is_numeric($id_item)) {
			return false;
		}

		if ($getParam->horario == '') {
			$getParam->horario = '1-7,00:00-24:00';
		}

		$update_query = "INSERT INTO `bm_item_profile` (`id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, `nextcheck`, `lastclock`, `lastvalue`, `prevvalue`, `error`, `retry`, `status`, `active`)
						VALUES ($getParam->id_item, $getParam->id_host, '$getParam->snmp_oid', $getParam->snmp_port, '$getParam->snmp_community', NOW(), NULL, NULL, NULL, NULL, 0, '$status', 'true')
						ON DUPLICATE KEY UPDATE  error=NULL, `nextcheck` = NOW()";

		$result = $this->conexion->query($update_query, false, 'logs_poller');

		$ip = $getParam->ip_wan . ":" . $getParam->snmp_port;

		switch ($getParam->type_item) {
			case 'float' :
				$tablehistory = "bm_history";
				break;

			case 'string' :
				$tablehistory = "bm_history_str";
				break;

			case 'text' :
				$tablehistory = "bm_history_text";
				break;

			case 'log' :
				$tablehistory = "bm_history_uint";
				break;

			default :
				$tablehistory = "bm_history";
				break;
		}

		//compliance schedule

		$c_schedule = $this->basic->cdateObj($getParam->horario, $getParam->feriados);

		if (($c_schedule == FALSE) || (is_object($c_schedule))) {

			if (isset($c_schedule->nexcheck)) {
				$getParam->nextcheck = $c_schedule->nexcheck;
			}

			$update_query = "UPDATE `bm_item_profile` SET `error`='Fuera de horario', `status`='error' ,`nextcheck` = '$getParam->nextcheck' , `lastclock` =" . $getParam->uptime . ",prevvalue=lastvalue,lastvalue=0 WHERE id_item=" . $getParam->id_item . " AND `id_host`=$getParam->id_host";

			$this->conexion->query($update_query, false, 'logs_poller');

			return false;
		}

		if ($getParam->snmpMonitor == 'false') {

			if ((int)$getParam->id_item === 1) {

				//Get Status
				//$itemStatus = $this->parametro->get("DASHBOARD_ITEM_MONITOR_SONDA", 4);
				$itemStatus = 4;

				$getStatusAgent = "SELECT NOW() as fechahora, H.`id_host`, H.`groupid`, H.`host`,
        IF((IF(IP.`lastclock` IS NULL, 1, IP.`lastclock`)+(35*60)) > UNIX_TIMESTAMP(),1,0) as STATUS_SONDA, 
        IF(IP.`lastclock` IS NULL ,'1970-01-01 00:00:00',IP.`lastclock`) AS updateSONDA,
        H.`dns`, IP.`lastclock`
            FROM `bm_host`  H 
                LEFT OUTER JOIN `bm_item_profile` IP USING (`id_host`)
                LEFT JOIN `bm_host_groups` HG ON HG.`groupid` = H.`groupid`
                LEFT OUTER JOIN `bm_items` I ON I.`id_item`=IP.`id_item`
            WHERE H.`borrado` = 0 AND IP.`id_item` = $itemStatus  AND H.`id_host` = $getParam->id_host";

				$getStatusAgentResult = $this->conexion->queryFetch($getStatusAgent);

				if ($getStatusAgentResult) {
					$status = (int)$getStatusAgentResult[0]['STATUS_SONDA'];
					$lastclock = $getStatusAgentResult[0]['lastclock'];
					if (is_null($lastclock)) {
						$lastclock = $getParam->uptime;
					}
				} else {
					$lastclock = $getParam->uptime;
					$status = 0;
				}

				if (((int)$getParam->id_item === 1) && ($status == 1)) {
					$check_ok = ', `check_ok` = NOW()';
				} else {
					$check_ok = ', `check_ok` = IF(`check_ok` <= "1970-01-01 00:00:00","1970-01-01 00:00:00",`check_ok`)';
				}

				$update_query = "UPDATE `bm_item_profile` JOIN `bm_items` USING(`id_item`) SET error=NULL,  
	                            `status`='ok' $check_ok ,
	                            `nextcheck` = '$getParam->nextcheck' , 
	                            `lastclock` =" . $lastclock . ",
	                            prevvalue=lastvalue,lastvalue=$status 
	                            where id_item=" . $getParam->id_item . " AND `id_host`=$getParam->id_host";


				$result = $this->conexion->query($update_query, false, 'logs_poller');

				$set_status_host_sql = "UPDATE `bm_host` SET `availability` = $status WHERE `id_host` = " . $getParam->id_host;
				echo "1. " . $set_status_host_sql . "\n";
				$this->conexion->query($set_status_host_sql, false, 'logs_poller');

				$setHistorySQL = "/* BSW - Poller OK */ INSERT INTO `$tablehistory` (`id_item`,`id_host`,`clock`,`value`,`valid`) values ( $getParam->id_item,$getParam->id_host,$getParam->uptime, $status ,1)";
				$this->conexion->query($setHistorySQL, true, 'logs_poller');

			} else {
				$update_query = "UPDATE `bm_item_profile` JOIN `bm_items` USING(`id_item`) SET error=NULL,  
	                            `status`='ok',
	                            `check_ok` = IF(`check_ok` <= '1970-01-01 00:00:00','1970-01-01 00:00:00',`check_ok`),
	                            `nextcheck` = '$getParam->nextcheck' , 
	                            `lastclock` =" . $getParam->uptime . ",
	                            prevvalue=lastvalue,lastvalue=0 
	                            where id_item=" . $getParam->id_item . " AND `id_host`=$getParam->id_host";

				$result = $this->conexion->query($update_query, false, 'logs_poller');

			}

			return false;
		}

		if (($getParam->ip_wan == '0.0.0.0') || ($getParam->ip_wan == '1.1.1.1') || ($getParam->ip_wan == '1.0.0.0')) {
			$update_query = "UPDATE `bm_item_profile` SET `error`='Invalid IP', `status`='error' ,`nextcheck` = '$getParam->nextcheck' , `lastclock` =" . $getParam->uptime . ",prevvalue=lastvalue,lastvalue=0 WHERE id_item=" . $getParam->id_item . " AND `id_host`=$getParam->id_host";

			$this->conexion->query($update_query, false, 'logs_poller');

			$insert_query = "/* BSW - Poller NOK */  INSERT INTO $tablehistory (`id_item`,`id_host`,`clock`,`value`,`valid`) values ( $getParam->id_item,$getParam->id_host,$getParam->uptime, 0 ,0)";
			$this->conexion->query($insert_query, false, 'logs_poller');

			//Status Host
			if ((int)$getParam->id_item === 1) {
				$set_status_host_sql = "/* BSW */ UPDATE `bm_host` SET `availability` = '0' WHERE `id_host` = '$getParam->id_host';";
			echo "2. " . $set_status_host_sql . "\n";
				$this->conexion->query($set_status_host_sql, false, 'logs_poller');
			}
			return false;
		}

		//Validamos si el modulo o paquete php_snmp se encuentra activo
		if (function_exists('snmp2_get')) {
			//Via modulo Inicio
			$time_start = microtime(true);
			$this->logs->debug("$poler_log_text Iniciando consulta snmp -> host: [$getParam->host] ip: [$ip] objetid: [$getParam->snmp_oid]  ", NULL, 'logs_poller');
			$result = @snmp2_get($ip, $getParam->snmp_community, $getParam->snmp_oid, $timeout, $retry);
			$time_end = microtime(true);
			$time = $time_end - $time_start;

			if ($result) {

				$this->logs->debug("$poler_log_text La consulta snmp -> host: [$getParam->host] ip: [$ip] objetid: [$getParam->snmp_oid]  , tiempo: $time respondio: ", $result, 'logs_poller');

				//validaciones

				if (strpos($result, 'uci') !== false) {
					$this->logs->error("$poler_log_text La consulta snmp -> host: [$getParam->host] ip: [$ip] objetid: [$getParam->snmp_oid] respondio con Error: ", $result, 'logs_poller');
					$valorSnmp = 'UCI : Entry not found';
					$tipoValorSnmp = 'STRING';
				} elseif (strpos($result, 'OID') !== false) {
					$this->logs->error("$poler_log_text La consulta snmp -> host: [$getParam->host] ip: [$ip] objetid: [$getParam->snmp_oid] respondio con Error: ", $result, 'logs_poller');
					$valorSnmp = 'No Such Object available on this agent at this OID';
					$tipoValorSnmp = 'STRING';
				} else {
					list($tipoValorSnmp, $valorSnmp) = explode(": ", $result);
				}

				if (($tipoValorSnmp == 'STRING') && (!preg_match('/"/i', $valorSnmp))) {
					$valorSnmp = '"' . $valorSnmp . '"';
				}

				$filtroObligatorio = false;
				if (($tipoValorSnmp == 'STRING') && ($getParam->type_item == 'float')) {
					$this->logs->warning("$poler_log_text Revisar: Se esperaba un valor float y se recibio un string $valorSnmp", NULL, 'logs_poller');
					$filtroObligatorio = true;
				}

				$this->logs->debug("$poler_log_text Buscando filtro especial", NULL, 'logs_poller');

				$filterStatus = $this->filter($getParam->description, $getParam->dns, $valorSnmp, $poler_log_text);

				if ($filterStatus) {
					$valorSnmp = $this->snmpFilterValue;
				}

				if (($filterStatus == false) && ($filtroObligatorio)) {
					$this->logs->error("$poler_log_text Revisar: Se esperaba un valor float y se recibio un string y no tiene filtro", NULL, 'logs_poller');
					return false;
				}

				$query = "/* BSW - Poller OK */ INSERT INTO `$tablehistory` (`id_item`,`id_host`,`clock`,`value`,`valid`) values ( $getParam->id_item,$getParam->id_host,$getParam->uptime, $valorSnmp ,1)";
				$this->conexion->query($query, true, 'logs_poller');

				if (empty($getParam->lastvalue)) {
					$getParam->lastvalue = "0";
				}

				if (((int)$getParam->id_item === 1) && ($valorSnmp == 1)) {
					$check_ok = ', `check_ok` = NOW()';
				} else {
					$check_ok = '';
				}

				if (is_numeric($valorSnmp)) {
					$valorSnmp = 'abs(' . $valorSnmp . ')';
				}

				$update_query = "UPDATE `bm_item_profile` JOIN `bm_items` USING(`id_item`) SET error=NULL,  
                                `status`='ok' $check_ok ,
                                `nextcheck` = '$getParam->nextcheck' , 
                                `lastclock` =" . $getParam->uptime . ",prevvalue=lastvalue,lastvalue=" . $valorSnmp . " where id_item=" . $getParam->id_item . " AND `id_host`=$getParam->id_host";
				$result = $this->conexion->query($update_query, false, 'logs_poller');

				//Status Host
				if ((int)$getParam->id_item === 1) {
					$set_status_host_sql = "/* BSW */ UPDATE `bm_host` SET `availability` = '1' WHERE `id_host` = '$getParam->id_host';";
					echo "3. " . $set_status_host_sql . "\n";
					$this->conexion->query($set_status_host_sql, false, 'logs_poller');
				}

				return false;

			} else {
				$this->logs->warning("$poler_log_text La consulta snmp -> host: [$getParam->host] ip: [$ip] objetid: [$getParam->snmp_oid]  , tiempo: $time termino en Timeout", $result, 'logs_poller');

				//Notificando error
				if ($getParam->type_poller == 'index') {
					$update_query = "UPDATE `bm_item_profile` SET `status`='retry' ,`nextcheck` = NOW() WHERE id_item=" . $getParam->id_item . " AND `id_host`=$getParam->id_host";
					$this->conexion->query($update_query, false, 'logs_poller');
					$update_query = "UPDATE `bm_poller_active` SET `status`='retry' WHERE `id_item`=" . $getParam->id_item . " AND `id_host`=$getParam->id_host";
					$this->conexion->query($update_query, false, 'logs_poller');
				} else {

					$update_query = "UPDATE `bm_item_profile` SET `error`='Router no responde', `status`='error' ,`nextcheck` = '$getParam->nextcheck' , `lastclock` =" . $getParam->uptime . ",prevvalue=lastvalue,lastvalue=0 WHERE id_item=" . $getParam->id_item . " AND `id_host`=$getParam->id_host";

					$this->conexion->query($update_query, false, 'logs_poller');

					$insert_query = "/* BSW - Poller NOK */  INSERT INTO $tablehistory (`id_item`,`id_host`,`clock`,`value`,`valid`) values ( $getParam->id_item,$getParam->id_host,$getParam->uptime, 0 ,0)";
					$this->conexion->query($insert_query, false, 'logs_poller');

					//Status Host
					if ((int)$getParam->id_item === 1) {
						$set_status_host_sql = "/* BSW */ UPDATE `bm_host` SET `availability` = '0' WHERE `id_host` = '$getParam->id_host';";
						$this->conexion->query($set_status_host_sql, false, 'logs_poller');
					}

					return false;
				}
			}
			//Via modulo Fin
		} else {
			//Via comando Inicio
			$this->logs->error("El modulo snmp no existe por lo cual se intenta via comando", NULL, 'logs_poller');
			$retval = shell_exec("whereis snmpget | wc -w");
			if ($retval == 1) {
				$this->logs->error("Error el comando snmpget no existe", NULL, 'logs_poller');
				return false;
			}
			$snmpget_cmd = "snmpget -t10 -Oqv -v$version -c$getParam->snmp_community $ip $getParam->snmp_oid";
			$snmpget = shell_exec($snmpget_cmd);
			$this->logs->info("El comando snmp: ", $snmpget_cmd . " Respondio: " . $snmpget, 'logs_poller');
			//Via comando Fin
		}
	}

	public function sendMail() {
		$servers = $this->getServer();

		if ($servers !== false) {
			foreach ($servers as $keyServer => $server) {

				$this->setTimeZone($server['timezone']);

				$this->logs->info("Start poller server name: ", $server['name'], 'logs_poller');

				///Send mail for server
				$change = $this->conexion->changeDB($server['dbName']);

				if ($change == false) {
					continue;
				}

				$getServerSQL = 'SELECT NOW() as "fechahora", H.`id_host`, H.`groupid`,G.`name`, H.`host`, IF(IP.`lastclock` > ( UNIX_TIMESTAMP(NOW()) - 7000 ),1,0) as "STATUS_SONDA", 
											IF(IP.`lastclock` IS NULL , "1970-01-01 00:00:00" ,FROM_UNIXTIME(IP.`lastclock`))  as "updateSONDA", H.`codigosonda` as "dns" 
										FROM `bm_item_profile` IP
											LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host`
											LEFT JOIN `bm_host_groups` G ON G.`groupid`=H.`groupid`
										WHERE IP.`id_item` =4 AND H.`status`=1  AND  H.`borrado` = 0 GROUP BY H.`id_host` ORDER BY H.`id_host`';

				$getServerRESULT = $this->conexion->queryFetch($getServerSQL);

				if ($getServerRESULT) {

					//Count Agent
					$down = 0;
					$up = 0;
					$agents = array();
					foreach ($getServerRESULT as $keyAlert => $alerta) {
						if ($alerta["STATUS_SONDA"] == 0) {
							$agents[$alerta["id_host"]] = false;
							$down++;
						} else {
							$agents[$alerta['id_host']] = true;
							$up++;
						}
					}

					$site = $this->parametro->get("SITE", $server['name']);

					//Validando Cambios

					$fileName = SITE_PATH . 'tmp/' . md5($server['name']) . '.mail';

					$diffAlert = array();
					$arryPrevious = array();
					$hashPrevious = '';

					if (file_exists($fileName)) {

						$fp = fopen($fileName, "r");

						if ($fp) {
							if (filesize($fileName) > 0) {
								$hashPreviousContainer = trim(@fread($fp, filesize($fileName)));
								list($hashPrevious, $statusPrevious) = explode(';', $hashPreviousContainer);
								$hashPrevious = trim($hashPrevious);
								//Comparando para colorear diferencia
								$arryPrevious = json_decode($statusPrevious, true, JSON_NUMERIC_CHECK);

							}
						}

						fclose($fp);
					}

					foreach ($agents as $keyAgent => $agent) {
						if ((isset($arryPrevious[$keyAgent])) && ($agent !== $arryPrevious[$keyAgent])) {
							$diffAlert[$keyAgent] = $agent;
						}
					}

					$header = '<html>
							<head>
							</head><body><style type="text/css">
								tr.rowB:hover { background: #d9ebf5 !important; border-left: 1px solid #eef8ff !important; border-bottom: 1px dotted #a8d8eb !important; }
								tr.rowA:hover { background: #d9ebf5 !important; border-left: 1px solid #eef8ff !important; border-bottom: 1px dotted #a8d8eb !important; }
								></style>';

					$tableCount = '<div style="border: 1px solid bisque; padding: 18px;">Smart Agents: <font color="green">' . $up . ' OK </font>and <font color="red">' . $down . ' DOWN </font></div><br>';

					$table = '<table class="ui-grid-content ui-widget-content">';

					$tableHeader = '<tr>
						<th style="min-width: 60px!important;text-align: left" class="ui-state-default">' . $this->language->GROUPS . '</th>
						<th style="min-width: 150px!important;text-align: left" class="ui-state-default">' . $this->language->EQUIPO_PLANTILLA . '</th>
						<th style="min-width: 100px!important;text-align: left" class="ui-state-default">' . $this->language->PROBLEM . '</th>
						<th class="ui-state-default">' . $this->language->AGENT_CODE . '</th>
						<th style="min-width: 100px!important;text-align: left" class="ui-state-default">' . $this->language->LAST_DATE . '</th></tr>';

					$tableHeaderDiff = '<tr>
						<th style="min-width: 60px!important;text-align: left" class="ui-state-default">' . $this->language->STATUS . '</th>
						<th style="min-width: 150px!important;text-align: left" class="ui-state-default">' . $this->language->EQUIPO_PLANTILLA . '</th>
						<th style="min-width: 100px!important;text-align: left" class="ui-state-default">' . $this->language->PROBLEM . '</th></tr>';

					//$portlet_table .= '<th class="ui-state-default">' . $this->language->LAST_CONNECTION . '</th>';

					$portlet_table = $header . $tableCount . '<div style="border: 1px solid #2694e8;padding: 4px;width: 509px;background-color: aliceblue;color: red;text-align: center;">Down Agents</div>' . $table . $tableHeader;

					$class = 'class="rowA" style="background-color: #eeffee;" bgcolor="#eeffee"';
					$diffTable = '';
					foreach ($getServerRESULT as $key => $alerta) {

						if ($alerta["STATUS_SONDA"] == 0) {
							$tt = $this->language->DASHBOARD_AGENT_NOT_REPORT;
						} else {
							$tt = "Data received";
						}

						$trTable = '<tr ' . $class . '><td class="ui-grid-content">' . $alerta["name"] . '</td><td class="ui-grid-content">' . $alerta["host"] . '</td>';
						$trTable .= '<td class="ui-grid-content">' . $tt . '</td>';
						// $portlet_table .= '<td class="ui-grid-content">' . $alerta["updateSONDA"] . '</td>';
						$trTable .= '<td class="ui-grid-content">' . $alerta["dns"] . '</td>';
						$trTable .= '<td class="ui-grid-content">' . $alerta["updateSONDA"] . '</td></tr>';

						if ($class == 'class="rowA" style="background-color: #eeffee;" bgcolor="#eeffee"') {
							$class = 'class="rowB" style="background-color: #ddffdd;" bgcolor="#ddffdd"';
						} else {
							$class = 'class="rowA" style="background-color: #eeffee;" bgcolor="#eeffee"';
						}

						if ($alerta["STATUS_SONDA"] == 0) {
							$portlet_table .= $trTable;
						}

						if (isset($diffAlert[$alerta['id_host']])) {

							if ($diffAlert[$alerta['id_host']] == true) {
								$trTableFilter = '<tr ' . $class . '><td class="ui-grid-content"><font color="green">OK</font></td><td class="ui-grid-content">' . $alerta["host"] . '</td>';
								$trTableFilter .= '<td class="ui-grid-content">' . $tt . '</td><tr>';
							} else {
								$trTableFilter = '<tr ' . $class . '><td class="ui-grid-content"><font color="red">DOWN</font></td><td class="ui-grid-content">' . $alerta["host"] . '</td>';
								$trTableFilter .= '<td class="ui-grid-content">' . $tt . '</td><tr>';
							}

							$diffTable .= $trTableFilter;

						}

					}

					$portlet_table .= '</table>';

					if (count($diffAlert) > 0) {
						$portlet_table .= '<br><div style="border: 1px solid #2694e8;padding: 4px;width: 509px;background-color: aliceblue;text-align: center;">Changes compared to the previous state</div><br>' . $table;
						$portlet_table .= $tableHeaderDiff;
						$portlet_table .= $diffTable;
						$portlet_table .= '</table>';
					}

					$portlet_table .= '</body></html>';

					$hash = md5(serialize($agents));

					$fp = fopen($fileName, "w");
					if ($fp) {
						$agentsJson = json_encode($agents, JSON_NUMERIC_CHECK);
						if (json_last_error() !== JSON_ERROR_NONE) {
							//error_log(print_r($agents,true));
						}
						fwrite($fp, $hash . ";" . $agentsJson);
						fclose($fp);
					}

					if ($hashPrevious !== $hash) {
						echo "Alert";
						$this->basic->mail("soporte@bsw.cl", "Soporte BSW", "soporte@bsw.cl", "QoE Smart Agent Information " . $site, $portlet_table);
						//Configuration DB bsw_bi
						if (!is_null($server['mailAlert'])) {
							$this->basic->mail($server['mailAlert'], "Soporte BSW", "soporte@bsw.cl", "QoE Smart Agent Information " . $site, $portlet_table);
						}
					}

				} else {
					continue;
				}
			}

		}
	}
	
	public function alertMonitor($server) {
		
		
		//Create Table IF NOT EXIST

		$createAlertTable = $this->conexion->query("CREATE TABLE IF NOT EXISTS `bm_alert` (
			  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `idHost` int(11) unsigned NOT NULL DEFAULT '0',
			  `idItemProfile` int(11) unsigned NOT NULL DEFAULT '0',
			  `idItemHost` int(11) unsigned NOT NULL DEFAULT '0',
			  `datetime` datetime DEFAULT NULL,
			  `valueOk` int(11) unsigned NOT NULL DEFAULT '0',
			  `valueFail` int(11) unsigned NOT NULL DEFAULT '0',
			  `datetimeAlert` datetime NOT NULL DEFAULT '1970-01-01 00:00:00',
			  `countAlert` int(11) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

		$getProbeSQL = "SELECT PI.`id_item`, PI.`cycles`,PI.`maxTh`,PI.`minTh`,PV.`id_monitor` 
				FROM `bm_profiles_item` PI
				LEFT JOIN `bm_profiles_values` PV ON PI.`id_item`=PV.`id_item` 
				WHERE `thold`='true'";

		$getProbeRESULT = $this->conexion->queryFetch($getProbeSQL);

		if ($getProbeRESULT) {

			$insertAlertSQL = "INSERT INTO `bm_alert` (`datetime`,`idHost`, `idItemProfile`, `idItemHost`, `valueOk`, `valueFail`) VALUES ";

			foreach ($getProbeRESULT as $keyThold => $thold) {
				$getAlertSQL = "SELECT `id_host`, `id_item`, COUNT(*) AS Total, 
						SUM(IF(`value` >= " . $thold['minTh'] . " AND `value` <= " . $thold['maxTh'] . " , 1, 0 )) as 'ok',
						SUM(IF(`value` >= " . $thold['minTh'] . " AND `value` <= " . $thold['maxTh'] . " , 0, 1 )) as 'fail'
					FROM `bm_history`  
					WHERE  `id_item` = " . $thold['id_monitor'] . "  AND `clock` > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 HOUR)) 
					GROUP BY  `id_host`, `id_item` ";

				$getAlertRESULT = $this->conexion->queryFetch($getAlertSQL);

				if ($getAlertRESULT) {
					echo "get Alert Result";

					$insertValueAlert = array();
					foreach ($getAlertRESULT as $keyAlert => $alert) {
						$insertValueAlert[]  = "(NOW(),".$alert['id_host']." , ".$thold['id_item'].", ".$alert['id_item'].", ".$alert['ok'].", ".$alert['fail'].")";
					}
					
					$insertAlertRESULT = $this->conexion->query($insertAlertSQL.join(',', $insertValueAlert)." ON DUPLICATE KEY UPDATE `valueOk`=VALUES(`valueOk`), `valueOk`=VALUES(`valueOk`), `datetime` = NOW() ");
				}
			}
		}
	}

}



$cmd = new Control(true, 'devel3.baking.cl', true);
/*
 $cmd->parametro->remove('STDOUT');
 $cmd->parametro->set('STDOUT', true);
 $cmd->parametro->remove('DEBUG');
 $cmd->parametro->set('DEBUG', true);
 */

//Start Split
$poller = new Poller($cmd);

$poller->validStart();

$poller->start();

$poller->sendMail();
?>
