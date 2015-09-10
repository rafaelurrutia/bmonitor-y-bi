<?php
class AlDia {
	private $db = "";
	private $mail = "";
	private $host = "";
	private $user = "";
	private $pass = "";
	
	private $records = array();

	public function __construct($h, $u, $p, $d){
		if((isset($h) || trim($h) != "") || (isset($u) || trim($u) != "") || (isset($p) || trim($p) != "") || (isset($d) || trim($d) != "")){
        	$this->host = $h;
			$this->user = $u;
			$this->pass = $p;
			$this->db = $d;

			echo $this->host."\n";
            echo $this->user."\n";
	        echo $this->pass."\n";
			echo $this->db."\n";
			
			$this->records = "<tr><td>Host</td><td>Prueba</td><td>Num. pruebas realizadas</td><td>Num. pruebas cumplidas</td><td>Num. pruebas aceptables</td><td>Num. pruebas criticas</td><td>Disp. Host</td></tr>";

			$conn = mysql_connect($this->host, $this->user, $this->pass);
            mysql_select_db($this->db, $conn);

			$sqlThold = "SELECT BT.`id_item`, BT.`nominal`, BT.`warning`, BT.`critical`, BT.`cicle`, BHG.`groupid`, LOWER(BHG.`name`) AS 'groupname', BI.`descriptionLong` AS 'itemname', IF(lower(BI.`descriptionLong`) LIKE '%nac%', 'nac', IF(lower(BI.`descriptionLong`) LIKE '%int%', 'int', IF(lower(BI.`descriptionLong`) LIKE '%loc%', 'loc', 'nac'))) AS plan_type, BI.`unit` FROM `bm_threshold` BT JOIN `bm_items` BI ON BI.`id_item` = BT.`id_item` JOIN `bm_items_groups` BIG ON BIG.`id_item` = BI.`id_item` JOIN `bm_host_groups` BHG ON BIG.`groupid` = BHG.`groupid` WHERE BT.`alert` = 'true'";
			$resultThold = mysql_query($sqlThold, $conn);

			while($valueThold = mysql_fetch_assoc($resultThold)){
				echo "''' ".$valueThold["itemname"]." '''\n";
				
				if($valueThold["nominal"] == -1){
					$sqlPlan = "SELECT BP.`id_plan`, BP.`plan`, (BP.`".$valueThold["plan_type"]."D` * 1024) AS 'down' FROM `bm_plan_groups` BPG JOIN `bm_plan` BP ON BPG.`id_plan` = BP.`id_plan` WHERE BPG.`groupid` = ".$valueThold["groupid"]." AND BPG.`borrado` = 0";
					$resultPlan = mysql_query($sqlPlan, $conn);
					
					while($valuePlan = mysql_fetch_assoc($resultPlan)){
						$valPLAN = $valuePlan["down"];
						
						$sqlHost = "SELECT `id_host`, `host`, `codigosonda`, availability FROM `bm_host` WHERE `id_plan` = ".$valuePlan["id_plan"]." AND `borrado` = 0";
						$resultHost = mysql_query($sqlHost, $conn);
						
						while($valueHost = mysql_fetch_assoc($resultHost)){
							$tmpTable = "SHOW TABLES LIKE 'xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)."'";
							$numTmpTable = mysql_num_rows(mysql_query($tmpTable, $conn));
							
							if($numTmpTable > 0){
								$sqlHistory = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) LIMIT 1";
								$resultHistory = mysql_query($sqlHistory, $conn);
								$resHist = mysql_fetch_assoc($resultHistory);
								
								$sqlHistoryGood = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value > ".$valueThold["warning"]." LIMIT 1";
								$resultHistoryGood = mysql_query($sqlHistoryGood, $conn);
								$resHistGood = mysql_fetch_assoc($resultHistoryGood);
								
								$sqlHistoryAccept = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value <= ".$valueThold["warning"]." AND value > ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryAccept = mysql_query($sqlHistoryAccept, $conn);
								$resHistAccept = mysql_fetch_assoc($resultHistoryAccept);
								
								$sqlHistoryBad = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value < ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryBad = mysql_query($sqlHistoryBad, $conn);
								$resHistBad = mysql_fetch_assoc($resultHistoryBad);
								
								if($valueHost["availability"] == 0){
									$avail = "Off";
								}else{
									$avail = "On";
								}
								
								$this->records .= "<tr><td>{$valueHost["host"]}</td><td>{$valueThold["itemname"]}</td><td>{$resHist["numTest"]}</td><td>{$resHistGood["numTest"]}</td><td>{$resHistAccept["numTest"]}</td><td>{$resHistBad["numTest"]}</td><td>$avail</td></tr>";
							}else{
								$sqlHistory = "SELECT COUNT(*) AS numTest FROM bm_history WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) LIMIT 1";
								$resultHistory = mysql_query($sqlHistory, $conn);
								$resHist = mysql_fetch_assoc($resultHistory);
								
								$sqlHistoryGood = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value > ".$valueThold["warning"]." LIMIT 1";
								$resultHistoryGood = mysql_query($sqlHistoryGood, $conn);
								$resHistGood = mysql_fetch_assoc($resultHistoryGood);
								
								$sqlHistoryAccept = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value <= ".$valueThold["warning"]." AND value > ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryAccept = mysql_query($sqlHistoryAccept, $conn);
								$resHistAccept = mysql_fetch_assoc($resultHistoryAccept);
								
								$sqlHistoryBad = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value < ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryBad = mysql_query($sqlHistoryBad, $conn);
								$resHistBad = mysql_fetch_assoc($resultHistoryBad);
								
								if($valueHost["availability"] == 0){
									$avail = "Off";
								}else{
									$avail = "On";
								}
								
								$this->records .= "<tr><td>{$valueHost["host"]}</td><td>{$valueThold["itemname"]}</td><td>{$resHist["numTest"]}</td><td>{$resHistGood["numTest"]}</td><td>{$resHistAccept["numTest"]}</td><td>{$resHistBad["numTest"]}</td><td>$avail</td></tr>";
							}
						}
					}
				}else if($valueThold["nominal"] == -2){
					$sqlPlan = "SELECT BP.`id_plan`, BP.`plan`, (BP.`".$valueThold["plan_type"]."U` * 1024) AS 'up' FROM `bm_plan_groups` BPG JOIN `bm_plan` BP ON BPG.`id_plan` = BP.`id_plan` WHERE BPG.`groupid` = ".$valueThold["groupid"]." AND BPG.`borrado` = 0";
					$resultPlan = mysql_query($sqlPlan, $conn);
					
					while($valuePlan = mysql_fetch_assoc($resultPlan)){
						$valPLAN = $valuePlan["up"];
						
						$sqlHost = "SELECT `id_host`, `host`, `codigosonda`, availability FROM `bm_host` WHERE `id_plan` = ".$valuePlan["id_plan"]." AND `borrado` = 0";
						$resultHost = mysql_query($sqlHost, $conn);
						
						while($valueHost = mysql_fetch_assoc($resultHost)){
							$tmpTable = "SHOW TABLES LIKE 'xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)."'";
							$numTmpTable = mysql_num_rows(mysql_query($tmpTable, $conn));
							
							if($numTmpTable > 0){
								$sqlHistory = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) LIMIT 1";
								$resultHistory = mysql_query($sqlHistory, $conn);
								$resHist = mysql_fetch_assoc($resultHistory);
								
								$sqlHistoryGood = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value > ".$valueThold["warning"]." LIMIT 1";
								$resultHistoryGood = mysql_query($sqlHistoryGood, $conn);
								$resHistGood = mysql_fetch_assoc($resultHistoryGood);
								
								$sqlHistoryAccept = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value <= ".$valueThold["warning"]." AND value > ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryAccept = mysql_query($sqlHistoryAccept, $conn);
								$resHistAccept = mysql_fetch_assoc($resultHistoryAccept);
								
								$sqlHistoryBad = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value < ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryBad = mysql_query($sqlHistoryBad, $conn);
								$resHistBad = mysql_fetch_assoc($resultHistoryBad);
								
								if($valueHost["availability"] == 0){
									$avail = "Off";
								}else{
									$avail = "On";
								}
								
								$this->records .= "<tr><td>{$valueHost["host"]}</td><td>{$valueThold["itemname"]}</td><td>{$resHist["numTest"]}</td><td>{$resHistGood["numTest"]}</td><td>{$resHistAccept["numTest"]}</td><td>{$resHistBad["numTest"]}</td><td>$avail</td></tr>";
							}else{
								$sqlHistory = "SELECT COUNT(*) AS numTest FROM bm_history WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) LIMIT 1";
								$resultHistory = mysql_query($sqlHistory, $conn);
								$resHist = mysql_fetch_assoc($resultHistory);
								
								$sqlHistoryGood = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value > ".$valueThold["warning"]." LIMIT 1";
								$resultHistoryGood = mysql_query($sqlHistoryGood, $conn);
								$resHistGood = mysql_fetch_assoc($resultHistoryGood);
								
								$sqlHistoryAccept = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value <= ".$valueThold["warning"]." AND value > ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryAccept = mysql_query($sqlHistoryAccept, $conn);
								$resHistAccept = mysql_fetch_assoc($resultHistoryAccept);
								
								$sqlHistoryBad = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value <= ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryBad = mysql_query($sqlHistoryBad, $conn);
								$resHistBad = mysql_fetch_assoc($resultHistoryBad);
								
								if($valueHost["availability"] == 0){
									$avail = "Off";
								}else{
									$avail = "On";
								}
								
								$this->records .= "<tr><td>{$valueHost["host"]}</td><td>{$valueThold["itemname"]}</td><td>{$resHist["numTest"]}</td><td>{$resHistGood["numTest"]}</td><td>{$resHistAccept["numTest"]}</td><td>{$resHistBad["numTest"]}</td><td>$avail</td></tr>";
							}
						}
					}
				}else{
					$sqlHost = "SELECT `id_host`, `host`, `codigosonda`, availability FROM `bm_host` WHERE `borrado` = 0 AND groupid = ".$valueThold["groupid"];
					$resultHost = mysql_query($sqlHost, $conn);
					
					while($valueHost = mysql_fetch_assoc($resultHost)){
						$tmpTable = "SHOW TABLES LIKE 'xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)."'";
						$numTmpTable = mysql_num_rows(mysql_query($tmpTable, $conn));
						
						if($numTmpTable > 0){
							$sqlHistory = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) LIMIT 1";
							$resultHistory = mysql_query($sqlHistory, $conn);
							$resHist = mysql_fetch_assoc($resultHistory);
														
							if($valueThold["warning"] > $valueThold["critical"]){
								$sqlHistoryGood = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value > ".$valueThold["warning"]." LIMIT 1";
								$resultHistoryGood = mysql_query($sqlHistoryGood, $conn);
								$resHistGood = mysql_fetch_assoc($resultHistoryGood);
								
								$sqlHistoryAccept = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value <= ".$valueThold["warning"]." AND value > ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryAccept = mysql_query($sqlHistoryAccept, $conn);
								$resHistAccept = mysql_fetch_assoc($resultHistoryAccept);
								
								$sqlHistoryBad = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value <= ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryBad = mysql_query($sqlHistoryBad, $conn);
								$resHistBad = mysql_fetch_assoc($resultHistoryBad);
								
								if($valueHost["availability"] == 0){
									$avail = "Off";
								}else{
									$avail = "On";
								}
								
								//echo "host: {$valueHost["host"]} - item: {$valueThold["itemname"]} - count total: {$resHist["numTest"]} - count good: {$resHistGood["numTest"]} - count accpet: {$resHistAccept["numTest"]} - count bad: {$resHistBad["numTest"]}\n";
								$this->records .= "<tr><td>{$valueHost["host"]}</td><td>{$valueThold["itemname"]}</td><td>{$resHist["numTest"]}</td><td>{$resHistGood["numTest"]}</td><td>{$resHistAccept["numTest"]}</td><td>{$resHistBad["numTest"]}</td><td>$avail</td></tr>";
							}else{
								$sqlHistoryGood = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value < ".$valueThold["warning"]." LIMIT 1";
								$resultHistoryGood = mysql_query($sqlHistoryGood, $conn);
								$resHistGood = mysql_fetch_assoc($resultHistoryGood);
								
								$sqlHistoryAccept = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value >= ".$valueThold["warning"]." AND value < ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryAccept = mysql_query($sqlHistoryAccept, $conn);
								$resHistAccept = mysql_fetch_assoc($resultHistoryAccept);
								
								$sqlHistoryBad = "SELECT COUNT(*) AS numTest FROM xyz_".str_pad($valueThold["id_item"], 10, "0", STR_PAD_LEFT)." WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value >= ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryBad = mysql_query($sqlHistoryBad, $conn);
								$resHistBad = mysql_fetch_assoc($resultHistoryBad);
								
								if($valueHost["availability"] == 0){
									$avail = "Off";
								}else{
									$avail = "On";
								}
								
								//echo "host: {$valueHost["host"]} - item: {$valueThold["itemname"]} - count total: {$resHist["numTest"]} - count good: {$resHistGood["numTest"]} - count accpet: {$resHistAccept["numTest"]} - count bad: {$resHistBad["numTest"]}\n";
								$this->records .= "<tr><td>{$valueHost["host"]}</td><td>{$valueThold["itemname"]}</td><td>{$resHist["numTest"]}</td><td>{$resHistGood["numTest"]}</td><td>{$resHistAccept["numTest"]}</td><td>{$resHistBad["numTest"]}</td><td>$avail</td></tr>";
							}
						}else{
							$sqlHistory = "SELECT COUNT(*) AS numTest FROM bm_history WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) LIMIT 1";
							$resultHistory = mysql_query($sqlHistory, $conn);
							$resHist = mysql_fetch_assoc($resultHistory);
							
							if($valueThold["warning"] > $valueThold["critical"]){
								$sqlHistoryGood = "SELECT COUNT(*) AS numTest FROM bm_history WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value > ".$valueThold["warning"]." LIMIT 1";
								$resultHistoryGood = mysql_query($sqlHistoryGood, $conn);
								$resHistGood = mysql_fetch_assoc($resultHistoryGood);
								
								$sqlHistoryAccept = "SELECT COUNT(*) AS numTest FROM bm_history WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value <= ".$valueThold["warning"]." AND value > ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryAccept = mysql_query($sqlHistoryAccept, $conn);
								$resHistAccept = mysql_fetch_assoc($resultHistoryAccept);
								
								$sqlHistoryBad = "SELECT COUNT(*) AS numTest FROM bm_history WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value <= ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryBad = mysql_query($sqlHistoryBad, $conn);
								$resHistBad = mysql_fetch_assoc($resultHistoryBad);
								
								if($valueHost["availability"] == 0){
									$avail = "Off";
								}else{
									$avail = "On";
								}
								
								//echo "host: {$valueHost["host"]} - item: {$valueThold["itemname"]} - count total: {$resHist["numTest"]} - count good: {$resHistGood["numTest"]} - count accpet: {$resHistAccept["numTest"]} - count bad: {$resHistBad["numTest"]}\n";
								$this->records .= "<tr><td>{$valueHost["host"]}</td><td>{$valueThold["itemname"]}</td><td>{$resHist["numTest"]}</td><td>{$resHistGood["numTest"]}</td><td>{$resHistAccept["numTest"]}</td><td>{$resHistBad["numTest"]}</td><td>$avail</td></tr>";
							}else{
								$sqlHistoryGood = "SELECT COUNT(*) AS numTest FROM bm_history WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value < ".$valueThold["warning"]." LIMIT 1";
								$resultHistoryGood = mysql_query($sqlHistoryGood, $conn);
								$resHistGood = mysql_fetch_assoc($resultHistoryGood);
								
								$sqlHistoryAccept = "SELECT COUNT(*) AS numTest FROM bm_history WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value >= ".$valueThold["warning"]." AND value < ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryAccept = mysql_query($sqlHistoryAccept, $conn);
								$resHistAccept = mysql_fetch_assoc($resultHistoryAccept);
								
								$sqlHistoryBad = "SELECT COUNT(*) AS numTest FROM bm_history WHERE id_host = ".$valueHost["id_host"]." AND clock >= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 00:00:00')) AND clock <= UNIX_TIMESTAMP(DATE_FORMAT(DATE_SUB(NOW(), INTERVAL 1 DAY), '%Y-%m-%d 23:59:59')) AND value >= ".$valueThold["critical"]." LIMIT 1";
								$resultHistoryBad = mysql_query($sqlHistoryBad, $conn);
								$resHistBad = mysql_fetch_assoc($resultHistoryBad);
								
								if($valueHost["availability"] == 0){
									$avail = "Off";
								}else{
									$avail = "On";
								}
								
								//echo "host: {$valueHost["host"]} - item: {$valueThold["itemname"]} - count total: {$resHist["numTest"]} - count good: {$resHistGood["numTest"]} - count accpet: {$resHistAccept["numTest"]} - count bad: {$resHistBad["numTest"]}\n";
								$this->records .= "<tr><td>{$valueHost["host"]}</td><td>{$valueThold["itemname"]}</td><td>{$resHist["numTest"]}</td><td>{$resHistGood["numTest"]}</td><td>{$resHistAccept["numTest"]}</td><td>{$resHistBad["numTest"]}</td><td>$avail</td></tr>";
							}
						}
					}	
				}		
			}

			mysql_free_result($resultThold);

			mysql_close($conn);
    	}
	}
	
	public function sendMail(){
		$bodyMail = "";
		$bodyMail .= "<table>";
		$bodyMail .= $this->records;
		$bodyMail .= "</table>";
		
		$from_email = "soporte@bsw.cl";
			
		$from_user = "Soporte BSW";
		$from_user = "=?UTF-8?B?" .base64_encode($from_user) ."?=";
		
		$subject = "BSW Alert Daily";
		$subject = "=?UTF-8?B?" .base64_encode($subject) ."?=";
			
		$headers = "From: $from_user <$from_email>\r\n" ."MIME-Version: 1.0" ."\r\n";
		$headers .= "Content-type: text/html; charset=UTF-8" ."\r\n";
		
		mail("pablo.santana@bsw.cl,soporte@bsw.cl", $subject, $bodyMail, $headers);
	}
}

$aldia = new AlDia("localhost", "root", "bsw$$2009", "bmonitor");
$aldia->sendMail();
?>