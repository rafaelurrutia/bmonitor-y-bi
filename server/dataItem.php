<?php
if(!function_exists('pcntl_fork')) die ('PCNTL no disponible');

class DataItem {
	private $dbCore = "bsw_bi";
	private $mailDestination = "";
	private $domainClient = "";
	
	private $aServer = array();
	
	private $host = "";
	private $user = "";
	private $pass = "";
	
	public function __construct($h, $u, $p, $d = false){
		if(isset($h)){
			$this->host = $h;
		}
		
		if(isset($u)){
			$this->user = $u;
		}
		
		if(isset($p)){
			$this->pass = $p;
		}
		
		$strWhere = "";
		
		if($d != false || $d != ""){
			$strWhere = " AND `dbName` = '".strtolower($d)."'";
		}
		
		$conn = mysql_connect($this->host, $this->user, $this->pass);
		mysql_select_db($this->dbCore, $conn);
		
		$sqlServer = "SELECT `idServer`, `name`, `domain`, `dbName`, `timezone`, `mailAlert` FROM `$this->dbCore`.`bi_server` WHERE `active` = 'true' $strWhere";
		$resultServer = mysql_query($sqlServer, $conn);
		
		while($rowServer = mysql_fetch_assoc($resultServer)){
			$server = array();
			$server["id_server"] = $rowServer["idServer"];
			$server["name"] = $rowServer["name"];
			$server["domain"] = $rowServer["domain"];
			$server["dbname"] = $rowServer["dbName"];
			$server["timezone"] = $rowServer["timezone"];
			$server["mail"] = $rowServer["mailAlert"];
			
			$this->aServer[] = $server;
		}
		
		mysql_free_result($resultServer);
		mysql_close($conn);		
	}
	
	public function start(){
		$this->createFork();
	}
	
	private function createFork(){
		foreach ($this->aServer as $keyServer => $valueServer) {
			$pid = pcntl_fork();
			
			if($pid == -1){
				echo "Error al crear el hilo perteneciente de la empresa ".$valueServer["name"]."\n";
				continue;
			}else if(!$pid){
				$this->mailDestination = $valueServer["mail"];
				$this->domainClient = $valueServer["domain"];
				
				echo "Ejecutando hilo perteneciente a ".$valueServer["name"].", dominio $this->domainClient, base de datos ".$valueServer["dbname"].", mail de contacto $this->mailDestination\n";
				$threadconn = mysql_connect($this->host, $this->user, $this->pass);
				mysql_select_db($valueServer["dbname"], $threadconn);
				
				$this->setTimezone($valueServer["timezone"], $threadconn);
				$this->validateItem($threadconn);
				//$this->sendTrap($threadconn);
				
				mysql_close($threadconn);
				exit();
			}
		}
	}
	
	private function setTimezone($tmz, $id){
		date_default_timezone_set($tmz);
		ini_set("date.timezone", $tmz);
		
		$now = new DateTime();
		$mins = $now->getOffset() / 60;
		$sgn = ($mins < 0 ? -1 : 1);
		$mins = abs($mins);
		$hrs = floor($mins / 60);
		$mins -= $hrs * 60;

		$offset = sprintf('%+d:%02d', $hrs * $sgn, $mins);
		mysql_query("SET time_zone='$offset'", $id);
		echo "TIME: $offset\n";
	}
	
