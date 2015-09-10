<?php
/**
 * This file is property of Baking Software SpA.
 *
 * PHP version 5
 *
 * @category PHP 
 * @package  cli.serverQoe
 * @author   Carlos Lazcano <carlos@bsw.cl>
 * @license  http://www.baking.cl/license BSD Licence
 * @version  SVN: 1: index.phpl,v 1.0 2012-11-22 14:06:47 cweiske Exp $
 * @link     http://baking.cl
 */
header('Content-type: text/html; charset=UTF-8');

ini_set('memory_limit','512M');

$SITE_PATH = realpath(dirname(__FILE__) .  '/../') . "/";

require '/var/www/site/bmonitor25/core/startup.inc.php';  //$SITE_PATH.'core/startup.inc.php';  

/**
 *  Class Poller
 */

/**
 *
 */
class ServerQoe {
	private $dbNameCore = 'bsw_bi';
	private $delay_box = array();
	private $logName = "logs_qoe"; 

	function __construct($cmd) {
		$this->conexion = $cmd->conexion;
		$this->logs = $cmd->logs;
		$this->basic = $cmd->basic;
		$this->parametro = $cmd->parametro;
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

				$beforeYear = date("Y",strtotime("-1 day"));
				$beforeMonth = date("n",strtotime("-1 day"));
				//$beforeYear = 2015;
				//$beforeMonth =4;
			
				$server['dbnameBI'] = '`bi_history_' . $server['idServer'] . '_' . $beforeYear . '_' . $beforeMonth . '`';
				
				///Cargar Funciones

				$this->qoeReport((object)$server);
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

	private function getServer() {
		$getServerSQL = 'SELECT `idServer`, `name`, `domain`, `dbName`, `timezone` FROM `'.$this->dbNameCore.'`.`bi_server`;';
		$getServerRESULT = $this->conexion->queryFetch($getServerSQL);

		if ($getServerRESULT) {
			return $getServerRESULT;
		} else {
			return false;
		}
	}

	public function calPercentil($id_item, $id_plan, $groupid, $location, $where) {

		$PERCENTILE_TYPE_REPORT_QOE = $this->parametro->get('PERCENTILE_TYPE_REPORT_QOE', 0);

		if ($PERCENTILE_TYPE_REPORT_QOE == 0) {
			$percentileType = 'INC';
		} else {
			$percentileType = 'EXC';
		}

		$PERCENTILE_REPORT_QOE = json_decode($this->parametro->get('PERCENTILE_REPORT_QOE', '[]'));

		$per = array();

		if (strlen(trim($location)) < 2) {
			$sql = "SELECT value FROM tmp_percentil WHERE id_item = '" . $id_item . "' AND id_plan='" . $id_plan . "' AND " . $where . " AND groupid=" . $groupid . " order by value";
		} else {

			$sql = "SELECT value FROM tmp_percentil WHERE id_item = '" . $id_item . "' AND id_plan='" . $id_plan . "' AND " . $where . " AND groupid=" . $groupid . " AND location='" . $location . "' order by value";
		}

		$res = $this->conexion->queryFetch($sql);

		if ($res) {

			foreach ($PERCENTILE_REPORT_QOE as $key => $value) {
				$perValue = $this->basic->getPercentil($res, $value, $percentileType, TRUE, 'value');
				if ($perValue !== FALSE) {
					$per[$value] = $perValue;
				} else {
					$per[$value] = 0;
				}
			}
			// $this->logs->info("[QoS] calculate percentil for location: $location and item:$id_item ", $per);
			return $per;
		} else {
			return false;
		}

	}

	public function qoeReport($server) {
		$change = $this->conexion->changeDB($server->dbName);

		if ($change == false) {
			return false;
		}

		$idItemDownload = $this->parametro->get('ITEM_QOE_REPORT_SPEEDTEST_DOWNLOAD', 148);
		$idItemUpload = $this->parametro->get('ITEM_QOE_REPORT_SPEEDTEST_UPLOAD', 149);
		$idItemDelay = $this->parametro->get('ITEM_QOE_REPORT_SPEEDTEST_DELAY', 150);

		//Set Param
		$param = (object) array();

		$param->year = date("Y", strtotime("yesterday"));
		$param->month = date("n", strtotime("yesterday"));
		//$param->year = 2015;
		//$param->month = 4;

		if ($param->month < 10) {
			$param->month = "0" . $param->month;
		}

		$param->clock = $param->year . "/" . $param->month;
		$param->clockS = $param->year . $param->month;

		$param->where = "H.`clock` BETWEEN UNIX_TIMESTAMP('" . $param->year . "-" . $param->month . "-01') AND 
	                                  UNIX_TIMESTAMP(adddate('" . $param->year . "-" . $param->month . "-01', interval 1 month))-1 ";

		$QOE_ISP_SPEEDTEST = $this->parametro->get('QOE_ISP_SPEEDTEST', '');

		//Valid Table report_speedtest

		$createTableSpeedSQL = "CREATE TABLE IF NOT EXISTS `report_speedtest` (
              `id_speedtest` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `date` int(6) DEFAULT NULL,
              `groupid` int(11) unsigned NOT NULL,
              `ip` varchar(16) NOT NULL DEFAULT '',
              `city` varchar(100) NOT NULL DEFAULT '',
              `region` varchar(100) NOT NULL DEFAULT '',
              `country` varchar(100) NOT NULL DEFAULT '',
              `isp` varchar(100) NOT NULL DEFAULT '',
              `latitude` decimal(10,7) NOT NULL,
              `longitude` decimal(10,7) NOT NULL,
              `testDate` datetime NOT NULL,
              `serverName` varchar(150) NOT NULL DEFAULT '',
              `download` int(11) unsigned NOT NULL,
              `upload` int(11) unsigned NOT NULL,
              `latency` int(11) unsigned NOT NULL,
              `browser` varchar(100) NOT NULL DEFAULT '',
              `operatingSystem` varchar(100) NOT NULL DEFAULT '',
              `userAgent` varchar(150) NOT NULL DEFAULT '',
              `pdownload` int(11) DEFAULT NULL,
              `pupload` int(11) DEFAULT NULL,
              PRIMARY KEY (`id_speedtest`)
        ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;";
		/*
		 $createTableSpeedREPORT = $this->conexion->query($createTableSpeedSQL);

		 if( ! $createTableSpeedREPORT) {
		 $this->logs->error("[QoS] Create Table Report Speed, ", NULL);
		 $this->_crontab($var->crontab, "finish");
		 exit ;
		 }*/

		//GetHost Data

		$getHostDataSQL = "SELECT H.`id_host`, H.`groupid`, H.`host` , H.`ip_wan` ,
    HP.`id_plan`, HP.`nacD`, HP.`nacU`, 
    GROUP_CONCAT(CONCAT(HD.`id_feature`,':', HD.`value`) ORDER BY `id_feature`) as 'feature'
    FROM `bm_host`  H
        LEFT JOIN `bm_host_groups` HG ON H.`groupid`=HG.`groupid`
        LEFT JOIN `bm_host_detalle` HD ON HD.`id_host`=H.`id_host`
        LEFT JOIN `bm_plan` HP ON H.`id_plan` = HP.`id_plan`
    WHERE H.`borrado` = 0 AND HG.`type` = 'QoS' AND HD.`id_feature` IN (67,68,69,70,71,72,77,78,79,80,81,82,84,85,3)
    GROUP BY H.`id_host`";

		$getHostDataRESULT = $this->conexion->queryFetch($getHostDataSQL);

		if (!$getHostDataRESULT) {
			$this->logs->error("[QoS] Error get host data, ", $server->name);
			return false;
		}

		$hostDetail = array();

		foreach ($getHostDataRESULT as $key => $value) {
			$allFeature = explode(',', $value['feature']);

			$featureList = array(
				67 => 0, //LotitudGrados
				68 => 0, //LatitudMinutos
				69 => 0, //LatitudSegundos
				70 => 0, //LongitudGrados
				71 => 0, //LongitudMinutos
				72 => 0, //LongitudSegundos
				77 => '', //Country
				78 => '', //Region
				79 => '', //City
				80 => '', //State
				82 => '', //Other
				84 => '0', //daneMunicipio
				85 => '0', //daneDepartamento
			);

			foreach ($allFeature as $keyLocation => $valueLocation) {
				list($idFeauture, $valueFeature) = explode(':', $valueLocation);
				if ($valueFeature != '' && !is_null($valueFeature)) {
					$featureList[$idFeauture] = $valueFeature;
				}
			}
			if (isset($featureList[67]) && $featureList[67] != "") {
				$latitude = $this->basic->convertDMStoDEC($featureList[67], $featureList[68], $featureList[69]);
			} else {
				$latitude = '0.0000000';
			}

			if (isset($featureList[70]) && $featureList[70] != "") {
				$longitude = $this->basic->convertDMStoDEC($featureList[70], $featureList[71], $featureList[72]);
			} else {
				$longitude = '0.0000000';
			}

			//LOCATION
				
			$country = $this->basic->getLocationValue('country', $featureList[77]);
			$state = $this->basic->getLocationValue('state', $featureList[80]);
				
			if ($featureList[78] != "" && !is_null($featureList[78])) {
				
				$getLocationSQL = "SELECT `city`, `idLocation`, `additionalForm` FROm `bm_location` WHERE `idLocation` = ".$featureList[78];
				$getLocationRESULT = $this->conexion->queryFetch($getLocationSQL);
				
				if($getLocationRESULT){
					$region = $getLocationRESULT[0]['city'];
					$additionalForm = json_decode($getLocationRESULT[0]['additionalForm']);
				} else {
					$region = '';
					$additionalForm = '';
				}
				
				
			} else {
				$region = '';
			}
						
			/*

			if ($featureList[78] != "" && !is_null($featureList[78])) {
				$regionR = $this->basic->getLocationValue($featureList[78]);
				if ($regionR != false) {
					$region = $regionR->name;
				} else {
					$region = '';
				}
			} else {
				$region = '';
			}*/

			if ($featureList[82] != "" && !is_null($featureList[82])) {
				$city = $featureList[82];
			} elseif ($featureList[79] != "" && !is_null($featureList[79])) {
				$cityR = $this->basic->getLocationValue('other',$featureList[79]);
				if ($cityR != false) {
					$city = $cityR->name;
				} else {
					$city = $featureList[79];
				}
			} else {
				$city = '';
			}

			$hostDetail[$value['id_host']] = array(
				"latitude" => $latitude,
				"longitude" => $longitude,
				"country" => $country,
				"state" => $state,
				"region" => $region,
				"city" => $city,
				"additionalForm" => $additionalForm,
				"idLocation" => $featureList[78],
				"groupid" => $value['groupid'],
				"host" => $value['host'],
				"id_plan" => $value['id_plan'],
				"nacD" => $value['nacD'] * 1024,
				"nacU" => $value['nacU'] * 1024,
				"isp" => $QOE_ISP_SPEEDTEST,
				"ip" => $value['ip_wan'],
				"daneMunicipio" => $featureList[84],
				"daneDepartamento" => $featureList[85]
			);

			$hostList[] = $value['id_host'];
		}
		/*
		$getHistorySQL = "SELECT  H.`id_host`, H.`clock` , HI.`ip`, H.`id_item` , 
    SUBSTRING_INDEX(I.`descriptionLong`,'-',-1) As 'ServerName', SUBSTRING_INDEX(SUBSTRING_INDEX(I.`description`,'_',2),'_',-1) As 'IDCategory', 
    GROUP_CONCAT(CONCAT(H.`id_item`,':', H.`value`) ORDER BY I.`description`) as 'feature'
    FROM `".$this->dbNameCore."`.$server->dbnameBI H
        LEFT JOIN `bm_items` I ON H.`id_item` = I.`id_item`
        LEFT OUTER JOIN `bm_history_ip` HI ON ( HI.`clock`=H.`clock`  AND H.`id_host`=HI.`id_host` AND H.`id_item`=HI.`id_item`)
    WHERE 
        H.`id_host` IN (" . join(',', $hostList) . ") AND  ( I.`description` LIKE \"LSP_%_AVGD\"  OR I.`description` LIKE \"LSP_%_AVGU\"  OR I.`description` LIKE \"LSP_%_PING\")  
        AND $param->where 
    GROUP BY `clock`
    ORDER BY `clock`";
*/
		$getHistoryRESULT = $this->conexion->queryFetch($getHistorySQL);
		if (!$getHistoryRESULT) {
			$this->logs->error("[QoS] Error get history: ", $server->name);
			return false;
		}
		//Delete report
		$deleteReportRESULT = $this->conexion->query("DELETE FROM `report_speedtest` WHERE `date` = '$param->clockS';");		
		if (!$deleteReportRESULT) {
			$this->logs->error("[QoS] delete report: ", $param->clockS);
			return false;
		}
		////--- cambio por demora de la query
		/// para cada item usado en el speedtest
		$getItemsSpeedtest_sql="select I.id_item, I.description, I.descriptionLong from bm_items I where I.description LIKE 'LSP_%_AVGD' OR I.description LIKE 'LSP_%_AVGU' OR I.description LIKE 'LSP_%_PING'";
		$getItemsSpeedtest_RESULT= $this->conexion->queryFetch($getHistorySQL);
		
	
		foreach ($getItemsSpeedtest_RESULT as $key => $value) {
			$idItem=$value['id_item'];
			$descripcion1=$value['description'];
			$descripcionLong1=$value['descriptionLong'];
			$tableHistory= 'xyz_' . str_pad($idItem, 10, "0", STR_PAD_LEFT);
			$getHistorySQL = "SELECT  H.`id_host`, H.`clock` , HI.`ip`, H.`id_item` , 
    SUBSTRING_INDEX(I.`descriptionLong`,'-',-1) As 'ServerName', SUBSTRING_INDEX(SUBSTRING_INDEX(I.`description`,'_',2),'_',-1) As 'IDCategory', 
    GROUP_CONCAT(CONCAT(H.`id_item`,':', H.`value`) ORDER BY I.`description`) as 'feature'
    FROM `".$this->dbNameCore."`.$server->dbnameBI H
        LEFT JOIN `bm_items` I ON H.`id_item` = I.`id_item`
        LEFT OUTER JOIN `bm_history_ip` HI ON ( HI.`clock`=H.`clock`  AND H.`id_host`=HI.`id_host` AND H.`id_item`=HI.`id_item`)
    WHERE 
        H.`id_host` IN (" . join(',', $hostList) . ") AND  ( I.`description` LIKE \"LSP_%_AVGD\"  OR I.`description` LIKE \"LSP_%_AVGU\"  OR I.`description` LIKE \"LSP_%_PING\")  
        AND $param->where 
    GROUP BY `clock`
    ORDER BY `clock`";
	
	
			$getHistorySQL = "SELECT  H.`id_host`, H.`clock` , HI.`ip`, $idItem as `id_item` , 
    SUBSTRING_INDEX($descripcionLong1,'-',-1) As 'ServerName', SUBSTRING_INDEX(SUBSTRING_INDEX($descripcion1,'_',2),'_',-1) As 'IDCategory', 
    GROUP_CONCAT(CONCAT($idItem,':', H.`value`) ORDER BY $descripcion1) as 'feature'
    FROM `".$this->dbNameCore."`.$server->dbnameBI H
        LEFT OUTER JOIN `bm_history_ip` HI ON ( HI.`clock`=H.`clock`  AND H.`id_host`=HI.`id_host` AND H.`id_item`=HI.`id_item`)
    WHERE 
        H.`id_host` IN (" . join(',', $hostList) . ") 
        AND $param->where 
    GROUP BY `clock`
    ORDER BY `clock`";
	
	
			
			
		
		}

		
		$count = 0;
		$loop = 0;
		foreach ($getHistoryRESULT as $keyHistory => $hvalue) {

			$host = (object)$hostDetail[$hvalue['id_host']];

			$detailResultTest = explode(",", $hvalue['feature']);

			list($downloadID, $download) = explode(':', $detailResultTest[0]);
			list($uploadID, $upload) = explode(':', $detailResultTest[1]);
			list($latencyID, $latency) = explode(':', $detailResultTest[2]);

			if (!isset($download) || $download == "") {
				$download = 0;
			}

			if (!isset($upload) || $upload == "") {
				$upload = 0;
			}

			if (!isset($latency) || $latency == "") {
				$latency = 0;
			}

			$percenDownload = ($download / $host->nacD) * 100;
			$percenDownloadResult = round($percenDownload);

			$percenUpload = ($upload / $host->nacU) * 100;
			$percenUploadResult = round($percenUpload);

			if ($hvalue['ip'] == "") {
				$hvalue['ip'] = $host->ip;
			}
			
			$reportValue_{$loop}[] = "( " . $hvalue['id_host'] . ", " . $host->groupid . ", " . $param->clockS . " , '" . $hvalue['ip'] . "', '" . $host->city . "', '" . $host->state . "', '" . $host->country . "', '" . $host->isp . "', " . $host->latitude . ", " . $host->longitude . ", FROM_UNIXTIME(" . $hvalue['clock'] . "), '" . trim($hvalue['ServerName']) . "', " . $download . ", " . $upload . ", " . $latency . ", " . $percenDownloadResult . ", " . $percenUploadResult . ", 'Internet Explorer 9.0', 'Windows 7 Home', '','" . $host->idLocation . "')";
			



			$rv= "( " . $hvalue['id_host'] . ", " . $host->groupid . ", " . $param->clockS . " , '" . $hvalue['ip'] . "', '" . $host->city . "', '" . $host->state . "', '" . $host->country . "', '" . $host->isp . "', " . $host->latitude . ", " . $host->longitude . ", FROM_UNIXTIME(" . $hvalue['clock'] . "), '" . trim($hvalue['ServerName']) . "', " . $download . ", " . $upload . ", " . $latency . ", " . $percenDownloadResult . ", " . $percenUploadResult . ", 'Internet Explorer 9.0', 'Windows 7 Home', '','" . $host->idLocation . "')";
			$rh = "INSERT INTO `report_speedtest` ( `idHost` , `groupid`, `date`, `ip`, `city`, `region`, `country`, `isp`, `latitude`, `longitude`, `testDate`, `serverName`, `download`, `upload`, `latency`,  `pdownload`, `pupload`, `browser`, `operatingSystem`, `userAgent`,`idLocation`) VALUES ";
                        $insertReportSpeedValueRESULT = $this->conexion->query($rh .$rv );
			//echo $rh . $rv . "\n";



			if($count === 5000){
				$count = 0;
				$loop++;
			}				 
							 
			$count++;
		}


		}
//		$insertReportSpeedValueSQL = "INSERT INTO `report_speedtest` ( `idHost` , `groupid`, `date`, `ip`, `city`, `region`, `country`, `isp`, `latitude`, `longitude`, `testDate`, `serverName`, `download`, `upload`, `latency`,  `pdownload`, `pupload`, `browser`, `operatingSystem`, `userAgent`,`idLocation`) VALUES ";
//		for ($i=0; $i <= $loop; $i++) { 
//var_dump($insertReportSpeedValueSQL . join(',', $reportValue_{$i}));
//			$insertReportSpeedValueRESULT = $this->conexion->query($insertReportSpeedValueSQL . join(',', $reportValue_{$i}));
//		}
		

//		if (!$insertReportSpeedValueRESULT) {
//			$this->logs->error("[QoS] Error insert value " , NULL);
//			return false;
//		}
	

	public function createTMP()
	{
		$createSQL = "DROP table tableCacheLocation; CREATE TABLE IF NOT EXISTS 
		  tableCacheLocation ( INDEX(id_host) ) 
		AS (
		SELECT H.`id_host` , H.`host`, H.`groupid` ,L.`city` , HD2.city as 'cityf',L.`minTest`,  L.`additionalForm`, CONCAT('SDS ', HD2.city ,' (', L.`city`,')') AS 'name'
			 FROM `bm_host` H
				LEFT OUTER JOIN (SELECT  `id_host`, `value` as 'idLocation' FROM `bm_host_detalle` WHERE `id_feature` = 78) HD1 ON ( H.`id_host`=HD1.`id_host`)
				LEFT OUTER JOIN (SELECT  `id_host`, `value` as 'city' FROM `bm_host_detalle` WHERE `id_feature` = 79) HD2 ON ( H.`id_host`=HD2.`id_host`)
				LEFT OUTER JOIN `bm_location` L ON L.`idLocation`=HD1.`idLocation`
		WHERE `borrado` = 0 AND H.`status` = 1	
		)";
		
		$createRESULT = $this->conexion->query($createSQL);	
	}
}

$cmd = new Control(true, 'core.bi.node1.baking.cl', true);
/*
$cmd->parametro->remove('STDOUT');
$cmd->parametro->set('STDOUT', true);

$cmd->parametro->remove('DEBUG');
$cmd->parametro->set('DEBUG', true);
 * 
 */

$cmd->parametro->set('LOGS_FILE', "logs_qoe");

$cmd->logs->info("[QoS] ===============================================");
$cmd->logs->info("[QoS] Starting Statistic Server");
$cmd->logs->info("[QoS] Copyright Baking Software 2013");
$cmd->logs->info("[QoS] Version 3");
$cmd->logs->info("[QoS] ===============================================");

$poller = new ServerQoe($cmd);

$poller->validStart();

$poller->start();

$poller->createTMP();
?>

