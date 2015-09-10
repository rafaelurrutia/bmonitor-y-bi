<?php
if(!function_exists('pcntl_fork')) die ('PCNTL no disponible');

class Disponibilidad {
	private $dbCore = "bsw_bi";
	private $domainClient = "";
	private $mailDestination = "";
	private $minutesQOE = 5;
	private $minutesQOS = 30;
	
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
				$this->validData($threadconn);
				$this->setAvailability($threadconn);			
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
	
	private function validData($id){
		$sqlDeleteDisponibilidad = "DELETE FROM `bm_disponibilidad` WHERE `id_host` IN (SELECT `id_host` FROM `bm_host` WHERE `borrado` = 1)";
		mysql_query($sqlDeleteDisponibilidad, $id);
		
		$sqlHost = "SELECT BH.`id_host`, BH.`host`, BH.`dns`, BH.`ip_wan`, BHG.`type`, BHG.`name`, BHG.`groupid` FROM `bm_host` BH JOIN `bm_host_groups` BHG ON BH.`groupid` = BHG.`groupid` WHERE BH.`borrado` = 0 AND BHG.`borrado` = 0";
		$resultHost = mysql_query($sqlHost, $id);
		
		while ($rowHost = mysql_fetch_assoc($resultHost)) {
			$interval = 0;
			
			if(strtolower($rowHost["type"]) == "neutralidad" || strtolower($rowHost["type"]) == "qos"){
					$interval = $this->minutesQOS;
			}else{
					$interval = $this->minutesQOE;
			}
			
			$status_receive = "false";
			
			$sqlData = "SELECT * FROM `bm_history` WHERE `id_host` = ".$rowHost["id_host"]." AND `clock` <= UNIX_TIMESTAMP(NOW()) AND `clock` >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL $interval MINUTE)) ORDER BY `id_history` DESC LIMIT 20";
			$resultData = mysql_query($sqlData, $id);
			
			$numData = mysql_num_rows($resultData);
			
			if($numData > 0){
				$status_receive = "true";
			}
			
			$keepalive = "false";
			
			$sqlKeepalive = "SELECT * FROM `bm_dns` WHERE `name` = '".$rowHost["dns"]."' AND (`type` = 'keepalive' OR `type` = 'uploaddata') AND UNIX_TIMESTAMP(`fechahora_update`) <= UNIX_TIMESTAMP(NOW()) AND UNIX_TIMESTAMP(`fechahora_update`) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL $interval MINUTE)) LIMIT 1";
			$resultKeepalive = mysql_query($sqlKeepalive, $id);
			$numDataKeepalive = mysql_num_rows($resultKeepalive);
			
			if($numDataKeepalive > 0){
				$keepalive = "true";
			}
			
			$lastdate = "";
			
			$sqlLastDate = "SELECT `fechahora_update` FROM `bm_dns` WHERE `name` = '".$rowHost["dns"]."' LIMIT 1";
			$resultLastDate = mysql_query($sqlLastDate, $id);
			$rowLastDate = mysql_fetch_assoc($resultLastDate);
			
			if($rowLastDate["fechahora_update"] != ""){
				$lastdate = $rowLastDate["fechahora_update"]; 
			}
			
			$disponibilidad = "false";
			
			if($status_receive == "true" || $keepalive == "true"){
				$disponibilidad = "true";
			}	
			
			$sqlExistDisponibilidad = "SELECT * FROM `bm_disponibilidad` WHERE `id_host` = ".$rowHost["id_host"]." LIMIT 1";
			$resultExistDisponibilidad = mysql_query($sqlExistDisponibilidad, $id);
			$rowExistDisponibilidad = mysql_fetch_assoc($resultExistDisponibilidad);
			$numExistDisponibilidad = mysql_num_rows($resultExistDisponibilidad);
			
			$changeStatus = "false";
			
			if($rowExistDisponibilidad["status"] != "" && ($rowExistDisponibilidad["status"] != $disponibilidad)){
				$changeStatus = "true";
			}
			
			
			//$sqlImproveInsert = "INSERT INTO `bm_disponibilidad` (`id_host`, `name_host`, `status`, `datetime`, `receive_data`, `ultima_fecha`, `keepalive`, `change_status`, `sendtrap`, `id_group`, `name_group`) VALUES(".$rowHost["id_host"].", '".$rowHost["host"]."', '$disponibilidad', NOW(), '$status_receive', '$lastdate', '$keepalive', '$changeStatus', 'false', ".$rowHost["groupid"].", '".$rowHost["name"]."') ON DUPLICATE KEY UPDATE `id_host` = `id_host`, `name_host` = VALUES(`name_host`), `status` = VALUES(`status`), `datetime` = VALUES(`datetime`), `receive_data` = VALUES(`receive_data`), `ultima_fecha` = VALUES(`ultima_fecha`), `keepalive` = VALUES(`keepalive`), `change_status` = VALUES(`change_status`), `sendtrap` = VALUES(`sendtrap`), `id_group` = VALUES(`id_group`), `name_group` = VALUES(`name_group`)";
			//mysql_query($sqlImproveInsert, $id);
			
			if($numExistDisponibilidad > 0){
				if($rowExistDisponibilidad["status"] != $disponibilidad){
					$sqlUpdateDisponibilidad = "UPDATE `bm_disponibilidad` SET `change_status` = 'true', `sendtrap` = 'false', `datetime` = NOW(), `receive_data` = '$status_receive', `ultima_fecha` = '$lastdate', `keepalive` = '$keepalive', `status` = '$disponibilidad' WHERE `id` = ".$rowExistDisponibilidad["id"];
					mysql_query($sqlUpdateDisponibilidad, $id);
				}else{
					$sqlUpdateDisp = "UPDATE `bm_disponibilidad` SET `change_status` = 'false' WHERE `id` = ".$rowExistDisponibilidad["id"];
					mysql_query($sqlUpdateDisponibilidad, $id);
				}				
			}else{
				$sqlInsertDisp = "INSERT INTO `bm_disponibilidad` (`id_host`, `name_host`, `status`, `datetime`, `receive_data`, `ultima_fecha`, `keepalive`, `change_status`, `sendtrap`, `id_group`, `name_group`) VALUES(".$rowHost["id_host"].", '".$rowHost["host"]."', '$disponibilidad', NOW(), '$status_receive', '$lastdate', '$keepalive', 'true', 'false', ".$rowHost["groupid"].", '".$rowHost["name"]."')";
				mysql_query($sqlInsertDisp, $id);	
			}
		}

		mysql_free_result($resultHost);
		
		$this->sendTrap($id);
	}