	private function validateItem($id){
		$sqlItems = "SELECT DISTINCT(BPV.`id_monitor`) AS 'id_item', BI.`descriptionLong` AS 'name_item', BI.`unit` AS 'unit_item' FROM `bm_profiles_category` BPC LEFT JOIN `bm_profiles_values` BPV ON BPV.`id_category` = BPC.`id_category` LEFT JOIN `bm_items` BI ON BI.`id_item` = BPV.`id_monitor` WHERE BPC.`status` = 'true' AND BPV.`id_monitor` > 0 AND BI.`unit` != ''";
		$resultItems = mysql_query($sqlItems, $id);
		
		while($valueItems = mysql_fetch_assoc($resultItems)){
			/*
			$sqlData = "SELECT COUNT(*) AS 'ndata' FROM `bm_history` WHERE `id_item` = ".$valueItems["id_item"]." AND `clock` <= UNIX_TIMESTAMP(NOW()) AND `clock` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR))";
			$numData = mysql_fetch_assoc(mysql_query($sqlData, $id));
			
			$withData = "false";
			$dateLastData = "";
			
			if($numData["ndata"] > 0){
				$withData = "true";
				
				$sqlDateDate = "SELECT FROM_UNIXTIME(`clock`) AS dateData FROM `bm_history` WHERE `id_item` = ".$valueItems["id_item"]." ORDER BY `clock` DESC LIMIT 1";
				$tmpDate = mysql_fetch_assoc(mysql_query($sqlDateDate, $id));
				$dateLastData = $tmpDate["dateData"];				
			}
			
			$sqlExistData = "SELECT * FROM `bm_items_alert_data` WHERE `id_item` = ".$valueItems["id_item"]." LIMIT 1";
			$resultExistData = mysql_query($sqlExistData, $id);
			$numExistData = mysql_num_rows($resultExistData);
			
			if($numExistData > 0){
				while($filaExistData = mysql_fetch_assoc($resultExistData)){
					if($filaExistData["with_data"] != $withData){
						$sqlUpdateData = "UPDATE `bm_items_alert_data` SET `with_data` = '$withData', `changetrap` = 'true', `sendtrap` = 'false', `datetime_last_data` = '$dateLastData', `datetime_sendtrap` = NOW() WHERE `id_item` = ".$filaExistData["id_item"];
						mysql_query($sqlUpdateData, $id);
					}
				}
				
				mysql_free_result($resultExistData);
			}else{
				$sqlInsertData = "INSERT INTO `bm_items_alert_data` (`id_item`, `name_item`, `unit`, `with_data`, `changetrap`, `sendtrap`, `datetime_last_data`, `datetime_sendtrap`) VALUES(".$valueItems["id_item"].", '".$valueItems["name_item"]."', '".$valueItems["unit_item"]."', '$withData', 'true', 'false', '$dateLastData', NOW())";
				mysql_query($sqlInsertData, $id);
				echo "insertando id item: ".$valueItems["id_item"]." wit_data: $withData datatetime_data: $dateLastData \n";
			}
			 * 
			 */
			 
			$sqlExistTable = "SHOW TABLES LIKE 'xyz_".str_pad($valueItems["id_item"], 10, "0", STR_PAD_LEFT)."'";
			$numExistTable = mysql_num_rows(mysql_query($sqlExistTable, $id));
			
			if($numExistTable > 0){
				$sqlData = "SELECT COUNT(*) AS 'ndata' FROM `xyz_".str_pad($valueItems["id_item"], 10, "0", STR_PAD_LEFT)."` WHERE `clock` <= UNIX_TIMESTAMP(NOW()) AND `clock` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR))";
				$numData = mysql_fetch_assoc(mysql_query($sqlData, $id));
			
				$withData = "false";
				$dateLastData = "";
			
				if($numData["ndata"] > 0){
					$withData = "true";
				
					$sqlDateDate = "SELECT FROM_UNIXTIME(`clock`) AS dateData FROM `xyz_".str_pad($valueItems["id_item"], 10, "0", STR_PAD_LEFT)."` ORDER BY `clock` DESC LIMIT 1";
					$tmpDate = mysql_fetch_assoc(mysql_query($sqlDateDate, $id));
					$dateLastData = $tmpDate["dateData"];				
				}
			
				$sqlExistData = "SELECT * FROM `bm_items_data` WHERE `id_item` = ".$valueItems["id_item"]." LIMIT 1";
				$resultExistData = mysql_query($sqlExistData, $id);
				$numExistData = mysql_num_rows($resultExistData);
			
				if($numExistData > 0){
					while($filaExistData = mysql_fetch_assoc($resultExistData)){
						if($filaExistData["with_data"] != $withData){
							$sqlUpdateData = "UPDATE `bm_items_data` SET `with_data` = '$withData', `changetrap` = 'true', `sendtrap` = 'false', `datetime_sendtrap` = NOW() WHERE `id_item` = ".$filaExistData["id_item"];
							mysql_query($sqlUpdateData, $id);
						}
					}
				
					mysql_free_result($resultExistData);
				}else{
					$sqlInsertData = "INSERT INTO `bm_items_data` (`id_item`, `name_item`, `unit`, `with_data`, `changetrap`, `sendtrap`, `datetime_sendtrap`) VALUES(".$valueItems["id_item"].", '".$valueItems["name_item"]."', '".$valueItems["unit_item"]."', '$withData', 'true', 'false', NOW())";
					mysql_query($sqlInsertData, $id);
					error_log("insertando de tabla xyz id item: ".$valueItems["id_item"]." with_data: $withData datatetime_data: $dateLastData \n", 3, "/home/webdeveloper/error_data.log");
				}
			}else{
				$sqlData = "SELECT COUNT(*) AS 'ndata' FROM `bm_history` WHERE `id_item` = ".$valueItems["id_item"]." AND `clock` <= UNIX_TIMESTAMP(NOW()) AND `clock` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR))";
				$numData = mysql_fetch_assoc(mysql_query($sqlData, $id));
			
				$withData = "false";
				$dateLastData = "";
			
				if($numData["ndata"] > 0){
					$withData = "true";
				
					$sqlDateDate = "SELECT FROM_UNIXTIME(`clock`) AS dateData FROM `bm_history` WHERE `id_item` = ".$valueItems["id_item"]." ORDER BY `clock` DESC LIMIT 1";
					$tmpDate = mysql_fetch_assoc(mysql_query($sqlDateDate, $id));
					$dateLastData = $tmpDate["dateData"];				
				}
			
				$sqlExistData = "SELECT * FROM `bm_items_data` WHERE `id_item` = ".$valueItems["id_item"]." LIMIT 1";
				$resultExistData = mysql_query($sqlExistData, $id);
				$numExistData = mysql_num_rows($resultExistData);
			
				if($numExistData > 0){
					while($filaExistData = mysql_fetch_assoc($resultExistData)){
						if($filaExistData["with_data"] != $withData){
							$sqlUpdateData = "UPDATE `bm_items_data` SET `with_data` = '$withData', `changetrap` = 'true', `sendtrap` = 'false', `datetime_sendtrap` = NOW() WHERE `id_item` = ".$filaExistData["id_item"];
							mysql_query($sqlUpdateData, $id);
						}
					}
				
					mysql_free_result($resultExistData);
				}else{
					$sqlInsertData = "INSERT INTO `bm_items_data` (`id_item`, `name_item`, `unit`, `with_data`, `changetrap`, `sendtrap`, `datetime_sendtrap`) VALUES(".$valueItems["id_item"].", '".$valueItems["name_item"]."', '".$valueItems["unit_item"]."', '$withData', 'true', 'false', NOW())";
					mysql_query($sqlInsertData, $id);
					error_log("insertando de tabla bm_history id item: ".$valueItems["id_item"]." with_data: $withData datatetime_data: $dateLastData \n", 3, "/home/webdeveloper/error_data.log");
				}				
			}
		}
		
		mysql_free_result($resultItems);
	}
	
