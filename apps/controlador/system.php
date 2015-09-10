<?php 
class system extends Control {
	
	protected $key = 'perro$$muerde&&al$$que33Roba22';
	
	public function getLoadAVG()
	{
		$load = sys_getloadavg();
		if ($load[0] > 80) {
		    header('HTTP/1.1 503 Too busy, try again later');
		    die('Server too busy. Please try again later.');
		} else {
			return  $load;
		}
	}
	
	function GetCpuInformation() {
		$stat1 = file('/proc/stat'); 
		sleep(1); 
		$stat2 = file('/proc/stat'); 
		$info1 = explode(" ", preg_replace("!cpu +!", "", $stat1[0])); 
		$info2 = explode(" ", preg_replace("!cpu +!", "", $stat2[0])); 
		$dif = array(); 
		$dif['user'] = $info2[0] - $info1[0]; 
		$dif['nice'] = $info2[1] - $info1[1]; 
		$dif['sys'] = $info2[2] - $info1[2]; 
		$dif['idle'] = $info2[3] - $info1[3]; 
		$total = array_sum($dif); 
		$cpu = array(); 
		foreach($dif as $x=>$y) $cpu[$x] = round($y / $total * 100, 1);
		
		return $cpu;
	}

	function GetTotalSondas($group) {
		
		$get_total_sonda_sql = 'SELECT G.`name`,COUNT(*) As total FROM `bm_host` H 
								LEFT JOIN `bm_host_groups` G USING(`groupid`)';
								
		if($group !== 'ALL') {
			$get_total_sonda_sql .= " WHERE G.`name`='$group' AND `status` = 1";
		} else {
			$get_total_sonda_sql .= ' WHERE `status` = 1 GROUP BY `groupid`';
		}
									
		$get_total_sonda_result = $this->conexion->queryFetch($get_total_sonda_sql);
		
		if($get_total_sonda_result) {
			if($group !== 'ALL') {
				$return = $get_total_sonda_result[0]['Total'];
			} else {
				$return = array();
				foreach ($get_total_sonda_result as $key => $value) {
					$return[] = array(
						"group" =>  $value['name'],
						"total" => $value['total']
					);
				}
			}
			
			return $return;
			
		} else {
			return false;
		}
	}
	
	private function getDownHost($group)
	{
		$get_total_sonda_sql = 'SELECT G.`name`,COUNT(*) As total FROM `bm_availability` H 
								LEFT JOIN `bm_host_groups` G USING(`groupid`) WHERE H.`STATUS_SNMP`=0';
								
		if($group !== 'ALL') {
			$get_total_sonda_sql .= " AND G.`name`='$group'";
		} else {
			$get_total_sonda_sql .= ' GROUP BY `groupid`';
		}
									
		$get_total_sonda_result = $this->conexion->queryFetch($get_total_sonda_sql);
		
		if($get_total_sonda_result) {
			if($group !== 'ALL') {
				$return = $get_total_sonda_result[0]['Total'];
			} else {
				$return = array();
				foreach ($get_total_sonda_result as $key => $value) {
					$return[] = array(
						"group" =>  $value['name'],
						"total" => $value['total']
					);
				}
			}
			
			return $return;
			
		} else {
			return false;
		}		
	}
	
