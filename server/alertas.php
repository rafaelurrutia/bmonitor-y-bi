<?php
if(!function_exists('pcntl_fork')) die ('PCNTL no disponible');

class Alertas {
	private $dbCore = "bsw_bi";
	private $mailDestination = "";
	private $domainClient = "";
	
	private $aServer = array();
	
	private $host = "";
	private $user = "";
	private $pass = "";
	
	public function __construct($h, $u, $p){
		if(isset($h)){
			$this->host = $h;
		}
		
		if(isset($u)){
			$this->user = $u;
		}
		
		if(isset($p)){
			$this->pass = $p;
		}
		
		$conn = mysql_connect($this->host, $this->user, $this->pass);
		mysql_select_db($this->dbCore, $conn);
		
		$sqlServer = "SELECT `idServer`, `name`, `domain`, `dbName`, `timezone`, `mailAlert` FROM `$this->dbCore`.`bi_server` WHERE `active` = 'true'";
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
				$this->validateAlert($threadconn);
				$this->sendTrap($threadconn);
				
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
	
	private function validateAlert($id){
		$sqlThold = "SELECT BT.`id_item`, BT.`nominal`, BT.`warning`, BT.`critical`, BT.`cicle`, BHG.`groupid`, LOWER(BHG.`name`) AS 'groupname', BI.`descriptionLong` AS 'itemname', IF(lower(BI.`descriptionLong`) LIKE '%nac%', 'nac', IF(lower(BI.`descriptionLong`) LIKE '%int%', 'int', IF(lower(BI.`descriptionLong`) LIKE '%loc%', 'loc', 'nac'))) AS plan_type, BI.`unit` FROM `bm_threshold` BT JOIN `bm_items` BI ON BI.`id_item` = BT.`id_item` JOIN `bm_items_groups` BIG ON BIG.`id_item` = BI.`id_item` JOIN `bm_host_groups` BHG ON BIG.`groupid` = BHG.`groupid` WHERE BT.`alert` = 'true'";
		$resultThold = mysql_query($sqlThold, $id);
		
		while($valueThold = mysql_fetch_assoc($resultThold)){
			if($valueThold["nominal"] == -1){
				$sqlPlan = "SELECT BP.`id_plan`, BP.`plan`, (BP.`".$valueThold["plan_type"]."D` * 1024) AS 'down' FROM `bm_plan_groups` BPG JOIN `bm_plan` BP ON BPG.`id_plan` = BP.`id_plan` WHERE BPG.`groupid` = ".$valueThold["groupid"]." AND BPG.`borrado` = 0";
				$resultPlan = mysql_query($sqlPlan, $id);			
				
				while($valuePlan = mysql_fetch_assoc($resultPlan)){
					$valPLAN = $valuePlan["down"];
					
					$sqlHost = "SELECT `id_host`, `host`, `codigosonda` FROM `bm_host` WHERE `id_plan` = ".$valuePlan["id_plan"]." AND `borrado` = 0";
					$resultHost = mysql_query($sqlHost, $id);
					
					while($valueHost = mysql_fetch_assoc($resultHost)){
						$tmpTable = "SHOW TABLES LIKE 'xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)."'";
						$numTmpTable = mysql_num_rows(mysql_query($tmpTable, $id));
						
						if($numTmpTable > 0){
							$sqlData = "SELECT BH.`value`, FROM_UNIXTIME(BH.`clock`) AS 'datetime' FROM `xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)."` BH WHERE BH.`id_host` = ".$valueHost["id_host"]." ORDER BY BH.`clock` DESC LIMIT ".$valueThold["cicle"];
						}else{
							$sqlData = "SELECT BH.`value`, FROM_UNIXTIME(BH.`clock`) AS 'datetime' FROM `bm_history` BH WHERE BH.`id_item` = ".$valueThold["id_item"]." AND BH.`id_host` = ".$valueHost["id_host"]." ORDER BY BH.`clock` DESC LIMIT ".$valueThold["cicle"];	
						}
						
						
						$resultData = mysql_query($sqlData, $id);
					
						$firstRecord = array();
						$contador = 0;
						$sumador = 0;
						$promedio = 0;
					
						$isWarning = 0;
						$isCritical = 0;
						$isClear = 0;
					
						while($valueData = mysql_fetch_assoc($resultData)){
							if($contador == 0){
								$firstRecord["datetime"] = $valueData["datetime"];
								$firstRecord["id_host"] = $valueHost["id_host"];
								$firstRecord["host"] = $valueHost["host"];
								$firstRecord["code_host"] = $valueHost["codigosonda"];
								$firstRecord["last_value"] = $valueData["value"];
								$firstRecord["id_item"] = $valueThold["id_item"];
								$firstRecord["item"] = $valueThold["itemname"];
								$firstRecord["id_group"] = $valueThold["groupid"];
								$firstRecord["group"] = $valueThold["groupname"];
								$firstRecord["nominal_value"] = $valPLAN;
								$firstRecord["unit"] = $valueThold["unit"];
								$firstRecord["id_plan"] = $valuePlan["id_plan"];
								$firstRecord["plan"] = $valuePlan["plan"];
							}
						
							$contador++;
						
							$sumador += $valueData["value"];
						}
					
						$promedio = $sumador / $valueThold["cicle"];
					
						$validacion_promedio = round(($promedio * 100) / $valPLAN, 2);
					
						if($validacion_promedio <= $valueThold["warning"] && $validacion_promedio > $valueThold["critical"]){
							$isWarning = 1;
						}else if($validacion_promedio <= $valueThold["critical"]){
							$isCritical = 1;
						}else{
							$isClear = 1;
						}
					
						$sqlExistAlert = "SELECT * FROM `bm_items_alert` WHERE `id_host` = ".$firstRecord["id_host"]." AND `id_item` = ".$firstRecord["id_item"]." LIMIT 1";
						$resultExistAlert = mysql_query($sqlExistAlert, $id);
						$numExistAlert = mysql_num_rows($resultExistAlert);
					
						if($numExistAlert > 0){
							while($filaExistAlert = mysql_fetch_assoc($resultExistAlert)){
								if($filaExistAlert["fail_warning"] == 1 && ($isCritical == 1 || $isClear == 1)){
									$sqlUpdateAlert = "UPDATE `bm_items_alert` SET `fail_warning` = 0, `fail_critical` = $isCritical, `fail_repair` = $isClear, `changetrap` = 'true', `sendtrap` = 'false', `lastvalue` = ".$firstRecord["last_value"].", `nominal_value` = $valPLAN, `warning_value` = ".$valueThold["warning"].", `critical_value` = ".$valueThold["critical"].", `averagevalue` = $validacion_promedio, `datetime_sendtrap` = NOW(), `datetime` = '".$firstRecord["datetime"]."' WHERE `id` = ".$filaExistAlert["id"];
									mysql_query($sqlUpdateAlert, $id);	
								}else if($filaExistAlert["fail_critical"] == 1 && $isClear == 1){
									$sqlUpdateAlert = "UPDATE `bm_items_alert` SET `fail_warning` = 0, `fail_critical` = 0, `fail_repair` = $isClear, `changetrap` = 'true', `sendtrap` = 'false', `lastvalue` = ".$firstRecord["last_value"].", `nominal_value` = $valPLAN, `warning_value` = ".$valueThold["warning"].", `critical_value` = ".$valueThold["critical"].", `averagevalue` = $validacion_promedio, `datetime_sendtrap` = NOW(), `datetime` = '".$firstRecord["datetime"]."' WHERE `id` = ".$filaExistAlert["id"];
									mysql_query($sqlUpdateAlert, $id);
								}else if($filaExistAlert["fail_repair"] == 1 && ($isWarning == 1 || $isCritical == 1)){
									$sqlUpdateAlert = "UPDATE `bm_items_alert` SET `fail_warning` = $isWarning, `fail_critical` = $isCritical, `fail_repair` = 0, `changetrap` = 'true', `sendtrap` = 'false', `lastvalue` = ".$firstRecord["last_value"].", `nominal_value` = $valPLAN, `warning_value` = ".$valueThold["warning"].", `critical_value` = ".$valueThold["critical"].", `averagevalue` = $validacion_promedio, `datetime_sendtrap` = NOW(), `datetime` = '".$firstRecord["datetime"]."' WHERE `id` = ".$filaExistAlert["id"];
									mysql_query($sqlUpdateAlert, $id);
								}
							}
					
							mysql_free_result($resultExistAlert);
						}else{
							if($isWarning == 1 || $isCritical == 1){
								$sqlInsertAlert = "INSERT INTO `bm_items_alert` (`id_host`, `name_host`, `id_item`, `name_item`, `datetime`, `groupname`, `lastvalue`, `groupid`, `fail_warning`, `fail_critical`, `code_host`, `nominal_value`, `changetrap`, `sendtrap`, `fail_repair`, `unit`, `warning_value`, `critical_value`, `id_plan`, `name_plan`, `averagevalue`, `datetime_sendtrap`) VALUES(".$firstRecord["id_host"].", '".$firstRecord["host"]."', ".$firstRecord["id_item"].", '".$firstRecord["item"]."', '".$firstRecord["datetime"]."', '".$firstRecord["group"]."', ".$firstRecord["last_value"].", ".$firstRecord["id_group"].", $isWarning, $isCritical, '".$firstRecord["code_host"]."', '".$firstRecord["nominal_value"]."', 'true', 'false', $isClear, '".$firstRecord["unit"]."', ".$valueThold["warning"].", ".$valueThold["critical"].", ".$firstRecord["id_plan"].", '".$firstRecord["plan"]."', ".$validacion_promedio.", NOW())";
								mysql_query($sqlInsertAlert, $id);
							}
						}
					
						mysql_free_result($resultData);
					}

					mysql_free_result($resultHost);
				}
			}else if($valueThold["nominal"] == -2){
				$sqlPlan = "SELECT BP.`id_plan`, BP.`plan`, (BP.`".$valueThold["plan_type"]."U` * 1024) AS 'up' FROM `bm_plan_groups` BPG JOIN `bm_plan` BP ON BPG.`id_plan` = BP.`id_plan` WHERE BPG.`groupid` = ".$valueThold["groupid"]." AND BPG.`borrado` = 0";
				$resultPlan = mysql_query($sqlPlan, $id);
				
				while($valuePlan = mysql_fetch_assoc($resultPlan)){
					$valPLAN = $valuePlan["up"];
					
					$sqlHost = "SELECT `id_host`, `host`, `codigosonda` FROM `bm_host` WHERE `id_plan` = ".$valuePlan["id_plan"]." AND `borrado` = 0";
					$resultHost = mysql_query($sqlHost, $id);
					
					while($valueHost = mysql_fetch_assoc($resultHost)){
						$tmpTable = "SHOW TABLES LIKE 'xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)."'";
						$numTmpTable = mysql_num_rows(mysql_query($tmpTable, $id));
						
						if($numTmpTable > 0){
							$sqlData = "SELECT BH.`value`, FROM_UNIXTIME(BH.`clock`) AS 'datetime' FROM `xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)."` BH WHERE BH.`id_host` = ".$valueHost["id_host"]." ORDER BY BH.`clock` DESC LIMIT ".$valueThold["cicle"]; 
						}else{
							$sqlData = "SELECT BH.`value`, FROM_UNIXTIME(BH.`clock`) AS 'datetime' FROM `bm_history` BH WHERE BH.`id_item` = ".$valueThold["id_item"]." AND BH.`id_host` = ".$valueHost["id_host"]." ORDER BY `id_history` DESC LIMIT ".$valueThold["cicle"];	
						}
						
						$resultData = mysql_query($sqlData, $id);
					
						$firstRecord = array();
						$contador = 0;
						$sumador = 0;
						$promedio = 0;
					
						$isWarning = 0;
						$isCritical = 0;
						$isClear = 0;
					
						while($valueData = mysql_fetch_assoc($resultData)){
						
							if($contador == 0){
								$firstRecord["datetime"] = $valueData["datetime"];
								$firstRecord["id_host"] = $valueHost["id_host"];
								$firstRecord["host"] = $valueHost["host"];
								$firstRecord["code_host"] = $valueHost["codigosonda"];
								$firstRecord["last_value"] = $valueData["value"];
								$firstRecord["id_item"] = $valueThold["id_item"];
								$firstRecord["item"] = $valueThold["itemname"];
								$firstRecord["id_group"] = $valueThold["groupid"];
								$firstRecord["group"] = $valueThold["groupname"];
								$firstRecord["nominal_value"] = $valPLAN;
								$firstRecord["unit"] = $valueThold["unit"];
								$firstRecord["id_plan"] = $valuePlan["id_plan"];
								$firstRecord["plan"] = $valuePlan["plan"];
							}
						
							$contador++;
						
							$sumador += $valueData["value"];
						}
					
						$promedio = $sumador / $valueThold["cicle"];
					
						$validacion_promedio = round(($promedio * 100) / $valPLAN, 2);
					
						if($validacion_promedio <= $valueThold["warning"] && $validacion_promedio > $valueThold["critical"]){
							$isWarning = 1;
						}else if($validacion_promedio <= $valueThold["critical"]){
							$isCritical = 1;
						}else{
							$isClear = 1;
						}
					
						$sqlExistAlert = "SELECT * FROM `bm_items_alert` WHERE `id_host` = ".$firstRecord["id_host"]." AND `id_item` = ".$firstRecord["id_item"]." LIMIT 1";
						$resultExistAlert = mysql_query($sqlExistAlert, $id);
						$numExistAlert = mysql_num_rows($resultExistAlert);
					
						if($numExistAlert > 0){
							while($filaExistAlert = mysql_fetch_assoc($resultExistAlert)){
								if($filaExistAlert["fail_warning"] == 1 && ($isCritical == 1 || $isClear == 1)){
									$sqlUpdateAlert = "UPDATE `bm_items_alert` SET `fail_warning` = 0, `fail_critical` = $isCritical, `fail_repair` = $isClear, `changetrap` = 'true', `sendtrap` = 'false', `lastvalue` = ".$firstRecord["last_value"].", `nominal_value` = $valPLAN, `warning_value` = ".$valueThold["warning"].", `critical_value` = ".$valueThold["critical"].", `averagevalue` = $validacion_promedio, `datetime_sendtrap` = NOW(), `datetime` = '".$firstRecord["datetime"]."' WHERE `id` = ".$filaExistAlert["id"];
									mysql_query($sqlUpdateAlert, $id);	
								}else if($filaExistAlert["fail_critical"] == 1 && $isClear == 1){
									$sqlUpdateAlert = "UPDATE `bm_items_alert` SET `fail_warning` = 0, `fail_critical` = 0, `fail_repair` = $isClear, `changetrap` = 'true', `sendtrap` = 'false', `lastvalue` = ".$firstRecord["last_value"].", `nominal_value` = $valPLAN, `warning_value` = ".$valueThold["warning"].", `critical_value` = ".$valueThold["critical"].", `averagevalue` = $validacion_promedio, `datetime_sendtrap` = NOW(), `datetime` = '".$firstRecord["datetime"]."' WHERE `id` = ".$filaExistAlert["id"];
									mysql_query($sqlUpdateAlert, $id);
								}else if($filaExistAlert["fail_repair"] == 1 && ($isWarning == 1 || $isCritical == 1)){
									$sqlUpdateAlert = "UPDATE `bm_items_alert` SET `fail_warning` = $isWarning, `fail_critical` = $isCritical, `fail_repair` = 0, `changetrap` = 'true', `sendtrap` = 'false', `lastvalue` = ".$firstRecord["last_value"].", `nominal_value` = $valPLAN, `warning_value` = ".$valueThold["warning"].", `critical_value` = ".$valueThold["critical"].", `averagevalue` = $validacion_promedio, `datetime_sendtrap` = NOW(), `datetime` = '".$firstRecord["datetime"]."' WHERE `id` = ".$filaExistAlert["id"];
									mysql_query($sqlUpdateAlert, $id);
								}
							}
					
							mysql_free_result($resultExistAlert);
						}else{
							if($isWarning == 1 || $isCritical == 1){
								$sqlInsertAlert = "INSERT INTO `bm_items_alert` (`id_host`, `name_host`, `id_item`, `name_item`, `datetime`, `groupname`, `lastvalue`, `groupid`, `fail_warning`, `fail_critical`, `code_host`, `nominal_value`, `changetrap`, `sendtrap`, `fail_repair`, `unit`, `warning_value`, `critical_value`, `id_plan`, `name_plan`, `averagevalue`, `datetime_sendtrap`) VALUES(".$firstRecord["id_host"].", '".$firstRecord["host"]."', ".$firstRecord["id_item"].", '".$firstRecord["item"]."', '".$firstRecord["datetime"]."', '".$firstRecord["group"]."', ".$firstRecord["last_value"].", ".$firstRecord["id_group"].", $isWarning, $isCritical, '".$firstRecord["code_host"]."', '".$firstRecord["nominal_value"]."', 'true', 'false', $isClear, '".$firstRecord["unit"]."', ".$valueThold["warning"].", ".$valueThold["critical"].", ".$firstRecord["id_plan"].", '".$firstRecord["plan"]."', ".$validacion_promedio.", NOW())";
								mysql_query($sqlInsertAlert, $id);
							}
						}
					
						mysql_free_result($resultData);
					}
					
					mysql_free_result($resultHost);
				}
				
				mysql_free_result($resultPlan);
			}else{
				$sqlHost = "SELECT `id_host`, `host`, `codigosonda` FROM `bm_host` WHERE `borrado` = 0";
				$resultHost = mysql_query($sqlHost, $id);
				
				while($valueHost = mysql_fetch_assoc($resultHost)){
					$tmpTable = "SHOW TABLES LIKE 'xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)."'";
					$numTmpTable = mysql_num_rows(mysql_query($tmpTable, $id));
					
					if($numTmpTable > 0){
						$sqlData = "SELECT BH.`value`, FROM_UNIXTIME(BH.`clock`) AS 'datetime' FROM `xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)."` BH WHERE BH.`id_host` = ".$valueHost["id_host"]." ORDER BY BH.`clock` DESC LIMIT ".$valueThold["cicle"];
					}else{
						$sqlData = "SELECT BH.`value`, FROM_UNIXTIME(BH.`clock`) AS 'datetime' FROM `bm_history` BH WHERE BH.`id_item` = ".$valueThold["id_item"]." AND BH.`id_host` = ".$valueHost["id_host"]." ORDER BY `id_history` DESC LIMIT ".$valueThold["cicle"];
					}
						
					$resultData = mysql_query($sqlData, $id);
				
					$firstRecord = array();
					$contador = 0;
					$sumador = 0;
					$promedio = 0;
				
					$isWarning = 0;
					$isCritical = 0;
					$isClear = 0;
				
					while($valueData = mysql_fetch_assoc($resultData)){
						if($contador == 0){
							$firstRecord["datetime"] = $valueData["datetime"];
							$firstRecord["id_host"] = $valueHost["id_host"];
							$firstRecord["host"] = $valueHost["host"];
							$firstRecord["code_host"] = $valueHost["codigosonda"];
							$firstRecord["last_value"] = $valueData["value"];
							$firstRecord["id_item"] = $valueThold["id_item"];
							$firstRecord["item"] = $valueThold["itemname"];
							$firstRecord["id_group"] = $valueThold["groupid"];
							$firstRecord["group"] = $valueThold["groupname"];
							$firstRecord["nominal_value"] = $valueThold["nominal"];
							$firstRecord["unit"] = $valueThold["unit"];
						}
						
						$contador++;
						
						$sumador += $valueData["value"];
					}
				
					$promedio = $sumador / $valueThold["cicle"];
					
					$validacion_promedio = round(($promedio * 100) / $firstRecord["nominal_value"], 2);
				
					if($valueThold["warning"] > $valueThold["critical"]){
						if($validacion_promedio <= $valueThold["warning"] && $validacion_promedio > $valueThold["critical"]){
							$isWarning = 1;
						}else if($validacion_promedio <= $valueThold["critical"]){
							$isCritical = 1;
						}else{
							$isClear = 1;
						}
					}else{
						if($validacion_promedio >= $valueThold["warning"] && $validacion_promedio < $valueThold["critical"]){
							$isWarning = 1;
						}else if($validacion_promedio >= $valueThold["critical"]){
							$isCritical = 1;
						}else{
							$isClear = 1;
						}
					}
				
					$sqlExistAlert = "SELECT * FROM `bm_items_alert` WHERE `id_host` = ".$firstRecord["id_host"]." AND `id_item` = ".$firstRecord["id_item"]." LIMIT 1";
					$resultExistAlert = mysql_query($sqlExistAlert, $id);
					$numExistAlert = mysql_num_rows($resultExistAlert);
				
					if($numExistAlert > 0){
						while($filaExistAlert = mysql_fetch_assoc($resultExistAlert)){
							if($filaExistAlert["fail_warning"] == 1 && ($isCritical == 1 || $isClear == 1)){
								$sqlUpdateAlert = "UPDATE `bm_items_alert` SET `fail_warning` = 0, `fail_critical` = $isCritical, `fail_repair` = $isClear, `changetrap` = 'true', `sendtrap` = 'false', `lastvalue` = ".$firstRecord["last_value"].", `nominal_value` = ".$valueThold["nominal"].", `warning_value` = ".$valueThold["warning"].", `critical_value` = ".$valueThold["critical"].", `averagevalue` = $validacion_promedio, `datetime_sendtrap` = NOW(), `datetime` = '".$firstRecord["datetime"]."' WHERE `id` = ".$filaExistAlert["id"];
								mysql_query($sqlUpdateAlert, $id);	
							}else if($filaExistAlert["fail_critical"] == 1 && $isClear == 1){
								$sqlUpdateAlert = "UPDATE `bm_items_alert` SET `fail_warning` = 0, `fail_critical` = 0, `fail_repair` = $isClear, `changetrap` = 'true', `sendtrap` = 'false', `lastvalue` = ".$firstRecord["last_value"].", `nominal_value` = ".$valueThold["nominal"].", `warning_value` = ".$valueThold["warning"].", `critical_value` = ".$valueThold["critical"].", `averagevalue` = $validacion_promedio, `datetime_sendtrap` = NOW(), `datetime` = '".$firstRecord["datetime"]."' WHERE `id` = ".$filaExistAlert["id"];
								mysql_query($sqlUpdateAlert, $id);
							}else if($filaExistAlert["fail_repair"] == 1 && ($isWarning == 1 || $isCritical == 1)){
								$sqlUpdateAlert = "UPDATE `bm_items_alert` SET `fail_warning` = $isWarning, `fail_critical` = $isCritical, `fail_repair` = 0, `changetrap` = 'true', `sendtrap` = 'false', `lastvalue` = ".$firstRecord["last_value"].", `nominal_value` = ".$valueThold["nominal"].", `warning_value` = ".$valueThold["warning"].", `critical_value` = ".$valueThold["critical"].", `averagevalue` = $validacion_promedio, `datetime_sendtrap` = NOW(), `datetime` = '".$firstRecord["datetime"]."' WHERE `id` = ".$filaExistAlert["id"];
								mysql_query($sqlUpdateAlert, $id);
							}
						}
					
						mysql_free_result($resultExistAlert);
					}else{
						if($isWarning == 1 || $isCritical == 1){
							$sqlInsertAlert = "INSERT INTO `bm_items_alert` (`id_host`, `name_host`, `id_item`, `name_item`, `datetime`, `groupname`, `lastvalue`, `groupid`, `fail_warning`, `fail_critical`, `code_host`, `nominal_value`, `changetrap`, `sendtrap`, `fail_repair`, `unit`, `warning_value`, `critical_value`, `averagevalue`, `datetime_sendtrap`) VALUES(".$firstRecord["id_host"].", '".$firstRecord["host"]."', ".$firstRecord["id_item"].", '".$firstRecord["item"]."', '".$firstRecord["datetime"]."', '".$firstRecord["group"]."', ".$firstRecord["last_value"].", ".$firstRecord["id_group"].", $isWarning, $isCritical, '".$firstRecord["code_host"]."', '".$firstRecord["nominal_value"]."', 'true', 'false', $isClear, '".$firstRecord["unit"]."', ".$valueThold["warning"].", ".$valueThold["critical"].", ".$validacion_promedio.", NOW())";
							mysql_query($sqlInsertAlert, $id);
						}
					}
				
					mysql_free_result($resultData);
				}

				mysql_free_result($resultHost);
			}
		}
		
		mysql_free_result($resultThold);
	}

	private function sendTrap($id){	
		$trapserver = "";
		$community = "";
		
		$sqlAlert = "SELECT BIA.`id`, BIA.`id_host`, BIA.`name_host`, BIA.`code_host`, DATE(BIA.`datetime_sendtrap`) AS 'date', DATE_FORMAT(BIA.`datetime_sendtrap`, '%H:%i') AS 'hour', BIA.`groupname`, BIA.`lastvalue`, BIA.`fail_warning`, BIA.`fail_critical`, BIA.`fail_repair`, BIA.`nominal_value`, BIA.`changetrap`, BIA.`sendtrap`, BI.`descriptionLong` AS 'itemname', DATABASE() AS 'dbase', BIA.`unit`, BIA.`warning_value`, BIA.`critical_value`, BIA.`averagevalue` FROM `bm_items_alert` BIA LEFT JOIN `bm_items` BI ON BIA.`id_item` = BI.`id_item` WHERE DATE(BIA.`datetime_sendtrap`) <= DATE(NOW()) AND DATE(BIA.`datetime_sendtrap`) >= DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)) AND BIA.`sendtrap` = 'false'";
		$resultAlert = mysql_query($sqlAlert, $id);
		
		$sqlParametroServer = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'TRAPS_ALERT_SERVER' LIMIT 1";
		$resultParametroServer = mysql_query($sqlParametroServer, $id);
		$snmpServer = mysql_fetch_assoc($resultParametroServer);
		
		$sqlParametroCommunity = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'TRAPS_ALERT_COMMUNITY' LIMIT 1";
		$resultParametroCommunity = mysql_query($sqlParametroCommunity, $id);
		$snmpCommunity = mysql_fetch_assoc($resultParametroCommunity);
		
		$sqlParametroHeader = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'TRAPS_ALERT_HEADER' LIMIT 1";
		$resultParametroHeader = mysql_query($sqlParametroHeader, $id);
		$snmpHeader = mysql_fetch_assoc($resultParametroHeader);
		
		if($snmpServer["valor"] != "" && $snmpCommunity["valor"] != "" && $snmpHeader["valor"] != ""){
			
			$trapserver = $snmpServer["valor"];
			$community = $snmpCommunity["valor"];
			
			while($valueAlert = mysql_fetch_assoc($resultAlert)){
				if($valueAlert["changetrap"] == "true"){
					$typeAlert = "";
					$oid = "";
				
					if($valueAlert["fail_warning"] == 1){
						$typeAlert = "WARNING";
						$oid = $snmpHeader["valor"].".1";
					}else if($valueAlert["fail_critical"] == 1){
						$typeAlert = "CRITICAL";
						$oid = $snmpHeader["valor"].".2";
					}else{
						$typeAlert = "CLEAR";
						$oid = $snmpHeader["valor"].".3";
					}
				
					$valueAlert["name_host"] = str_replace(" ", "_", $valueAlert["name_host"]);
					$valueAlert["code_host"] = str_replace(" ", "_", $valueAlert["code_host"]);
					$valueAlert["itemname"] = str_replace(" ", "", $valueAlert["itemname"]);
				
					$sh = "snmptrap -v 2c -c $community $trapserver '' $oid $oid s \"DB=".$valueAlert["dbase"]."*HST=".$valueAlert["name_host"]."*CDHST=".$valueAlert["code_host"]."*IT=".$valueAlert["itemname"]."*DT=".$valueAlert["date"]."*HR=".$valueAlert["hour"]."*NVL=".$valueAlert["nominal_value"]."*MVL=".$valueAlert["lastvalue"]."*PMVL=".$valueAlert["averagevalue"]."*WRNVL=".$valueAlert["warning_value"]."*CTLVL=".$valueAlert["critical_value"]."*UT=".$valueAlert["unit"]."*TA=$typeAlert\"";
					shell_exec($sh);
						
					$sqlUpdateAlert = "UPDATE `bm_items_alert` SET `sendtrap` = 'true', `changetrap` = 'false' WHERE `id` = ".$valueAlert["id"];
					mysql_query($sqlUpdateAlert, $id);
					
					$this->sendMail($valueAlert["id"], $id);
				}
			}
		}		
	}

	private function sendMail($idalert, $id){
		$sqlAlert = "SELECT DATABASE() AS 'dbase', BIA.`name_host`, BIA.`name_item`, BIA.`unit`, BIA.`fail_warning`, BIA.`fail_critical`, BIA.`fail_repair`, BIA.`lastvalue`, BIA.`nominal_value`, BIA.`warning_value`, BIA.`critical_value`, BIA.`averagevalue`, BIA.`name_plan`, BIA.`datetime_sendtrap`, BT.`emailOptional` FROM `bm_items_alert` BIA LEFT JOIN `bm_threshold` BT ON BIA.`id_item` = BT.`id_item` WHERE BIA.`id` = $idalert AND BT.`emailOptional` != ''";
		$resutAlert = mysql_query($sqlAlert, $id);
		
		while($filaAlert = mysql_fetch_assoc($resutAlert)){
			$from_email = "soporte@bsw.cl";
			
			$from_user = "Soporte BSW";
			$from_user = "=?UTF-8?B?" .base64_encode($from_user) ."?=";
			
			$subject = "BSW Support Alert from ".$filaAlert["dbase"];
			$subject = "=?UTF-8?B?" .base64_encode($subject) ."?=";
			
			$headers = "From: $from_user <$from_email>\r\n" ."MIME-Version: 1.0" ."\r\n";
			$headers .= "Content-type: text/html; charset=UTF-8" ."\r\n";
			
			$typeAlert = "";
			
			if($filaAlert["fail_warning"] == 1){
				$typeAlert = "WARNING";
			}else if($filaAlert["fail_critical"] == 1){
				$typeAlert = "CRITICAL";
			}else{
				$typeAlert = "CLEAR";
			}
			
			$message = "<table>";
			$message .= "<tr><td>HOST:&nbsp;</td><td>".$filaAlert["name_host"]."</td></tr>";
			$message .= "<tr><td>ITEM:&nbsp;</td><td>".$filaAlert["name_item"]."</td></tr>";
			$message .= "<tr><td>UNIT:&nbsp;</td><td>".$filaAlert["unit"]."</td></tr>";
			$message .= "<tr><td>TYPE ALERT:&nbsp;</td><td>$typeAlert</td></tr>";
			$message .= "<tr><td>LAST VALUE:&nbsp;</td><td>".$filaAlert["lastvalue"]."</td></tr>";
			$message .= "<tr><td>AVERAGE VALUE:&nbsp;</td><td>".$filaAlert["averagevalue"]."</td></tr>";
			$message .= "<tr><td>NOMINAL VALUE:&nbsp;</td><td>".$filaAlert["nominal_value"]."</td></tr>";
			$message .= "<tr><td>WARNING VALUE:&nbsp;</td><td>".$filaAlert["warning_value"]."</td></tr>";
			$message .= "<tr><td>CRITICAL VALUE:&nbsp;</td><td>".$filaAlert["critical_value"]."</td></tr>";
			$message .= "<tr><td>PLAN:&nbsp;</td><td>".$filaAlert["name_plan"]."</td></tr>";
			$message .= "<tr><td>DATETIME TRAP:&nbsp;</td><td>".$filaAlert["datetime_sendtrap"]."</td></tr>";
			$message .= "</table>";
			
			mail($filaAlert["emailOptional"], $subject, $message, $headers);
		}
		
		mysql_free_result($resutAlert);
	}
}

$alert = new Alertas("localhost", "root", "bsw$$2009");
$alert->start();
?>
