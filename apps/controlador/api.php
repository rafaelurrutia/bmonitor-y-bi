<?php
	class api extends Control {

		protected $key = 'perro$$muerde&&al$$que33Roba22';

		public function index( ) {
		}

		public function ping( ) {
			echo 'Pong';
		}

		public function refresh( ) {
			$this->parametro->refresh();
		}

		public function getLoadAVG( ) {
			$load = sys_getloadavg();
			if($load[0] > 80) {
				header('HTTP/1.1 503 Too busy, try again later');
				die('Server too busy. Please try again later.');
			} else {
				return $load;
			}
		}

		public function GetCpuInformation( ) {
			$stat1 = file('/proc/stat');
			sleep(1);
			$stat2 = file('/proc/stat');
			$info1 = explode(" ",preg_replace("!cpu +!","",$stat1[0]));
			$info2 = explode(" ",preg_replace("!cpu +!","",$stat2[0]));
			$dif = array();
			$dif['user'] = $info2[0] - $info1[0];
			$dif['nice'] = $info2[1] - $info1[1];
			$dif['sys'] = $info2[2] - $info1[2];
			$dif['idle'] = $info2[3] - $info1[3];
			$total = array_sum($dif);
			$cpu = array();
			$cpu['core'] = count($info1) - 1;
			foreach($dif as $x => $y) {
				$cpu[$x] = round($y / $total * 100,1);
			}

			return $cpu;
		}

		public function servicios( ) {
			$result_xml = new SimpleXMLElement( '<?xml version="1.0" standalone="yes"?><status></status>' );

			$get_crontab_sql = "SELECT  `id_crontab`,`type_crontab`,`estado`,`fecha_hora_inicio`, `ciclo`,`exec_max_time` FROM `bm_crontab` ";

			$get_crontab_result = $this->conexion->queryFetch($get_crontab_sql);

			if($get_crontab_result) {

				foreach($get_crontab_result as $key => $crontab) {

					$server = $result_xml->addChild('crontab');
					$server->addAttribute('id',$crontab['id_crontab']);
					$server->addAttribute('name',$crontab['type_crontab']);
					$server->addChild('estado',$crontab['estado']);
					$server->addChild('fechahora',$crontab['fecha_hora_inicio']);
					$server->addChild('ciclo',$crontab['ciclo']);
					$server->addChild('exec_max_time',$crontab['exec_max_time']);

				}

			}
			//header("Content-type: text/xml; charset=utf-8");
			return $this->basic->encrypted($result_xml->asXML(),$this->key);
		}

		public function sistema( ) {
			$result_xml = new SimpleXMLElement( '<?xml version="1.0" standalone="yes"?><status></status>' );

			$server = $result_xml->addChild('load_average');

			$load = $this->getLoadAVG();

			$server->addChild('min_1',$load[0]);
			$server->addChild('min_5',$load[1]);
			$server->addChild('min_15',$load[2]);

			$server = $result_xml->addChild('cpus');

			$CpuInformation = $this->GetCpuInformation();

			$server->addChild('cores',$CpuInformation['core']);
			$server->addChild('user',$CpuInformation['user']);
			$server->addChild('system',$CpuInformation['sys']);
			$server->addChild('nice',$CpuInformation['nice']);
			$server->addChild('idle',$CpuInformation['idle']);

			return $this->basic->encrypted($result_xml->asXML(),$this->key);
		}

		function GetTotalSondas( $group ) {

			$get_total_sonda_sql = 'SELECT G.`name`, SUM(IF(`status` = 1, 1,0)) As total FROM `bm_host_groups` G ';

			if($group !== 'ALL') {
				$get_total_sonda_sql .= " WHERE G.`name`='$group' ";
			} else {
				$get_total_sonda_sql .= ' GROUP BY `groupid`';
			}

			$get_total_sonda_result = $this->conexion->queryFetch($get_total_sonda_sql);

			if($get_total_sonda_result) {
				if($group !== 'ALL') {
					$return = $get_total_sonda_result[0]['Total'];
				} else {
					$return = array();
					foreach($get_total_sonda_result as $key => $value) {
						$return[] = array("group" => $value['name'],"total" => $value['total']);
					}
				}

				return $return;

			} else {
				return false;
			}
		}

		private function getDownHost( $group ) {
			$get_total_sonda_sql = 'SELECT G.`name`,SUM(IF(H.`STATUS_SNMP`=0, 1,0)) As total FROM `bm_host_groups` G
									LEFT OUTER JOIN `bm_availability` H USING(`groupid`)';

			if($group !== 'ALL') {
				$get_total_sonda_sql .= " AND G.`name`='$group'";
			} else {
				$get_total_sonda_sql .= ' GROUP BY `groupid`';
			}

			$get_total_sonda_result = $this->conexion->queryFetch($get_total_sonda_sql);

			if($get_total_sonda_result) {
				if($group !== 'ALL') {
					$return = $get_total_sonda_result[0]['total'];
				} else {
					$return = array();
					foreach($get_total_sonda_result as $key => $value) {
						$return[] = array("group" => $value['name'],"total" => $value['total']);
					}
				}

				return $return;

			} else {
				return false;
			}
		}

		private function getDataHost( $group ) {
			$get_total_sonda_sql = "SELECT HG.name, SUM(IF(IP.`nextcheck` != '0000-00-00 00:00:00' AND H.`borrado`=0 AND H.`status`=1 AND
			`nextcheck` > NOW()
			AND I.`description` IN ('NACbandwidthdown.sh','NACbandwidthup.sh','LOCbandwidthdown.sh','LOCbandwidthup.sh','INTbandwidthdown.sh','INTbandwidthup.sh','NACping_avg.sh','LOCping_avg.sh','INTping_avg.sh','ip_renew.sh','INTping_std.sh','LOCping_std.sh','NACping_std.sh'), 1,0)) AS total FROM `bm_host_groups` HG
	LEFT JOIN `bm_host` H ON H.`groupid` = HG.`groupid`
	LEFT JOIN `bm_item_profile` IP ON IP.`id_host` = H.`id_host`
	LEFT JOIN `bm_items` I ON I.`id_item`=IP.`id_item`";

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
					foreach($get_total_sonda_result as $key => $value) {
						$return[] = array("group" => $value['name'],"total" => $value['total']);
					}
				}

				return $return;

			} else {
				return false;
			}
		}

		private function getNoDataHost( $group ) {
			$get_total_sonda_sql = " SELECT HG.name, SUM(IF(IP.`nextcheck` != '0000-00-00 00:00:00' AND H.`borrado`=0  AND H.`status`=1  AND
			`nextcheck` < DATE_ADD(NOW(), INTERVAL - 120 SECOND)
			AND I.`description` IN ('NACbandwidthdown.sh','NACbandwidthup.sh','LOCbandwidthdown.sh','LOCbandwidthup.sh','INTbandwidthdown.sh','INTbandwidthup.sh','NACping_avg.sh','LOCping_avg.sh','INTping_avg.sh','ip_renew.sh','INTping_std.sh','LOCping_std.sh','NACping_std.sh'), 1,0)) AS total FROM `bm_host_groups` HG
	LEFT JOIN `bm_host` H ON H.`groupid` = HG.`groupid`
	LEFT JOIN `bm_item_profile` IP ON IP.`id_host` = H.`id_host`
	LEFT JOIN `bm_items` I ON I.`id_item`=IP.`id_item`";

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
					foreach($get_total_sonda_result as $key => $value) {
						$return[] = array("group" => $value['name'],"total" => $value['total']);
					}
				}

				return $return;

			} else {
				return false;
			}
		}

		public function dashboard( ) {
			$result_xml = new SimpleXMLElement( '<?xml version="1.0" standalone="yes"?><status></status>' );

			$server = $result_xml->addChild('total_sondas');

			$groupTotal = $this->GetTotalSondas('ALL');

			foreach($groupTotal as $key => $value) {
				$server->addChild($value['group'],$value['total']);
			}

			$server = $result_xml->addChild('total_sondas_error');

			$groupTotal = $this->getDownHost('ALL');

			foreach($groupTotal as $key => $value) {
				$server->addChild($value['group'],$value['total']);
			}

			$server = $result_xml->addChild('total_monitores_con_datos');

			$groupTotal = $this->getDataHost('ALL');

			foreach($groupTotal as $key => $value) {
				$server->addChild($value['group'],$value['total']);
			}

			$server = $result_xml->addChild('total_monitores_sin_datos');

			$groupTotal = $this->getNoDataHost('ALL');

			foreach($groupTotal as $key => $value) {
				$server->addChild($value['group'],$value['total']);
			}

			return $this->basic->encrypted($result_xml->asXML(),$this->key);
		}

		public function status( $type = false ) {
			switch ($type) {
				case 'sys':
					echo $this->sistema();
					break;
				case 'servicios':
					echo $this->servicios();
					break;
				case 'dashboard':
					echo $this->dashboard();
					break;
				default:
					echo "Error Api";
			}
		}

		private function config( $value = '',$table = 'config' ) {
			$query_config = "SELECT $value FROM $table";

			$config = $this->conexion->queryFetch($query_config);

			if($config) {
				return $config[0]["$value"];
			} else {
				return false;
			}

		}

		private function validaCampo( $campo,$remplazo = '' ) {

			if($campo > 0) {
				$result = $campo;
			} else {
				$result = $remplazo;
			}
			return $result;
		}

		private function fetch( $query ) {

			$queryResult = $this->conexion->queryFetch($query);

			if($queryResult) {
				return (object)$queryResult[0];
			} else {
				return false;
			}
		}

		private function validMAC( $mac,$type_logs = 'logs_api' ) {
			if(isset($mac)) {

				$dnsReportado = "BSW".str_replace(":","",$mac);

				if(strpos($mac,":") == false) {
					$mac = rtrim(chunk_split(trim($mac),2,":"),":");
				}
				if(preg_match("/^[0-9a-fA-F]{2}(?=([:;.]?))(?:\\1[0-9a-fA-F]{2}){5}$/",$mac,$result)) {
					$query_validaHost = "SELECT H.`id_host`,H.`dns`,H.`host`,H.`status`, HG.`delay` , HG.`snmp_monitor`
                        FROM `bm_host` H LEFT JOIN `bm_host_groups` HG USING(`groupid`)
                        WHERE H.`borrado`=0 AND (H.`mac`='$mac'  OR H.`mac_lan`='$mac' OR `codigosonda`='$mac')";
					$validaHost = $this->fetch($query_validaHost);
					if($validaHost) {
						$validaHost->mac = $mac;
						return $validaHost;
					} else {
						$this->logs->error("Mac no registrada: ",$mac,$type_logs);
						return false;
					}
				} else {
					header("HTTP/1.1 400 Bad Request");
					print "ERROR: Invalid MAC\n";
					exit ;
				}
			} else {
				header("HTTP/1.1 400 Bad Request");
				print "ERROR: param MAC not found\n";
				exit ;
			}
		}

		public function validID( $identification,$type_logs = 'logs_api' ) {

			$identification = strtoupper($identification);

			if(isset($identification) && $identification != '') {

				if(strpos($identification,":") == false) {
					$mac = rtrim(chunk_split($identification,2,":"),":");
				} else {
					$mac = $identification;
				}

				if(preg_match("/^[0-9a-fA-F]{2}(?=([:;.]?))(?:\\1[0-9a-fA-F]{2}){5}$/",$mac,$result)) {
					$isMac = true;
				} else {
					$isMac = false;
				}

				$BSWID = "BSW".str_replace(":", '', $mac);

				$getValidaHostSQL = "SELECT H.`id_host` AS idHost,H.`dns`,H.`host`,H.`status`, HG.`delay`,IF(`identificator` = '', H.`dns`, `identificator`) as 'identificator' , `id_profile`  , HG.`snmp_monitor`, H.`mac`
            FROM `bm_host` H LEFT JOIN `bm_host_groups` HG USING(`groupid`)
            WHERE H.`borrado`=0 AND ";

                $identification = $this->conexion->quote($identification);
                $BSWID = $this->conexion->quote($BSWID);

				if($isMac == false) {
					$getValidaHostSQL = $getValidaHostSQL."( H.`codigosonda`=$identification OR `dns` = $BSWID  )  LIMIT 1";
				} else {
					$getValidaHostSQL = $getValidaHostSQL."( H.`codigosonda`=$identification OR H.`mac`='$mac' OR `dns` = $BSWID) LIMIT 1";
				}

				$getValidaHostRESULT = $this->conexion->queryFetch($getValidaHostSQL);

				if($getValidaHostRESULT) {
					$param = (object)$getValidaHostRESULT[0];
                    return $param;
				} else {
				    $this->logs->warning("ID not registered: ",$identification,$type_logs);
					return false;
				}


			} else {
				return false;
			}
		}

		private function getDelay( $id_sonda ) {

		}

		public function poweroff( ) {

			$getParam = (object)$_GET;

			$validaMac = @$this->validMAC($getParam->mac);

			$ip = $this->logs->getIP();

			if(($validaMac) && ($getParam->clock > 1000)) {

				print "# poweroff\n";
				//Ejecuto algunas cosas

				$this->conexion->query("INSERT INTO bm_dns (`name`,`ip`,`type`,`fechahora_update`)  VALUES ('$validaMac->dns','$ip','poweroff',NOW())
  									ON DUPLICATE KEY UPDATE `ip`= '$ip', `type`= 'poweroff', `fechahora_update` = NOW() ;");

				$this->conexion->query("update bm_host set ip_wan='1.0.0.0' where ip_wan='$ip' and dns <> '$validaMac->dns'");
				$this->conexion->query("update bm_host set ip_wan='$ip' where dns='$validaMac->dns' and ip_wan <> '$ip' ");

				/*
				 $this->conexion->query("UPDATE `bm_history` h LEFT JOIN  `bm_items`
				 i USING(`id_item`)  SET `value`=2
				 WHERE `value`=0 and clock >= $getParam->clock and
				 i.`description`='Availability.bsw' AND
				 h.`id_host`=$validaMac->id_host");
				 $this->conexion->query("UPDATE `bm_history_uint` h LEFT JOIN
				 `bm_items` i USING(`id_item`)  SET `value`=2
				 WHERE `value`=0 and clock >= $getParam->clock and
				 i.`description`='Availability.bsw' AND
				 h.`id_host`=$validaMac->id_host");
				 */
				//Fin de esas cosas
				print "# Talk to you later.\n";
				print "# END\n";

			} else {
				print "501 Go Away\n";
				print "# END\n";
			}

		}

		public function getConfig( $identificator,$method = 'json' ) {
			$getParam = (object)$_GET;
			$status = true;

			$param = $this->validID($identificator);

			if(($param == false)) {
				header("HTTP/1.1 403 Identificator Not Found");
				print "ERROR: Identificator not found\n";
				exit ;
			}

			//Get Detail
			$getSonda_Q = "SELECT  `bm_host`.`groupid`,  `bm_host_groups`.`name` as groupname , `bm_host`.`identificator`,
                                `bm_host`.`id_host` AS idHost, `bm_host`.`host`, `ip_lan` AS ipLan, `mac_lan`  as macLan, `netmask_lan` as netmaskLan FROM `bm_host`
                                JOIN `bm_host_groups` USING(`groupid`)
                                WHERE `bm_host`.`id_host`=:idHost";

			$getSonda_R = $this->conexion->queryExecuteFetch($getSonda_Q,array('idHost' => $param->idHost));

			if($getSonda_R) {

				$result['detail'] = $getSonda_R[0];

				//Get Config

				//$result['profile']  = array( 'CALL_LOOP' => 1);

				//Get Profile

				$getProfile_Q = "SELECT `profile_code`,`value`,P.`subgroups`,PG.`item_monitor`, PG.`class`  FROM `bm_profile_value` PV
                                LEFT JOIN `bm_profile` P USING(`id_profile`)
                                LEFT JOIN `bm_profile_groups` PG ON PG.`id_profile_groups`=P.`id_profile_groups`
                                WHERE `id_host`=:idHost
                                ORDER BY PG.`id_profile_groups`,`order`";
				$getProfile_R = $this->conexion->queryExecuteFetch($getProfile_Q,array('idHost' => $param->idHost));

				if($getProfile_R) {
					foreach($getProfile_R as $idProfile => $profile) {

						if( ! isset($profileSubGroups)) {
							$profileSubGroups = $profile['subgroups'];
							$count = 0;
						} else {
							if($profileSubGroups != $profile['subgroups']) {
								$count++;
								$profileSubGroups = $profile['subgroups'];
							}
						}
						$profiles[$count]['CLASS'] = $profile['class'];
						$profiles[$count][$profile['profile_code']] = $profile['value'];
						$profiles[$count]['profile_item_tmp'] = explode(',',$profile['item_monitor']);

					}

					$getItem_Q = "SELECT `id_item`,`name`,`description`
                                    FROM `bm_items` JOIN `bm_items_groups` USING(`id_item`)
                                    LEFT JOIN  `bm_host` ON  `bm_host`.`groupid`= `bm_items_groups`.`groupid`
                                    WHERE `bm_host`.`id_host`=:idHost AND `bm_items`.`type_poller`='bsw_agent'";

					$getItem_R = $this->conexion->queryExecuteFetch($getItem_Q,array('idHost' => $param->idHost));

					if($getItem_R) {
						foreach($getItem_R as $idItem => $item) {
							$items[$item['id_item']] = $item['description'];
						}

						foreach($profiles as $key => $value) {
							foreach($value['profile_item_tmp'] as $itemid => $item) {
								if(isset($items[$item])) {
									$profiles[$key]['item'][$items[$item]] = $item;
								}
							}
							unset($profiles[$key]['profile_item_tmp']);
						}

						$result['profile'] = array('CALL_LOOP' => 1);
						$result['profile']['subprofiles'] = $profiles;

					} else {
						$status = false;
					}

				} else {
					$status = false;
				}

			} else {
				$status = false;
			}

			if($status) {

				if($method == 'xml') {

					$result_xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><config></config>' );

					/*
					 $detail = $result_xml->addChild('detail');

					 foreach ($result['detail'] as $key => $value) {

					 }

					 $profile = $result_xml->addChild('profile');
					 */

					$this->arrayToXml($result,$result_xml);

					//var_dump($result);
					header("Content-Type:text/xml");
					echo $result_xml->asXML();
					exit ;

				} else {
					$this->basic->jsonEncode($result);
				}
			} else {
				header("HTTP/1.1 403 Error system");
				print "ERROR: Error system\n";
				print_r($param);
				exit ;
			}
		}

		public function getIni( $identificator,$hostName = 'none' ) {
			$getParam = (object)$_GET;
			$status = true;

			$param = $this->validID($identificator);

			if(($param == false)) {
				$param = $this->validID($hostName);
				if(($param == false)) {
					header("HTTP/1.1 403 Identificator Not Found");
					print "ERROR: Identificator not found\n";
					exit ;
				}
				header("HTTP/1.1 403 Identificator Not Found");
				print "ERROR: Identificator not found\n";
				exit ;
			}

			//Get Detail
			$getSonda_Q = "SELECT
                            H.`groupid`,  HG.`name` as groupname , H.`identificator`, H.`id_profile`,
                            H.`id_host` AS idHost, H.`host`, `ip_lan` AS ipLan,`mac` as macWan  ,`mac_lan`  as macLan, `netmask_lan` as netmaskLan ,
                            P.*
                        FROM `bm_host` H
                            LEFT JOIN `bm_host_groups` HG ON H.`groupid`=HG.`groupid`
                            LEFT JOIN `bm_plan` P ON P.`id_plan`=H.`id_plan`
                        WHERE H.`id_host`=:idHost";

			$getSonda_R = $this->conexion->queryExecuteFetch($getSonda_Q,array('idHost' => $param->idHost));

			if($getSonda_R) {

				$detail = (object)$getSonda_R[0];

				$iniData = "[MAIN]\n";

				$licence = $this->basic->encrypted(str_replace(":","",$detail->macWan),"8#K1NG S0FTWAR3");

				if($licence === false){
					$licence = 'NONE_LICENCE';
				}

				$iniData .= "VERSION=".$this->parametro->get('VERSION_QOS','3.48')."\n";
				$iniData .= "LIC=".$licence."\n";
				$iniData .= "SERIAL=".$this->parametro->get('SERIAL_QOS','V23D4567')."\n";
				$iniData .= "HOST=".$this->basic->cleanSpecialCharacters($detail->host)."\n";
				$iniData .= "HOSTID=".$param->idHost."\n";
				$iniData .= "NACD=".$detail->nacD."\n";
				$iniData .= "NACU=".$detail->nacU."\n";
				$iniData .= "LOCD=".$detail->locD."\n";
				$iniData .= "LOCU=".$detail->locU."\n";
				$iniData .= "INTD=".$detail->intD."\n";
				$iniData .= "INTU=".$detail->intU."\n";

				//Get Profile
				/*
				 $getProfile_Q = "SELECT DISTINCT `profile_code`,IF(PV.`value` IS NULL ,
				 P.`default_value`,PV.`value`) AS
				 'value', P.`subgroups`,PG.`item_monitor`, PG.`class`
				 FROM
				 `bm_profile` P
				 LEFT OUTER JOIN `bm_profile_groups` PG ON PG.`id_profile_groups`=P.`id_profile_groups`
				 LEFT OUTER JOIN `bm_profile_value` PV ON PV.`id_profile`=P.`id_profile`
				 WHERE
				 PG.`subgroups` =:idProfile AND
				 ( PV.`id_profile` IS NULL OR PV.`id_host`=:idHost)";

				 $getProfile_R = $this->conexion->queryExecuteFetch($getProfile_Q, array('idHost' => $param->idHost,
				 'idProfile' => $detail->id_profile));
				 */

				//Method two

				$getProfile_Q = "SELECT  `id_profile`,`profile_code`,P.`default_value` AS 'value', P.`subgroups`,PG.`item_monitor`, PG.`class`
                                FROM `bm_profile` P
                                        LEFT OUTER JOIN `bm_profile_groups` PG ON PG.`id_profile_groups`=P.`id_profile_groups`
                                WHERE
                                    PG.`subgroups` =:idProfile ORDER BY `profile_code`";

				$getProfile_R = $this->conexion->queryExecuteFetch($getProfile_Q,array('idProfile' => $detail->id_profile));

				if($getProfile_R) {
					$getProfileHost_Q = "SELECT  P.`id_profile`,PV.`value`
                        FROM
                            `bm_profile` P
                            LEFT OUTER JOIN `bm_profile_groups` PG ON PG.`id_profile_groups`=P.`id_profile_groups`
                            LEFT OUTER JOIN `bm_profile_value` PV ON PV.`id_profile`=P.`id_profile`
                        WHERE
                            PG.`subgroups` =:idProfile AND PV.`id_host`=:idHost";

					$getProfileHost_R = $this->conexion->queryExecuteFetch($getProfileHost_Q,array('idHost' => $param->idHost,'idProfile' => $detail->id_profile));

					if($getProfileHost_R) {

						foreach($getProfileHost_R as $profile) {
							$profileValue[$profile['id_profile']] = $profile['value'];
						}

						foreach($getProfile_R as $value) {
							if(isset($profileValue[$value['id_profile']])) {
								$value['value'] = $profileValue[$value['id_profile']];
							}
							$getProfileNew[] = $value;
						}

						$getProfile_R = $getProfileNew;
					}

				} else {
					$status = false;
				}

				if($status) {

					$getItem_Q = "SELECT `id_item`,`name`, `description`
                                    FROM `bm_items` JOIN `bm_items_groups` USING(`id_item`)
                                    LEFT JOIN  `bm_host` ON  `bm_host`.`groupid`= `bm_items_groups`.`groupid`
                                    WHERE `bm_host`.`id_host`=:idHost AND `bm_items`.`type_poller`='bsw_agent' ORDER BY `name`";

					$getItem_R = $this->conexion->queryExecuteFetch($getItem_Q,array('idHost' => $param->idHost));

					foreach($getItem_R as $key => $value) {
						$item[$value['id_item']] = $value['description'];
					}

					foreach($getProfile_R as $idProfile => $profile) {
						$titleProfile[$profile['profile_code']] = $profile['value'];
					}

					foreach($getProfile_R as $idProfile => $profile) {

						if( ! isset($profileGroups)) {
							$iniData .= "\n[".$profile['class']."]\n";
							$profileGroups = $profile['class'];

							$monitors = explode(',',$profile['item_monitor']);

							foreach($monitors as $key => $monitor) {
								if(isset($item[$monitor])) {
									$header = explode('_',$item[$monitor]);
									$var = str_replace($header[0],$profile['class'],$item[$monitor]);
									$var = $var."_".$titleProfile[$header[0]];

									$iniData .= $var."=".$monitor."\n";
								}
							}

						} elseif($profileGroups !== $profile['class']) {

							$monitors = explode(',',$profile['item_monitor']);

							$iniData .= "\n[".$profile['class']."]\n";
							$profileGroups = $profile['class'];

							foreach($monitors as $key => $monitor) {
								if(isset($item[$monitor])) {
									$header = explode('_',$item[$monitor]);
									$var = str_replace($header[0],$profile['class'],$item[$monitor]);
									$var = $var."_".$titleProfile[$header[0]];

									$iniData .= $var."=".$monitor."\n";
								}
							}

						}

						$iniData .= $profile['profile_code']."=".$profile['value']."\n";

					}

				} else {
					$status = false;
				}

				$iniData .= "\n[SAFE]\n";
				$iniData .= "SAFELINE=YES\n";

				echo $iniData;
			} else {
				$status = false;
				$msg = 'Error get detail host';
			}

		}

		public function identify() {
			$xmlString = file_get_contents("php://input");

			$xmlObject = $this->basic->getXML($xmlString);

			if($xmlObject !== false) {

				if(isset($xmlObject->item)) {
					foreach ($xmlObject->item as $key => $item) {
						$param = $this->validID($item);
						if(($param !== false)) {

							echo '<profile><host>'.$param->host.'</host><mac>'.$param->mac.'</mac><identify>'.$item.'</identify></profile>'."\n";

							exit;
						}
					}
				} else {
					echo "item not found";
					exit;
				}

				header("HTTP/1.1 403 Identificator Not Found");
				print "ERROR: Identificator not found\n";
				exit ;

			} else {
				echo "syntax error";
				exit;
			}
		}

		public function getProfile( $identificator,$hostName = 'none' ) {
			if($hostName == 'xml') {
				$this->getProfileXML($identificator);
			}

			$getParam = (object)$_GET;
			$status = true;

			$param = $this->validID($identificator);

			if(($param == false)) {
				header("HTTP/1.1 403 Identificator Not Found");
				print "ERROR: Identificator not found\n";
				exit ;
			}

			//Get Detail
			$getSonda_Q = "SELECT
                            H.`groupid`,  HG.`name` as groupname , H.`identificator`, H.`id_profile`, H.`status`,
                            H.`id_host` AS idHost, H.`host`, `ip_lan` AS ipLan,`mac` as macWan  ,`mac_lan`  as macLan, `netmask_lan` as netmaskLan ,
                            P.*
                        FROM `bm_host` H
                            LEFT JOIN `bm_host_groups` HG ON H.`groupid`=HG.`groupid`
                            LEFT JOIN `bm_plan` P ON P.`id_plan`=H.`id_plan`
                        WHERE H.`id_host`=:idHost AND H.`borrado` = 0";

			$getSonda_R = $this->conexion->queryExecuteFetch($getSonda_Q,array('idHost' => $param->idHost));

			if($getSonda_R) {

				$detail = (object)$getSonda_R[0];

				$getProfileSQL = "SELECT PP.`name`, PP.`description` , PP.`value` as 'defaultValue' , IF(PPH.`value` IS NOT NULL, PPH.`value`, PP.`value`)  as 'value', `id_host` , IF(`id_host` IS NULL, true,false) as 'default'
                            FROM `bm_profiles_param` PP
                                LEFT OUTER JOIN `bm_profiles_param_host` PPH ON PP.`id_param`=PPH.`id_param` AND `id_host` = ?
                            WHERE `id_profile` = ? AND `visible` = 'true'";
				$getProfileRESULT = $this->conexion->queryFetch($getProfileSQL,$param->idHost,$detail->id_profile);

				if($getProfileRESULT) {
					foreach($getProfileRESULT as $key => $value) {
						$paramProfile[$value['name']] = $value['value'];

						$profileParamSearch[] = '$$'.$value['name'];
						$profileParamSearch[] = '$'.$value['name'];
						$profileParamReplace[] = $value['defaultValue'];
						$profileParamReplace[] = $value['value'];

					}
				} else {
					$paramProfile = array("SCHEDULE" => '1-7,00:00-24:00',"SERIAL" => 'V23D4567',"HOLIDAY" => '');
				}

				if( ! isset($paramProfile['HOLIDAY'])) {
					$paramProfile['HOLIDAY'] = '';
				}

				if( ! isset($paramProfile['SCHEDULE'])) {
					$paramProfile['SCHEDULE'] = '1-7,00:00-24:00';
				}

				$ACTIVE = $this->basic->cdate($paramProfile['SCHEDULE'],$paramProfile['HOLIDAY']);
				$iniData = "";
				$iniData = "[MAIN]\n"; 
				
				if($detail->status == 0) {
					$iniData .= "STATUS=PAUSED"."\n";
				} elseif($ACTIVE == true) {
					$iniData .= "STATUS=ACTIVE"."\n";
				} else {
					$iniData .= "STATUS=DISABLED"."\n";
				}

				$server1 = $this->parametro->get("BSWMASTER");
				if(strpos($server1,'http') === false) {
					$server1 = "http://".$server1;
				}

				$server2 = $this->parametro->get("BSWSLAVE");
				if(strpos($server2,'http') === false) {
					$server2 = "http://".$server2;
				}

				$iniData .= "SERVER1=".$server1.":".$this->parametro->get("BSW_PORT_API",80)."\n";
				$iniData .= "SERVER2=".$server2.":".$this->parametro->get("BSW_PORT_API",80)."\n";
				$iniData .= "SERVER=".$server1.":".$this->parametro->get("BSW_PORT_API",80)."\n";

				$iniData .= "VERSION=".$this->parametro->get('VERSION_QOS','3.48')."\n";
				$iniData .= "LIC=".$this->basic->encrypted(str_replace(":","",$detail->macWan),"8#K1NG S0FTWAR3")."\n";
				$iniData .= "SERIAL=".$paramProfile['SERIAL']."\n";
				$iniData .= "HOST=".$this->basic->cleanSpecialCharacters($detail->host)."\n";
				$iniData .= "HOSTID=".$param->idHost."\n";
				$iniData .= "NACD=".$detail->nacD."\n";
				$iniData .= "NACU=".$detail->nacU."\n";
				$iniData .= "LOCD=".$detail->locD."\n";
				$iniData .= "LOCU=".$detail->locU."\n";
				$iniData .= "INTD=".$detail->intD."\n";
				$iniData .= "INTU=".$detail->intU."\n";;

                unset($paramProfile['SERIAL']);
                unset($paramProfile['SCHEDULE']);
                unset($paramProfile['HOLIDAY']);

                foreach ($paramProfile as $key => $value) {
                    $iniData .= "$key=".$value."\n";
                }

				//Method two

				$getProfile_Q = "SELECT PCT.`count` , PCIES.`class`, PI.`item`, PI.`type`, PI.`type_result`,PV.`id_monitor`,IF(PHOST.`value` IS NOT NULL , PHOST.`value`, PV.`value`) AS value
                FROM
                    `bm_profiles` P
                LEFT JOIN `bm_profiles_categories`  PCIES ON P.`id_profile` = PCIES.`id_profile`
                LEFT JOIN `bm_profiles_item` PI ON PI.`id_categories`=PCIES.`id_categories`
                LEFT JOIN `bm_profiles_category` PCT ON PCIES.`id_categories`= PCT.`id_categories`
                LEFT JOIN `bm_profiles_values` PV ON (PI.`id_item`=PV.`id_item` AND PCT.`id_category`=PV.`id_category`)
                LEFT OUTER JOIN (SELECT `id_value`,`value` FROM `bm_profiles_host` WHERE `id_host` = :idHost) AS PHOST ON PV.`id_value`=PHOST.`id_value`
                WHERE P.`id_profile` = :idProfile AND PCT.`status` = 'true'
                GROUP BY PV.`id_value`
                ORDER BY PCIES.`class`, PCT.`count`, PI.`type`,  PI.`item`";

				$getProfile_R = $this->conexion->queryExecuteFetch($getProfile_Q,array('idProfile' => $detail->id_profile, 'idHost' => $param->idHost));

				if($getProfile_R) {
					$class = '';
					foreach($getProfile_R as $key => $profile) {
						if($class !== $profile['class']) {
							$iniData .= "\n".'['.$profile['class']."]";
							$class = $profile['class'];
						}

						if($profile['type'] == 'param') {
							if($profile['item'] == 'SCRIPT'){
								$valueProfile = str_replace($profileParamSearch,$profileParamReplace,$profile['value']);
							} else {
								$valueProfile = $profile['value'];
							}
							$iniData .= "\n".$profile['class']."_".$profile['count']."_PARAM_".$profile['item']."=".$valueProfile;
						} else {
							$iniData .= "\n".$profile['class']."_".$profile['count']."_RESULT_".$profile['item']."=".$profile['id_monitor'];
						}
						$count = $profile['count'];

						if( ! isset($getProfile_R[$key + 1]['class']) || ($getProfile_R[$key + 1]['class'] != $class)) {
							$iniData .= "\n".$profile['class']."_COUNT=".$count;
						}

					}

				} else {
					header("HTTP/1.1 403 Pofile Not Found");
					print "ERROR: Profile not found\n";
					exit ;
				}

				$iniData .= "\n[SAFE]\n";
				$iniData .= "SAFELINE=YES";

				echo $iniData;
				//$this->logs->error("se ariel getProfile envia bmonitor data: $iniData");  
				exit ;
			} else {
				$status = false;
				$msg = 'Error get detail host';
			}
		}

		public function getProfileXML( $identificator,$hostName = 'none' ) {
			$getParam = (object)$_GET;
			$status = true;

			$param = $this->validID($identificator);

			if(($param == false)) {
				header("Content-type: text/xml; charset=UTF8");
				header("HTTP/1.1 403 Identificator Not Found");
				print "<profile>ERROR: Identificator not found</profile>";
				exit ;
			}

			//Get Detail
			$getSonda_Q = "SELECT
                            H.`groupid`,  HG.`name` as groupname , H.`identificator`, H.`id_profile`, H.`status`,
                            H.`id_host` AS idHost, H.`host`, `ip_lan` AS ipLan,`mac` as macWan  ,`mac_lan`  as macLan, `netmask_lan` as netmaskLan ,
                            P.*
                        FROM `bm_host` H
                            LEFT JOIN `bm_host_groups` HG ON H.`groupid`=HG.`groupid`
                            LEFT JOIN `bm_plan` P ON P.`id_plan`=H.`id_plan`
                        WHERE H.`id_host`=:idHost AND H.`borrado` = 0";

			$getSonda_R = $this->conexion->queryExecuteFetch($getSonda_Q,array('idHost' => $param->idHost));

			if($getSonda_R) {

				$detail = (object)$getSonda_R[0];

				$getProfileSQL = "SELECT PP.`name`, IF(PPH.`value` IS NOT NULL, PPH.`value`, PP.`value`)  as 'value'
                            FROM `bm_profiles_param` PP
                                LEFT JOIN `bm_profiles_param_host` PPH ON PP.`id_param`=PPH.`id_param`
                            WHERE `id_profile` = ? AND `visible` = 'true'";
				$getProfileRESULT = $this->conexion->queryFetch($getProfileSQL,$detail->id_profile);

				if($getProfileRESULT) {
					foreach($getProfileRESULT as $key => $value) {
						$paramProfile[$value['name']] = $value['value'];
					}
				} else {
					$paramProfile = array("SCHEDULE" => '1-7,00:00-24:00',"SERIAL" => 'V23D4567',"HOLIDAY" => '');
				}

				if(!isset($paramProfile['SCHEDULE'])){
				    $paramProfile['SCHEDULE'] = '1-7,00:00-24:00';
				}

                if(!isset($paramProfile['HOLIDAY'])){
                    $paramProfile['HOLIDAY'] = '';
                }

				$ACTIVE = $this->basic->cdate($paramProfile['SCHEDULE'],$paramProfile['HOLIDAY']);

				$profileXML = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><profile></profile>' );

				$mainSec = $profileXML->addChild('main');

				if($detail->status == 0) {
					$STATUS = "PAUSED";
				} elseif($ACTIVE == true) {
					$STATUS = "ACTIVE";
				} else {
					$STATUS = "DISABLED";
				}

				$mainSec->addChild("status",$STATUS);

				$server1 = $this->parametro->get("BSWMASTER");
				if(strpos($server1,'http') === false) {
					$server1 = "http://".$server1.":".$this->parametro->get("BSW_PORT_API",80);
				}

				$server2 = $this->parametro->get("BSWSLAVE");
				if(strpos($server2,'http') === false) {
					$server2 = "http://".$server2.":".$this->parametro->get("BSW_PORT_API",80);
				}

				$mainSec->addChild("server1",$server1);
				$mainSec->addChild("server2",$server2);
				$mainSec->addChild("server",$server1);
				$mainSec->addChild("version",$this->parametro->get('VERSION_QOS','3.48'));

				$mainSec->addChild("license",$this->basic->encrypted(str_replace(":","",$detail->macWan),"8#K1NG S0FTWAR3"));
				$mainSec->addChild("serial",$paramProfile['SERIAL']);
				$mainSec->addChild("hostname",$this->basic->cleanSpecialCharacters($detail->host));
				$mainSec->addChild("hostid",$param->idHost);
				$mainSec->addChild("NACD",$detail->nacD);
				$mainSec->addChild("NACU",$detail->nacU);

                /*
				$mainSec->addChild("LOCD",$detail->locD);
				$mainSec->addChild("LOCU",$detail->locU);
				$mainSec->addChild("INTD",$detail->intD);
				$mainSec->addChild("INTU",$detail->intU);
                */

                unset($paramProfile['SERIAL']);
                unset($paramProfile['SCHEDULE']);
                unset($paramProfile['HOLIDAY']);

                foreach ($paramProfile as $key => $value) {
                    $mainSec->addChild("$key",$value);
                }

				//Method two
				$modulesSec = $profileXML->addChild('modules');
				$getProfile_Q = "SELECT PCT.`count` , PCIES.`class`, PCIES.`sequenceCode`, PI.`item`, PI.`type`,
			            			PI.`type_result`,PV.`id_monitor`,IF(PHOST.`value` IS NOT NULL , PHOST.`value`, PV.`value`) AS value
			                FROM
			                    `bm_profiles` P
			                LEFT JOIN `bm_profiles_categories`  PCIES ON P.`id_profile` = PCIES.`id_profile`
			                LEFT JOIN `bm_profiles_item` PI ON PI.`id_categories`=PCIES.`id_categories`
			                LEFT JOIN `bm_profiles_category` PCT ON PCIES.`id_categories`= PCT.`id_categories`
			                LEFT JOIN `bm_profiles_values` PV ON (PI.`id_item`=PV.`id_item` AND PCT.`id_category`=PV.`id_category`)
			                LEFT OUTER JOIN (SELECT `id_value`,`value` FROM `bm_profiles_host` WHERE `id_host` = :idHost) AS PHOST ON PV.`id_value`=PHOST.`id_value`
			                WHERE P.`id_profile` = :idProfile AND PCT.`status` = 'true'
			                GROUP BY PV.`id_value`
			                ORDER BY PCIES.`class`, PCT.`count`, PI.`type`,  PI.`item`";

				$getProfile_R = $this->conexion->queryExecuteFetch($getProfile_Q,array('idProfile' => $detail->id_profile,'idHost' => $param->idHost));

				if($getProfile_R) {
					$profileItems = array();
                    $profileCode = array();
					foreach($getProfile_R as $key => $profile) {
                        $profileCode[$profile['class']] = $profile['sequenceCode'];

						if($profile['type'] == 'param') {
							$profileItems[$profile['class']][$profile['count']]['param'][$profile['item']] = $profile['value'];
						} else {
							$profileItems[$profile['class']][$profile['count']]['result'][$profile['item']] = $profile['id_monitor'];
						}
					}

					foreach($profileItems as $keyModule => $valueModule) {
						$secName = $modulesSec->addChild($keyModule);
						$secName->addAttribute('code', $profileCode[$keyModule]);
						foreach($valueModule as $keyItem => $valueItem) {
							$subSecName = $secName->addChild("item");
							if(isset($valueItem['param']) && is_array($valueItem['param']) && count($valueItem['param']) > 0) {
    							$param = $subSecName->addChild("param");
    							foreach($valueItem['param'] as $keyParam => $valueParam) {
    								$param->$keyParam = $valueParam;
    							}
                            }
                            if(isset($valueItem['param']) && is_array($valueItem['result']) && count($valueItem['param']) > 0) {
    							$result = $subSecName->addChild("result");
    							foreach($valueItem['result'] as $keyResult => $valueResult) {
                                   $result->$keyResult = $valueResult;
    							}
                            }
						}
					}

				} else {
					header("HTTP/1.1 403 Pofile Not Found");
					print "ERROR: Profile not found\n";
					exit ;
				}

				header("Content-type: text/xml; charset=UTF8");
				header('HTTP/1.1 200 OK');
				echo $profileXML->asXML();
				exit ;
			} else {
				$status = false;
				$msg = 'Error get detail host';
			}
		}

		private function arrayToXml( $student_info,&$xml_student_info ) {
			foreach($student_info as $key => $value) {
				if(is_array($value)) {
					if( ! is_numeric($key)) {
						$subnode = $xml_student_info->addChild("$key");
						$this->arrayToXml($value,$subnode);
					} else {
						$subnode = $xml_student_info->addChild("subprofile");
						$subnode->addAttribute('id',$key);
						$this->arrayToXml($value,$subnode);
					}
				} else {
					if( ! is_numeric($key)) {
						$xml_student_info->addChild("$key","$value");
					} else {
						$subnode = $xml_student_info->addChild("subprofile");
						$subnode->addAttribute('id',$key);
					}
				}
			}
		}

		public function configuratorDEPRECATED( ) {
			$getParam = (object)$_GET;

			$validaMac = @$this->validMAC($getParam->mac);

			if(( ! $validaMac)) {
				header("HTTP/1.1 403 Mac Not Found");
				print "ERROR: MAC not found\n";
				exit ;
			}

			$printConfig = "# $getParam->mac\n";

			$queryHost = "SELECT  `bm_host`.`groupid`, `bm_host_groups`.`tz`  , `bm_host_groups`.`name` as groupname ,`bm_host`.`id_host`, `bm_host`.`host`, `ip_lan`, `mac_lan` , `netmask_lan`, `sysctl`, `ppp`, `bm_plan`.`plan`, `bm_plan`.`id_plan`, nacDS,nacDT,nacUS,nacUT,locDS,locDT,locUS,locUT,intDS,intDT,intUS,intUT,nacD,nacU,intD,intU,locD,locU  FROM `bm_host`
						JOIN `bm_host_groups` USING(`groupid`)
						JOIN `bm_plan` ON `bm_host`.`id_plan`= `bm_plan`.`id_plan`
						WHERE `bm_host`.`id_host`='$validaMac->id_host'";

			$host = $this->fetch($queryHost);

			$queryFeature = "SELECT `bm_host_feature`.`feature`, `bm_host_detalle`.`value` , `bm_host_feature`.`default_value`
							 FROM `bm_host_detalle`  JOIN `bm_host_feature` USING(`id_feature`)
							 WHERE `bm_host_detalle`.`id_host`=$host->id_host";

			$feature_host = $this->conexion->queryFetch($queryFeature);

			foreach($feature_host as $key => $feature) {
				if((isset($feature['value'])) && ($feature['value'] != "")) {
					$value = $feature['value'];
				} else {
					$value = $feature['default_value'];
				}
				$features[$feature['feature']] = $value;
			}

			$features = (object)$features;

			if($host) {

				//Vacation:

				$select_vacation_sql = "SELECT unix_timestamp(`fecha_inicio`) as fecha_inicio,unix_timestamp(fecha_fin-5) as fecha_fin FROM `bm_vacation` WHERE `id_host` = $validaMac->id_host AND `fecha_fin` >= NOW() ORDER BY  `fecha_fin` ASC";

				$select_vacation_result = $this->conexion->queryFetch($select_vacation_sql);

				if($select_vacation_result) {
					if(count($select_vacation_result) > 0) {

						$vacation_ndw1 = $select_vacation_result[0]['fecha_inicio'];
						$vacation_ndw2 = $select_vacation_result[0]['fecha_fin'];

					} else {
						$vacation_ndw1 = '';
						$vacation_ndw2 = '';
					}
				} else {
					$vacation_ndw1 = '';
					$vacation_ndw2 = '';
				}

				if($host->sysctl != "") {
					$host->sysctl = $this->basic->limpiarMetas($host->sysctl);

					$printConfig .= "echo \"".$host->sysctl."\" > /etc/sysctl.conf\n";
					$printConfig .= "sysctl -p\n";
				}

				//sas

				if( ! isset($features->wl_channel)) {
					$features->wl_channel = "6";
				} elseif($features->wl_channel == "0") {
					$features->wl_channel = "auto";
				}

				if( ! isset($features->wl_net_mode)) {
					$features->wl_net_mode = "ap";
				}

				if($features->wifi == "0") {
					$features->wifi = "1";
				} elseif($features->wifi == "1") {
					$features->wifi = "0";
				}

				if($host->ppp != "") {
					$printConfig .= "echo \"".$host->ppp."\" > /etc/ppp/options\n";
				}

				$printConfig .= "uci show baking | grep ITEM | awk 'BEGIN {FS=\"=\"}{print $1 \"=\"}' | xargs  -n1 uci set\n";

				//Limpiando

				$hostname_limpio = $this->basic->cleanSpecialCharacters($host->host);

				$printConfig .= 'uci set system.@system[0].hostname="'.$hostname_limpio.'"'."\n";
				$printConfig .= "uci set system.@system[0].reboot=0\n";
				$printConfig .= "uci set system.@system[0].timezone=".$this->parametro->get('TIMEZONE','GMT+4')."\n";
				$printConfig .= "uci set system.@system[0].company=".$this->parametro->get('COMPANY','BSW')."\n";
				$printConfig .= "uci set system.@rdate[0]=\n";
				$printConfig .= "uci commit system\n";
				$printConfig .= "uci set baking.pcontrol=$features->cparental\n";
				$printConfig .= "URL=upload\n";
				$printConfig .= "uci set wireless.radio0.channel=$features->wl_channel\n";
				$printConfig .= "uci set wireless.radio0.disabled=$features->wifi\n";
				$printConfig .= "uci set wireless.radio0.mode=$features->wl_net_mode\n";
				$printConfig .= "uci set wireless.@wifi-iface[0].ssid=\"$features->wl_ssid\"\n";
				$printConfig .= "uci set wireless.@wifi-iface[0].encryption=".$this->parametro->get('ENCRYPTION_WL','mixed-psk+tkip+aes')."\n";
				$printConfig .= "uci set wireless.@wifi-iface[0].key=\"$features->wl_key\"\n";
				$printConfig .= "wifi\n";
				if(isset($features->block_ip) && $features->block_ip != "") {
					$ipblocks = explode(";",$features->block_ip);
					foreach($ipblocks as $ipblock) {
						$printConfig .= "iptables -I FORWARD -p tcp --dport 80 -d $ipblock  -j DROP\n";
						$printConfig .= "iptables -I FORWARD -p tcp --dport 8080 -d $ipblock  -j DROP\n";
						$printConfig .= "iptables -I FORWARD -p tcp --dport 443 -d $ipblock  -j DROP\n";
						$printConfig .= "iptables -I FORWARD -p tcp --dport 25 -d $ipblock  -j DROP\n";
						$printConfig .= "iptables -I FORWARD -p tcp --dport 110 -d $ipblock  -j DROP\n";
						$printConfig .= "iptables -I FORWARD -p tcp --dport 465 -d $ipblock  -j DROP\n";
					}
				}

				$printConfig .= "sh setme.sh network.lan.ipaddr $host->ip_lan\n";
				$printConfig .= "sh setme.sh network.lan.netmask $host->netmask_lan\n";

				$printConfig .= 'uci set baking.id_host="'.$host->id_host.'"'."\n";
				$printConfig .= 'uci set baking.groupid="'.$host->groupid.'"'."\n";
				$printConfig .= 'uci set baking.id_plan="'.$host->id_plan.'"'."\n";

				$printConfig .= "uci set baking.INT_D=$host->intD\n";
				$printConfig .= "uci set baking.INT_U=$host->intU\n";
				$printConfig .= "uci set baking.NAC_D=$host->nacD\n";
				$printConfig .= "uci set baking.NAC_U=$host->nacU\n";
				$printConfig .= "uci set baking.LOC_D=$host->locD\n";
				$printConfig .= "uci set baking.LOC_U=$host->locU\n";
				$printConfig .= "uci set baking.plan=".$this->basic->cleanSpecialCharacters($host->plan)."\n";

				$bsw_config_query = "select `bm_host_groups`.* from `bm_host_groups` where `groupid` =$host->groupid";
				$bsw_config = $this->fetch($bsw_config_query);

				$speedtest_NAC_sessions_B = $this->validaCampo($host->nacDS,$bsw_config->speedtest_NAC_sessions_B);
				$speedtest_INT_sessions_B = $this->validaCampo($host->intDS,$bsw_config->speedtest_INT_sessions_B);
				$speedtest_LOC_sessions_B = $this->validaCampo($host->locDS,$bsw_config->speedtest_LOC_sessions_B);
				$speedtest_NAC_sessions_S = $this->validaCampo($host->nacUS,$bsw_config->speedtest_NAC_sessions_S);
				$speedtest_INT_sessions_S = $this->validaCampo($host->intUS,$bsw_config->speedtest_INT_sessions_S);
				$speedtest_LOC_sessions_S = $this->validaCampo($host->locUS,$bsw_config->speedtest_LOC_sessions_S);
				$speedtest_NAC_time_B = $this->validaCampo($host->nacDT,$bsw_config->speedtest_NAC_time_B);
				$speedtest_INT_time_B = $this->validaCampo($host->intDT,$bsw_config->speedtest_INT_time_B);
				$speedtest_LOC_time_B = $this->validaCampo($host->locDT,$bsw_config->speedtest_LOC_time_B);
				$speedtest_NAC_time_S = $this->validaCampo($host->nacUT,$bsw_config->speedtest_NAC_time_S);
				$speedtest_INT_time_S = $this->validaCampo($host->intUT,$bsw_config->speedtest_INT_time_S);
				$speedtest_LOC_time_S = $this->validaCampo($host->locUT,$bsw_config->speedtest_LOC_time_S);

				$printConfig .= "uci set baking.ping_NAC_server=$bsw_config->ping_NAC_server\n";
				$printConfig .= "uci set baking.ping_INT_server=$bsw_config->ping_INT_server\n";
				$printConfig .= "uci set baking.ping_LOC_server=$bsw_config->ping_LOC_server\n";
				$printConfig .= "uci set baking.ping_NAC_count=$bsw_config->ping_NAC_count\n";
				$printConfig .= "uci set baking.ping_INT_count=$bsw_config->ping_INT_count\n";
				$printConfig .= "uci set baking.ping_LOC_count=$bsw_config->ping_LOC_count\n";
				$printConfig .= "uci set baking.ping_NAC_size=$bsw_config->ping_NAC_size\n";
				$printConfig .= "uci set baking.ping_INT_size=$bsw_config->ping_INT_size\n";
				$printConfig .= "uci set baking.ping_LOC_size=$bsw_config->ping_LOC_size\n";
				$printConfig .= "uci set baking.ping_NAC_latencia=$bsw_config->ping_NAC_latencia\n";
				$printConfig .= "uci set baking.ping_INT_latencia=$bsw_config->ping_INT_latencia\n";
				$printConfig .= "uci set baking.ping_LOC_latencia=$bsw_config->ping_LOC_latencia\n";
				$printConfig .= "uci set baking.speedtest_NAC_server=$bsw_config->speedtest_NAC_server\n";
				$printConfig .= "uci set baking.speedtest_INT_server=$bsw_config->speedtest_INT_server\n";
				$printConfig .= "uci set baking.speedtest_LOC_server=$bsw_config->speedtest_LOC_server\n";
				$printConfig .= "uci set baking.speedtest_download_path=$bsw_config->speedtest_download_path\n";
				$printConfig .= "uci set baking.speedtest_upload_path=$bsw_config->speedtest_upload_path\n";

				$printConfig .= "uci set baking.speedtest_NAC_sessions_B=$speedtest_NAC_sessions_B\n";
				$printConfig .= "uci set baking.speedtest_INT_sessions_B=$speedtest_INT_sessions_B\n";
				$printConfig .= "uci set baking.speedtest_LOC_sessions_B=$speedtest_LOC_sessions_B\n";
				$printConfig .= "uci set baking.speedtest_NAC_sessions_S=$speedtest_NAC_sessions_S\n";
				$printConfig .= "uci set baking.speedtest_INT_sessions_S=$speedtest_INT_sessions_S\n";
				$printConfig .= "uci set baking.speedtest_LOC_sessions_S=$speedtest_LOC_sessions_S\n";
				$printConfig .= "uci set baking.speedtest_NAC_time_B=$speedtest_NAC_time_B\n";
				$printConfig .= "uci set baking.speedtest_INT_time_B=$speedtest_INT_time_B\n";
				$printConfig .= "uci set baking.speedtest_LOC_time_B=$speedtest_LOC_time_B\n";
				$printConfig .= "uci set baking.speedtest_NAC_time_S=$speedtest_NAC_time_S\n";
				$printConfig .= "uci set baking.speedtest_INT_time_S=$speedtest_INT_time_S\n";
				$printConfig .= "uci set baking.speedtest_LOC_time_S=$speedtest_LOC_time_S\n";

				$printConfig .= "uci set baking.ndw1=$vacation_ndw1\n";
				$printConfig .= "uci set baking.ndw2=$vacation_ndw2\n";

				$printConfig .= "uci set baking.renew=$bsw_config->ip_max\n";
				$printConfig .= "uci set baking.groupid=$host->groupid\n";
				$printConfig .= "uci set baking.groupname=\"$host->groupname\"\n";
				$printConfig .= "uci set baking.horario=\"$bsw_config->horario\"\n";
				$printConfig .= "uci set baking.feriados=\"$bsw_config->feriados\"\n";
				$printConfig .= "sh /root/checkdelay.sh $bsw_config->delay\n";
				$printConfig .= "uci set baking.delay=$bsw_config->delay\n";

				$globalTimezone = $this->parametro->get('TIMEZONE_GLOBAL',true);
				$globalTimezoneAuto = $this->parametro->get('TIMEZONE_GLOBAL_AUTO',true);

				if($globalTimezone) {
					if($globalTimezoneAuto) {
						$timeZone = $this->basic->getTimeZoneOffset("UTC");
					} else {
						$timeZone = $this->parametro->get('TIMEZONE','UTC+4');
					}
				} else {
					if(isset($host->tz) && ($host->tz !== '')) {
						$timeZone = $host->tz;
					} else {
						$timeZone = $this->parametro->get('TIMEZONE','UTC+4');
					}
				}

				$printConfig .= "uci set baking.TZ=".$timeZone."\n";
				$printConfig .= "uci set baking.retry=$bsw_config->retry\n";
				$printConfig .= "uci set baking.maxretry=$bsw_config->maxretry\n";
				$printConfig .= "echo ".$timeZone." > /etc/TZ\n";

				$printConfig .= "uci set baking.horus_server=".$this->parametro->get("HORUS_SERVER",'horus.baking.cl').":".$this->parametro->get("HORUS_SERVER_PORT_API",80)."\n";

				$printConfig .= "uci set baking.slot=\n";
				$printConfig .= "uci commit\n";
				$printConfig .= "date -s \"".date("Y.m.d-H:i:s").'"'."\n";
				//$printConfig .= "rdate -s nist1.symmetricom.com\n";
				//$printConfig .= "/usr/sbin/ntpclient -c 1 -h pool.ntp.org\n";
				//$item_query="SELECT `id_item`,`name`,`description` FROM `bm_items`
				// JOIN `bm_item_profile` USING(`id_item`) WHERE `id_host` =
				// $host->id_host AND `type_poller`='bsw_agent'";
				$item_query = "SELECT `id_item`,`name`,`description`
									FROM `bm_items` JOIN `bm_items_groups` USING(`id_item`)
									LEFT JOIN  `bm_host` ON  `bm_host`.`groupid`= `bm_items_groups`.`groupid`
									WHERE `bm_host`.`id_host`=$host->id_host AND `bm_items`.`type_poller`='bsw_agent'";
				$items = $this->conexion->queryFetchAllAssoc($item_query);
				if($items) {
					$itemCount = count($items);
					$printConfig .= "# ".$itemCount." items\n";
					foreach($items as $key => $item) {
						$itemid = $item['id_item'];
						$desc = str_replace(".sh","",$item['description']);
						$printConfig .= "uci set baking.ITEM".$desc."=".$itemid."\n";
					}
				}
				$printConfig .= "uci commit\n";
				$printConfig .= "r=\$(uci get system.@system[0].reboot)\n";
				$printConfig .= "if [ \"\$r\" == \"1\" ] ; then\n";
				$printConfig .= "  uci set system.@system[0].reboot=0\n";
				$printConfig .= "  uci commit\n";
				$printConfig .= "    cp /tmp/bsw/* /root/bsw\n";
				$printConfig .= "    cp /tmp/*.sh /root/last\n";
				$printConfig .= "  reboot\n";
				$printConfig .= "fi\n";
				$printConfig .= "# END\n";
				print $printConfig;
			} else {
				print "uci set system.bswslot=1\n";
				print "uci commit\n";
				print "# END\n";
			}

		}

		//configurator Profile BGS018 lo q estaba en devel OK
		public function configurator($mac = false) {
			$getParam = (object)$_GET;

			$param = $this->validID($getParam->mac);

			if(($param == false)) {
				header("HTTP/1.1 403 Identificator Not Found");
				print "ERROR: Identificator not found\n";
				exit ;
			}

			$printConfig = "# $getParam->mac\n";

			$queryHost = "SELECT  `bm_host`.`groupid`, `bm_host_groups`.`tz`  , `bm_host_groups`.`name` as groupname ,`bm_host`.`id_host`, `bm_host`.`id_profile` as idProfile , `bm_host`.`host`, `ip_lan`, `mac_lan` , `netmask_lan`, `sysctl`, `ppp`, `bm_plan`.`plan`, `bm_plan`.`id_plan`, nacDS,nacDT,nacUS,nacUT,locDS,locDT,locUS,locUT,intDS,intDT,intUS,intUT,nacD,nacU,intD,intU,locD,locU  FROM `bm_host` 
						JOIN `bm_host_groups` USING(`groupid`) 
						JOIN `bm_plan` ON `bm_host`.`id_plan`= `bm_plan`.`id_plan`
						WHERE `bm_host`.`id_host`='$param->idHost'";

			$host = $this->fetch($queryHost);

			$queryFeature = "SELECT `bm_host_feature`.`feature`, `bm_host_detalle`.`value` , `bm_host_feature`.`default_value` 
							 FROM `bm_host_detalle`  JOIN `bm_host_feature` USING(`id_feature`) 
							 WHERE `bm_host_detalle`.`id_host`=$param->idHost";

			$feature_host = $this->conexion->queryFetch($queryFeature);

			foreach($feature_host as $key => $feature) {
				if((isset($feature['value'])) && ($feature['value'] != "")) {
					$value = $feature['value'];
				} else {
					$value = $feature['default_value'];
				}
				$features[$feature['feature']] = $value;
			}

			$features = (object)$features;

			if($host) {

				//Vacation:

				$select_vacation_sql = "SELECT unix_timestamp(`fecha_inicio`) as fecha_inicio,unix_timestamp(fecha_fin-5) as fecha_fin FROM `bm_vacation` WHERE `id_host` = $param->idHost AND `fecha_fin` >= NOW() ORDER BY  `fecha_fin` ASC";

				$select_vacation_result = $this->conexion->queryFetch($select_vacation_sql);

				if($select_vacation_result) {
					if(count($select_vacation_result) > 0) {

						$vacation_ndw1 = $select_vacation_result[0]['fecha_inicio'];
						$vacation_ndw2 = $select_vacation_result[0]['fecha_fin'];

					} else {
						$vacation_ndw1 = '';
						$vacation_ndw2 = '';
					}
				} else {
					$vacation_ndw1 = '';
					$vacation_ndw2 = '';
				}

				if($host->sysctl != "") {
					$host->sysctl = $this->basic->limpiarMetas($host->sysctl);

					$printConfig .= "echo \"".$host->sysctl."\" > /etc/sysctl.conf\n";
					$printConfig .= "sysctl -p\n";
				}

				//sas

				if( ! isset($features->wl_channel)) {
					$features->wl_channel = "6";
				} elseif($features->wl_channel == "0") {
					$features->wl_channel = "auto";
				}

				if( ! isset($features->wl_net_mode)) {
					$features->wl_net_mode = "ap";
				}

				if($features->wifi == "0") {
					$features->wifi = "1";
				} elseif($features->wifi == "1") {
					$features->wifi = "0";
				}

				if($host->ppp != "") {
					$printConfig .= "echo \"".$host->ppp."\" > /etc/ppp/options\n";
				}

				$printConfig .= "uci show baking | grep ITEM | awk 'BEGIN {FS=\"=\"}{print $1 \"=\"}' | xargs  -n1 uci set\n";

				//Limpiando

				$hostname_limpio = $this->basic->cleanSpecialCharacters($host->host);

				$printConfig .= 'uci set system.@system[0].hostname="'.$hostname_limpio.'"'."\n";
				$printConfig .= "uci set system.@system[0].reboot=0\n";
				$printConfig .= "uci set system.@system[0].timezone=".$this->parametro->get('TIMEZONE','GMT+4')."\n";
				$printConfig .= "uci set system.@system[0].company=".$this->parametro->get('COMPANY','BSW')."\n";
				$printConfig .= "uci set system.@rdate[0]=\n";
				$printConfig .= "uci commit system\n";
				$printConfig .= "uci set baking.pcontrol=$features->cparental\n";
				$printConfig .= "URL=upload\n";
				$printConfig .= "uci set wireless.radio0.channel=$features->wl_channel\n";
				$printConfig .= "uci set wireless.radio0.disabled=$features->wifi\n";
				$printConfig .= "uci set wireless.radio0.mode=$features->wl_net_mode\n";
				$printConfig .= "uci set wireless.@wifi-iface[0].ssid=\"$features->wl_ssid\"\n";
				$printConfig .= "uci set wireless.@wifi-iface[0].encryption=".$this->parametro->get('ENCRYPTION_WL','mixed-psk+tkip+aes')."\n";
				$printConfig .= "uci set wireless.@wifi-iface[0].key=\"$features->wl_key\"\n";
				$printConfig .= "wifi\n";
				if(isset($features->block_ip) && $features->block_ip != "") {
					$ipblocks = explode(";",$features->block_ip);
					foreach($ipblocks as $ipblock) {
						$printConfig .= "iptables -I FORWARD -p tcp --dport 80 -d $ipblock  -j DROP\n";
						$printConfig .= "iptables -I FORWARD -p tcp --dport 8080 -d $ipblock  -j DROP\n";
						$printConfig .= "iptables -I FORWARD -p tcp --dport 443 -d $ipblock  -j DROP\n";
						$printConfig .= "iptables -I FORWARD -p tcp --dport 25 -d $ipblock  -j DROP\n";
						$printConfig .= "iptables -I FORWARD -p tcp --dport 110 -d $ipblock  -j DROP\n";
						$printConfig .= "iptables -I FORWARD -p tcp --dport 465 -d $ipblock  -j DROP\n";
					}
				}

				$printConfig .= "sh setme.sh network.lan.ipaddr $host->ip_lan\n";
				$printConfig .= "sh setme.sh network.lan.netmask $host->netmask_lan\n";

				$printConfig .= 'uci set baking.id_host="'.$host->id_host.'"'."\n";
				$printConfig .= 'uci set baking.groupid="'.$host->groupid.'"'."\n";
				$printConfig .= 'uci set baking.id_plan="'.$host->id_plan.'"'."\n";

				$printConfig .= "uci set baking.plan=".$this->basic->cleanSpecialCharacters($host->plan)."\n";
				$printConfig .= "uci set baking.INT_D=$host->intD\n";
				$printConfig .= "uci set baking.INT_U=$host->intU\n";
				$printConfig .= "uci set baking.NAC_D=$host->nacD\n";
				$printConfig .= "uci set baking.NAC_U=$host->nacU\n";
				$printConfig .= "uci set baking.LOC_D=$host->locD\n";
				$printConfig .= "uci set baking.LOC_U=$host->locU\n";
				
				//Load Speedtest

				$getProfile_Q = "SELECT PCT.`count` , PCIES.`class`, PCIES.`sequenceCode`, PI.`item`, PI.`type`,
            PI.`type_result`,PV.`id_monitor`,PV.`value`, PCT.`display`
             FROM
             `bm_profiles` P
             LEFT JOIN `bm_profiles_categories`  PCIES ON P.`id_profile` = PCIES.`id_profile`
             LEFT JOIN `bm_profiles_item` PI ON PI.`id_categories`=PCIES.`id_categories`
             LEFT JOIN `bm_profiles_category` PCT ON PCIES.`id_categories`= PCT.`id_categories`
             LEFT JOIN `bm_profiles_values` PV ON (PI.`id_item`=PV.`id_item` AND PCT.`id_category`=PV.`id_category`)
             WHERE P.`id_profile` = :idProfile AND PCT.`status` = 'true'
             ORDER BY PCIES.`class`, PCT.`count`, PI.`type`";

				$getProfile_R = $this->conexion->queryExecuteFetch($getProfile_Q,array('idProfile' => $host->idProfile));

				if($getProfile_R) {
					$profileItems = array();
                    $profileCode = array();
					foreach($getProfile_R as $key => $profile) {				    
                        $profileCode[$profile['class']] = $profile['sequenceCode'];

						if($profile['type'] == 'param') {
							$profileItems[$profile['class']][$profile['count']]['param'][$profile['item']] = $profile['value'];
						} else {
							$profileItems[$profile['class']][$profile['count']]['result'][$profile['item']] = $profile['id_monitor'];
						}
						
						$profileItems[$profile['class']][$profile['count']]['code'] = $profile['display'];
						
					}
				}
                    
                //UCI Speedtest  BGS018
//if(isset($profileItems['Bandwidth'])){ OJOOOO con el nombre de las clases es sencible a mayusculas, minisculas 
                if(isset($profileItems['BANDWIDTH'])){  
	                foreach ($profileItems['BANDWIDTH'] as $key => $value) {	// OJOOOO con el nombre de las clases es sencible a mayusculas, minisculas
	                	$printConfig .= "uci set baking.speedtest_".$value['code']."_server=".$value['param']['SERVER']."\n";
						$printConfig .= "uci set baking.speedtest_".$value['code']."_download_path=".$value['param']['PATH_DOWNLOAD']."\n";
						$printConfig .= "uci set baking.speedtest_".$value['code']."_upload_path=".$value['param']['UPLOAD_PATH']."\n"; 
						$printConfig .= "uci set baking.speedtest_".$value['code']."_time_B=".$value['param']['TIME_B']."\n";
	                	$printConfig .= "uci set baking.speedtest_".$value['code']."_time_S=".$value['param']['TIME_S']."\n";
	                	$printConfig .= "uci set baking.speedtest_".$value['code']."_sessions_B=".$value['param']['SESSIONS_B']."\n";
						$printConfig .= "uci set baking.speedtest_".$value['code']."_sessions_S=".$value['param']['SESSIONS_S']."\n";
	                }
				}
                if(isset($profileItems['BANDWIDTH'])){
	                foreach ($profileItems['PING'] as $key => $value) {	
	                	$printConfig .= "uci set baking.ping_".$value['code']."_server=".$value['param']['SERVER']."\n";
						$printConfig .= "uci set baking.ping_".$value['code']."_count=".$value['param']['COUNT']."\n";
						$printConfig .= "uci set baking.ping_".$value['code']."_size=".$value['param']['SIZE']."\n"; 
						$printConfig .= "uci set baking.ping_".$value['code']."_latencia=".$value['param']['DELAY']."\n";
	                }
                }
		
		
				$getProfileSQL = "SELECT PP.`name`, IF(PPH.`value` IS NOT NULL, PPH.`value`, PP.`value`)  as 'value'
                            FROM `bm_profiles_param` PP
                                LEFT JOIN `bm_profiles_param_host` PPH ON PP.`id_param`=PPH.`id_param`
                            WHERE `id_profile` = ? AND `visible` = 'true'";
				$getProfileRESULT = $this->conexion->queryFetch($getProfileSQL,$host->idProfile);
				
				if($getProfileRESULT){
					foreach ($getProfileRESULT as $key => $value) {
						$profileParam[$value['name']] = $value['value'];
					}	
				}
			
				$printConfig .= "uci set baking.ndw1=\n";
				$printConfig .= "uci set baking.ndw2=\n";
								
				
				$printConfig .= "uci set baking.renew=".$profileParam['RENEW']."\n";
				$printConfig .= "uci set baking.groupid=$host->groupid\n";
				$printConfig .= "uci set baking.groupname=\"$host->groupname\"\n";
				$printConfig .= "uci set baking.horario=\"".$profileParam['SCHEDULE']."\"\n";
				if(isset($profileParam['HOLIDAY'])){
					$HOLIDAY = $profileParam['HOLIDAY'];
				} else {
					$HOLIDAY = '';
				}
				$printConfig .= "uci set baking.feriados=\"".$HOLIDAY."\"\n";
				$printConfig .= "sh /root/checkdelay.sh ".$profileParam['DELAY']."\n";
				$printConfig .= "uci set baking.delay=".$profileParam['DELAY']."\n";
							
				$globalTimezone = $this->parametro->get('TIMEZONE_GLOBAL',true);
				$globalTimezoneAuto = $this->parametro->get('TIMEZONE_GLOBAL_AUTO',true);

				if($globalTimezone) {
					if($globalTimezoneAuto) {
						$timeZone = $this->basic->getTimeZoneOffset("UTC");
					} else {
						$timeZone = $this->parametro->get('TIMEZONE','UTC+4');
					}
				} else {
					if(isset($host->tz) && ($host->tz !== '')) {
						$timeZone = $host->tz;
					} else {
						$timeZone = $this->parametro->get('TIMEZONE','UTC+4');
					}
				}

				$printConfig .= "uci set baking.TZ=".$timeZone."\n";
				$printConfig .= "uci set baking.retry=".$profileParam['RETRY']."\n";
				$printConfig .= "uci set baking.maxretry=".$profileParam['MAXRETRY']."\n";
				$printConfig .= "echo ".$timeZone." > /etc/TZ\n";

				$printConfig .= "uci set baking.horus_server=".$this->parametro->get("HORUS_SERVER",'horus.baking.cl').":".$this->parametro->get("HORUS_SERVER_PORT_API",80)."\n";

				$printConfig .= "uci set baking.slot=\n";
				$printConfig .= "uci commit\n";
				$printConfig .= "date -s \"".date("Y.m.d-H:i:s").'"'."\n";
				//START ITEMS

				foreach ($profileItems as $keyClass => $classes){
					
					foreach ($classes as $key => $class) {
						
						foreach ($class['result'] as $item => $itemid) {
							$itemName = $class['code'].strtolower($keyClass).$item;
							$printConfig .= "uci set baking.ITEM".$itemName."=".$itemid."\n";
						}
						
					}
					
				}
				
				$printConfig .= "uci commit\n";
				//END ITEMS
				$printConfig .= "r=\$(uci get system.@system[0].reboot)\n";
				$printConfig .= "if [ \"\$r\" == \"1\" ] ; then\n";
				$printConfig .= "  uci set system.@system[0].reboot=0\n";
				$printConfig .= "  uci commit\n";
				$printConfig .= "    cp /tmp/bsw/* /root/bsw\n";
				$printConfig .= "    cp /tmp/*.sh /root/last\n";
				$printConfig .= "  reboot\n";
				$printConfig .= "fi\n";
				$printConfig .= "# END\n";
													      
				echo $printConfig;
				exit;

			} else {
				print "uci set system.bswslot=1\n";
				print "uci commit\n";
				print "# END\n";
			}

		}//fin BGS018 OK
		
				
		public function trap( ) {

			$getParam = (object)$_GET;

			$this->logs->debug("Iniciando Envio Traps:",$getParam,"logs_trap");

			$validaMac = @$this->validMAC($getParam->mac);

			if(( ! $validaMac)) {
				header("HTTP/1.1 403 Mac Not Found");
				print "ERROR: MAC not found\n";
				exit ;
			}

			$TZOffset = 3600;
			//No se por que esta esta parte

			//system('date +"%s"'); //No se por que esta esta parte

			if($getParam->item == $getParam->dir) {
				$getParam->dir = "";
			}
			if($getParam->item == $getParam->type) {
				$getParam->type = "";
			}

			if($validaMac) {
				$query_datosHost = "SELECT H.`mac`,H.`id_host`,P.`plan`,H.`host`,H.`dns`,HG.`name`,HG.`groupid`,HF.`feature`,HD.`value`
									FROM `bm_host` H
										JOIN `bm_host_detalle` HD USING(`id_host`)
										JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature`
										JOIN `bm_host_groups` HG ON H.`groupid`=HG.`groupid`
										JOIN `bm_plan` P ON H.`id_plan`=P.`id_plan`
									WHERE
										H.`id_host`='".$validaMac->id_host."' AND
										H.`status` = 1 AND H.`borrado`=0";

				$datosHost_values = $this->conexion->queryFetch($query_datosHost,"logs_trap");
				if($datosHost_values) {
					$datosHost = new stdClass( );
					foreach($datosHost_values as $key => $value) {
						if(($value['value'] == '') || (empty($value['value']))) {
							$value_asig = NULL;
						} else {
							$value_asig = $value['value'];
						}
						if(($value['feature'] != '') || ( ! empty($value['feature']))) {
							$datosHost->$value['feature'] = $value_asig;
						}
					}

					$datosHost->hostid = $datosHost_values[0]['id_host'];
					$datosHost->macaddress = $datosHost_values[0]['mac'];
					$datosHost->plan = $datosHost_values[0]['plan'];
					$datosHost->host = $datosHost_values[0]['host'];
					$datosHost->dns = $datosHost_values[0]['dns'];
					$datosHost->groupname = $datosHost_values[0]['name'];
					$datosHost->groupid = $datosHost_values[0]['groupid'];

					$datosHost->macaddress = str_replace(":","",$datosHost->macaddress);
					$datosHost->maccm = @strtoupper(str_replace(":","",$datosHost->mac_cm));

					if($datosHost->groupname == "FDT") {
						$datosHost->groupname = "ENLACES";
					}

					$trap = $getParam->trap;

					$query_trq = "SELECT trapserver,$trap as traps
						  FROM
						  		bm_host_groups
						  WHERE
						  		groupid = ".$datosHost->groupid;

					$trq = $this->fetch($query_trq);

					$trapserver = $trq->trapserver;

					$trapoid = $trq->traps;

					if(isset($datosHost->rdb) && isset($datosHost->groupid)) {
						$query_enlaces = "SELECT `bm_colegios`.*, `bm_region`.`REGION_NOMBRE`, `bm_provincia`.`PROVINCIA_NOMBRE`, `bm_comuna`.`COMUNA_NOMBRE` FROM `bm_colegios`
								LEFT JOIN `bm_region` USING(`REGION_ID`)
								LEFT JOIN `bm_provincia` ON `bm_colegios`.`PROVINCIA_ID`=`bm_provincia`.`PROVINCIA_ID`
								LEFT JOIN `bm_comuna` ON `bm_colegios`.`COMUNA_ID`=`bm_comuna`.`COMUNA_ID`
								WHERE `rdb`=".$datosHost->rdb;
						$enlaces = $this->fetch($query_enlaces);
					} else {
						if(isset($datosHost->rdb)) {
							$datosHost->rdb = 'NONE_RDB';
						}
						$enlaces = false;
					}

					if($enlaces) {
						$datosHost->nodo = $enlaces->nodo;
						$datosHost->comuna = $enlaces->COMUNA_NOMBRE;
						$datosHost->subnodo = $enlaces->subnodo;
						$datosHost->cuadrante = $enlaces->cuadrante;
						$rut = $enlaces->rut;
					} else {
						$datosHost->nodo = "N00";
						$datosHost->comuna = "SIN COMUNA";
						$datosHost->subnodo = "S00";
						$datosHost->cuadrante = "C00";
						$rut = 'SIN_RUT';
					}

					if(@$datosHost->idservicio == "")
						$datosHost->idservicio = "0";
					if(@$datosHost->id_vivienda == "")
						$datosHost->id_vivienda = "0";
					if($datosHost->nodo == "")
						$datosHost->nodo = "N00";
					if($datosHost->subnodo == "")
						$datosHost->subnodo = "S00";
					if($datosHost->cuadrante == "")
						$datosHost->cuadrante = "C00";
					if($datosHost->comuna == "")
						$datosHost->comuna = "SIN COMUNA";
					if($datosHost->maccm == "")
						$datosHost->maccm = "SIN MACCM";

					if($datosHost->groupname == "NEUTRALIDAD" || $datosHost->groupname == "QoS") {
						//$this->logs->error("item.dir.type: $getParam->item . $getParam->dir . $getParam->type");
						switch ($getParam->item . $getParam->dir . $getParam->type) {
							case "DISP":
								$ntrap = "NEU0001";
								break;
							// No se usa aqui.
							case "VELUPLOC":
								$ntrap = "NEU0002";
								break;
							case "VELUPNAC":
								$ntrap = "NEU0003";
								break;
							case "VELUPINT":
								$ntrap = "NEU0004";
								break;
							case "VELDOWNLOC":
								$ntrap = "NEU0005";
								break;
							case "VELDOWNNAC":
								$ntrap = "NEU0006";
								break;
							case "VELDOWNINT":
								$ntrap = "NEU0007";
								break;
							case "PINGLOC":
								$ntrap = "NEU0008";
								break;
							case "PINGNAC":
								$ntrap = "NEU0009";
								break;
							case "PINGINT":
								$ntrap = "NEU0010";
								break;
							case "RENEW":
								$ntrap = "NEU0011";
								break;

							default:
								$ntrap = "NEU9999";
								break;
						}

						$getParam->item = $ntrap;
						$getParam->dir = "";
						$getParam->type = "";
					}

					$ENCABEZADO = str_replace("#",$datosHost->groupname,$this->parametro->get("BSWTRAP"));

					$TRAPS_INSERT_TABLE = $this->parametro->get('TRAPS_INSERT_TABLE',false);

					if($TRAPS_INSERT_TABLE) {//BGS020
						$TRAPS_INSERT_TABLE_HOST = $this->parametro->get('TRAPS_INSERT_TABLE_HOST','localhost');//('TRAPS_INSERT_TABLE_HOST','127.0.0.1');
						$TRAPS_INSERT_TABLE_BDNAME = $this->parametro->get('TRAPS_INSERT_TABLE_BDNAME','bswtraps');
						$TRAPS_INSERT_TABLE_USER = $this->parametro->get('TRAPS_INSERT_TABLE_USER','root');//('TRAPS_INSERT_TABLE_USER','bswtraps');
						$TRAPS_INSERT_TABLE_PASS = $this->parametro->get('TRAPS_INSERT_TABLE_PASS','bsw$$2009');//('TRAPS_INSERT_TABLE_PASS','bswtraps321');
						//$this->logs->error("TRAP_INSERT_TABLE: hOST: $TRAPS_INSERT_TABLE_HOST, DBname: $TRAPS_INSERT_TABLE_BDNAME, User: $TRAPS_INSERT_TABLE_USER, Pass: $TRAPS_INSERT_TABLE_PASS");
						$conn = $this->conexion->connectR('mysql',$TRAPS_INSERT_TABLE_HOST,$TRAPS_INSERT_TABLE_USER,$TRAPS_INSERT_TABLE_PASS,$TRAPS_INSERT_TABLE_BDNAME);

						if($conn) {
							$TRAPS_INSERT_TABLE_TABLE = $this->parametro->get('TRAPS_INSERT_TABLE_TABLE','traps');
							
							//$this->logs->error("LA DATA ES: HORA:NOW(), ENCABEZADO: $ENCABEZADO ,'WARNING',IDHOST: $datosHost->rdb , RUT$rut,$datosHost->nodo,$datosHost->cuadrante,$datosHost->comuna,$datosHost->host,$datosHost->dns,$datosHost->groupname,$datosHost->plan,$trapoid,$getParam->item.$getParam->dir.$getParam->type,1,0,MJ");//<--- SACAR DESPUES
														
							$insert_sql = "INSERT INTO `$TRAPS_INSERT_TABLE_TABLE` (`FECHA_HORA`, `ENCABEZADO`, `TRAPCRIT`, `RDB`, `RUT`, `NODO`, `CUADRANTE`, `COMUNA` , `HOST`, `DNS`, `GROUPNAME`, `PLAN`, `OID`, `TIPO`, `VALORESPERADO`, `VALOROBTENIDO`, `LOGIN`) VALUES ";

							$insert_sql .= sprintf("(NOW(),'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",$ENCABEZADO,'WARNING',$datosHost->rdb,$rut,$datosHost->nodo,$datosHost->cuadrante,$datosHost->comuna,$datosHost->host,$datosHost->dns,$datosHost->groupname,$datosHost->plan,$trapoid,$getParam->item.$getParam->dir.$getParam->type,'1','0','MJ');

							$valid = $conn->query($insert_sql);
							//$this->logs->error("------ EL TRAP FUE ALMACENADO OKKOKKK: ------");///<------ SACAR DESPUES  
						} else {
							$this->logs->error("Error al insertar traps a base remota:",NULL,"logs_trap");
						}
					}

					if(!isset($datosHost->rdb)){
						$datosHost->rdb = '0';
					}

					$mytrap = $datosHost->rdb."*".$getParam->item.$getParam->dir.$getParam->type."*WARNING_".$datosHost->host."_N".$datosHost->nodo."_C".$datosHost->cuadrante."_".$datosHost->maccm."_".$datosHost->comuna."_".$datosHost->dns."_".$datosHost->plan."*MJ";
					$sh = 'snmptrap -v2c -c '.$this->parametro->get("BSWCOMMUNITY").' '.$trapserver.' 1 '.$trapoid.' '.$trapoid.' s "'.str_replace("#",$datosHost->groupname,$this->parametro->get("BSWTRAP")).'_'.$mytrap.'"';

					$this->logs->debug("Snmp de consultas: ",$sh,"logs_trap");
					if(isset($_GET["test"]))
						echo $sh."\n";
					if($trapserver != "" && $trapoid != ""){
						$output = shell_exec($sh);
					} else {
						$output = '';
					}

					$this->logs->debug("Snmp salida:",$output,"logs_trap");

				} else {
					$this->logs->error("Error al consultar a la BD: ",NULL,"logs_trap");
				}
			} else {
				$this->logs->error("Mac invalida",$getParam,"logs_trap");
				print "ERROR: Invalid MAC\n";
				exit ;
			}
		}

		//Funcion deprecate , no es utilizada actualmente por el router
		public function upload( ) {
			$this->logs->error("HOLAA ESTOY POSTEANDO QoS");
			$getParam = (object)$_GET;

			if(empty($getParam->mac)) {
				print "ERROR: Invalid MAC\n";
				exit ;
			}

			//system('date +"%s"');

			$TZOffset = - 3600 * 2;

			$u = $getParam->u + $TZOffset;

			$q1 = "SELECT count(*) as cnt
				 FROM
				 	items,
				 	hosts,
				 	hosts_profiles
				 WHERE
				 	itemid=".$getParam->itemid." AND
				 	hosts.hostid=items.hostid AND
				 	hosts.hostid=hosts_profiles.hostid AND
				 	macaddress='".$getParam->mac."'";

			$validaMac = $this->fetch($q1);

			if($validaMac->cnt == 1) {
				$query_1 = "INSERT INTO history values(".$getParam->itemid.",".$u.",".$getParam->value.",1);";
				$query_1 .= "INSERT INTO trends values(".$getParam->itemid.",".$u.",2,".$getParam->value.",".$getParam->value.",".$getParam->value.");";
				$result_1 = $this->conexion->query($query_1);

				if($result_1) {
					$query_2 = "UPDATE items SET nextcheck=delay+".$u.",lastclock=".$u.",prevvalue=lastvalue,lastvalue=".$getParam->value." where itemid=".$getParam->itemid;
					$result_2 = $this->conexion->query($query_2);
					if($result_2) {
						print "200 OK\n";
					} else {
						print "ERROR\n";
					}
				} else {
					print "ERROR\n";
				}
			} else {
				print "ERROR: Invalid MAC\n";
			}
		}

		public function keepalive( ) {

			$param = $this->validID($_GET['mac']);

            if($param == false) {
                $param = $this->validID($_GET['host']);
                if($param == false) {
                    header("HTTP/1.1 403 Identificator Not Found");
                    print "ERROR: Identificator not found ".$_GET['mac']."\n";
                    exit ;
                }
            }

			if($param->snmp_monitor == false || $param->snmp_monitor == 'false') {
				$this->conexion->query("UPDATE `bm_host` SET `availability` = '1' WHERE `id_host` = '$param->idHost';");
			}

			$mac = $param->mac;

			$dnsReportado = $param->identificator;

			$API_USE_IP_PARAM = $this->parametro->get('API_USE_IP_PARAM',false);
			$API_USE_IP_PARAM = false;

			if($API_USE_IP_PARAM == true) {
				if(isset($_GET['ips'])) {
					$ip = $_GET['ips'];
				} elseif (isset($_GET['ip'])) {
				    $ip = explode(" ",$_GET['ip']);

                    if(count($ip) > 1){
                        $ip = $ip[0];
                    } else {
                        $ip = $_GET['ip'];
                    }

				} else {
					$ip = $this->logs->getIP();
				}
			} else {
				$ip = $this->logs->getIP();
			}

			$this->logs->debug("[KEEEPALIVE] Registro de sonda ($dnsReportado) desde la ip $ip : ",$_GET,'logs_api');

			//Valida cambios en la configuracion
			$query_valida_trigger = "SELECT min(`bm_trigger`.`fecha_modificacion`) as fmod, `bm_trigger`.`command`
						FROM `bm_trigger`
						WHERE `bm_trigger`.`id_host`='$param->idHost' AND  `fecha_consume` IS NULL AND `bm_trigger`.`command` IN ('configure','reboot','bamrescue','upgrade') HAVING fmod IS NOT NULL";

			$trigger = $this->fetch($query_valida_trigger);

			$command = false;

			if($trigger) {

				if($trigger->command == 'configure') {
					print "\n";
					print "rm /tmp/config.sh\n";
					if($trigger->fmod == "0000-00-00 00:00:00") {
						print "reboot\n";
					}
					$command = true;
					print "# END\n";
				} elseif($trigger->command == 'reboot') {
					print "\n";
					print "reboot\n";
					$command = true;
					print "# END\n";
				} elseif($trigger->command == 'bamrescue') {
					print "\n";
					print 'rm -rf /root/.ssh'."\n";
					print 'mkdir /root/.ssh/'."\n";
					print 'chmod 700 /root/.ssh/'."\n";
					print 'dropbearkey -t rsa -f /root/.ssh/rssh_key | grep ssh-rsa > /root/.ssh/rssh_key.pub'."\n";
					print "if [ -f /root/getmac.sh ]\n";
					print "then\n";
					print " MAC=`sh /root/getmac.sh` \n";
					print "else\n";
					print " MAC=`sh /root/includes/getmac.sh exec`\n";
					print "fi\n";
					//print 'curl "$(uci get baking.server | awk \'{print $1}\'
					// FS=":"):3362/api/keyUpload?trigger=false&mac=$MAC" -F
					// "file=@/root/.ssh/rssh_key.pub" -X POST'."\n";
					print 'curl "bmonitor.baking.cl:3362/api/keyUpload?trigger=false&mac=$MAC" -F "file=@/root/.ssh/rssh_key.pub" -X POST'."\n";
					print 'ps | grep rssh_key | grep -v grep | awk \'{system("kill -9 " $1)\'}'."\n";
					print 'sleep 5'."\n";
					print 'ssh -N -y -I 600 -i /root/.ssh/rssh_key -Rgf 8022:localhost:22 gateway@bmonitor.baking.cl &'."\n";
					print "# END\n";
					$updateEnlaceTrigger = $this->conexion->query("UPDATE `bm_trigger` SET `fecha_consume` = NOW() WHERE `id_host` = '$param->idHost' AND `command` = '$trigger->command';");
					$command = true;
					exit ;
				} elseif($trigger->command == 'upgrade') {
					print "\n";
					/*print 'listofps=$(ps | grep \'\.sh\' | grep -v $0 | grep -v
					 * \'grep\' | awk \'{print $1}\')'."\n";
					 print ' for i in $listofps; do'."\n";
					 print '  kill $i'."\n";
					 print ' done'."\n";*/
					print "sh /root/restore.sh\n";
					print "reboot\n";
					print "# END\n";
					$updateEnlaceTrigger = $this->conexion->query("UPDATE `bm_trigger` SET `fecha_consume` = NOW() WHERE `id_host` = '$param->idHost' AND `command` = '$trigger->command';");
					$command = true;
					exit ;
				}

				if($command) {
					//Se consume todo los trigger pendientes.
					$updateEnlaceTrigger = $this->conexion->query("UPDATE `bm_trigger` SET `fecha_consume` = NOW() WHERE `id_host` = '$param->idHost' AND `command` = '$trigger->command';");
				}
			}

			print "\n# Hi $mac\n";

			//Registra keepalive

			$this->conexion->query("INSERT INTO bm_dns (`name`,`ip`,`type`,`fechahora_update`)  VALUES ('$dnsReportado','$ip','keepalive',NOW())
  									ON DUPLICATE KEY UPDATE `ip`= '$ip' ,  `fechahora_update` = NOW() ;");

			//Modifica el campo ip de otro host en caso de que tenga la misma ip
			//$this->conexion->query("UPDATE `bm_host` SET `ip_wan`='1.0.0.0' WHERE `ip_wan` ='$ip' AND `dns` <> '$dnsReportado'");

			//Actualiza si corresponde
			$this->conexion->query("UPDATE `bm_host` SET `ip_wan`='$ip' WHERE `id_host` =".$param->idHost);

			print "#".$dnsReportado."\n";
			print "uci set baking.server1=".$this->parametro->get("BSWMASTER").":".$this->parametro->get("BSW_PORT_API",3362)."\n";
			print "uci set baking.server2=".$this->parametro->get("BSWSLAVE").":".$this->parametro->get("BSW_PORT_API",3362)."\n";

			if(isset($_GET['branch'])) {
				$branch = $_GET['branch'];

				if(is_numeric($branch) && $branch == 2) {
					$version = $this->parametro->get('VERSION_V2','08.08.2012.a');
				} else {
					$version = $this->parametro->get('VERSION','08.08.2012.a');
				}
			} else {
				$version = $this->parametro->get('VERSION','08.08.2012.a');
			}

			print "uci set system.version=$version\n";

			print "uci set baking.server=".$this->parametro->get("BSW_SERVER").":".$this->parametro->get("BSW_PORT_API",3362)."\n";

			print "uci set baking.horus_server=".$this->parametro->get("HORUS_SERVER","horus.baking.cl").":".$this->parametro->get("HORUS_SERVER_PORT_API",3362)."\n";

			if($param->status === '0') {
				$status = '1';
			} else {
				$status = '0';
			}
			print "uci set system.status=".$status."\n";

			$CHECK_VERSION_KEEPALIVE = $this->parametro->get('CHECK_VERSION_KEEPALIVE',TRUE);

			print "uci commit\n";

			if($CHECK_VERSION_KEEPALIVE) {
				print "c=\$(cat runall.sh | grep VERSION | grep $version | wc -l)\n";
				print "if [ \$c -eq 0 ] ; then\n";
				/*print '  listofps=$(ps | grep \'\.sh\' | grep -v $0 | grep -v
				 * \'grep\' | awk \'{print $1}\')'."\n";
				 print '  for i in $listofps; do'."\n";
				 print '   kill $i'."\n";
				 print '  done'."\n";*/
				print "  sh /root/restore.sh\n";
				print "	 reboot\n";
				print "fi\n";
			}

			print "# Talk to you later.\n";
			print "# END\n";
		}

		public function triggers( ) {

			$validaMac = @$this->validMAC($_GET['mac']);

			if(( ! $validaMac)) {
				header("HTTP/1.1 403 Mac Not Found");
				print "ERROR: MAC not found\n";
				exit ;
			}

			$mac = $validaMac->mac;

			$ip = $this->logs->getIP();

			//Valida cambios en la configuracion
			$query_valida_trigger = "SELECT min(`bm_trigger`.`fecha_modificacion`) as fmod, `bm_trigger`.`command`
						FROM `bm_trigger`
						WHERE `bm_trigger`.`id_host`='$validaMac->id_host'  AND  `fecha_consume` IS NULL AND `bm_trigger`.`command` IN ('ssh','ssh_reverse') HAVING fmod IS NOT NULL";

			$trigger = $this->fetch($query_valida_trigger);

			print "\n# Hi $mac\n";
			$dnsReportado = "BSW".str_replace(":","",$mac);
			print "#".$dnsReportado."\n";

			if($trigger) {

				if($trigger->command == 'ssh') {
					$KEY = $this->parametro->get("SSH-KEY");
					print 'rm -rf /root/.ssh'."\n";
					print 'mkdir /root/.ssh/'."\n";
					print 'chmod 700 /root/.ssh/'."\n";
					print 'dropbearkey -t rsa -f /root/.ssh/rssh_key | grep ssh-rsa > /root/.ssh/rssh_key.pub'."\n";
					print "if [ -f /root/getmac.sh ]\n";
					print "then\n";
					print " MAC=`sh /root/getmac.sh` \n";
					print "else\n";
					print " MAC=`sh /root/includes/getmac.sh exec`\n";
					print "fi\n";
					print 'curl "$(uci get baking.server | awk \'{print $1}\' FS=":"):3362/api/keyUpload?mac=$MAC" -F "file=@/root/.ssh/rssh_key.pub" -X POST'."\n";
				}

				if($trigger->command == 'ssh_reverse') {
					print 'ps | grep rssh_key | grep -v grep | awk \'{system("kill -9 " $1)\'}'."\n";
					print 'ssh -N -y -I 600 -i /root/.ssh/rssh_key -Rgf 8022:localhost:22 gateway@$(uci get baking.server | awk \'{print $1}\' FS=":") &'."\n";
				}

				//Borramos todo los trigger pendientes.
				$updateEnlaceTrigger = $this->conexion->query("UPDATE `bm_trigger` SET `fecha_consume` = NOW() WHERE `id_host` = '$validaMac->id_host' AND `fecha_modificacion` = '$trigger->fmod'  AND `command` = '$trigger->command' LIMIT 1;");

				$deleteEnlaceTrigger = $this->conexion->query("DELETE FROM `bm_trigger` WHERE ( `id_host`='$validaMac->id_host' AND `command`='$trigger->command') && ( `id_host` !=  '$validaMac->id_host' AND `fecha_modificacion` != '$trigger->fmod'  AND `command` != '$trigger->command')");
			}

			print "# END\n";
		}
// NO es usado, es uadao uploaddata() para envio de sonda QoS
		public function uploaddata2( ) {
			$getParam = (object)$_POST;

			$validaMac = @$this->validMAC($getParam->mac);

			if(( ! $validaMac)) {
				header("HTTP/1.1 403 Mac Not Found");
				print "ERROR: MAC not found\n";
				exit ;
			}

			$this->logs->debug("Iniciando upload data del equipo con la mac: ",$validaMac->mac,'logs_uploaddata');

			if(empty($getParam->u)) {
				$getParam->u = time();
			}

			if(isset($_FILES["data"]["tmp_name"])) {
				$file_data = fopen($_FILES["data"]["tmp_name"],"r");
				$file_data = fread($file_data,filesize($_FILES["data"]["tmp_name"]));
				$getParam->data = $file_data;
			} elseif(( ! isset($getParam->data) || ($getParam->data = ''))) {
				$this->logs->error("Datos no recibidos del equipo con la mac: ",$validaMac->mac,'logs_uploaddata');
				header("HTTP/1.1 403 Data Not Found");
				print "ERROR: Data not found\n";
				exit ;
			}

			$ip = $this->logs->getIP();

			$dnsReportado = "BSW".str_replace(":","",$validaMac->mac);

			$this->conexion->query("INSERT INTO bm_dns (`name`,`ip`,`type`,`fechahora_update`)  VALUES ('$dnsReportado','$ip','keepalive',NOW())
									ON DUPLICATE KEY UPDATE `ip`= '$ip', `type`= 'keepalive', `fechahora_update` = NOW() ;");

			//Modifica el campo ip de otro host en caso de que tenga la misma ip
			//$this->conexion->query("UPDATE `bm_host` SET `ip_wan`='1.0.0.0' WHERE `ip_wan` ='$ip' AND `dns` <> '$dnsReportado'");

			//Actualiza si corresponde
			$this->conexion->query("UPDATE `bm_host` SET `ip_wan`='$ip' WHERE `id_host` =".$validaMac->id_host);
			//$this->conexion->query("UPDATE `bm_host` SET `ip_wan`='$ip' WHERE `ip_wan` <> '$ip' AND `dns` = '$dnsReportado'");

			//$this->logs->debug("Datos recibidos:",$getParam,'logs_uploaddata');

			//Datos generados por la shell postvalue.sh en el router
			$line = explode("\n",$getParam->data);
			$line = array_values(array_diff($line,array('')));

			for($i = 0;$i < count($line);$i++) {
				list( $itemid,$value ) = explode(" ",$line[$i]);
				if($value == '-') {
					$this->logs->warning("Error , dato incorrecto, item($itemid) analizar postvalue.sh en el router $dnsReportado , valor: ",$line[$i],'logs_uploaddata');
					continue;
				} else {
					$value = "$value";
				}

				if($value < 0) {
					$valid = 0;
				} else {
					$valid = 1;
				}

				if(is_numeric($value)) {
					$table = "bm_history";
					$value = "abs(".$value.")";
				} else {
					$table = "bm_history_str";
					$value = '"'.$value.'"';
				}

				$this->conexion->InicioTransaccion();
				$insert_history = "INSERT INTO `$table`  (`id_item`, `id_host`, `clock`, `value`, `valid`) VALUES ('$itemid', '$validaMac->id_host', '$getParam->u',$value, $valid);";

				$resultInsert = $this->conexion->query($insert_history,true);

				if($resultInsert) {
					$update_query = "UPDATE `bm_item_profile` JOIN `bm_items` USING(`id_item`) SET error=NULL,  `status`='ok' , `nextcheck` = DATE_ADD(NOW(), INTERVAL `delay` SECOND) , `lastclock` =".$getParam->u.",prevvalue=lastvalue,lastvalue=".$value." where id_item=".$itemid." AND `id_host`=$validaMac->id_host";
					$result = $this->conexion->query($update_query);
					if($result) {
						$this->logs->debug("Registro numero $itemid , correspondiente al equipo  $validaMac->id_host  fue exitoso",NULL,'logs_uploaddata');
						$this->conexion->commit();
					} else {
						$this->logs->error("Error al registrar el siguiente check del equipo con la mac: ",$getParam->mac,'logs_uploaddata');
						header("HTTP/1.1 403 Param incorrect");
						print "ERROR: Param incorrect\n";
						exit ;
					}
				} else {
					$this->logs->error("Error al insertar el history  del equipo con la mac: ",$getParam->mac,'logs_uploaddata');
					header('HTTP/1.1 500 Internal Server Error');
					echo "UPS , no se que paso :(\n";
					exit ;
				}

			}
			header('HTTP/1.1 200 OK');
			echo "200 OK\n";
			exit ;
		}






























		//Ultimo
		public function setData( $identificator,$method = 'post' ) {
//$this->logs->warning("setData YYYYYY");
			if($method == 'json') {
				$postdata_sec = file_get_contents("php://input");
			} else {
				$getParam = (object)$_POST;
			}

			$status = true;

           $ACTIVESETDATA = $this->parametro->get('ACTIVESETDATA',true);

            if($ACTIVESETDATA == false){
                header('HTTP/1.1 500 Internal Server Error');
                print "Post Data Inactive\n";
                exit ;
            }

			$param = $this->validID($identificator);

			if($param == false) {
				header("HTTP/1.1 403 Identificator Not Found");
				print "ERROR: Identificator not found $identificator\n";
				exit ;
			}

			$ip = $this->logs->getIP();

			$this->conexion->query("INSERT INTO bm_dns (`name`,`ip`,`type`,`fechahora_update`)  VALUES ('$identificator','$ip','uploaddata',NOW())
                                    ON DUPLICATE KEY UPDATE `ip`= '$ip', `type`= 'uploaddata', `fechahora_update` = NOW() ;");

			$this->conexion->query("UPDATE `bm_host` SET `ip_wan`='$ip' WHERE `ip_wan` <> '$ip' AND `id_host` = '".$param->idHost."'");

			//Validando los Items:

			$insertItemBasicSQL = "INSERT IGNORE INTO `bm_items` (`id_item`, `name`, `description`, `descriptionLong`, `type_item`, `delay`, `history`, `trend`, `type_poller`, `unit`, `snmp_oid`, `snmp_community`, `snmp_port`, `display`, `tags`)
VALUES
    (1, 'Availability.bsw', 'Availability.bsw', 'A.Core - Availability', 'float', 600, 90, 365, 'snmp', ' ', '.1.3.6.1.2.1.1.5.0', 'public', 1161, 'none', NULL),
    (3, 'DurationOfTheTest.sh', 'DurationOfTheTest.sh', 'A.Core - DurationOfTheTest.sh', 'float', 600, 90, 365, 'bsw_agent', ' ', '', '', 0, 'none', NULL),
    (4, 'FinishTest', 'FinishTest', 'A.Core - FinishTest', 'float', 600, 90, 365, 'bsw_agent', ' ', '', '', 0, 'none', NULL);
    DELETE FROM `bm_items` WHERE `id_item` IN ('79','80') AND `name` IN ('WAN IP','version.bsw');";

			$insertItemBasicRESULT = $this->conexion->query($insertItemBasicSQL);
			//$this->logs->error("el metodo es: ".$method);  
			if($method == 'json') {
				//IS JSON
				$jsonParam = json_decode($postdata_sec);

				if(json_last_error() == JSON_ERROR_NONE) {

					if(empty($jsonParam->unix)) {
						$unixtime = time();
					} else {
						$unixtime = $jsonParam->unix;
					}

					foreach($jsonParam as $key => $item) {

						$value = $item->value;
						$itemid = $item->itemid;

						if(is_numeric($value)) {
							$table = "bm_history";
							$value = "abs(".$value.")";
							$delayCheck = (int)$param->delay;
							if((int)$itemid > 0){
								$value_int_insert[] = sprintf("('%s','%s','%s',%s,1)",$itemid,$param->idHost,$unixtime,$value);
								$value_int_update[] = "('$itemid', '$param->idHost', 'ok' , DATE_ADD(NOW(), INTERVAL $delayCheck SECOND), '$unixtime', $value, NULL)";
							}
						} else {
							$table = "bm_history_str";
							$value = '"'.$value.'"';
							$delayCheck = (int)$param->delay;
							if((int)$itemid > 0){
								$value_string_insert[] = sprintf("('%s','%s','%s',%s,1)",$itemid,$param->idHost,$unixtime,$value);
								$value_int_update[] = "('$itemid', '$param->idHost', 'ok' , DATE_ADD(NOW(), INTERVAL $delayCheck SECOND), '$unixtime', $value , NULL)";
							}
						}

					}

				} else {
					echo "NOK";
					exit ;
				}

			} 
			elseif($method == 'post') {

				$validParam = (object) array();

				if(isset($getParam->u)) {

					if(is_numeric($getParam->u)) {
						$validParam->unixtime = $getParam->u;
					} else {
						$unixtime = explode(',',$getParam->u);
						if(is_numeric($unixtime[0])) {
							$getParam->u = $unixtime[0];
							$validParam->unixtime = $unixtime[0];
						} else {
							$getParam->u = time();
							$validParam->unixtime = time();
							//error_log(print_r($getParam,true));
						}
					}

				} else {
					$getParam->u = time();
					$validParam->unixtime = time();
				}

				if(isset($_FILES["data"]["tmp_name"])) {
					$file_data = fopen($_FILES["data"]["tmp_name"],"r");
					$validParam->items = fread($file_data,filesize($_FILES["data"]["tmp_name"]));
				} elseif(isset($getParam->data) && ($getParam->data != '') && (strlen(trim($getParam->data)) > 0)) {
					$validParam->items = $getParam->data;
				} else {
					$this->logs->error("Datos no recibidos del equipo con el ID: ",$identificator,'logs_uploaddata');
					header("HTTP/1.1 422 Data Not Found");
					print "ERROR: Data not found\n";
					exit ;
				}

				if( ! isset($param->identificator) || empty($param->identificator)) {
					//error_log(print_r($param,true));
				}

				$fp = fopen(SITE_PATH.'tmp/'.$param->identificator.'.config',"w+");
				if($fp) {
					fwrite($fp,$validParam->items.PHP_EOL);
					fclose($fp);
				}

				$getParam->data = trim($getParam->data);

				$lineas = explode("\n",$getParam->data);
				$lineas = array_values(array_diff($lineas,array('')));

				foreach($lineas as $key => $linea) {

					$array = explode(" ",$linea);

					if(count($array) == 3) {
						list( $itemid,$value,$validS ) = $array;
					} else {
						list( $itemid,$value ) = $array;
					}

					if( ! is_numeric($itemid)) {
						continue;
					} else {
						$itemidO = $itemid;
						$itemid = (int)trim($itemid);
					}

					if( $itemid == '-1') {
						continue;
					}

					$value = trim($value);

					if($value == '-') {
						$this->logs->warning("Error , dato incorrecto, item($itemid) analizar postvalue.sh en el router $dnsReportado , valor: ",$value,'logs_uploaddata');
						continue;
					} else {
						$value = "$value";
					}

					if($value <= 0) {
						$valid = 0;
					} elseif(isset($validS) && ((int)$validS === 0 OR (int)$validS === 1)) {
						$valid = (int)$validS;
					} else {
						$valid = 1;
					}
//$this->logs->warning("YYYYYYYYY value=" . $value . "   valid=" . $valid."  Host:".$param->idHost."   itemYYYY:".$itemid);

					$iditem[] = $itemid;
					$valueItem[] = $value;

					if(is_numeric($value)) {

						$value = "abs(".$value.")";
						$delayCheck = (int)$param->delay;

						$value_int_insert[] = sprintf("('%s','%s','%s',%s,%s)",$itemid,$param->idHost,$validParam->unixtime,$value,$valid);
	//					$this->logs->warning(sprintf("('%s','%s','%s',%s,%s)",$itemid,$param->idHost,$validParam->unixtime,$value,$valid));
						$value_int_update[] = "('$itemid', '$param->idHost', 'ok' , DATE_ADD(NOW(), INTERVAL $delayCheck SECOND), '$validParam->unixtime', $value, NULL)";

					} else {
						$value = '"'.$value.'"';
						$delayCheck = (int)$param->delay;

						$value_string_insert[] = sprintf("('%s','%s','%s',%s,%s)",$itemid,$param->idHost,$validParam->unixtime,$value,$valid);
						$value_int_update[] = "('$itemid', '$param->idHost', 'ok' , DATE_ADD(NOW(), INTERVAL $delayCheck SECOND), '$validParam->unixtime', $value , NULL)";

					}

				}

			} else {
				header("HTTP/1.1 422 Invalid method");
				print "ERROR: Invalid method $method\n";
				exit ;
			}

			//Cargando Monitor
			$value_int_update[] = "(4, '$param->idHost', 'ok' , DATE_ADD(NOW(), INTERVAL 600 SECOND), UNIX_TIMESTAMP(), 1 , NULL)";

			//Registrando int Value

			if(!isset($value_int_insert) && !isset($value_string_insert)){
				$this->logs->warning("Sin datos, ID HOST :  ".$param->idHost."  , IDENTIFICATION: ",$identificator,'logs_uploaddata');
				header('HTTP/1.1 200 OK');
				echo "200 OK\n";
				exit ;
			}

			$getDuplicateSQL = 'SELECT count(*) as "Total" FROM `bm_item_profile` WHERE `id_item` IN ("'.join('","',$iditem).'") AND `lastvalue` IN ("'.join('","',$valueItem).'") AND  `lastclock` = '.$validParam->unixtime.';';
			$getDuplicateRESULT = $this->conexion->queryFetch($getDuplicateSQL);

			if($getDuplicateRESULT) {
				if((int)$getDuplicateRESULT[0]['Total'] == count($iditem)) {
					$this->logs->error("Duplicate data, ID HOST :  ".$param->idHost."  , IDENTIFICATION: ",$identificator,'logs_uploaddata');
					//$this->logs->error("consulta Duplicate data (api.php, setdata): $getDuplicateSQL, IDENTIFICATION: ",$identificator,'logs_uploaddata');
					header('HTTP/1.1 200 OK');
					echo "200 OK\n";
					exit ;
				}
			}

			$insertHistoryInt = "INSERT INTO `bm_history`  (`id_item`, `id_host`, `clock`, `value`, `valid`) VALUES ";

//$this->logs->warning("insertHistoryInt:" . $insertHistoryInt);
//$this->logs->warning("value_string_insert valores q inserto:" . $value_string_insert);

			$resultInsertINT = $this->conexion->queryRetry($insertHistoryInt.join(',',$value_int_insert));

			if(isset($value_string_insert)) {
				$insertHistoryString = "INSERT INTO `bm_history_str`  (`id_item`, `id_host`, `clock`, `value`, `valid`) VALUES ";

				$resultInsertString = $this->conexion->query($insertHistoryString.join(',',$value_string_insert));
			} else {
				$resultInsertString = true;
			}

			if(($resultInsertINT !== false) && ($resultInsertString !== false)) {

                $getIPSaveSQL = "INSERT INTO `bm_history_ip` ( `id_host`, `id_item`, `clock`, `ip`)
                                    SELECT '$param->idHost' as 'id_host',`id_item`, '$validParam->unixtime' as 'clock' , '$ip' as 'ip'
                                                FROM `bm_items` WHERE `id_item` IN (".join(',',$iditem).") AND `saveIP` = 'true';";

                $getIPSaveRESULT = $this->conexion->query($getIPSaveSQL);


				$insert_profile = "INSERT INTO `bm_item_profile` (`id_item`,`id_host`,`status`,`nextcheck`, `lastclock`, `lastvalue`, `prevvalue`) VALUES";

				$insert_profile_duplicate = " ON DUPLICATE KEY UPDATE `status`=VALUES(`status`),`nextcheck`=VALUES(`nextcheck`),`lastclock`=VALUES(`lastclock`),`prevvalue`=`lastvalue`,`lastvalue`=VALUES(`lastvalue`);";

				$result = $this->conexion->query($insert_profile.join(',',$value_int_update).$insert_profile_duplicate);

				if($result) {
					$this->logs->debug("Registro del profile, correspondiente al equipo ".$param->idHost." fue exitoso ID:",$identificator,'logs_uploaddata');
				} else {
					$this->logs->error("Error al registrar el siguiente check del equipo con el ID :  ".$param->idHost."  , con la identificacion: ",$identificator,'logs_uploaddata');
				}

			} else {
				$this->logs->error("Error al insertar el history  del equipo con la ID: ",$param->idHost,'logs_uploaddata');
				header('HTTP/1.1 500 Internal Server Error');
				echo "UPS , no se que paso :(\n";
				exit ;
			}

			header('HTTP/1.1 200 OK');
			echo "200 OK\n";
			exit ;
		}

























		public function uploaddata( ) {
  			 $this->logs->warning("ZZZZZZZZZ public function UPLOADDATA");
			$getParam = (object)$_POST;

			$validaMac = @$this->validMAC($getParam->mac);

			if(( ! $validaMac)) {
				header("HTTP/1.1 403 Mac Not Found");
				print "ERROR: MAC not found\n";
				exit ;
			}

			//Validando los Items:

			$insertItemBasicSQL = "INSERT IGNORE INTO `bm_items` (`id_item`, `name`, `description`, `descriptionLong`, `type_item`, `delay`, `history`, `trend`, `type_poller`, `unit`, `snmp_oid`, `snmp_community`, `snmp_port`, `display`, `tags`)
					VALUES
					    (1, 'Availability.bsw', 'Availability.bsw', 'A.Core - Availability', 'float', 600, 90, 365, 'snmp', ' ', '.1.3.6.1.2.1.1.5.0', 'public', 1161, 'none', NULL),
					    (3, 'DurationOfTheTest.sh', 'DurationOfTheTest.sh', 'A.Core - DurationOfTheTest.sh', 'float', 600, 90, 365, 'bsw_agent', ' ', '', '', 0, 'none', NULL),
					    (4, 'FinishTest', 'FinishTest', 'A.Core - FinishTest', 'float', 600, 90, 365, 'bsw_agent', ' ', '', '', 0, 'none', NULL);
					    DELETE FROM `bm_items` WHERE `id_item` IN ('79','80') AND `name` IN ('WAN IP','version.bsw');";

			$dnsReportado = $validaMac->dns;

			$API_USE_IP_PARAM = $this->parametro->get('API_USE_IP_PARAM',false);
			$API_USE_IP_PARAM = false;

			if($API_USE_IP_PARAM) {
				if(isset($getParam->ips)) {
					$ip = $getParam->ips;
				} else {
					$ip = $this->logs->getIP();
				}
			} else {
				$ip = $this->logs->getIP();
			}

			$this->logs->debug("Iniciando upload data del equipo $dnsReportado y la ip  $ip : ",$validaMac,'logs_uploaddata');

			if(empty($getParam->u)) {
				$getParam->u = time();
			}

			if(isset($_FILES["data"]["tmp_name"])) {
				$file_data = fopen($_FILES["data"]["tmp_name"],"r");
				$file_data = fread($file_data,filesize($_FILES["data"]["tmp_name"]));
				$getParam->data = $file_data;
			}

			if(( ! isset($getParam->data) || ($getParam->data === ''))) {
				$this->logs->error("Datos no recibidos del equipo con la mac: ",$validaMac->mac,'logs_uploaddata');
				header("HTTP/1.1 403 Data Not Found");
				print "ERROR: Data not found\n";
				exit ;
			}

			//Test
			$getParam->data = trim($getParam->data);

			if(strlen($getParam->data) === 0) {
				$this->logs->error("Datos no recibidos del equipo con la mac: ",$validaMac->mac,'logs_uploaddata');
				header("HTTP/1.1 403 Data Not Found");
				print "ERROR: Data not found\n";
				exit ;
			}

			$fp = fopen('tmp/'.$dnsReportado.'.txt',"w+");
			if($fp) {
				fwrite($fp,$getParam->data.PHP_EOL);
				fclose($fp);
			}

			$this->conexion->query("INSERT INTO bm_dns (`name`,`ip`,`type`,`fechahora_update`)  VALUES ('$dnsReportado','$ip','keepalive',NOW())
									ON DUPLICATE KEY UPDATE `ip`= '$ip', `type`= 'keepalive', `fechahora_update` = NOW() ;");

			///Insert

			$value_int_update[] = "(4, '$validaMac->id_host', 'ok' , DATE_ADD(NOW(), INTERVAL 600 SECOND), UNIX_TIMESTAMP(), 1 , NULL)";

			$insert_profile = "INSERT INTO `bm_item_profile` (`id_item`,`id_host`,`status`,`nextcheck`, `lastclock`, `lastvalue`, `prevvalue`) VALUES";

			$insert_profile_duplicate = " ON DUPLICATE KEY UPDATE `status`=VALUES(`status`),`nextcheck`=VALUES(`nextcheck`),`lastclock`=VALUES(`lastclock`),`prevvalue`=`lastvalue`,`lastvalue`=VALUES(`lastvalue`);";

			$result = $this->conexion->query($insert_profile.join(',',$value_int_update).$insert_profile_duplicate);

			//Modifica el campo ip de otro host en caso de que tenga la misma ip
			//$this->conexion->query("UPDATE `bm_host` SET `ip_wan`='1.0.0.0' WHERE `ip_wan` ='$ip' AND `dns` <> '$dnsReportado'");

			//Actualiza si corresponde
			$this->conexion->query("UPDATE `bm_host` SET `ip_wan`='$ip' WHERE `ip_wan` <> '$ip' AND `dns` = '$dnsReportado'");

			//$this->logs->debug("Datos recibidos:",$getParam,'logs_uploaddata');

			//Datos generados por la shell postvalue.sh en el router

			$lineas = explode("\n",$getParam->data);
			$lineas = array_values(array_diff($lineas,array('')));
			foreach($lineas as $key => $linea) {
				$array = explode(" ",trim($linea));
						
				if(count($array) == 3) {
					list( $itemid,$value,$validS ) = $array;
					$this->logs->error("Q HAYYYYYY:  Y%$validS%Y ");
				} else {
					list( $itemid,$value ) = $array;
					$this->logs->error("solo 2 parametros ");
				}

				if( ! is_numeric($itemid)) {
					continue;
				}

				$value = trim($value);

				if($value == '-') {
					$this->logs->warning("Error , dato incorrecto, item($itemid) analizar postvalue.sh en el router $dnsReportado , valor: ",$value,'logs_uploaddata');
					continue;
				} else {
					$value = "$value";
				}


				if($value <= 0) {
					$valid = 0;
				} elseif(isset($validS) && ((int)$validS === 0 OR (int)$validS === 1)) {
					$valid = (int)$validS;
				} else {
					$valid = 1;
				}

				if(is_numeric($value)) {
					$table = "bm_history";
					$value = "abs(".$value.")";
					$delayCheck = (int)$validaMac->delay;
					$value_int_insert[] = "('$itemid', '$validaMac->id_host', '$getParam->u',$value, $valid)";
$this->logs->warning("ZZZZZZZ insert_history:" . "('$itemid', '$validaMac->id_host', '$getParam->u',$value, $valid)" );   
					$value_int_update[] = "('$itemid', '$validaMac->id_host', 'ok' , DATE_ADD(NOW(), INTERVAL $delayCheck SECOND), '$getParam->u', $value, NULL)";
				} else {
					$table = "bm_history_str";
					$value = '"'.$value.'"';
					$delayCheck = (int)$validaMac->delay;
					$value_string_insert[] = "('$itemid', '$validaMac->id_host', '$getParam->u',$value, $valid)";
					$value_int_update[] = "('$itemid', '$validaMac->id_host', 'ok' , DATE_ADD(NOW(), INTERVAL $delayCheck SECOND), '$getParam->u', $value , NULL)";
				}
			}

			$value_int_update[] = "(4, '$validaMac->id_host', 'ok' , DATE_ADD(NOW(), INTERVAL $delayCheck SECOND), '$getParam->u', 1 , NULL)";

			//Registrando int Value

			$insert_history = "INSERT IGNORE INTO `bm_history`  (`id_item`, `id_host`, `clock`, `value`, `valid`) VALUES ";

			$resultInsert = $this->conexion->query($insert_history.join(',',$value_int_insert),true);

			if(isset($value_string_insert)) {
				$insert_history = "INSERT IGNORE INTO `bm_history_str`  (`id_item`, `id_host`, `clock`, `value`, `valid`) VALUES ";

				$resultInsert = $this->conexion->query($insert_history.join(',',$value_string_insert),true);
			}

			if($resultInsert) {

				$insert_profile = "INSERT INTO `bm_item_profile` (`id_item`,`id_host`,`status`,`nextcheck`, `lastclock`, `lastvalue`, `prevvalue`) VALUES";

				$insert_profile_duplicate = " ON DUPLICATE KEY UPDATE `status`=VALUES(`status`),`nextcheck`=VALUES(`nextcheck`),`lastclock`=VALUES(`lastclock`),`prevvalue`=`lastvalue`,`lastvalue`=VALUES(`lastvalue`);";

				//$update_query="UPDATE `bm_item_profile` JOIN `bm_items`
				// USING(`id_item`) SET error=NULL,  `status`='ok' , `nextcheck` =
				// DATE_ADD(NOW(), INTERVAL `delay` SECOND) , `lastclock` =" .
				// $getParam->u . ",prevvalue=lastvalue,lastvalue=" . $value[1] . "
				// where id_item=". $value[0] ." AND `id_host`=$validaMac->id_host";

				$result = $this->conexion->query($insert_profile.join(',',$value_int_update).$insert_profile_duplicate);

				if($result) {
					$this->logs->debug("Registro numero $itemid , correspondiente al equipo  $validaMac->id_host  fue exitoso MAC:",$getParam->mac,'logs_uploaddata');
				} else {
					$this->logs->error("Error al registrar el siguiente check del equipo con la mac: ",$getParam->mac,'logs_uploaddata');
				}

			} else {
				$this->logs->error("Error al insertar el history  del equipo con la mac: ",$getParam->mac,'logs_uploaddata');
				header('HTTP/1.1 500 Internal Server Error');
				echo "UPS , no se que paso :(\n";
				exit ;
			}

			header('HTTP/1.1 200 OK');
			echo "200 OK\n";
		}


		public function uploadFile( ) {
			$getParam = (object)$_POST;

			$validaMac = @$this->validMAC($getParam->mac);

			if(( ! $validaMac)) {
				header("HTTP/1.1 403 Mac Not Found");
				print "ERROR: MAC not found\n";
				exit ;
			}

			$log = array();
			$respuesta = array();
			$status = false;

			$tamano = $_FILES["file"]['size'];
			$tipo = $_FILES["file"]['type'];
			$archivo = $_FILES["file"]['name'];
			$prefijo = substr(md5(uniqid(rand())),0,6);

			if($archivo != "") {
				$destino = "upload/".$prefijo."_".$validaMac->id_host."_".ereg_replace("([     ]+)","",trim($archivo));
				if(copy($_FILES['file']['tmp_name'],$destino)) {
					$msg = "$archivo";
					$status = true;
					$respuesta['archivo'] = $prefijo."_".$archivo;
				} else {
					$msg = "Error al subir el archivo: $archivo";
					$status = false;
				}

			} else {
				$msg = "Error al subir archivo2";
				$status = false;

			}

			if($status) {
				header('HTTP/1.1 200 OK');
				echo "200 OK\n";
			} else {
				header("HTTP/1.1 403 $msg");
				print $msg."\n";
			}

		}//fin upload

		public function backup( ) {
			$name = $_FILES["file"]["name"];
			$type = $_FILES["file"]["type"];
			$size = $_FILES["file"]["size"];
			$tmp_name = $_FILES["file"]["tmp_name"];
			move_uploaded_file($_FILES["file"]["tmp_name"],"upload/".$_FILES["file"]["name"]);
		}

		public function keyUpload( ) {
			/*
			 $validaMac = @$this -> validMAC($_GET['mac']);

			 if ((!$validaMac)) {
			 header("HTTP/1.1 403 Mac Not Found");
			 print "ERROR: MAC not found\n";
			 exit ;
			 }*/

			$name = $_FILES["file"]["name"];
			$type = $_FILES["file"]["type"];
			$size = $_FILES["file"]["size"];
			$tmp_name = $_FILES["file"]["tmp_name"];
			$valid = move_uploaded_file($_FILES["file"]["tmp_name"],"key/".$_FILES["file"]["name"]);
			exit ;
			if($valid) {

				$mac = $validaMac->mac;

				if(isset($_GET['trigger'])) {
					if($_GET['trigger'] == 'true') {
						$insert_ssh = "INSERT INTO `bm_trigger` (`id_host`, `fecha_modificacion`, `type`, `command`) VALUES ('$validaMac->id_host', NOW(), 'insert', 'ssh_reverse');";
						sleep(10);
						$inserta_tunel = $this->conexion->query($insert_ssh);
					}
				} else {
					$insert_ssh = "INSERT INTO `bm_trigger` (`id_host`, `fecha_modificacion`, `type`, `command`) VALUES ('$validaMac->id_host', NOW(), 'insert', 'ssh_reverse');";
					sleep(10);
					$inserta_tunel = $this->conexion->query($insert_ssh);
				}
			}
		}

		public function backupSec( ) {
			$name_file = $_FILES["file1"]["name"];
			$type_file = $_FILES["file1"]["type"];
			$size_file = $_FILES["file1"]["size"];
			$tmp_name_file = $_FILES["file1"]["tmp_name"];

			$name_md5 = $_FILES["file2"]["name"];
			$type_md5 = $_FILES["file2"]["type"];
			$size_md5 = $_FILES["file2"]["size"];
			$tmp_name_md5 = $_FILES["file2"]["tmp_name"];

			$file_md5_open = fopen($_FILES["file2"]["tmp_name"],"r");
			$file_md5_valid = fread($file_md5_open,filesize($_FILES["file2"]["tmp_name"]));
			$file_md5_valid = explode(' ',$file_md5_valid);
			$file_md5_valid = $file_md5_valid[0];
			fclose($file_md5_open);

			if(move_uploaded_file($_FILES["file1"]["tmp_name"],"upload/openbsw2.tar")) {

				$md5_file = md5_file("upload/openbsw2.tar");

				if($file_md5_valid == $md5_file) {

					if(move_uploaded_file($_FILES["file2"]["tmp_name"],"upload/openbsw2.md5")) {
						echo "200 OK"."\n";
					} else {
						echo '500 Internal Error E10';
					}

				} else {
					unlink("upload/openbsw2.tar");
					echo "403 ERROR INVALID FILE"."\n";
				}

			} else {
				echo '500 Internal Error E11 ups :( ';
			}
		}

		public function getLogs() {
			$param = (object)$_POST;

			if(isset($param->prueba)){
				$prueba = $this->conexion->quote($param->prueba);
			} else {
				$prueba = NULL;
			}

			if(isset($param->resultado)){
				$resultado = $this->conexion->quote($param->resultado);
			} else {
				$resultado = NULL;
			}

			if(isset($param->excepcion)){
				$excepcion = $this->conexion->quote($param->excepcion);
			} else {
				$excepcion = NULL;
			}

			if(isset($param->detalle)){
				$detalle = $this->conexion->quote($param->detalle);
			} else {
				$detalle = NULL;
			}

			if(isset($param->fecha)){
				$fecha = $this->conexion->quote($param->fecha);
			} else {
				$fecha = "NOW()";
			}

			$insertLogsSQL = "INSERT INTO `bm_logs` (`id_host`, `prueba`, `resultado`, `excepcion`, `detalle`, `fechahora`, `geo1`, `geo2`)
					VALUES
						( 1, $prueba, $resultado, $excepcion, $detalle, $fecha, $param->geo1, $param->geo2)";

			$insertLogsRESULT = $this->conexion->query($insertLogsSQL);

			if($insertLogsSQL){
				header('HTTP/1.1 200 OK');
				echo "200 OK\n";
			} else{
				header('HTTP/1.1 500 Internal Server Error');
				echo "UPS , no se que paso :(\n";
			}
			error_log(print_r($param,true));
			exit;
		}

		function getMinuteHour(){
			$minuteNow = date("i");
			$lasMinuteHour = 60 - $minuteNow;
			echo  $lasMinuteHour;
			exit;
		}
		
		function traceroute($server=false, $hops=10){
			header("Content-Type: text/plain");
			$init_time = time();
			if($server == false){
				$server = $_SERVER["REMOTE_ADDR"];
			}
			
			$resultado_inicial = shell_exec("traceroute -m $hops -w 1 $server");
			$resultado = explode("\n", $resultado_inicial);
			$resultado = array_diff($resultado, array(""));
			
			$nulos = 0;
			$loops = 0;
			$max = 0;
			$min = 0;
			$avg = 0;
			$sum = 0;
			$desviacion = 0;
			$varianza = 0;
			
			$arreglo_loop = array();
			$promedios = array();
			$ip_unique = array();
			
			for($i = 1; $i < count($resultado); $i++){
				/*	
				$fila = explode(" ", $resultado[$i]);
				$fila = array_diff($fila, array(""));
				$fila = array_values($fila);
				
				$j = 0;
				
				if($fila[1] == "*"){
					$nulos++;
				}
				
				if($fila[2] != "*"){
					$arreglo_loop[] = $fila[2];
					$promedio_fila = round(($fila[3]+$fila[5]+$fila[7]) / 3, 3);
					$promedios[] = $promedio_fila;
				}
				*/
				
				preg_match_all("/\*/", $resultado[$i], $encontrados);
				if(count($encontrados[0]) >= 3){
					$nulos++;
				}else{
					preg_match("/\([0-9]{2,3}\.[0-9]{2,3}\.[0-9]{2,3}\.[0-9]{2,3}\)/", $resultado[$i], $ip);
					if(count($ip) > 0){
						$arreglo_loop[] = $ip[0];
					}
					
					preg_match_all("/[0-9]+([,\.][0-9]*)?\sms/", $resultado[$i], $tiempos);
					if(count($tiempos[0]) > 0){
						$arreglo_promedio_fila = array();
						
						foreach ($tiempos[0] as $key => $value) {
							preg_match("/[0-9]+([,\.][0-9]*)?/", $value, $numero);
							$arreglo_promedio_fila[] = $numero[0];
						}
						
						$suma_tiempos = array_sum($arreglo_promedio_fila);
						$prom_tiempos = $suma_tiempos / count($arreglo_promedio_fila);
						$prom_tiempos = round($prom_tiempos, 0);
						$promedios[] = $prom_tiempos;
					}
				}
			}
			
			if(count($arreglo_loop) > 1){
				$ip_unique = $arreglo_loop_unique = array_unique($arreglo_loop);
				$arreglo_loop_resumen = array_count_values($arreglo_loop);
			
				foreach($arreglo_loop_unique as $unico){
					if($arreglo_loop_resumen[$unico] > 1){
						$loops++;
					}
				}	
			}
			
			if(count($promedios) > 0){
				$max = max($promedios);
				$min = min($promedios);
				$avg = array_sum($promedios) / count($promedios);
				$sum = array_sum($promedios);
				
				foreach($promedios as $prom){
					$rango = pow($prom - $avg, 2);
					$varianza += $rango;
				}
				
				$varianza = round($varianza / count($promedios), 0);
				$desviacion = round(sqrt($varianza), 0);
			}
			
			$finish_time = time();
			$time = $finish_time - $init_time;
			 
			echo "num_hops=".(count($resultado) - 1).";\n";
			echo "num_nulls=".$nulos.";\n";
			echo "num_loops=".$loops.";\n";
			echo "num_ip_diff=".count($ip_unique).";\n";
			echo "time_max=$max;\n";
			echo "time_min=$min;\n";
			echo "avg_time=$avg;\n";
			echo "variance=$varianza;\n";
			echo "std_dvt=$desviacion;\n";
			echo "time_execution=".intval(date("s", $time)).";\n";
			echo "text=$resultado_inicial;\n";	
			exit;
		}

        /*
		 * funcion biGraph
		 * 
		 * permite visualizar gráfico de sondas por plan proveniente de BI
		 */
		public function biGraph($item,$plan=false,$sonda=false,$from=false){
			$this->plantilla->load("api/grafico");
			
			if($from == false){
				$from = "Last 36 Hours";
			}
			
			if(!isset($item)){
				echo "falta primer parametro: item";
				exit;
			}
			
			
			if(!$this->loadDataBI($item, $plan, $sonda, $from)){
				echo "item no existe";
				exit;
			}
		 
			$structgraph = $this->loadDataBI($item, $plan, $sonda, $from);
			$var["title"] = "Sondas for '".$structgraph["item"]."'";
			$var["unit"] = $structgraph["unit_item"];
			
			if($structgraph["plan"] != ""){
				$var["title"] .= " on plan '".$structgraph["plan"]."'";
			}
			
			$var["subtitle"] = $structgraph["range_date"];
			
			$dates = array();
			
			$begin = strtotime(($structgraph["init_date"]));
			$end = strtotime(($structgraph["end_date"]));
			
			$i = 1;
			do {
				$dates[] = date("Y/m/d", $begin);
   				//$newTime = strtotime('+'.$i++.' days',$startTime);
   				$begin = strtotime('+'.$i++.' days',$begin);
				$this->logs->info("API BIGRAPH $begin");
			} while ($begin <= $end);
			
			$this->logs->info("num fechas ".count($dates));
			
			$datos = array();
			
			if(count($structgraph["hosts"]) > 0){
				foreach ($structgraph["hosts"] as $key => $value) {
					$fila = array("name" => $key, "data" => $value);
					$datos[] = $fila;
				}
			}
			
			$var["data"] = json_encode($datos, JSON_NUMERIC_CHECK);
			$var["category"] = "";
			
			$var["url_item"] = $item;
			
			if($plan == false){
				$plan = "false";
			}
			
			if($sonda == false){
				$sonda = "false";
			}
			
			$var["url_plan"] = $plan;
			$var["url_sonda"] = $sonda;
			$var["url_from"] = $from;
			
			$this->plantilla->set($var);
			$this->plantilla->finalize();
		}		
				
		/*
		 * funcion getDates
		 * 
		 * Proveniente de BI
		 */
		 
		private function getDates($from){
			    $dt = time();
				//echo "from " . $from;
			    switch ($from) {
			    	case 'Last 7 Days' :
			        	$dt = strtotime('now', $dt);
			        	$to = gmdate("Y/m/d", $dt);
			        	$from = gmdate("Y/m/d", strtotime("-7 days", $dt));
			        	break;
					case 'Last 36 Hours':
						$dt = strtotime('now', $dt);
						$to = gmdate("Y/m/d", $dt);
						$from = gmdate("Y/m/d", strtotime("-36 hours", $dt));
						break;
			    }
				
			    return array($from, $to);
			}

			private function loadDataBI($item, $plan, $sonda, $from){
				if($this->getItem($item)){
					$aData = array();
					$aDataHost = array();
					$dt = $this->getDates($from);
					$var_from = $dt[0];
					$var_to = $dt[1];
				
					$aData["item"] = $this->getItem($item);	
					//$aData["init_date"] = str_replace("/", "-", $var_from);
					//$aData["end_date"] = str_replace("/", "-", $var_to);
					$tmpBeginDate = explode("/", $var_from);
					$ut_start = date("Y-m-d", mktime(0, 0, 0, $tmpBeginDate[1], $tmpBeginDate[2], $tmpBeginDate[0]));
					$aData["init_date"] = $ut_start;
					$tmpEndDate = explode("/", $var_to);
					$ut_end = date("Y-m-d", mktime(0, 0, 0, $tmpEndDate[1], $tmpEndDate[2], $tmpEndDate[0]));
					$aData["end_date"] = $ut_end;
					$aData["range_date"] = "From $var_from to $var_to";
					$aData["unit_item"] = $this->getUnitItem($item);
					$aData["plan"] = "";
					$aData["hosts"] = array();
				
					$str_plan = "";
				
					if($plan){
						if(is_numeric($plan)){
							$str_plan = " AND BP.`id_plan` = $plan ";	
						}else{
							$plan = strtolower($plan);
							$str_plan = " AND LOWER(BP.`plan`) = '$plan'";
						}
						
						$aData["plan"] = $this->getPlan($plan);
					}
				
					$str_sonda = "";
				
					if($sonda){
						if(is_numeric($sonda)){
							$str_sonda = "AND BH.`id_host` = $sonda";	
						}else{
							$sonda = strtolower($sonda);
							$str_sonda = "AND LOWER(BH.`host`) = '$sonda'";
						}
					}
				
					if(is_numeric($item)){
						$table='xyz_'.str_pad($item, 10, "0", STR_PAD_LEFT);	
					}else{
						$sql = "SELECT `id_item` FROM `bm_items` WHERE LOWER(`descriptionLong`) = '$item' LIMIT 1";
						$valor = $this->conexion->queryFetch($sql);
						$table = $valor[0]["id_item"];
						$table = 'xyz_'.str_pad($table, 10, "0", STR_PAD_LEFT);
					}
				
					$existTable = $this->conexion->queryFetch("SHOW TABLES LIKE '$table'");
				
					if($existTable){
						$sql = "SELECT FROM_UNIXTIME(TBITEM.`clock`) AS 'clock', TBITEM.`value`, BH.`id_host`, BH.`host` FROM `$table` AS TBITEM JOIN `bm_host` AS BH ON TBITEM.`id_host` = BH.`id_host` JOIN `bm_plan` AS BP ON BH.`id_plan` = BP.`id_plan` WHERE `clock` BETWEEN UNIX_TIMESTAMP('$var_from 00:00:00') AND UNIX_TIMESTAMP('$var_to 23:59:59') $str_plan $str_sonda";
						$dataItem = $this->conexion->queryFetch($sql);
					
						if($dataItem){
							foreach($dataItem as $key => $value){
								$aDataHost[$value["host"]][] = array($value["clock"],$value["value"]);
							}
						
							$aData["hosts"] = $aDataHost;
						}
					
						return $aData;
					}else{
						return "no existe tabla";
					}
				}else{
					return false;
				}
			}

			private function getItem($item){
				if(is_numeric($item)){
					$sql = "SELECT `descriptionLong` FROM `bm_items` WHERE `id_item` = $item LIMIT 1";
					$valor = $this->conexion->queryFetch($sql);
					return $valor[0]["descriptionLong"];	
				}else{
					$item = strtolower($item);
					$sql = "SELECT `descriptionLong` FROM `bm_items` WHERE LOWER(`descriptionLong`) = '$item' LIMIT 1";
					$valor = $this->conexion->queryFetch($sql);
					
					if($valor){
						return $valor[0]["descriptionLong"];
					}else{
						return false;
					}
				}
			}
			
			private function getPlan($plan){
				if(is_numeric($plan)){
					$sql = "SELECT `plan` FROM `bm_plan` WHERE `id_plan` = $plan LIMIT 1";
					$valor = $this->conexion->queryFetch($sql);
					return $valor[0]["plan"];
				}else{
					$plan = strtolower($plan);
					$sql = "SELECT `plan` FROM `bm_plan` WHERE LOWER(`plan`) = '$plan' LIMIT 1";
					$valor = $this->conexion->queryFetch($sql);
					return $valor[0]["plan"];
				}
			}
			
			private function getUnitItem($item){
				if(is_numeric($item)){
					$sql = "SELECT `unit` FROM `bm_items` WHERE `id_item` = $item LIMIT 1";
					$valor = $this->conexion->queryFetch($sql);
					return $valor[0]["unit"];
				}else{
					$item = strtolower($item);
					$sql = "SELECT `unit` FROM `bm_items` WHERE LOWER(`descriptionLong`) = '$item' LIMIT 1";
					$valor = $this->conexion->queryFetch($sql);
					return $valor[0]["unit"];
				}
			}
			
			public function dashboardHost(){
				$this->plantilla->load("api/dashboard");
				
				$queryOffset = "SELECT valor FROM Parametros WHERE nombre = 'TIMEZONE_OFFSET'";
				$resOffset = $this->conexion->queryFetch($queryOffset);
				$offset = 0;
				
				if($resOffset[0]["valor"] != ""){
					$offset = $resOffset[0]["valor"];
				}
				
				$queryTimezone = "SELECT valor FROM Parametros WHERE nombre = 'TIMEZONE'";
				$resTimezone = $this->conexion->queryFetch($queryTimezone);
				
				if($resTimezone[0]["valor"] != ""){
					$this->conexion->query("SET time_zone = '".$resTimezone[0]["valor"]."'");
				}
				
				$hostOk = "";
				$hostNOk = "";
				$qn = 0;
				$qo = 0;
				
				$queryHStatus = "SELECT bm_host.codigosonda, bm_host.host, FROM_UNIXTIME(MAX(bm_item_profile.lastclock + $offset)) AS lastclock, (UNIX_TIMESTAMP(NOW()) - MAX(bm_item_profile.lastclock + $offset)) AS diff, bm_plan.plan FROM bm_item_profile, bm_host, bm_plan WHERE bm_host.id_plan = bm_plan.id_plan AND bm_item_profile.id_host = bm_host.id_host AND bm_host.borrado = 0 AND bm_host.status = 1 GROUP BY bm_host.id_host ORDER BY bm_item_profile.lastclock DESC";
				$resHStatus = $this->conexion->queryFetch($queryHStatus);
				
				foreach($resHStatus as $id => $valor){
					$last = $valor["lastclock"];
					$host = $valor["host"];
					$plan = str_replace("_", "&nbsp;", str_pad($valor["plan"], 15, "_", STR_PAD_RIGHT));
					$diff = $valor["diff"];
					$codigosonda = $valor["codigosonda"];
					
					if($host != $codigosonda){
						$host = $codigosonda."/".$host;
					}
					
					if($diff > (60 * 40)){
						$color="#ff0000";
						
						if($diff >= (60 * 40)){
							$color = "#ff6600";
						}
						
						if($diff >= (60 * 60)){
							$color = "#000000";
						}
						
						$hostNOk = $hostNOk . "<div><span style='color: $color'>$last&nbsp;&nbsp;$plan&nbsp;$host</span></div>";
						$qn++;
					}else{
						$color = "#47a447";
						
						if($last != ""){
							$hostOk = $hostOk . "<div><span style='color: $color'>$last&nbsp;&nbsp;$plan&nbsp;$host</span></div>";
							$qo++;
						}
					}
				}
				
				$vars["qn"] = $qn;
				$vars["qo"] = $qo;
				$vars["ho"] = $hostOk;
				$vars["hn"] = $hostNOk;
				
				$this->plantilla->set($vars);				
				$this->plantilla->finalize();
			}
			
			public function getDashboardGropsHosts(){
				$group_in = array();
				$host_in = array();
				$groups_ids = "";
				$hosts_ids = "";
				
				$queryAllGroup = "SELECT hg.`groupid`, hg.`name` ,  IF(uhg.`id_group` IS NULL, 0, 1) as selected FROM `bm_host_groups` hg LEFT OUTER JOIN `bm_user_host_group` uhg ON uhg.`groupid`= hg.`groupid` WHERE hg.`borrado` = 0 AND hg.`name` != '' GROUP BY hg.`groupid` ORDER BY hg.`name`";
				$resAllGroup = $this->conexion->queryFetch($queryAllGroup);
				
				foreach($resAllGroup as $ind => $val){
					$group_in[] = $val['groupid'];
				}
				
				if(count($group_in) > 0){
					$groups_ids = $this->conexion->arrayToIN($group_in);
				}
				
				if($groups_ids != ""){
					$queryAllHost = "SELECT id_host FROM bm_host WHERE borrado = 0 AND groupid IN $groups_ids AND status = 1";
					$resAllHost = $this->conexion->queryFetch($queryAllHost);
					
					foreach($resAllHost as $indi => $valu){
						$host_in[] = $valu["id_host"];
					}
					
					if(count($host_in) > 0){
						$hosts_ids = $this->conexion->arrayToIN($host_in);
					}	
				}
				
				if($groups_ids != "" || $hosts_ids != ""){
					$nSQL = "SELECT BD.name_group as `name`, SUM(IF((BD.`status`='true'), 1, 0)) AS AVAILABILITY, SUM(IF((BD.`status`='false'), 1, 0)) AS NO_AVAILABILITY, BD.id_group as `groupid` from bm_disponibilidad BD
		WHERE BD.id_group IN $groups_ids 
		AND BD.id_host IN $hosts_ids
		GROUP BY BD.id_group";
					
					$aResultado = array();
					
					$resultado = $this->conexion->queryFetch($nSQL);
					
					if($resultado){
						foreach ($resultado as $key => $value) {
							$aRegistro = array();
							$aRegistro["id_group"] = $value["groupid"];
							$aRegistro["name"] = $value["name"];
							$aRegistro["available"] = $value["AVAILABILITY"];
							$aRegistro["no_available"] = $value["NO_AVAILABILITY"];
							
							$nDetail = "SELECT H.`host` AS name_host, AV.ultima_fecha, AV.datetime, AV.id_host FROM `bm_disponibilidad` AV 
				RIGHT JOIN bm_host H ON AV.id_host=H.id_host    
				WHERE AV.`status` = 'false'  AND AV.`id_group` IN (" . $value["groupid"] . ") ORDER BY AV.ultima_fecha DESC LIMIT 20 ";
							$rDetail = $this->conexion->queryFetch($nDetail);
							
							$allDetail = array();
							
							if($rDetail){
								foreach ($rDetail as $dkey => $dvalue) {
									$dvalue["updateSONDA"]=substr($dvalue["datetime"], 0, -3);						
									$aDetail = array();
									$aDetail["host"] = $dvalue["name_host"];
									$aDetail["lastcheck"] = $dvalue["ultima_fecha"];
									$aDetail["agent_code"] = $dvalue["id_host"];
						
									$allDetail[] = $aDetail;
								}
							}
							
							$aRegistro["details"] = $allDetail;
							$aResultado["data"][] = $aRegistro;
						}
					}

					$this->basic->jsonEncode($aResultado);
					exit;
				}else{
					$this->basic->jsonEncode(array("error" => true));
					exit;
				}
			}
	}
?>