	private function getDataHost($group)
	{
		$get_total_sonda_sql = "SELECT HG.name, COUNT(*) AS total FROM `bm_item_profile` IP 
LEFT JOIN  `bm_items` I USING(`id_item`)
LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host`
LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=H.`groupid`
WHERE IP.`nextcheck` != '0000-00-00 00:00:00' AND H.`borrado`=0 AND H.`status`=1 AND
`nextcheck` > NOW()
AND I.`description` IN 
('NACbandwidthdown.sh','NACbandwidthup.sh','LOCbandwidthdown.sh','LOCbandwidthup.sh','INTbandwidthdown.sh','INTbandwidthup.sh','NACping_avg.sh','LOCping_avg.sh','INTping_avg.sh','ip_renew.sh','INTping_std.sh','LOCping_std.sh','NACping_std.sh') ";
								
		if($group !== 'ALL') {
			$get_total_sonda_sql .= " AND HG.`name`='$group'";
		}
									
		$get_total_sonda_sql .= ' GROUP BY H.`groupid`';
		
		$get_total_sonda_result = $this->conexion->queryFetch($get_total_sonda_sql);
		
		if($get_total_sonda_result) {
			if($group !== 'ALL') {
				$return = $get_total_sonda_result[0]['Total'];
			} else {
				$return = array();
				foreach ($get_total_sonda_result as $key => $value) {
					$return[] = array(
						"group" =>  $value['name'],
						"total" => $value['total']
					);
				}
			}
			
			return $return;
			
		} else {
			return false;
		}		
	}
	
	private function getNoDataHost($group)
	{
		$get_total_sonda_sql = "SELECT HG.name, COUNT(*) AS total FROM `bm_item_profile` IP 
LEFT JOIN  `bm_items` I USING(`id_item`)
LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host`
LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=H.`groupid`
WHERE IP.`nextcheck` != '0000-00-00 00:00:00' AND H.`borrado`=0  AND H.`status`=1  AND
`nextcheck` < DATE_ADD(NOW(), INTERVAL - 120 SECOND)
AND I.`description` IN 
('NACbandwidthdown.sh','NACbandwidthup.sh','LOCbandwidthdown.sh','LOCbandwidthup.sh','INTbandwidthdown.sh','INTbandwidthup.sh','NACping_avg.sh','LOCping_avg.sh','INTping_avg.sh','ip_renew.sh','INTping_std.sh','LOCping_std.sh','NACping_std.sh') ";
								
		if($group !== 'ALL') {
			$get_total_sonda_sql .= " AND HG.`name`='$group'";
		} else {
			$get_total_sonda_sql .= ' GROUP BY H.`groupid`';
		}
									
		$get_total_sonda_result = $this->conexion->queryFetch($get_total_sonda_sql);
		
		if($get_total_sonda_result) {
			if($group !== 'ALL') {
				$return = $get_total_sonda_result[0]['Total'];
			} else {
				$return = array();
				foreach ($get_total_sonda_result as $key => $value) {
					$return[] = array(
						"group" =>  $value['name'],
						"total" => $value['total']
					);
				}
			}
			
			return $return;
			
		} else {
			return false;
		}		
	}
	
	public function index()
	{
		if (isset($_GET["group"]) && isset($_GET["id"])) {
			$group=$_GET["group"];
			$id=$_GET["id"];
		}
		elseif(isset($_GET["group"])){
			$group=$_GET["group"];
		} elseif (isset($_GET["id"])) {
			$id=$_GET["id"];
		} elseif (!isset($_GET["group"]) && !isset($_GET["id"])) {
			echo "Param incorrect";
			exit;
		}

		$company = $this->parametro->get('COMPANY','BSW');
		
		switch (strtoupper($id))
		{
			case "ISP"    : echo $company; break;
			case "1MIN"   : 
				$load = $this->getLoadAVG();
				echo  $load[0];
				break;
			case "5MIN"   : 
				$load = $this->getLoadAVG();
				echo  $load[1];
				break;
			case "15MIN"  : 
				$load = $this->getLoadAVG();
				echo  $load[2];
				break;
			case "US"     : 
				$CpuInformation = $this->GetCpuInformation();
				echo $CpuInformation['user'];
				break;
			case "SY"     : 
				$CpuInformation = $this->GetCpuInformation();
				echo $CpuInformation['sys'];
				break;
			case "NI"     : 
				$CpuInformation = $this->GetCpuInformation();
				echo $CpuInformation['nice'];
				break;
			case "ID"     : 
				$CpuInformation = $this->GetCpuInformation();
				echo $CpuInformation['idle'];
				break;
			case "TOTAL"  : echo $this->GetTotalSondas($group); break;
			case "DOWN"   :
				$DownHost = $this->getDownHost($group);
				echo $DownHost;
				break;
			case "DATA"   :
				$DownHost = $this->getDataHost($group);
				echo $DownHost;
				break;
			case "NODATA" :
				$DownHost = $this->getNoDataHost($group);
				echo $DownHost;
				break;
		}

	}

	public function status()
	{
		$result_xml = new SimpleXMLElement('<?xml version="1.0" standalone="yes"?><status></status>');
		
		$server = $result_xml->addChild('load_average');
			
		$load = $this->getLoadAVG();

		$server->addChild('min_1', $load[0]);
		$server->addChild('min_5', $load[1]);
		$server->addChild('min_15', $load[2]);

		$server = $result_xml->addChild('cpus');
			
		$CpuInformation = $this->GetCpuInformation();

		$server->addChild('user', $CpuInformation['user']);
		$server->addChild('system', $CpuInformation['sys']);
		$server->addChild('nice', $CpuInformation['nice']);
		$server->addChild('idle', $CpuInformation['idle']);	

		header("Content-type: text/xml; charset=utf-8");
		echo $result_xml->asXML();
		exit;
		//echo $this->basic->encrypted($result_xml->asXML(),$this->key);
	}
}
?>