	private function setAvailability($id){
		$sqlDisponibilidad = "SELECT * FROM `bm_disponibilidad`";
		$resultDisponibilidad = mysql_query($sqlDisponibilidad, $id);
		
		while($rowDisponibilidad = mysql_fetch_assoc($resultDisponibilidad)){
			$disponibilidad = 0;
			
			if($rowDisponibilidad["status"] == "true"){
				$disponibilidad = 1;
			}
			
			$sqlUpdateHost = "UPDATE `bm_host` SET `availability` = $disponibilidad WHERE `id_host` = ".$rowDisponibilidad["id_host"]." AND `borrado` = 0";
			mysql_query($sqlUpdateHost, $id);
		}
		
		mysql_free_result($resultDisponibilidad);
	}
	
	private function sendTrap($id){
		echo "preparando para mandar trap\n";
		$sqlParametroServer = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'TRAPS_AVAILABILITY_SERVER' LIMIT 1";
		$resultParametroServer = mysql_query($sqlParametroServer, $id);
		$snmpServer = mysql_fetch_assoc($resultParametroServer);
		echo "snmpServer ".$snmpServer["valor"]."\n";
		
		//$sqlParametroPort = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'TRAPS_ALERT_PORT' LIMIT 1";
		//$resultParametroPort = mysql_query($sqlParametroPort, $id);
		//$snmpPort = mysql_fetch_assoc($resultParametroPort);
		
		$sqlParametroCommunity = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'TRAPS_AVAILABILITY_COMMUNITY' LIMIT 1";
		$resultParametroCommunity = mysql_query($sqlParametroCommunity, $id);
		$snmpCommunity = mysql_fetch_assoc($resultParametroCommunity);
		echo "snmpCommunity ".$snmpCommunity["valor"]."\n";
		
		$sqlParametroHeader = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'TRAPS_AVAILABILITY_HEADER' LIMIT 1";
		$resultParametroHeader = mysql_query($sqlParametroHeader, $id);
		$snmpHeader = mysql_fetch_assoc($resultParametroHeader);
		echo "snmpHeader ".$snmpHeader["valor"]."\n";		
		
		$sqlState = "SELECT BH.`mac`, BD.`id`, BD.`status`, DATE(BD.`datetime`) AS 'date', DATE_FORMAT(BD.`datetime`, '%H:%i') AS 'hour', DATABASE(), BH.`host`, BH.`codigosonda` AS 'code_host' FROM `bm_disponibilidad` BD JOIN `bm_host` BH ON BD.`id_host` = BH.`id_host` WHERE BD.`change_status` = 'true' AND BD.`sendtrap` = 'false' AND UNIX_TIMESTAMP(BD.`datetime`) <= UNIX_TIMESTAMP(NOW()) AND UNIX_TIMESTAMP(BD.`datetime`) >= UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY))";
		$resultState = mysql_query($sqlState, $id);
		
		if($snmpServer["valor"] != "" && $snmpCommunity["valor"] != "" && $snmpHeader["valor"] != ""){
			echo "existen parametros\n";
			while($valueState = mysql_fetch_assoc($resultState)){
				$trapserver = $snmpServer["valor"];
				$community = $snmpCommunity["valor"];
				$header = $snmpHeader["valor"];
					
				$status = "";
				
				if($valueState["status"] == "true"){
					$status = "UP";
					$header .= ".1";
				}else{
					$status = "DOWN";
					$header .= ".2";
				}
				
				$valueState["host"] = str_replace(" ", "_", $valueState["host"]);
				$valueState["code_host"] = str_replace(" ", "_", $valueState["code_host"]);
				
				$sh = "snmptrap -v 2c -c $community $trapserver '' $header $header s \"DB=".$valueState["DATABASE()"]."*HST=".$valueState["host"]."*CDHST=".$valueState["code_host"]."*MC=".$valueState["mac"]."*DT=".$valueState["date"]."*HR=".$valueState["hour"]."*ST=$status\"";
				echo "$sh\n";
				shell_exec($sh);
				
				$sqlUpdateState = "UPDATE `bm_disponibilidad` SET `sendtrap` = 'true' WHERE `id` = ".$valueState["id"];
				mysql_query($sqlUpdateState, $id);
			}
		
			mysql_free_result($resultState);	
		}
	}
}

$disp = new Disponibilidad("localhost", "root", "bsw$$2009");
$disp->start();
?>
