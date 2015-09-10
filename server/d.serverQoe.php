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
 * @version  SVN: 1: index.phpl,v 2.0 2015-06-17 14:06:47 cweiske Exp $
 * @link     http://baking.cl
 */
header('Content-type: text/html; charset=UTF-8');

$SITE_PATH = realpath(dirname(__FILE__) .  '/../') . "/";

require '/var/www/site/bmonitor25/core/startup.inc.php'; // $SITE_PATH.'core/startup.inc.php'; 

/**
 *  Class Poller
 */

class ServerQoe {
	private $dbNameCore = 'bsw_bi';
	private $delay_box = array();
	private $logName = "logs_serverQoE";

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
				//$beforeMonth = 4;
			
				$server['dbnameBI'] = '`bi_history_' . $server['idServer'] . '_' . $beforeYear . '_' . $beforeMonth . '`';
				//$server['dbnameBI'] = '`bm_history`';
				
				
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
		$getServerSQL = 'SELECT `idServer`, `name`, `domain`, `dbName`, `timezone` FROM `bi_server`;';
		$getServerRESULT = $this->conexion->queryFetch($getServerSQL);

		if ($getServerRESULT) {
			return $getServerRESULT;
		} else {
			return false;
		}
	}

	public function calPercentil($id_item, $id_plan, $groupid, $location, $where)
	{
	        
	    $PERCENTILE_TYPE_REPORT_QOE = $this->parametro->get('PERCENTILE_TYPE_REPORT_QOE',0);
	    
	    if($PERCENTILE_TYPE_REPORT_QOE == 0) {
	        $percentileType = 'INC';
	    } else {
	        $percentileType = 'EXC';
	    }
	    
	    $PERCENTILE_REPORT_QOE = json_decode($this->parametro->get('PERCENTILE_REPORT_QOE','[]')); 
	    
	    $per = array();
	    
	    if(strlen(trim($location))  < 2){
	        $sql = "SELECT value FROM tmp_percentil WHERE id_item = '" . $id_item . "' AND id_plan='" . $id_plan . "' AND " . $where . " AND groupid=" . $groupid . " order by value";
	    } else {
	        
	        $sql = "SELECT value FROM tmp_percentil WHERE id_item = '" . $id_item . "' AND id_plan='" . $id_plan . "' AND " . $where . " AND groupid=" . $groupid . " AND location='" . $location . "' order by value";
	    }
	    
	    $res = $this->conexion->queryFetch($sql);
	    
	    if ($res) {
	
	        foreach ($PERCENTILE_REPORT_QOE as $key => $value) {
	            $perValue = $this->basic->getPercentil($res, $value, $percentileType, TRUE, 'value');
	            if($perValue !== FALSE) {
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
			
	public function qoeReport($server)
	{
		$change = $this->conexion->changeDB($server->dbName);
        if ($change == false) {
            exit ;
        }
		$dateStart=date("U");
		$param = (object) array();
		
		$param->year = date("Y", strtotime("yesterday"));
		$param->month = date("n", strtotime("yesterday"));
		//$param->year = 2015;
		//$param->month = 4;
		
		
		if ($param->month < 10) {
		    $param->month = "0" . $param->month;
		}
		$param->clock = $param->year . "/" . $param->month;
		
		$param->where = " `clock` BETWEEN UNIX_TIMESTAMP('" . $param->year . "-" . $param->month . "-01') 
		AND UNIX_TIMESTAMP(adddate('" . $param->year . "-" . $param->month . "-01', interval 1 month))-1 ";
		
		$param->grp = "%Y/%m";
		
		$this->logs->info("[QoS] Processing period " . $param->clock);
		//GetDBValue
		
		$getGroupIDResult = $this->conexion->queryFetch("SELECT HG.`groupid` FROM `bm_host_groups` HG WHERE HG.`type`='QoS' AND HG.borrado=0 AND HG.`name` like '%QoE%'"); 
		if ($getGroupIDResult) {
		    foreach ($getGroupIDResult as $key => $value) {
		        $groupID[] = $value['groupid'];
		    }
		} else {
		    $this->logs->error("[QoS] Error al obtener el id de los grupos, ", NULL);
		    return false;
		}
		$createTable = "CREATE TEMPORARY TABLE `tmp_hostid_itemid` (
		  `id_tmp` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `id_host` int(11) unsigned NOT NULL DEFAULT '0',
		  `id_plan` int(11) unsigned NOT NULL,
		  `groupid` int(11) unsigned NOT NULL,
		  `id_item` int(11) unsigned NOT NULL,
		  `type` enum('personalized','download','upload') NOT NULL DEFAULT 'personalized',
		  `order` enum('DESC','ASC') NOT NULL DEFAULT 'DESC',
		  `nominal` int(11) DEFAULT NULL,
		  `warning` int(11) DEFAULT NULL,
		  `critical` int(11) DEFAULT NULL,
		  `nacD` int(11) unsigned NOT NULL,
		  `nacU` int(11) unsigned NOT NULL,
		  `location` varchar(255)  DEFAULT '0',
		  PRIMARY KEY (`id_tmp`),
		  KEY `IDIH` (`id_item`,`id_host`)
		);";
		
		$temporaryTableCreate = $this->conexion->query($createTable);
		
		$this->conexion->query("UPDATE `tmp_hostid_itemid` SET `location` = '0|0' WHERE `location`  IS NULL;");
		
		if (!$temporaryTableCreate) {
		    $this->logs->error("[QoS] Error create temporary table, ", NULL);
			return false;
		}

		$getLocationFilter = $this->conexion->queryFetch("SELECT H.`id_host`, H.`host` ,  GROUP_CONCAT(CONCAT(HD.`id_feature`,':', HD.`value`) ORDER BY `id_feature`) as 'location'
		FROM `bm_host` H 
		LEFT OUTER JOIN `bm_host_detalle` HD ON H.`id_host`=HD.`id_host`
		WHERE H.`borrado` = 0  AND HD.`id_feature` IN (78,79,82,3)
		GROUP BY H.`id_host`;");
		
		if (!$getLocationFilter) {
		    $this->logs->error("[QoS] Error get location value, ", NULL);
		    return false;
		}
		
		$method = 2;
		$resultLocation = array();
		foreach ($getLocationFilter as $key => $value) {
		    $location = explode(',', $value['location']);
		    switch ($method) {
		        //separate host name
		        case 1:
		            $hostname = preg_replace('[\s+]',"", $value['host']);
		            $hostname = trim($hostname);
		            $resultLocation[] = "(".$value['id_host'].",'0|".$hostname."')";
		            break;
		        //separate for location
		        case 2:
		            $locationList = array(78 => 0, 79 => 0, 82 => '');
		            foreach ($location as $keyLocation => $valueLocation) {
		                list($idFeauture,$valueFeature) = explode(':',$valueLocation);
		                if($valueFeature != '' && !is_null($valueFeature)) {
		                    $locationList[$idFeauture] = $valueFeature;
		                }
		            }
		            
		            if($locationList[82] != '' && $locationList[79] > 0)  {
		                $resultLocation[] = "(".$value['id_host'].",'".$locationList[79]."|".$locationList[82]."')";
		            } elseif ($locationList[82] != '' && $locationList[78] > 0) {
		                $resultLocation[] = "(".$value['id_host'].",'".$locationList[78]."|".$locationList[82]."')";
		            } elseif ( $locationList[82] != '') {
		                $resultLocation[] = "(".$value['id_host'].",'0|".$locationList[82]."')";
		            } else {
		                $resultLocation[] = "(".$value['id_host'].",'".$locationList[78]."|".$locationList[79]."')";
		            }
		
		            break;
		        //Error method
		        default:
		            $this->logs->error("[QoS] Error method separate, ", NULL);
		            return false;           
		            break;
		    }  
		}
		
		$resultLocation = array_unique($resultLocation);
		//Create Temporary table location:
		
		$createTableLocationTemporarySQL = "CREATE TEMPORARY TABLE `tmp_locationHost` (
		  `id_host` int(11) unsigned NOT NULL AUTO_INCREMENT,
		  `location` varchar(244) DEFAULT NULL,
		  PRIMARY KEY (`id_host`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
		
		$createTableLocationTemporaryRESULT = $this->conexion->query($createTableLocationTemporarySQL);
		
		if (!$createTableLocationTemporaryRESULT) {
		    $this->logs->error("[QoS] Error create temporary location table, ", NULL);
		    return false;
		}
		
		$insertTableLocationTemporarySQL = "INSERT INTO `tmp_locationHost` (`id_host`, `location`)
		VALUES ".join(',', $resultLocation);
		
		$insertTableLocationTemporaryRESULT = $this->conexion->query($insertTableLocationTemporarySQL);
		
		  
		$getHostValid = $this->conexion->query("INSERT INTO `tmp_hostid_itemid` 
		SELECT null,H.`id_host`,H.`id_plan`,H.`groupid`,IG.`id_item`, TH.`type`, 
		    IF( TH.`nominal` > TH.`warning`,'DESC','ASC') as 'order' , 
		    IF(TH.`nominal` = -1 OR TH.`nominal` = -2, 130,TH.`nominal` ) AS `nominal`,TH.`warning` ,TH.`critical`, 
		    ( PL.`nacD` * 1024 ) as 'nacD',
		    (PL.`nacU`*1024) as 'nacU',
		    LH.`location`
		FROM `bm_host` H 
		LEFT JOIN `bm_items_groups` IG ON IG.`groupid` = H.`groupid`
		LEFT JOIN `bm_plan` PL ON PL.`id_plan`=H.`id_plan`
		    INNER JOIN `bm_threshold` TH ON IG.`id_item`=TH.`id_item`
		    LEFT OUTER JOIN `tmp_locationHost` LH ON LH.`id_host`=H.`id_host`
		WHERE
		    H.`borrado`=0  AND 
		    H.`id_plan` > 0 AND 
		    TH.`report` = 'true' 
		GROUP BY `groupid`,`id_host`,`id_item`");
		
		if (!$getHostValid) {
		    $this->logs->error("[QoS] Error al obtener las sondas, ", NULL);
		    return false;
		}
		
		$getHostIDResult = $this->conexion->queryFetch(" SELECT  DISTINCT `id_host` FROM `tmp_hostid_itemid`");
		
		if ($getHostIDResult) {
		    foreach ($getHostIDResult as $key => $value) {
		        $hostID[] = $value['id_host'];
		    }
		} else {
		    $this->logs->error("[QoS] Error al obtener el id de los host , ", NULL);
		    return false;
		}
		
		
		$getItemIDResult = $this->conexion->queryFetch("SELECT  DISTINCT `id_item` FROM `tmp_hostid_itemid`");
	
		if ($getItemIDResult) {
			
		    foreach ($getItemIDResult as $key => $value) {
		        $itemID[] = $value['id_item'];
		    }
		    
		} else {
		    $this->logs->error("[QoS] Error al obtener el id de los items, ", NULL);
		    return false;
		}
		
		$PERCENTILE_REPORT_QOE = json_decode($this->parametro->get('PERCENTILE_REPORT_QOE','[]'));
		
		if(count($PERCENTILE_REPORT_QOE) > 1){
		    foreach ($PERCENTILE_REPORT_QOE as $key => $value) {
		    	$PERCENTILE[] = '0 as PERCENTIL_'.$value;
		    }   
		    $PERCENTILE = join(',', $PERCENTILE);
		} else {
		    $PERCENTILE = '0 as PERCENTIL_5,
		    0 as PERCENTIL_95,
		    0 as PERCENTIL_80';
		}
		
		//Get var  y cosas
		$this->basic->timeStart();
		
		$getVarLocation = "CREATE TEMPORARY TABLE tmp_statsQoS AS SELECT 
		    THI.`groupid`, THI.`id_item` as 'idItem' , THI.`id_plan` as 'idPlan', IF(THI.`location` IS NULL,'0|0',THI.`location`) as 'location' , 
		    MIN(H.`id_history`) As 'minHistory',
		    DATE_FORMAT(from_unixtime(clock),'" . $param->grp . "') as 'DATE',
		    round(avg(IF(`valid` = 1,`value`,NULL)),2) as AVG,
		    round(min(IF(`valid` = 1,`value`,NULL)),2) as MIN,
		    round(max(IF(`valid` = 1,`value`,NULL)),2) as MAX,
		    round(stddev_samp(IF(`valid` = 1,`value`,NULL)),2) as STD_SAMP,
		    round(stddev(IF(`valid` = 1,`value`,NULL)),2) as STD,
		    (`nacD`*THI.nominal/100) as 'DOWNLOAD',
		    (`nacU`*THI.nominal/100) as 'UPLOAD',
		    case THI.`type` 
		        when 'personalized' then 
		                IF(THI.nominal > THI.warning, 
		                        SUM(IF(H.`value` <=  THI.nominal AND H.`value` > ((THI.nominal*THI.warning)/100),1,0)) , 
		                        SUM(IF(H.`value` >=  THI.nominal AND H.`value` < ((THI.nominal*THI.warning)/100),1,0)))   
		        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.nominal/100) AND H.`value` >  (`nacD`*THI.warning/100) ,1,0))
		        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.nominal/100) AND H.`value` >  (`nacU`*THI.warning/100) ,1,0))
		    end as nominal,
		    case THI.`type` 
		        when 'personalized' then 
		                IF(THI.nominal > THI.warning, 
		                        SUM(IF(H.`value` >  THI.nominal ,1,0)) , 
		                        SUM(IF(H.`value` <  THI.nominal ,1,0)))   
		        when 'download' then SUM(IF(H.`value` >  (`nacD`*THI.nominal/100),1,0))
		        when 'upload' then SUM(IF(H.`value` >  (`nacU`*THI.nominal/100),1,0))
		    end as nominalD,
		    case THI.`type` 
		        when 'personalized' then 
		                IF(THI.nominal > THI.warning, 
		                        SUM(IF(H.`value` <=  ((THI.nominal*THI.warning)/100) AND H.`value` >  ((THI.nominal*THI.critical)/100),1,0)) , 
		                        SUM(IF(H.`value` >=  ((THI.nominal*THI.warning)/100) AND H.`value` <  ((THI.nominal*THI.critical)/100),1,0)))   
		        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.warning/100) && H.`value` >  (`nacD`*THI.critical/100),1,0))
		        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.warning/100) && H.`value` >  (`nacU`*THI.critical/100),1,0))
		    end as warning,
		    case THI.`type` 
		        when 'personalized' then 
		                IF(THI.nominal > THI.warning, 
		                        SUM(IF(H.`value` <= ((THI.nominal*THI.critical)/100),1,0)) , 
		                        SUM(IF(H.`value` >= ((THI.nominal*THI.critical)/100),1,0)))  
		        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.critical/100),1,0))
		        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.critical/100),1,0))
		    end as critical,
		    sum(if(valid=1,1,0)) as EXITOSA,
		    sum(if(valid=0,1,0)) as FALLIDA,
		    round(stddev_samp(IF(`valid` = 1,`value`,NULL))*1.96/sqrt(SUM(IF(`valid` = 1,1,0))),2) as INTERVALO,
		    round(2*stddev_samp(IF(`valid` = 1,`value`,NULL))/sqrt(SUM(IF(`valid` = 1,1,0))),2) as ERROR,
		    $PERCENTILE
		FROM tmp_hostid_itemid THI
		LEFT JOIN `".$this->dbNameCore."`.$server->dbnameBI H ON THI.`id_host`=H.`id_host` AND THI.`id_item`=H.`id_item`
		WHERE
		    H.`id_item` IN (".join(',', $itemID).") AND
		    H.`id_host` IN (".join(',', $hostID).") AND
		    $param->where AND
		    H.`valid`=1 AND 
		    H.`value` > 0  AND
		    THI.`location` IS NOT NULL  
		GROUP BY
		    THI.`groupid`,
		    THI.`id_item`,
		    THI.`id_plan`,
		    THI.`location`,
		    `DATE`";
		$getVarValidLocation = $this->conexion->query($getVarLocation);
		
		if (!$getVarValidLocation) {
		    $this->logs->error("[QoS] Error al calcular Location statsQoS, ", NULL);
		    return false;
		}
		
		$getVar = "INSERT INTO `tmp_statsQoS` SELECT 
		    THI.`groupid`, THI.`id_item` as 'idItem' , THI.`id_plan` as 'idPlan', 0 as `location`,
		    MIN(H.`id_history`) As 'minHistory',
		    DATE_FORMAT(from_unixtime(clock),'" . $param->grp . "') as 'DATE',
		    round(avg(IF(`valid` = 1,`value`,NULL)),2) as AVG,
		    round(min(IF(`valid` = 1,`value`,NULL)),2) as MIN,
		    round(max(IF(`valid` = 1,`value`,NULL)),2) as MAX,
		    round(stddev_samp(IF(`valid` = 1,`value`,NULL)),2) as STD_SAMP,
		    round(stddev(IF(`valid` = 1,`value`,NULL)),2) as STD,
		    (`nacD`*THI.nominal/100) as 'DOWNLOAD',
		    (`nacU`*THI.nominal/100) as 'UPLOAD',
		    case THI.`type` 
		        when 'personalized' then 
		                IF(THI.nominal > THI.warning, 
		                        SUM(IF(H.`value` <=  THI.nominal AND H.`value` > ((THI.nominal*THI.warning)/100),1,0)) , 
		                        SUM(IF(H.`value` >=  THI.nominal AND H.`value` < ((THI.nominal*THI.warning)/100),1,0))) 
		        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.nominal/100) AND H.`value` >  (`nacD`*THI.warning/100) ,1,0))
		        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.nominal/100) AND H.`value` >  (`nacU`*THI.warning/100) ,1,0))
		    end as nominal,
		    case THI.`type` 
		        when 'personalized' then 
		                IF(THI.nominal > THI.warning, 
		                        SUM(IF(H.`value` >  THI.nominal ,1,0)) , 
		                        SUM(IF(H.`value` <  THI.nominal ,1,0)))  
		        when 'download' then SUM(IF(H.`value` >  (`nacD`*THI.nominal/100),1,0))
		        when 'upload' then SUM(IF(H.`value` >  (`nacU`*THI.nominal/100),1,0))
		    end as nominalD,
		    case THI.`type` 
		        when 'personalized' then 
		                IF(THI.nominal > THI.warning, 
		                        SUM(IF(H.`value` <= ((THI.nominal*THI.warning)/100) AND H.`value` > ((THI.nominal*THI.critical)/100),1,0)) , 
		                        SUM(IF(H.`value` >= ((THI.nominal*THI.warning)/100) AND H.`value` < ((THI.nominal*THI.critical)/100),1,0)))  
		        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.warning/100) && H.`value` >  (`nacD`*THI.critical/100),1,0))
		        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.warning/100) && H.`value` >  (`nacU`*THI.critical/100),1,0))
		    end as warning,
		    case THI.`type` 
		        when 'personalized' then 
		                IF(THI.nominal > THI.warning, 
		                        SUM(IF(H.`value` <= ((THI.nominal*THI.critical)/100),1,0)) , 
		                        SUM(IF(H.`value` >= ((THI.nominal*THI.critical)/100),1,0))) 
		        when 'download' then SUM(IF(H.`value` <=  (`nacD`*THI.critical/100),1,0))
		        when 'upload' then SUM(IF(H.`value` <=  (`nacU`*THI.critical/100),1,0))
		    end as critical,
		    sum(if(valid=1,1,0)) as EXITOSA,
		    sum(if(valid=0,1,0)) as FALLIDA,
		    round(stddev_samp(IF(`valid` = 1,`value`,NULL))*1.96/sqrt(SUM(IF(`valid` = 1,1,0))),2) as INTERVALO,
		    round(2*stddev_samp(IF(`valid` = 1,`value`,NULL))/sqrt(SUM(IF(`valid` = 1,1,0))),2) as ERROR,
		    $PERCENTILE 
		FROM tmp_hostid_itemid THI
		LEFT JOIN `".$this->dbNameCore."`.$server->dbnameBI H ON THI.`id_host`=H.`id_host` AND THI.`id_item`=H.`id_item`
		WHERE
		    H.`id_item` IN (".join(',', $itemID).") AND
		    H.`id_host` IN (".join(',', $hostID).") AND
		    H.`valid`=1 AND 
		    H.`value` > 0  AND
		    $param->where
		GROUP BY
		    THI.`groupid`,
		    THI.`id_item`,
		    THI.`id_plan`,
		    `DATE`";
		
		$getVarValid = $this->conexion->query($getVar);
		
		if (!$getVarValid) {
		    $this->logs->error("[QoS] Error al calcular statsQoS, ", NULL);
		    return false;
		}
		    
		$duration = $this->basic->timeEnd();
		$this->logs->info("[QoS] Temporary table tmp_statsQoS created in $duration  seconds", NULL);
		
		$getMinHistory = $this->conexion->queryFetch("SELECT MIN(minHistory) as 'minHistory' FROM tmp_statsQoS;");
		
		if (!$getMinHistory ||  !is_numeric($getMinHistory[0]['minHistory']) || $getMinHistory[0]['minHistory'] < 1) {
		    $this->logs->error("[QoS] Error get min id History, ", NULL);
		    return false;
		} else {
		    $minHistory = $getMinHistory[0]['minHistory'];
		}
			
		$this->basic->timeStart();
		 
		$getValuePercentil = "CREATE TEMPORARY TABLE tmp_percentil SELECT H.`clock`,  TMP.`groupid`, TMP.`id_plan`, TMP.`id_item`, TMP.`location`,H.`value`
		FROM tmp_hostid_itemid TMP
		LEFT JOIN  `".$this->dbNameCore."`.$server->dbnameBI H  ON TMP.`id_host`=H.`id_host` AND TMP.`id_item`=H.`id_item`
		WHERE H.`id_history` >= $minHistory AND  $param->where
		ORDER BY H.`value`;";
		
		$getValuePercentilValid = $this->conexion->query($getValuePercentil);
		
		if (!$getValuePercentilValid) {
		    $this->logs->error("[QoS] Error al obtener los datos del history, ", NULL);
		    return false;
		}
		
		$duration = $this->basic->timeEnd();
		$this->logs->info("[QoS] Temporary table tmp_percentil created in $duration  seconds", NULL);
		
		$getPlanPercentilSQL = "SELECT * FROM `tmp_statsQoS` WHERE `DATE`='" . $param->clock . "' ORDER BY `groupid`,`idPlan`,`idItem`";
		$getPlanPercentil = $this->conexion->queryFetch($getPlanPercentilSQL);
		
		if (!$getPlanPercentil) {
		    $this->logs->error("[QoS] Error al obtener los planes a calcular, ", NULL);
		    return false;
		}
		
		$countN = count($getPlanPercentil);
		$startN = 1;
		
		foreach ($getPlanPercentil as $key => $value) {

		    $this->basic->timeStart();
		
		    $groupid = $value["groupid"];
		    $idPlan = $value["idPlan"];
		    $iditem = $value["idItem"];
		    $location = $value["location"];
		
		    $percentil = $this->calPercentil($iditem, $idPlan, $groupid,$location, $param->where);
		    
		    $PercentilArray = array();
		    
		    foreach ($percentil as $pkey => $pvalue) {
		        $PercentilArray[] = "`PERCENTIL_$pkey`='".$pvalue."'";
		    }
		    
		    $updatePercentil="UPDATE tmp_statsQoS SET ".join(',', $PercentilArray)." WHERE groupid=" . $groupid .
		        " AND DATE='" . $param->clock . "'" .
		        " AND idPlan='" . $idPlan . "'" .
		        " AND idItem='$iditem'" .
		        " AND location='$location'";
		  
		    $this->conexion->query($updatePercentil);
		
		    $duration = $this->basic->timeEnd();
		    $this->logs->info("[QoS] Processing statistics " . $param->clock  . " [" . $groupid . "] " . $idPlan . "(" . ($startN) . " of " . $countN . ") in " . $duration . " seconds");
		    $startN++;
		}
		
		$getPlanPercentilSQL = "SELECT * FROM `tmp_statsQoS` WHERE `DATE`='" . $param->clock . "' ORDER BY `groupid`,`idPlan`,`idItem`";
		
		$getPlanPercentil = $this->conexion->queryFetch($getPlanPercentilSQL);
		
		
		if (!$getPlanPercentil) {
		    $this->logs->error("[QoS] Error al obtener los planes a calcular, ", NULL);
		    return false;
		}
		    
		//Limpiando datos anteriores
		
		$deleteClockDataSQL = "DELETE FROM bm_inform WHERE clock='" . $param->clock . "'";
		
		$deleteClockData = $this->conexion->numRow($deleteClockDataSQL);
		
		if ($deleteClockData === false) {
		    $this->logs->error("[QoS] Error al borrar datos correspondientes a la fecha $clock", NULL);
		    return false;
		} 
		
		$insertInform = "INSERT INTO `bm_inform` (`clock`, `groupid`, `idPlan`, `idItem`, `format`, `type`, `value`, `location`) VALUES ";
		
		foreach ($getPlanPercentil as $key => $value) {
		    
		    $varResult = $value;
		    $idPlan = $varResult['idPlan'];
		    $idItem = $varResult['idItem'];
		    $groupid = $varResult['groupid'];
		    $location = $varResult["location"];
		    unset($varResult['idPlan']);
		    unset($varResult['idItem']);
		    unset($varResult['groupid']);
		    unset($varResult['clock']);
		    unset($varResult['location']);
		    foreach ($varResult as $type => $valueParam) {
		        $values[] = "('$param->clock', $groupid, $idPlan, $idItem, 'decimal', '$type', '$valueParam','$location')";
		    }   
		}
		
		$insertInform = $this->conexion->query($insertInform.join(',', $values),false,'logs_qoe');
		
		if (!$insertInform) {
		    $this->logs->error("[QoS] Error al guardar resultado, ", NULL);
		    return false;
		} 
		
		$duracion=date("U")-$dateStart;
		$this->logs->info("[QoS] Statistics completed in " . $duracion . " seconds for period " . $param->clock);
	}
}

$cmd = new Control(true, 'core.bi.node1.baking.cl',true);

/*
$cmd->parametro->remove('STDOUT');
$cmd->parametro->set('STDOUT', true);
$cmd->parametro->remove('DEBUG');
$cmd->parametro->set('DEBUG', true);*/

$cmd->parametro->set('LOGS_FILE', "logs_qoe");

$cmd->logs->info("[QoS] ===============================================");
$cmd->logs->info("[QoS] Starting Statistic Server");
$cmd->logs->info("[QoS] Copyright Baking Software 2013");
$cmd->logs->info("[QoS] Version 3");
$cmd->logs->info("[QoS] ===============================================");

$poller = new ServerQoe($cmd);

$poller->validStart();

$poller->start();
?>