	private function sendTrap($id){
		$sqlDataItem = "SELECT `id_item`, `name_item`, `unit`, DATE(`datetime_last_data`) AS 'last_date', DATE_FORMAT(`datetime_last_data`, '%H:%i') AS 'last_hour', DATABASE() AS 'dbase' FROM `bm_items_alert_data` WHERE `sendtrap` = 'false' AND DATE(NOW()) = DATE(`datetime_sendtrap`)";
		$resultDataItem = mysql_query($sqlDataItem, $id);
		
		$sqlParametroServer = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'TRAPS_ALERT_DATAITEM_SERVER' LIMIT 1";
		$resultParametroServer = mysql_query($sqlParametroServer, $id);
		$snmpServer = mysql_fetch_assoc($resultParametroServer);
		
		$sqlParametroCommunity = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'TRAPS_ALERT_DATAITEM_COMMUNITY' LIMIT 1";
		$resultParametroCommunity = mysql_query($sqlParametroCommunity, $id);
		$snmpCommunity = mysql_fetch_assoc($resultParametroCommunity);
		
		$sqlParametroHeader = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'TRAPS_ALERT_DATAITEM_HEADER' LIMIT 1";
		$resultParametroHeader = mysql_query($sqlParametroHeader, $id);
		$snmpHeader = mysql_fetch_assoc($resultParametroHeader);
		
		if($snmpServer["valor"] != "" && $snmpCommunity["valor"] != "" && $snmpHeader["valor"]){
			$trapserver = $snmpServer["valor"];
			$community = $snmpCommunity["valor"];
			
			while($filaDataItem = mysql_fetch_assoc($resultDataItem)){
				if($filaDataItem["changetrap"] == "true"){
					$typeAlert = "";
					$oid = "";
					
					if($filaDataItem["with_data"] == "true"){
						$typeAlert = "WITH_DATA";
						$oid = $snmpHeader["valor"].".1";
					}else{
						$typeAlert = "WITHOUT_DATA";
						$oid = $snmpHeader["valor"].".2";
					}
					
					$filaDataItem["name_item"] = str_replace(" ", "-", $filaDataItem["name_item"]);
					
					$sh = "snmptrap -v 2c -c $community $trapserver '' $oid $oid s \"DB=".$filaDataItem["dbase"]."*IT=".$filaDataItem["name_item"]."*LDTD=".$filaDataItem["last_date"]."*LHRD=".$filaDataItem["last_hour"]."*TA=$typeAlert\"";
					shell_exec($sh);
					
					$sqlUpdateAlert = "UPDATE `bm_items_alert_data` SET `sendtrap` = 'true', `changetrap` = 'false' WHERE `id` = ".$filaDataItem["id_item"];
					mysql_query($sqlUpdateAlert, $id);
				}
			}
		}
	}
}

if($argv[1] != ""){
	$dataItem = new DataItem("localhost", "root", "bsw$$2009", $argv[1]);	
}else{
	$dataItem = new DataItem("localhost", "root", "bsw$$2009", false);
}

$dataItem->start();
?>