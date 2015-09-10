<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  cli.neutralidad
 * @author   Carlos Lazcano <carlos@bsw.cl>
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: 1: index.phpl,v 1.0 2012-11-22 14:06:47 cweiske Exp $
 * @link     http://baking.cl
 */
header('Content-type: text/html; charset=UTF-8');

$SITE_PATH = realpath(dirname(__FILE__) . '/../') . "/";

require $SITE_PATH . 'core/startup.inc.php';

$cmd = new Control(true, 'devel3.baking.cl', true);

/**
 * Neutralidad
 */
class Neutralidad {

	private $dbNameCore = 'bsw_bi';
	private $delay_box = array();
	
	function __construct($cmd) {
		$this->cmd = $cmd;
		$this->conexion = $cmd->conexion;
		$this->logs = $cmd->logs;
		$this->basic = $cmd->basic;
		$this->parametro = $cmd->parametro;
		$this->language = $cmd->language;
		$fechaTimeZone= ini_get("date.timezone");
		
		//SET SQL PARAM
		$fechaTimeZone= ini_get("date.timezone");
		$this->logs->error("time zone".$fechaTimeZone);
		$cmd->conexion->query("SET innodb_lock_wait_timeout = 500");
		$cmd->conexion->query("SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ");
	}

	public function validStart() {
		$file = $_SERVER["SCRIPT_NAME"];
		$status = shell_exec('ps -weafe | grep "' . $file . '"  | grep -v  grep | wc -l');

		if ($status > 1) {
			$this->logs->error('Start NOK', null, 'logs_poller');
			exit ;
		} else {
			$this->logs->error("ok ValidStart, no hay proceso de neutralidad corriendo");
			return true;
		}
	}

