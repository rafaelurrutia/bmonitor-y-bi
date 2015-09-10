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
	private $isMaster = true;

	function __construct($cmd) {
		$this->conexion = $cmd->conexion;
		$this->logs = $cmd->logs;
		$this->basic = $cmd->basic;
		//$this->isMaster = $cmd->isMaster();
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

			$active = $this->isMaster;

			if($active) {
				$this->logs->info("Is Master OK");
			} else {
				$this->logs->info("Is Master NOK");
				exit;
			}
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
				$this->fixItemsMonitors();
				$this->fixNameMonitor();
				$this->fixMonitores();
				$this->getHostStatus();
				$this->alertMonitor((object)$server);

// Borrar la basura restante de monitores
        $this->conexion->query("create temporary table aafix as (SELECT I.id_item
                    FROM `bm_host` H
                    LEFT JOIN `bm_items_groups` IG USING(`groupid`)
                    LEFT JOIN `bm_items` I ON  I.`id_item`=IG.`id_item`
                    LEFT OUTER JOIN `bm_item_profile` IP ON IP.`id_host`=H.`id_host` AND IP.`id_item`=IG.`id_item`
                    WHERE IP.`id_item` IS NULL AND H.`borrado` = 1)");
        $this->conexion->query("delete from bm_items where id_item in (select id_item from aafix)");
        $this->conexion->query("drop table aafix");


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

	public function fixIdMonitor() {
		$deleteIdMonitorGroupsSQL = "DELETE FROM `bm_items_groups` WHERE `id_item` IN (SELECT PV.`id_monitor` as 'id_item'
                    FROM `bm_profiles_values` PV
                    WHERE `id_monitor` > 0);";
		$deleteIdMonitorGroupsRESULT = $this->conexion->query($deleteIdMonitorGroupsSQL);

		$insertIdMonitorGroupsSQL = "INSERT INTO `bm_items_groups` ( `id_item`, `groupid`, `status`) SELECT DISTINCT PV.`id_monitor` as 'id_item',PP.`groupid`, '1' as `status`
    FROM `bm_profiles_values` PV
        LEFT JOIN `bm_profiles_category` PC ON PV.`id_category`=PC.`id_category`
        LEFT JOIN `bm_profiles_categories` PCG ON PC.`id_categories`=PCG.`id_categories`
        LEFT JOIN `bm_profiles_permit` PP ON PCG.`id_profile`=PP.`id_profile`
        INNER JOIN `bm_items` I ON I.`id_item`=PV.`id_monitor`
    WHERE
        `id_monitor` > 0 AND
        PP.`groupid` IS NOT NULL;";
		$insertIdMonitorGroupsRESULT = $this->conexion->query($insertIdMonitorGroupsSQL);

	}

	public function fixNameMonitor() {
		$this->fixIdMonitor();
		$getProfileItemValueSQL = "SELECT
                        PC.`creationCategory`, PC.`creationMonitor`, PC.`creationMonitorDisplay`, PC.`category` AS 'categories', PC.`class`,
                        PI.`item`, PI.`item_display`,PCY.`category`,PCY.`display`,PCY.`count`,PV.`id_monitor`,PI.`unit`,PI.`saveIP`
                    FROM
                        `bm_profiles_categories` PC
                            LEFT JOIN `bm_profiles_item` PI ON PC.`id_categories`=PI.`id_categories`
                            LEFT JOIN `bm_profiles_category` PCY ON PI.`id_categories` =PCY.`id_categories`
                            LEFT JOIN `bm_profiles_values` PV ON (PI.`id_item`=PV.`id_item` AND PCY.`id_category`=PV.`id_category`)
                    WHERE PI.`type`='result'";
		$getProfileItemValueRESULT = $this->conexion->queryFetch($getProfileItemValueSQL);

		if ($getProfileItemValueRESULT) {

			foreach ($getProfileItemValueRESULT as $key => $value) {

				$monitorName = str_replace("{CATEGORIES_CLASS}", $value['class'], $value['creationMonitor']);
				$monitorName = str_replace("{CATEGORIES_DESCRIPTION}", $value['categories'], $monitorName);
				$monitorName = str_replace("{ITEM_NAME}", $value['item'], $monitorName);
				$monitorName = str_replace("{ITEM_DESCRIPTION}", $value['item_display'], $monitorName);
				$monitorName = str_replace("{CATEGORY_CODE}", $value['category'], $monitorName);
				$monitorName = str_replace("{CATEGORY_DESCRIPTION}", $value['display'], $monitorName);
				$monitorName = str_replace("{COUNT}", $value['count'], $monitorName);

				$monitorDisplay = str_replace("{CATEGORIES_CLASS}", $value['class'], $value['creationMonitorDisplay']);
				$monitorDisplay = str_replace("{CATEGORIES_DESCRIPTION}", $value['categories'], $monitorDisplay);
				$monitorDisplay = str_replace("{ITEM_NAME}", $value['item'], $monitorDisplay);
				$monitorDisplay = str_replace("{ITEM_DESCRIPTION}", $value['item_display'], $monitorDisplay);
				$monitorDisplay = str_replace("{CATEGORY_CODE}", $value['category'], $monitorDisplay);
				$monitorDisplay = str_replace("{CATEGORY_DESCRIPTION}", $value['display'], $monitorDisplay);
				$monitorDisplay = str_replace("{COUNT}", $value['count'], $monitorDisplay);

				if (is_numeric($value['id_monitor'])) {

					if($value['saveIP'] != 'false' || $value['saveIP'] != 'true'){
						$saveIP = 'false';
					} else {
						$saveIP = $value['saveIP'];
					}

					$insertOrUpdateValue[] = "('" . $value['id_monitor'] . "', '$monitorName', '$monitorName', '$monitorDisplay',
                                                    'float', 600, 90, 365, 'bsw_agent', '" . $value['unit'] . "', NULL, '', 0, 'none', NULL,'$saveIP')";
				}

			}

			$insertOrUpdate = "INSERT INTO `bm_items` (`id_item`, `name`, `description`, `descriptionLong`,
                                        `type_item`, `delay`, `history`, `trend`, `type_poller`, `unit`, `snmp_oid`,
                                                `snmp_community`, `snmp_port`, `display`, `tags`,`saveIP`) VALUES ";

			$insertOrUpdateSQL = $insertOrUpdate . join(",", $insertOrUpdateValue) . " ON DUPLICATE KEY UPDATE `name`=VALUES(`name`),
                            `description`=VALUES(`description`), `descriptionLong`=VALUES(`descriptionLong`), `unit`=VALUES(`unit`) , `saveIP`=VALUES(`saveIP`) ";

			$insertOrUpdateRESULT = $this->conexion->query($insertOrUpdateSQL);

		}
	}

	public function fixMonitores() {
	    echo "public function fixMonitores()\n";

		$getMonitores_error_sql = "SELECT H.`id_host`,IG.`id_item`,I.`snmp_community`, I.`snmp_oid`, I.`snmp_port`
					FROM `bm_host` H
					LEFT JOIN `bm_items_groups` IG USING(`groupid`)
					LEFT JOIN `bm_items` I ON  I.`id_item`=IG.`id_item`
					LEFT OUTER JOIN `bm_item_profile` IP ON IP.`id_host`=H.`id_host` AND IP.`id_item`=IG.`id_item`
					WHERE IP.`id_item` IS NULL AND H.`borrado` = 0";

		$getMonitores_error_result = $this->conexion->queryFetch($getMonitores_error_sql);

		if ($getMonitores_error_result) {

			$insert_monitor_profile = "INSERT INTO `bm_item_profile` (`id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, `nextcheck`) VALUES ";

			foreach ($getMonitores_error_result as $key => $host) {
				$insert_monitor_value[] = '(' . $host['id_item'] . ',' . $host['id_host'] . ',\'' . $host['snmp_oid'] . '\',\'' . $host['snmp_port'] . '\',\'' . $host['snmp_community'] . '\', NOW())';
			}

			$insert_monitor_profile_f = join(",", $insert_monitor_value);
			$insert_monitor_profile_f = $insert_monitor_profile . $insert_monitor_profile_f;
			$result_monitor_profile = $this->conexion->query($insert_monitor_profile_f);

		}

	}

	public function getHostStatus() {

		//Borrar datos Anteriores

		$this->conexion->query("/* BSW */ DELETE  FROM `bm_availability` WHERE  `fechahora` < ADDDATE(NOW(), INTERVAL - 60 MINUTE);");

		$DASHBOARD_DELAY_MULTIPLO_MONITOR_SONDA = $this->parametro->get('DASHBOARD_DELAY_MULTIPLO_MONITOR_SONDA', 2);

		//Ingresando nuevos valores (Status de las sondas)
		$set_status_host_snmp_sql = 'INSERT INTO `bm_availability` (`fechahora`, `id_host`, `groupid`, `host`, `STATUS_SNMP`,`updateSNMP`,`dns`,`nextcheck` , `STATUS_SONDA`)
										(SELECT NOW() as "fechahora", H.`id_host`, H.`groupid`, H.`host`, IF(IP.`check_ok` > DATE_SUB(NOW(), INTERVAL 2100 SECOND),1,0) as "STATUS_SNMP",
											IF(`check_ok` = "0000-00-00 00:00:00" OR `check_ok` IS NULL , "1960-01-01 00:00:00",`check_ok`) AS updateSNMP, H.`codigosonda` as "dns" , `nextcheck` , 0 as "STATUS_SONDA"
										FROM `bm_item_profile` IP
											LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host`
										WHERE IP.`id_item` =1 AND H.`status`=1  AND  H.`borrado` = 0 GROUP BY H.`id_host`)
										ON DUPLICATE KEY UPDATE  `host`=VALUES(`host`),`dns`=VALUES(`dns`),`groupid`=VALUES(`groupid`),`STATUS_SNMP`=VALUES(`STATUS_SNMP`),`updateSNMP`=VALUES(`updateSNMP`), `nextcheck`=VALUES(`nextcheck`), `fechahora`=VALUES(`fechahora`)';

		$set_status_host_snmp_result = $this->conexion->query($set_status_host_snmp_sql);

		if ($set_status_host_snmp_result) {

			$itemStatus = $this->parametro->get("DASHBOARD_ITEM_MONITOR_SONDA", 4);

			$DASHBOARD_DELAY_MULTIPLO_MONITOR_SONDA_BSW = $this->parametro->get('DASHBOARD_DELAY_MULTIPLO_MONITOR_SONDA_BSW', 2);

			$set_status_host_sonda_sql = 'INSERT INTO `bm_availability` (`fechahora`, `id_host`, `groupid`, `host`, `STATUS_SONDA`,`updateSONDA`,`dns`, `STATUS_SNMP`)
			( SELECT NOW() as "fechahora", H.`id_host`, H.`groupid`, H.`host`, IF(IP.`lastclock` > ( UNIX_TIMESTAMP(NOW()) - 2100 ),1,0) as "STATUS_SONDA",
											IF(IP.`lastclock` IS NULL , "1970-01-01 00:00:00" ,FROM_UNIXTIME(IP.`lastclock`))  as "updateSONDA", H.`codigosonda` as "dns" , 0 as "STATUS_SNMP"
										FROM `bm_item_profile` IP
											LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host`
										WHERE IP.`id_item` =4 AND H.`status`=1  AND  H.`borrado` = 0 GROUP BY H.`id_host` )
			ON DUPLICATE KEY UPDATE `host`=VALUES(`host`),`dns`=VALUES(`dns`),`groupid`=VALUES(`groupid`),`STATUS_SONDA`=VALUES(`STATUS_SONDA`),`updateSONDA`=VALUES(`updateSONDA`), `fechahora`=VALUES(`fechahora`)';

			$set_status_host_sonda_result = $this->conexion->query($set_status_host_sonda_sql);

			if (!$set_status_host_sonda_result) {
				return false;
			}
		} else {
			return false;
		}
	}

	private function fixItemsMonitors()
	{
		echo "private function fixItemsMonitors()\n";
		$getItemsSQL = "SELECT `id_item`, `name` FROM `bm_items` WHERE `id_item` IN (1,3,4) AND `name` IN ('Availability.bsw','DurationOfTheTest.sh','FinishTest');";

		$getItemsRESULT = $this->conexion->queryFetch($getItemsSQL);

		if($getItemsRESULT){

				$getGroupsSQL = "SELECT groupid FROM bm_host_groups where borrado=0";

				$getGroupsRESULT = $this->conexion->queryFetch($getGroupsSQL);


				if($getGroupsRESULT){

					$insertSQL = "INSERT IGNORE INTO `bm_items_groups` ( `id_item`, `groupid`) VALUES ";
					$valueInsert = array();
					foreach ($getGroupsRESULT as $keyG => $group) {

						foreach ($getItemsRESULT as $keyI => $item) {
							$valueInsert[] = "(".$item['id_item'].",".$group['groupid'].")";
						}

					}
					echo $insertSQL.join(',', $valueInsert) . "\n";
					$insertResult = $this->conexion->query($insertSQL.join(',', $valueInsert));

				}
		}
        $this->conexion->query("delete from bm_items_groups where groupid not in (select groupid from bm_host_groups where borrado=0)");
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


		$deleteOld = $this->conexion->query("DELETE FROM `bm_alert` WHERE `datetime` < DATE_SUB(NOW(), INTERVAL 2 HOUR);");

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
					WHERE  `id_item` = " . $thold['id_monitor'] . "  AND `clock` > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR))
					GROUP BY  `id_host`, `id_item` ";

				$getAlertRESULT = $this->conexion->queryFetch($getAlertSQL);

				if ($getAlertRESULT) {
					$insertValueAlert = array();
					foreach ($getAlertRESULT as $keyAlert => $alert) {
						$insertValueAlert[]  = "(NOW(),".$alert['id_host']." , ".$thold['id_item'].", ".$alert['id_item'].", ".$alert['ok'].", ".$alert['fail'].")";
					}

					$insertAlertRESULT = $this->conexion->query($insertAlertSQL.join(',', $insertValueAlert)." ON DUPLICATE KEY UPDATE `valueOk`=VALUES(`valueOk`), `valueOk`=VALUES(`valueOk`), `datetime` = NOW() ");
				}
			}
		}
	}

	public function finish()
	{
		$this->_crontab("serverFix_crontab","finish");
	}

}

$cmd = new Control(true, 'core.bi.node1.baking.cl', true);

//Start Split
$poller = new Poller($cmd);

$poller->validStart();

$poller->start();

//$poller->finish();
?>
