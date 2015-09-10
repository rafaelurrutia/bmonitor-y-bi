<?php
if(!function_exists('pcntl_fork')) die ('PCNTL no disponible');

class Housekeeping {
	private $dbCore = "bsw_bi";
	private $aServer = array();
	private $daysParam = false;
	
	private $host = "";
	private $user = "";
	private $pass = "";
	
	public function __construct($h, $u, $p, $s = false, $d = false){
		if(isset($h)){
			$this->host = $h;
		}
		
		if(isset($u)){
			$this->user = $u;
		}
		
		if(isset($p)){
			$this->pass = $p;
		}
		
		$uniqueDB = "";
		
		if($s != false && $s != ""){
			$uniqueDB = " AND `dbName` = '".strtolower($s)."'";
		}
		
		if($d != false && $d != ""){
			$this->daysParam = $d;
		}
		
		$conn = mysql_connect($this->host, $this->user, $this->pass);
		mysql_select_db($this->dbCore, $conn);
		
		$sqlServer = "SELECT `idServer`, `name`, `domain`, `dbName`, `timezone`, `mailAlert` FROM `$this->dbCore`.`bi_server` WHERE `active` = 'true' $uniqueDB";
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
				echo "Housekeeping, empresa: ".$valueServer["name"].", base de datos ".$valueServer["dbname"];
				$threadconn = mysql_connect($this->host, $this->user, $this->pass);
				mysql_select_db($valueServer["dbname"], $threadconn);
				
				$this->setTimezone($valueServer["timezone"], $threadconn);
				error_log("housekeeping base de datos ".$valueServer["dbname"]."\n", 3, "/root/housekeeping.log");
				$this->deleteData($threadconn);
				$this->deleteDataAll($threadconn);
				
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
	
	private function deleteData($id){
		$sqlParamDays = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'HOUSEKEEPING_DAYS' LIMIT 1";
		$resultParamDays = mysql_query($sqlParamDays, $id);
		$housekeepingDays = mysql_fetch_assoc($resultParamDays);
		
		if($housekeepingDays["valor"] != ""){
			$daysDelete = $housekeepingDays["valor"];
			
			$sqlItems = "SELECT DISTINCT(BPV.`id_monitor`) AS 'id_item', BI.`descriptionLong` AS 'name_item', BI.`unit` AS 'unit_item' FROM `bm_profiles_category` BPC LEFT JOIN `bm_profiles_values` BPV ON BPV.`id_category` = BPC.`id_category` LEFT JOIN `bm_items` BI ON BI.`id_item` = BPV.`id_monitor` WHERE BPC.`status` = 'true' AND BPV.`id_monitor` > 0";
			$resultItems = mysql_query($sqlItems, $id);
			
			while ($filaItems = mysql_fetch_assoc($resultItems)) {
				$sqlExistTable = "SHOW TABLES LIKE 'xyz_".str_pad($filaItems["id_item"], 10, "0", STR_PAD_LEFT)."'";
				$resultExistTable = mysql_query($sqlExistTable, $id);
				$numExistTable = mysql_num_rows($resultExistTable);
				
				if($numExistTable > 0){
					$sqlDelete = "DELETE FROM `xyz_".str_pad($filaItems["id_item"], 10, "0", STR_PAD_LEFT)."` WHERE `clock` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$daysDelete." DAY))";
					mysql_query($sqlDelete, $id);
					error_log("item ".$filaItems["id_item"]." datos borrados: ".mysql_affected_rows()."\n", 3, "/root/housekeeping.log");
				}else{
					$sqlDelete = "DELETE FROM `bm_history` WHERE `id_item` = ".$filaItems["id_item"]." AND `clock` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$daysDelete." DAY))";
					mysql_query($sqlDelete, $id);
					error_log("item ".$filaItems["id_item"]." datos borrados: ".mysql_affected_rows()."\n", 3, "/root/housekeeping.log");
				}	
			}
			
			mysql_free_result($resultItems);		 	
		}else{
			if($this->daysParam != false){
				$daysDelete = $this->daysParam;
				
				$sqlItems = "SELECT DISTINCT(BPV.`id_monitor`) AS 'id_item', BI.`descriptionLong` AS 'name_item', BI.`unit` AS 'unit_item' FROM `bm_profiles_category` BPC LEFT JOIN `bm_profiles_values` BPV ON BPV.`id_category` = BPC.`id_category` LEFT JOIN `bm_items` BI ON BI.`id_item` = BPV.`id_monitor` WHERE BPC.`status` = 'true' AND BPV.`id_monitor` > 0";
				$resultItems = mysql_query($sqlItems, $id);
				
				while ($filaItems = mysql_fetch_assoc($resultItems)) {
					$sqlExistTable = "SHOW TABLES LIKE 'xyz_".str_pad($filaItems["id_item"], 10, "0", STR_PAD_LEFT)."'";
					$resultExistTable = mysql_query($sqlExistTable, $id);
					$numExistTable = mysql_num_rows($resultExistTable);
					
					if($numExistTable > 0){
						$sqlDelete = "DELETE FROM `xyz_".str_pad($filaItems["id_item"], 10, "0", STR_PAD_LEFT)."` WHERE `clock` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$daysDelete." DAY))";
						mysql_query($sqlDelete, $id);
						error_log("item ".$filaItems["id_item"]." datos borrados: ".mysql_affected_rows()."\n", 3, "/root/housekeeping.log");
					}else{
						$sqlDelete = "DELETE FROM `bm_history` WHERE `id_item` = ".$filaItems["id_item"]." AND `clock` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$daysDelete." DAY))";
						mysql_query($sqlDelete, $id);
						error_log("item ".$filaItems["id_item"]." datos borrados: ".mysql_affected_rows()."\n", 3, "/root/housekeeping.log");
					}
				}
				
				mysql_free_result($resultItems);
			}
		}
	}

	private function deleteDataAll($id){
		$sqlParamDays = "SELECT `valor` FROM `Parametros` WHERE `nombre` = 'HOUSEKEEPING_DAYS' LIMIT 1";
		$resultParamDays = mysql_query($sqlParamDays, $id);
		$housekeepingDays = mysql_fetch_assoc($resultParamDays);
		
		if($housekeepingDays["valor"] != ""){
			$daysDelete = $housekeepingDays["valor"];
			
			$sqlDeleteAll = "DELETE FROM `bm_history` WHERE `clock` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$daysDelete." DAY))";
			mysql_query($sqlDeleteAll, $id);
			error_log("bm_history datos borrados: ".mysql_affected_rows()."\n", 3, "/root/housekeeping.log");
		}else{
			if($this->daysParam != false){
				$daysDelete = $this->daysParam;
				
				$sqlDeleteAll = "DELETE FROM `bm_history` WHERE `clock` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL ".$daysDelete." DAY))";
				mysql_query($sqlDeleteAll, $id);
				error_log("bm_history datos borrados: ".mysql_affected_rows()."\n", 3, "/root/housekeeping.log");
			}
		}
	}

}

$base = false;

$days = false;

if(isset($argv[1]) && $argv[1] != ""){
	$base = $argv[1];
}

if(isset($argv[2]) && $argv[2] != ""){
	$days = $argv[2];
}

$housekeeping = new Housekeeping("localhost", "root", "bsw$$2009", $base, $days);
$housekeeping->start();
?>