	public function getServer() {
		$getServerSQL = 'SELECT `idServer`, `name`, `domain`, `dbName`, `timezone`, `mailAlert` ,`backto`  FROM `' . $this->dbNameCore . '`.`bi_server` WHERE `active` = \'true\'';
		$getServerRESULT = $this->conexion->queryFetch($getServerSQL);

		if ($getServerRESULT) {
			return $getServerRESULT;
		} else {
			return false;
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

	public function start() {
		$servers = $this->getServer();
		/*$this->logs->error("servidores activos: ");
		foreach ($servers as $key => $value1) {
			$this->logs->error("Un arreglo: ");
			foreach ($value1 as $key2 => $value2) {
				$this->logs->error("[$key2]: $value2");	
			}				
		}*/	
		if ($servers !== false) {
			foreach ($servers as $key => $server) {

				$this->setTimeZone($server['timezone']);

				$this->logs->info("Start poller server name: ", $server['name'], 'logs_neutralidad');

				$change = $this->conexion->changeDB($server['dbName']);

				if ($change == false) {
					$this->logs->error("Change Database", $change, 'logs_neutralidad');
					continue;
				}
				//Cargar Funciones
				//$this->logs->error("funcionando ok ");
				$this->startNeutralidad((object)$server);
			}

		}
	}

	private function PercentilF1($data, $percentile) {
		$cnt = count($data);

		if ($cnt >= 5) {
			$n = ($cnt * ($percentile / 100));
			if ($n != round($n)) {
				$n = floor($n);
				return ($data[$n]['value'] + $data[$n + 1]['value']) / 2;
			} else {
				if ($data[$n]['value'] > 0) {
					return $data[$n]['value'];
				}
			}
		} else {
			return FALSE;
		}
	}

	private function PercentilF2($data, $percentile, $type = 'INC', $order = FALSE, $value = FALSE) {
		if (0 < $percentile && $percentile < 1) {
			$p = $percentile;
		} else if (1 < $percentile && $percentile <= 100) {
			$p = $percentile * .01;
		} else {
			return FALSE;
		}

		$count = count($data);

		if ($order) {
			sort($data);
		}

		if ($type === 'EXC') {
			$allindex = $p * $count + (1 / 2);

			$allindex_round = round($allindex);

			if ($value != FALSE) {
				$result = $data[$allindex_round - 1][$value];
			} else {
				$result = $data[$allindex_round - 1];
			}

			return $result;
		}

		$allindex = ($count - 1) * $p;
		$intvalindex = round($allindex);
		$floatval = $allindex - $intvalindex;

		if (!is_float($floatval)) {
			if ($value != FALSE) {
				$result = $data[$intvalindex][$value];
			} else {
				$result = $data[$intvalindex];
			}
		} else {
			if ($count > $intvalindex + 1) {
				if ($value != FALSE) {
					$result = $floatval * ($data[$intvalindex + 1][$value] - $data[$intvalindex][$value]) + $data[$intvalindex][$value];
				} else {
					$result = $floatval * ($data[$intvalindex + 1] - $data[$intvalindex]) + $data[$intvalindex];
				}
			} else {
				if ($value != FALSE) {
					$result = $data[$intvalindex][$value];
				} else {
					$result = $data[$intvalindex];
				}
			}
		}

		return $result;
	}

	private function Percentil($id_item, $id_plan, $dt, $groupid) {
		$per = array();
		$sql = "SELECT value FROM bm_persentil_temp3 WHERE id_item = '" . $id_item . "' and id_plan='" . $id_plan . "' AND " . $this->where . " and groupid=" . $groupid . " order by value";
		$res = $this->conexion->queryFetch($sql);
		$per[5] = $this->PercentilF2($res, 5, 'EXC', TRUE, 'value');
		$per[80] = $this->PercentilF2($res, 80, 'EXC', TRUE, 'value');
		$per[95] = $this->PercentilF2($res, 95, 'EXC', TRUE, 'value');
		return $per;
	}

	public function getHistoryTable($server) {
		//return '`bm_history`';  esto esta agregado en producción, revisar. 
		$monthNow = date('n');
		$yearNow = date('Y');
		$dateNow = date('Y-m-j');
		$newDate = strtotime('-' . $server->backto . ' day', strtotime($dateNow));
		$this->logs->error("newDate: $newDate");
		$monthPrevious = date('n', $newDate);
		$yearPrevious = date('Y', $newDate);
		$this->logs->error("mesnow: $monthNow , yearNow: $yearNow , mesAntes: $monthPrevious , yearAntes: $yearPrevious");
		if ($monthNow == $monthPrevious) {
			$dbHistory = '`' . $this->dbNameCore . '`.`bi_history_' . $server->idServer . '_' . $yearNow . '_' . $monthNow . '`';
		} else {
			$dropView = 'DROP VIEW IF EXISTS biHistoriView;';
			$drop = $this->conexion->query($dropView);
			if ($drop) {
				$this->logs->error("borre vista y ahora creo una");
				$createView = 'CREATE VIEW `bsw_bi`.biHistoriView AS 
					SELECT * FROM `bsw_bi`.`bi_history_' . $server->idServer . '_' . $yearNow . '_' . $monthNow . '`
					UNION
					SELECT * FROM `bsw_bi`.`bi_history_' . $server->idServer . '_' . $yearPrevious . '_' . $monthPrevious . '`;';
				$this->logs->error("SQL de crear vista es: ".$createView);
				$view = $this->conexion->query($createView);
			}

			$dbHistory = '`' . $this->dbNameCore . '`.`biHistoriView`';
		}
		return $dbHistory;
	}

	public function setParam($argv) {
		if (!isset($argv[1])) {
			$month = date("n", strtotime("yesterday"));
			$year = date("Y", strtotime("yesterday"));
			if ($month < 10) {
				$month = "0" . $month;
			}
			$this->clock = $year . "/" . $month;
			$this->where = " clock between unix_timestamp('" . $year . "-" . $month . "-01') AND unix_timestamp(adddate('" . $year . "-" . $month . "-01', interval 1 month))-1 ";
			$this->grp = "%Y/%m";
			$this->logs->info("[Neutralidad] Processing period " . $this->clock);
		}

		if (isset($argv[1]) && $argv[1] == "Q") {
			$m = date("n", strtotime("yesterday"));

			switch(TRUE) {
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

			$y = date("Y", strtotime("yesterday"));
			$this->clock1 = $y . "/" . $m0;
			$this->clock2 = $y . "/" . $m1;
			$this->clock = $y . "/" . $qy;
			$this->where = " clock between unix_timestamp('" . $y . "-" . $m0 . "-01') AND unix_timestamp(adddate('" . $y . "-" . $m1 . "-01', interval 1 month))-1 ";
			$this->grp = "%Y/" . $qy;
			$this->logs->info("[Neutralidad] Processing " . $qy);
		}

		if (isset($argv[1]) && $argv[1] == "REPO") {
			if (is_numeric($argv[2]) && is_numeric($argv[3])) {
				$m = $argv[2];
				$y = $argv[3];
				if ($m < 10) {
					$m = "0" . $m;
				}
				$this->clock = $y . "/" . $m;
				$this->where = " clock between unix_timestamp('" . $y . "-" . $m . "-01') AND unix_timestamp(adddate('" . $y . "-" . $m . "-01', interval 1 month))-1 ";
				$this->grp = "%Y/%m";
				$this->logs->info("[Neutralidad] Processing period " . $this->clock);
			} elseif (isset($argv[2]) && $argv[2] == "Q" && is_numeric($argv[3])) {

				switch($argv[3]) {
					case 1 :
						$qy = "Q1";
						$m0 = "01";
						$m1 = "03";
						break;
					case 2 :
						$qy = "Q2";
						$m0 = "04";
						$m1 = "06";
						break;
					case 3 :
						$qy = "Q3";
						$m0 = "07";
						$m1 = "09";
						break;
					case 4 :
						$qy = "Q4";
						$m0 = "10";
						$m1 = "12";
						break;
					default :
						echo "Error Param";
						exit ;
				}

				if (isset($argv[4])) {
					$y = $argv[4];
				} else {
					$y = date("Y", strtotime("yesterday"));
				}

				$this->clock1 = $y . "/" . $m0;
				$this->clock2 = $y . "/" . $m1;
				$this->clock = $y . "/" . $qy;
				$this->where = " clock between unix_timestamp('" . $y . "-" . $m0 . "-01') AND unix_timestamp(adddate('" . $y . "-" . $m1 . "-01', interval 1 month))-1 ";
				$this->grp = "%Y/" . $qy;
				$this->logs->info("[Neutralidad] Processing " . $qy . " " . $y);

			} else {
				echo "Error Param";
				exit ;
			}
		}

	}

	public function startNeutralidad($server) {
		
		$historyTable = $this->getHistoryTable($server);
		$this->logs->error("la tabla de history es(string): ".$historyTable);
		$PERCETILE_TYPE = $this->parametro->get('PERCETILE_TYPE', 'INC');
		$this->logs->error("percetile_type: ".$PERCETILE_TYPE);
		// Grupos de neutralidad:

		$get_group = $this->conexion->queryFetch("SELECT HG.`groupid` FROM `bm_host_groups` HG WHERE ( HG.`type`='NEUTRALIDAD' OR HG.`type`='QoS' )");

		if (!$get_group) {
			$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: Get groupid (no hay grupos pertenceientes a QoS o NEUTRALIDAD)", NULL, 'logs_neutralidad');
			return false;
		}
//BGS013 cambia la query por obsoleta
		//$hostNeutralidad = "CREATE TEMPORARY TABLE bm_tmp_1 AS SELECT H.`id_host`,H.`id_plan`,IG.`id_item`, I.`description`,H.`groupid`
		//FROM `bm_host` H 
		//LEFT JOIN `bm_items_groups` IG USING(`groupid`)
		//LEFT JOIN `bm_items` I ON I.`id_item`=IG.`id_item`
		//LEFT JOIN `bm_host_groups` G ON G.`groupid`=H.`groupid`
		//WHERE ( G.`type`='NEUTRALIDAD' OR G.`type`='QoS' )  AND H.`borrado`=0 AND I.`descriptionLong` IN
		//('NACBandwidthdown','NACBandwidthup','LOCBandwidthdown','LOCBandwidthup','INTBandwidthdown','INTBandwidthup','NACPINGavg','LOCPINGavg','INTPINGavg','IPLOGINRENEW','INTPINGstd','LOCPINGstd','NACPINGstd','INTPINGlost','LOCPINGlost','NACPINGlost','INTPINGstdjitter','LOCPINGjitter','NACPINGjitter','Availability.bsw')";
		$hostNeutralidad = "CREATE TABLE bm_tmp_1 AS SELECT H.`id_host`,H.`id_plan`,IG.`id_item`, I.`descriptionLong`,H.`groupid` FROM `bm_host` H 
		LEFT JOIN `bm_items_groups` IG USING(`groupid`)
		LEFT JOIN `bm_items` I ON I.`id_item`=IG.`id_item`
		LEFT JOIN `bm_host_groups` G ON G.`groupid`=H.`groupid`	WHERE ( G.`type`='NEUTRALIDAD' OR G.`type`='QoS' )  AND H.`borrado`=0 AND I.`descriptionLong` IN
		('Bandwidth - down - NAC','Bandwidth - up - NAC','Bandwidth - down - LOC','Bandwidth - up - LOC','Bandwidth - down - INT', 'Bandwidth - up - INT',
		'Ping - avg - NAC','Ping - avg - LOC', 'Ping - avg - INT','Login - renew - Ip','Ping - std - INT','Ping - std - LOC', 'Ping - std - NAC', 'Ping - lost - INT',
		'Ping - lost - LOC','Ping - lost - NAC','Ping - jitter - INT','Ping - jitter - LOC', 'Ping - jitter - NAC','Availability.bsw')";
		
		$temporary_bm_tmp_1 = $this->conexion->query($hostNeutralidad, false, 'logs_neutralidad');

		if (!$temporary_bm_tmp_1) {
			$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: CREATE TEMPORARY TABLE bm_tmp_1", NULL, 'logs_neutralidad');
			return false;
		}

		$get_group = $this->conexion->arrayToIN($get_group, 'groupid');

		//Limpia stast 1

		$deleteCacheHistory_sql = "DELETE FROM `bm_stat1` WHERE clock='" . $this->clock . "' ";
		$this->logs->error($deleteCacheHistory_sql);
		$deleteCacheHistory_prepare = $this->conexion->prepare($deleteCacheHistory_sql);

		$deleteCacheHistory_result = $deleteCacheHistory_prepare->execute();

		if (!$deleteCacheHistory_result) {
			$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: delete bm_stat1 ", NULL, 'logs_neutralidad');
			return false;
		} else {
			$deleteCacheHistory_start = $deleteCacheHistory_prepare->rowCount();
			$this->logs->info("[Neutralidad] " . $deleteCacheHistory_start . "  records deleted from bm_stat1 table (start)", false, 'logs_neutralidad');
		}

		$bm_stat1 = "INSERT INTO `bm_stat1` SELECT 
			bm_tmp_1.`groupid`, bm_tmp_1.`id_item` , bm_tmp_1.`description`, bm_tmp_1.`id_plan`, DATE_FORMAT(from_unixtime(".$historyTable.".`clock`),'" . $this->grp . "') as clock,
			sum(if(".$historyTable.".`value`>=1,1,0)) as exitosa, sum(if(".$historyTable.".`value`=0,1,0)) as fallida, avg(if(".$historyTable.".`value`>0,1,0))*100 as cumple
		FROM bm_tmp_1 
		LEFT JOIN  ".$historyTable." ON bm_tmp_1.`id_host`=".$historyTable.".`id_host` AND bm_tmp_1.`id_item`=".$historyTable.".`id_item`
		WHERE 
			bm_tmp_1.`id_item` =1 AND" . $this->where . "
		GROUP BY
			bm_tmp_1.`groupid`,
			bm_tmp_1.`id_item`,
			bm_tmp_1.`id_plan`,
			DATE_FORMAT(from_unixtime(".$historyTable.".`clock`),'" . $this->grp . "')";

		$this->basic->timeStart();
		$this->logs->error("HASTA AQUI OK1 el SQL insert en bm_stat1: $bm_stat1");
/*
		$INSERT_bm_stat1 = $this->conexion->query($bm_stat1, false, 'logs_neutralidad');

		if (!$INSERT_bm_stat1) {
			$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: Insert bm_stat1 ", NULL, 'logs_neutralidad');
			return false;
		}

		$timeEnd = $this->basic->timeEnd();

		$this->logs->info("[Neutralidad] Processing statistics " . $this->clock . " 1/8 in " . $timeEnd . " seconds");

		// STATP

		$this->basic->timeStart();

		$statp_query = "CREATE TEMPORARY TABLE bm_statp_temp1 AS SELECT
			bm_tmp_1.`groupid`, bm_tmp_1.`id_item` , bm_tmp_1.`description`, bm_tmp_1.`id_plan`,
			DATE_FORMAT(from_unixtime(".$historyTable.".`clock`),'" . $this->grp . " %w %H') as clock,
			avg(".$historyTable.".`value`) as value,
			stddev_samp(".$historyTable.".`value`) as std_samp,
			stddev_pop(".$historyTable.".`value`) as std_pop, 
			stddev_pop(".$historyTable.".`value`) as max_std_pop, 
			stddev_samp(".$historyTable.".`value`) as max_std_samp, 
			count(*) as cnt 
		FROM bm_tmp_1 
		LEFT JOIN ".$historyTable." ON bm_tmp_1.`id_host`=".$historyTable.".`id_host` AND bm_tmp_1.`id_item`=".$historyTable.".`id_item`
		WHERE ".$historyTable.".`valid`=1 AND ".$historyTable.".`value` > 0  AND" . $this->where . " AND bm_tmp_1.`description` IN 
		('NACBandwidthdown','NACBandwidthup','LOCBandwidthdown','LOCBandwidthup','INTBandwidthdown','INTBandwidthup','NACPINGavg','LOCPINGavg','INTPINGavg','IPLOGINRENEW','INTPINGstd','LOCPINGstd','NACPINGstd','INTPINGlost','LOCPINGlost','NACPINGlost','INTPINGstdjitter','LOCPINGjitter','NACPINGjitter')
		GROUP BY
			bm_tmp_1.`groupid`,
			bm_tmp_1.`id_plan`,
			bm_tmp_1.`id_item`,
			DATE_FORMAT(from_unixtime(".$historyTable.".`clock`),'" . $this->grp . " %w %H');";

		$CREATE_TEMPORARY_bm_statp_temp1 = $this->conexion->query($statp_query, false, 'logs_neutralidad');

		if (!$CREATE_TEMPORARY_bm_statp_temp1) {
			$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: Create bm_statp_temp1 ", NULL, 'logs_neutralidad');
			return false;
		}

		$deleteCacheHistory_sql = "DELETE FROM `bm_statp` WHERE clock='" . $this->clock . "' ";

		$deleteCacheHistory_prepare = $this->conexion->prepare($deleteCacheHistory_sql);

		$deleteCacheHistory_result = $deleteCacheHistory_prepare->execute();

		if (!$deleteCacheHistory_result) {
			$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: delete bm_statp ", NULL, 'logs_neutralidad');
			return false;
		} else {
			$deleteCacheHistory_start = $deleteCacheHistory_prepare->rowCount();
			$this->logs->info("[Neutralidad] " . $deleteCacheHistory_start . "  records deleted from bm_statp table (start)", false, 'logs_neutralidad');
		}

		$timeEnd = $this->basic->timeEnd();

		$this->logs->info("[Neutralidad] Processing statistics " . $this->clock . " 2/8 in " . $timeEnd . " seconds");

		//Get Pesos

		$this->basic->timeStart();

		$getPesosSum = $this->conexion->queryFetch("SELECT sum(value) as Suma FROM bsw_pesos WHERE groupid in $get_group and clock='$this->clock';");

		$getPesosSum = $getPesosSum[0]['Suma'];

		if (isset($getPesosSum) && is_numeric($getPesosSum)) {

			//Cambiando pesos

			$this->logs->debug("Get suma de los pesos $getPesosSum");

			$bm_statp_temp2_query = "INSERT INTO `bm_statp` SELECT bm_statp_temp1.`groupid`, bm_statp_temp1.`id_plan`, bm_statp_temp1.`id_item`, bm_statp_temp1.`description`,
			substr(bm_statp_temp1.clock,1,7) as clock,substr(bm_statp_temp1.clock,9,1) as nweek,substr(bm_statp_temp1.clock,11) as nhour,
			bm_statp_temp1.`value`,
			std_samp,std_pop,
			bm_statp_temp1.value*bsw_pesos.value/($getPesosSum) as wvalue,
			bm_statp_temp1.std_samp*bsw_pesos.value/($getPesosSum) as wstd_samp, 
			bm_statp_temp1.std_pop*bsw_pesos.value/($getPesosSum) as wstd_pop,
			cnt,max_std_samp as std_samp_per_h,max_std_pop as std_pop_per_h,
			bsw_pesos.value/($getPesosSum) as w		     
			FROM  bm_statp_temp1 , bsw_pesos
			WHERE concat(bsw_pesos.clock,' ',dia,' ',hora)=bm_statp_temp1.clock AND bsw_pesos.groupid in $get_group AND bsw_pesos.clock='$this->clock'";

			$INSERT_bm_statp = $this->conexion->query($bm_statp_temp2_query, false, 'logs_neutralidad');

			if (!$INSERT_bm_stat1) {
				$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
				$this->logs->error("STATUS: NOK  DETAIL: Insert bm_statp ", NULL, 'logs_neutralidad');
				return false;
			}
		}

		$timeEnd = $this->basic->timeEnd();
		$this->logs->info("[Neutralidad] Processing statistics " . $this->clock . " 3/8 in " . $timeEnd . " seconds");

		// STAT

		$this->basic->timeStart();

		$deleteCacheHistory_sql = "DELETE FROM `bm_stat` WHERE clock='" . $this->clock . "' ";

		$deleteCacheHistory_prepare = $this->conexion->prepare($deleteCacheHistory_sql);

		$deleteCacheHistory_result = $deleteCacheHistory_prepare->execute();

		if (!$deleteCacheHistory_result) {
			$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: delete bm_stat ", NULL, 'logs_neutralidad');
			return false;
		} else {
			$deleteCacheHistory_start = $deleteCacheHistory_prepare->rowCount();
			$this->logs->info("[Neutralidad] " . $deleteCacheHistory_start . "  records deleted from bm_stat table (start)", false, 'logs_neutralidad');
		}

		$d1 = date("U");

		$q = "SELECT SP.`groupid`,SP.`id_plan`,SP.`id_item`,SP.`description`,SP.`clock`,sum(cnt) as cnt,sum(wvalue) as wvalue,stddev_pop(value) as std_pop,stddev_samp(value) as std_samp
				FROM bm_statp SP
				WHERE `clock`='$this->clock'
				GROUP BY SP.`groupid`,SP.`id_plan`,SP.`id_item`,SP.`clock`";

		$data = $this->conexion->queryFetch($q, 'logs_neutralidad');

		foreach ($data as $key => $value) {
			$groupid = $value["groupid"];
			$id_plan = $value["id_plan"];
			$wwvalue = $value["wvalue"];
			$cnt = $value["cnt"];
			$std_pop = $value["std_pop"];
			$std_samp = $value["std_samp"];
			$description = $value["description"];
			$id_item = $value["id_item"];
			$q1 = "SELECT  SP.`value`,SP.`wvalue`,SP.`w` FROM `bm_statp` SP WHERE SP.`id_plan`=$id_plan AND SP.`groupid`=$groupid AND SP.`id_item`=$id_item AND SP.`clock`='$this->clock'";
			$run = $this->conexion->queryFetch($q1, 'logs_neutralidad');
			$r1 = count($run);
			$std = 0;
			$sum = 0;
			foreach ($run as $keyR => $valueR) {
				$wvalue = $valueR["wvalue"];
				$w = $valueR["w"];
				$sum = $sum + $wvalue;
				$std = $std + $w * ($wvalue - $wwvalue) * ($wvalue - $wwvalue);
			}
			if ($r1 - 1 > 0) {
				$std = sqrt($std) / ($r1 - 1);
			} else {
				$std = 0;
			}
			$insert_sql = sprintf('INSERT INTO `bm_stat` values( %d , %d, "%s" , %d , "%s" , "%d" , "%d" ,"%d" , "%d" ) ', $groupid, $id_plan, $clock, $id_item, $description, $sum, $std, $std_samp, $cnt);

			$this->conexion->query($insert_sql, false, 'logs_neutralidad');
		}

		$timeEnd = $this->basic->timeEnd();
		$this->logs->info("[Neutralidad] Processing statistics " . $this->clock . " 4/8 in " . $timeEnd . " seconds");

		// STAT3

		$this->basic->timeStart();

		$q = "CREATE TEMPORARY TABLE bm_stat_temp3 AS SELECT
			bm_tmp_1.`groupid`, bm_tmp_1.`id_item` , bm_tmp_1.`description`, bm_tmp_1.`id_plan`,
			DATE_FORMAT(from_unixtime(clock),'$this->grp') as clock,
			round(avg(value),2) as vavg,
			round(min(value),2) as vmin,
			round(max(value),2) as vmax,
			round(stddev_samp(value),2) as std_samp,
			round(stddev(value),2) as std,
			sum(if(valid=1,1,0)) as exitosa,
			sum(if(valid=0,1,0)) as fallida,
			round(stddev_samp(value)*1.96/sqrt(count(*)),2) as intervalo,
			round(2*stddev_samp(value)/sqrt(count(*)),2) as error,
			0 as p5,
			0 as p95,
			1 as valid,
			0 as p80 
		FROM bm_tmp_1 
		LEFT JOIN ".$historyTable." ON bm_tmp_1.`id_host`=".$historyTable.".`id_host` AND bm_tmp_1.`id_item`=".$historyTable.".`id_item`
		WHERE ".$historyTable.".`valid`=1 AND ".$historyTable.".`value` > 0 AND" . $this->where . " AND bm_tmp_1.`description` IN 
		('NACBandwidthdown','NACBandwidthup','LOCBandwidthdown','LOCBandwidthup','INTBandwidthdown','INTBandwidthup','NACPINGavg','LOCPINGavg','INTPINGavg','IPLOGINRENEW','INTPINGstd','LOCPINGstd','NACPINGstd','INTPINGlost','LOCPINGlost','NACPINGlost','INTPINGstdjitter','LOCPINGjitter','NACPINGjitter')
		GROUP BY
			bm_tmp_1.`groupid`,
			bm_tmp_1.`id_item`,
			bm_tmp_1.`id_plan`,
			DATE_FORMAT(from_unixtime(".$historyTable.".`clock`),'$this->grp');";

		$CREATE_TEMPORARY_bm_stat_temp3 = $this->conexion->query($q, false, 'logs_neutralidad');

		if (!$CREATE_TEMPORARY_bm_stat_temp3) {
			$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: CREATE TEMPORARY TABLE bm_stat_temp3 ", NULL, 'logs_neutralidad');
			return false;
		}

		$timeEnd = $this->basic->timeEnd();
		$this->logs->info("[Neutralidad] Processing statistics " . $this->clock . " 5/8 in " . $timeEnd . " seconds");

		$this->basic->timeStart();
		$q = "INSERT INTO bm_stat_temp3 SELECT
			bm_tmp_1.`groupid`, bm_tmp_1.`id_item` , bm_tmp_1.`description`, bm_tmp_1.`id_plan`,
			DATE_FORMAT(from_unixtime(clock),'$this->grp') as clock,
			round(avg(value),2) as vavg,
			round(min(value),2) as vmin,
			round(max(value),2) as vmax,
			round(stddev_samp(value),2) as std_samp,
			round(stddev(value),2) as std,
			sum(if(valid=1,1,0)) as exitosa,
			sum(if(valid=0,1,0)) as fallida,
			round(stddev_samp(value)*1.96/sqrt(count(*)),2) as intervalo,
			round(2*stddev_samp(value)/sqrt(count(*)),2) as error,
			0 as p5,
			0 as p95,
			0 as valid,
			0 as p80 
		FROM bm_tmp_1 
		LEFT JOIN ".$historyTable." ON bm_tmp_1.`id_host`=".$historyTable.".`id_host` AND bm_tmp_1.`id_item`=".$historyTable.".`id_item`
		WHERE  ".$historyTable.".`value` > 0 AND" . $this->where . " AND bm_tmp_1.`description` IN 
		('NACBandwidthdown','NACBandwidthup','LOCBandwidthdown','LOCBandwidthup','INTBandwidthdown','INTBandwidthup','NACPINGavg','LOCPINGavg','INTPINGavg','IPLOGINRENEW','INTPINGstd','LOCPINGstd','NACPINGstd','INTPINGlost','LOCPINGlost','NACPINGlost','INTPINGstdjitter','LOCPINGjitter','NACPINGjitter')
		GROUP BY
			bm_tmp_1.`groupid`,
			bm_tmp_1.`id_item`,
			bm_tmp_1.`id_plan`,
			DATE_FORMAT(from_unixtime(".$historyTable.".`clock`),'$this->grp');";

		$INSERT_bm_stat_temp3 = $this->conexion->query($q);

		if (!$INSERT_bm_stat_temp3) {
			$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: Insert bm_stat_temp3 ", NULL, 'logs_neutralidad');
			return false;
		}

		$timeEnd = $this->basic->timeEnd();
		$this->logs->info("[Neutralidad] Processing statistics " . $this->clock . " 6/8 in " . $timeEnd . " seconds");

		$this->basic->timeStart();
		$tmp_table_persentil = "CREATE TEMPORARY TABLE bm_persentil_temp3 SELECT ".$historyTable.".`clock`, T1.`id_item`,T1.`description`,T1.`id_plan`,T1.`groupid`,".$historyTable.".`value`
FROM bm_tmp_1 T1
LEFT JOIN  ".$historyTable." ON T1.`id_host`=".$historyTable.".`id_host` AND T1.`id_item`=".$historyTable.".`id_item`
WHERE $this->where
ORDER BY ".$historyTable.".`value`;";

		$resul_query = $this->conexion->query($tmp_table_persentil, false, 'logs_neutralidad');

		if (!$resul_query) {
			sleep(10);

			$resul_query_2 = $this->conexion->query($tmp_table_persentil, false, 'logs_neutralidad');

			if (!$resul_query_2) {
				$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
				$this->logs->error("STATUS: NOK  QUERY:", $resul_query_2, 'logs_neutralidad');
				$this->logs->error("STATUS: NOK  DETAIL: Get bm_stat_temp3: ", $this->conexion->errorInfo(), 'logs_neutralidad');
				return false;
			}

		}

		$db_qos = "SELECT * FROM bm_stat_temp3 WHERE valid=1 and groupid in $get_group and clock='" . $this->clock . "' order by id_item,id_plan,groupid";

		$result_db_qos = $this->conexion->queryFetch($db_qos, 'logs_neutralidad');

		if (!$result_db_qos) {
			return false;
		}

		$n1 = count($result_db_qos);
		$start = 1;
		foreach ($result_db_qos as $key => $value) {
			$d1 = date("U");

			$groupid = $value["groupid"];
			$id_plan = $value["id_plan"];
			$clock = $value["clock"];
			$id_item = $value["id_item"];
			$description = $value["description"];
			$p = $this->percentil($id_item, $id_plan, $clock, $groupid);
			if (!isset($p[5]))
				$p[5] = 0;
			if (!isset($p[80]))
				$p[80] = 0;
			if (!isset($p[95]))
				$p[95] = 0;
			$q = "UPDATE bm_stat_temp3 set p5=" . $p[5] . ",p95=" . $p[95] . ",p80=" . $p[80] . " WHERE groupid=" . $groupid . " AND clock='" . $clock . "'" . " AND id_plan='" . $id_plan . "'" . " AND valid=1" . " AND id_item='" . $id_item . "'";
			$this->conexion->query($q);
			$d = date("U") - $d1;
			$this->logs->info("[Neutralidad] Processing statistics " . $clock . " [" . $groupid . "] " . $id_plan . "-" . $description . "(" . ($start) . " of " . $n1 . ") in " . $d . " seconds");
			$start++;
		}

		$deleteCacheHistory_sql = "DELETE FROM bm_stat2 WHERE clock='" . $this->clock . "' ";

		$deleteCacheHistory_prepare = $this->conexion->prepare($deleteCacheHistory_sql);

		$deleteCacheHistory_result = $deleteCacheHistory_prepare->execute();

		if (!$deleteCacheHistory_result) {
			$this->logs->error("Error al generar informe de neutralidad: ", $deleteCacheHistory_prepare->errorInfo(), 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: delete bm_stat2 ", NULL, 'logs_neutralidad');
			return false;
		} else {
			$deleteCacheHistory_start = $deleteCacheHistory_prepare->rowCount();
			$this->logs->debug("Query:", $deleteCacheHistory_sql, 'logs_neutralidad');
			$this->logs->info("[Neutralidad] " . $deleteCacheHistory_start . "  records deleted from bm_stat2 table (start)", false, 'logs_neutralidad');
		}

		$db_qos = $this->conexion->query("INSERT into bm_stat2 SELECT * FROM bm_stat_temp3", false, 'logs_neutralidad');

		if (!$db_qos) {
			$this->logs->error("Error al generar informe de neutralidad, ", NULL, 'logs_neutralidad');
			$this->logs->error("STATUS: NOK  DETAIL: Insert bm_stat2 ", NULL, 'logs_neutralidad');
			return false;
		}

		$timeEnd = $this->basic->timeEnd();
		$this->logs->info("[Neutralidad] Processing statistics " . $this->clock . " 7/8 in " . $timeEnd . " seconds");
		
		$this->subtel($server,$get_group);
 
 */
	}

	
	function CreateVel($vel,$dbname)
	{
		var_dump($vel);  //en producción no esta esta definicion de variable
		$valid_colum_sql = $this->conexion->queryFetch("SELECT * FROM INFORMATION_SCHEMA.COLUMNS 
	           WHERE TABLE_NAME='bm_neutralidad' AND column_name='$vel' AND TABLE_SCHEMA='$dbname'");
		var_dump($valid_colum_sql); //en producción no esta esta definición de variable
		if(!$valid_colum_sql || (count($valid_colum_sql) == 0)) {
			$this->conexion->query("ALTER TABLE `bm_neutralidad` ADD `" . $vel . "` DOUBLE(15,3)  NULL  DEFAULT NULL;");
		}
	}
	
	function vel_correct($id_plan,$plan,$vel,$clock,$desc,$id_item)
	{
		$this->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $vel*1 . ',2) WHERE `clock`="' . $clock . '" AND `id_item`='.$id_item);
	}
	
	function validRound($value)
	{
		$val_r = ($value != "") ? "'" . doubleval($value) . "'" : "0"; 
		return $val_r;
	}
	
	function fillVel($id_plan,$plan,$clock,$desc,$id_item,$get_group)
	{
		$from="Con peso";
	    $fal=0;
	    $db_qos = $this->conexion->queryFetch("SELECT * FROM `bm_stat2` WHERE `valid`=0 AND `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
	    
	    if($db_qos) {
		    foreach ($db_qos as $key => $qos_data) {
				$fal = $qos_data["fallida"]*1;
		    }   	
	    }
	    $db_qos = $this->conexion->queryFetch("SELECT * FROM `bm_stat2` WHERE `valid`=1 AND `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
	    
	    if($db_qos) {
		    foreach ($db_qos as $key => $qos_data) {
		    	$p95=$this->validRound($qos_data["p95"]);
				$p5=$this->validRound($qos_data["p5"]);
				$vavg=$this->validRound($qos_data["vavg"]);
				$std_samp=$this->validRound($qos_data["std_samp"]);
				
				$query = sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=3+%d;",$plan,$p95,$clock,$id_item).
				sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=4+%d;",$plan,$p5,$clock,$id_item).
				sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=5+%d;",$plan,$vavg,$clock,$id_item).
				sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=6+%d;",$plan,$std_samp,$clock,$id_item);
							
				$this->conexion->query($query,false,'logs_neutralidad');
							
		    }   	
	    }
	
	    if ($from == "Con peso")
	    {
			$db_qos = $this->conexion->queryFetch("SELECT * FROM `bm_stat` WHERE `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
			
			if($db_qos) {
				foreach ($db_qos as $key => $qos_data) {
					$vavg=$this->validRound($qos_data["avg"]);
					$std_samp=$this->validRound($qos_data["std1"]);
				
					//$query = sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=5+%d;",$plan,$vavg,$clock,$id_item).
					//sprintf("UPDATE `bm_neutralidad` SET `%s`=%s WHERE `clock`='%s' AND `id_item`=6+%d;",$plan,$std_samp,$clock,$id_item);
							
					//$cmd->conexion->query($query);
					if($vavg > 0){
						$this->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $vavg*1 . ',2) WHERE clock="' . $clock . '" AND `id_item`=5+'.$id_item);
					}
					
					if($std_samp > 0){
						$this->conexion->query('UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $std_samp*1 . ',2) WHERE clock="' . $clock . '" AND `id_item`=6+'.$id_item);
					}
					
					
				}
			}
	    }
	}
	
	function fillPing($id_plan,$plan,$clock,$desc,$id_item,$get_group)
	{
		$from="Con peso";
	   
		$db_qos = $this->conexion->queryFetch("SELECT * FROM `bm_stat2` WHERE `valid`=1 AND `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
		
		if($db_qos) {
			foreach ($db_qos as $key => $qos_data) {
				$query = 'UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["vavg"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=13+'.$id_item.";";
				$this->conexion->query($query);
			}
	    }
		if ($from == "Con peso")
		{
			$db_qos = $this->conexion->queryFetch("SELECT * FROM `bm_stat` WHERE `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
			
			if($db_qos) {
				foreach ($db_qos as $key => $qos_data) {
					$avg = (int)round($qos_data["avg"]*1/2);
					if($avg > 0) {
						$query = 'UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["avg"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=13+'.$id_item.";";
						$this->conexion->query($query);
					}
				}
			}
		}
	}
	
	function fillPingS($id_plan,$plan,$clock,$desc,$id_item,$get_group)
	{
		$from="Con peso";
	   
		$db_qos = $this->conexion->queryFetch("SELECT * FROM `bm_stat2` WHERE `valid`=1 AND `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
		
		if($db_qos) {
			foreach ($db_qos as $key => $qos_data) {
				$query = 'UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["std_samp"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=14+'.$id_item.";";
				$this->conexion->query($query);
			}
	    }
		if ($from == "Con peso")
		{
			$db_qos = $this->conexion->queryFetch("SELECT * FROM `bm_stat` WHERE `groupid` IN $get_group AND `clock`='" . $clock . "' AND `description`='" . $desc . "' AND `id_plan`='" . $id_plan . "'");
			
			if($db_qos) {
				foreach ($db_qos as $key => $qos_data) {
					$avg = (int)round($qos_data["std1"]*1/2);
					if($avg > 0) {
						$query = 'UPDATE `bm_neutralidad` set ' . $plan . '=round(' . $qos_data["std1"]*1/2 . ',2) WHERE clock="' . $clock . '" AND `id_item`=14+'.$id_item.";";
						$this->conexion->query($query);
					}
				}
			}
		}
	}
	
	public function subtel($server,$get_group)
	{
		$options = getopt("d::Q:");
		
		if (isset($options['d'])) {
			$this->parametro->remove('STDOUT');
			$this->parametro->set('STDOUT', true);
			echo "MODO DEBUG ON\n";	
		}
		
		//Calculando Parametros
		
		if(isset($options['Q']) && is_numeric($options['Q']) && ($options['Q'] > 0 && $options['Q'] < 5)){
			$quarter = $options['Q'];
			echo "\n Ocupando el Q $quarter\n";
		} else {
			$quarter=ceil(date("n",  strtotime("yesterday"))/3);	
		    echo "\n Ocupando el Q $quarter\n";
		}
		
		$year=date("Y",strtotime("yesterday"));
		
		switch($quarter)
		{
			case 1:
				$clocks = array($year.'/Q1',$year.'/01',$year.'/02',$year.'/03');
			break;
			case 2:
				$clocks = array($year.'/Q2',$year.'/04',$year.'/05',$year.'/06');
			break;
			case 3:
				$clocks = array($year.'/Q3',$year.'/07',$year.'/08',$year.'/09');
			break;
			case 4:
				$clocks = array($year.'/Q4',$year.'/10',$year.'/11',$year.'/12');
			break;
		}
		
		//Mediciones
		foreach ($clocks as $keyClock => $clock) {
		    
		    $this->logs->info("[Neutralidad-2] Creando reporte $clock",false,'logs_neutralidad');  
		
			//Limpiar Tabla Neutralidad
			$this->conexion->query("DELETE FROM `bm_neutralidad` WHERE clock='$clock'");
			
			$this->conexion->query('INSERT INTO `bm_neutralidad` (`id_item`,`clock`) SELECT `id_item`,"' . $clock . '" FROM `bm_neutralidad_item`');
			
			$get_plantes_sql="SELECT P.`id_plan`, P.`plan`, P.`nacD`, P.`locD`, P.`intD`, P.`nacU`, P.`locU`, P.`intU`
		FROM `bm_plan` P 
			LEFT JOIN `bm_plan_groups` PG USING(`id_plan`) 
			LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=PG.`groupid`
		WHERE ( HG.`type`='NEUTRALIDAD' OR HG.`type`='QoS' )";
		
			$get_plantes = $this->conexion->queryFetch($get_plantes_sql);
			
			
			
			foreach ($get_plantes as $keyPlan => $qos_data) {
				
				$plan=str_replace("-","",$qos_data["plan"]);
				$plan="planid_".$qos_data['id_plan'];
				$this->CreateVel($plan,$server->dbName);
				
				$this->vel_correct($qos_data['id_plan'],$plan,$qos_data['nacD'],$clock,'NACBandwidthdown',102);
				$this->vel_correct($qos_data['id_plan'],$plan,$qos_data['locD'],$clock,'LOCBandwidthdown',202);
				$this->vel_correct($qos_data['id_plan'],$plan,$qos_data['intD'],$clock,'INTBandwidthdown',302);
		
				$this->vel_correct($qos_data['id_plan'],$plan,$qos_data['nacU'],$clock,'NACBandwidthup',107);
				$this->vel_correct($qos_data['id_plan'],$plan,$qos_data['locU'],$clock,'LOCBandwidthup',207);
				$this->vel_correct($qos_data['id_plan'],$plan,$qos_data['intU'],$clock,'INTBandwidthup',307);
				
				$this->fillVel($qos_data['id_plan'],$plan,$clock,'NACBandwidthdown',100,$get_group);
				$this->fillVel($qos_data['id_plan'],$plan,$clock,'LOCBandwidthdown',200,$get_group);
				$this->fillVel($qos_data['id_plan'],$plan,$clock,'INTBandwidthdown',300,$get_group);
				
				$this->fillVel($qos_data['id_plan'],$plan,$clock,'NACBandwidthup',105,$get_group);
				$this->fillVel($qos_data['id_plan'],$plan,$clock,'LOCBandwidthup',205,$get_group);
				$this->fillVel($qos_data['id_plan'],$plan,$clock,'INTBandwidthup',305,$get_group);
				
				$this->fillPing($qos_data['id_plan'],$plan,$clock,'NACPINGavg',100,$get_group);
				$this->fillPing($qos_data['id_plan'],$plan,$clock,'LOCPINGavg',200,$get_group);
				$this->fillPing($qos_data['id_plan'],$plan,$clock,'INTPINGavg',300,$get_group);
				
				$this->fillPingS($qos_data['id_plan'],$plan,$clock,'NACPINGstd',100,$get_group);
				$this->fillPingS($qos_data['id_plan'],$plan,$clock,'LOCPINGstd',200,$get_group);
				$this->fillPingS($qos_data['id_plan'],$plan,$clock,'INTPINGstd',300,$get_group);
			}
		
			//Generando CSV
		
			$datos = array();
			$head = array();
			
			$head[] = '';
			foreach ($get_plantes as  $planes) {
				$head[] = $planes['plan'];
			}
			
			$datos[] = $head;
			
			$get_subtel_neutralidad_sql="SELECT * FROM `bm_neutralidad` 
											JOIN `bm_neutralidad_item` USING(`id_item`) 
										WHERE `clock`='$clock'
										ORDER BY id_item;";
		
			$get_subtel_neutralidad = $this->conexion->queryFetch($get_subtel_neutralidad_sql);
			
			if($get_subtel_neutralidad) {
				$count = 1;
				foreach ($get_subtel_neutralidad as $key => $value) {
					$nombreColumna ="columna_".$count;
					${$nombreColumna}=array();
					
					${$nombreColumna}[] = $value['item'];
							
					for ($i=0; $i < count($get_plantes); $i++) {
						$plan=str_replace("-","",$get_plantes[$i]["plan"]); 
						if(isset($value[$plan])) {
							${$nombreColumna}[] = $value[$plan];
						} else {
							${$nombreColumna}[] = "";
						}
						
					}
					$datos[] = ${$nombreColumna};
					$count++;
				}
			
			} else {
				$get_item_sql = "SELECT * FROM `bm_neutralidad_item` ORDER BY id_item;";
				$get_item = $this->conexion->queryFetch($get_item_sql);
				foreach ($get_item as $value) {
					$columna.$key=array();
					$columna.$key[] = $value['item'];
					for ($i=0; $i < count($get_plantes); $i++) {
						$columna.$key[] = '';
					}
					$datos[] = $columna.$key;
				}
			}
			
			$clock_file =  str_replace("/", "_", $clock);
		    
			$fp = fopen( SITE_PATH . '/upload/subtel'.$server->idServer.'.'.$clock_file.'.csv', 'w');
			
			if($fp && (count($datos) > 0)) {
				foreach ($datos as $fields) {
				    if($fields != ''){
				        $createcsv = fputcsv($fp, $fields);
				    }
				}
			}
			
			fclose($fp);
		}
	}
}

$neutralidad = new Neutralidad($cmd);

$neutralidad->validStart();

$neutralidad->setParam($argv);

$neutralidad->start();
?>