<?php 

	class poller extends Control {
				
		private function validaPoller($class,$tipo = 'padre',$pidAnterior = null){
				
			$this->pid=getmypid();
			
			$this->tipo=$tipo;
			
			$limpiaPoller = 'DELETE FROM `bsw_poller` WHERE `fecha_fin` IS NOT NULL AND `fecha_fin` <   DATE_ADD(NOW(), INTERVAL  - 10 MINUTE);';
			$this->conexion->query($limpiaPoller);
			
			$result = $this->pollerActivo($class,$tipo);
			
			if($result !== false ) {
				
				$sql_inicial = 'INSERT INTO `bsw_poller` (`clase`, `pid_padre`, `pid_hijo`, `tipo`, `fecha_inicio`) VALUES';
				
				if($tipo == 'padre') {
					if($result == 0) {
						$insert = $sql_inicial." ('$class', $this->pid, 0, 'padre', NOW());";
						$this->conexion->query($insert);
						print "OK";
					} else {
						print "Poller activo";
						exit;
					}
					
				} elseif ($tipo == 'hijo') {
					$insert = $sql_inicial." ('$class', $pidAnterior, $this->pid, 'hijo', NOW());";
					$this->conexion->query($insert);
				} else {
					$this->logs->error("Tipo de poller invalido: ",$tipo,'logs_poller');
				}	
			} else {
				$this->logs->error("Error al consultar  poller activo",NULL,'logs_poller');
				exit;
			}
		}
		
		private function pollerActivo($class,$type = 'hijo') {
			$select_poller = "SELECT count(*) As Active FROM `bsw_poller` WHERE `tipo`='$type' AND `fecha_fin` IS NULL AND `clase`='$class';";
			$result = $this->conexion->queryFetch($select_poller);
			if($result) {
				return $result[0]['Active'];
			} else {
				return false;
			}
		}
		
		public function index($value='')
		{
			$this->validaPoller("indexPoller");
			
			$LIMIT = $this->parametro->get('GET_MAX_POLLER',10);
			
			$activos = $this->pollerActivo("indexPoller");
			
			if($activos) {
				$LIMIT = $LIMIT - $activos;
			}
			
			if($LIMIT == 0) {
				$this->logs->error("Poller inicial supero el maximo permitido",NULL,'logs_poller');
				exit;
			} else {
				$this->logs->debug("Iniciando poller con un limite de [$activos/$LIMIT] hilos",NULL,'logs_poller');
			}
			
			$sql_host = 'select `ip`, `items`.`value_type`,`feriados`,`horario`,`prevorgvalue`,`lastclock`,`delta`,`snmp_community`,`snmp_port`,unix_timestamp(now()) as `utime`,`items`.`delay`,`items`.`type`,`snmp_oid`,`itemid`,`hosts`.`hostid`,`macaddress`,`plan`,`host`,`dns`,`description`,`lastvalue`,`prevvalue` 
						FROM `hosts`, `items`, `hosts_profiles`, `hosts_groups`, `bsw_config` 
						WHERE `hosts_profiles`.`hostid`=`hosts`.`hostid` and `hosts`.`status`=0 and `hosts`.`hostid`=`items`.`hostid` and `description` like "%.bsw" and `nextcheck` < unix_timestamp(now()) and `items`.`status`=0 and `items`.`error` = "" and `items`.`type`=4 and `hosts`.`hostid`=`hosts_groups`.`hostid` and `bsw_config`.`groupid`=`hosts_groups`.`groupid` and `lastlogsize`=0 AND `ip` !="::1" order by `nextcheck` LIMIT '.$LIMIT;
						
			$result_host = $this->conexion->queryFetchAllAssoc($sql_host);

			$totalHijos = 	count($result_host);
			
			$this->logs->debug("Cargando un total de $totalHijos hilos",NULL,'logs_poller');
			
			$base = $this->parametro->get("URL_BASE");
			
			$urlInicial = $base.'poller/process';
			
			if($totalHijos >= 1){
				foreach ($result_host as $key => $host) {				
					$post = array();
					$post['pidPadre'] = $this->pid;
					$post['typePid'] = 'indexPoller';
					$post['ip'] = $host['ip'];
					$post['itemid'] = $host['itemid'];
					$post['utime'] = $host['utime'];
					$post['snmp_community'] = $host['snmp_community'];
					$post['snmp_port'] = $host['snmp_port'];
					$post['snmp_oid'] = $host['snmp_oid'];
					$post['host'] = $host['host'];
					$post['description'] = $host['description'];
					$post['value_type'] = $host['value_type'];
					$post['delta'] = $host['delta'];
					$post['dns'] = $host['dns'];
					$post['lastvalue'] = $host['lastvalue'];
					
					$this->curl->cargar($urlInicial,$host['itemid'],$post);	
				}
	
				$this->curl->ejecutar();
			} else {
				 $queryReset="update items set lastlogsize=0 where lastlogsize=1 and (error='' or error is null)  and nextcheck < unix_timestamp(now())-60*5";
				 $this->conexion->query($queryReset);
			}
		}
		
		public function retry()
		{
			
			$this->validaPoller("indexPollerRetry");
			
			$LIMIT = $this->parametro->get('GET_MAX_POLLER',10);
			
			$activos = $this->pollerActivo("indexPoller");
			
			if($activos) {
				$LIMIT = $LIMIT - $activos;
			}
			
			if($LIMIT == 0) {
				$this->logs->error("Poller inicial supero el maximo permitido",NULL,'logs_poller');
				exit;
			} else {
				$this->logs->debug("Iniciando poller con un limite de [$activos/$LIMIT] hilos",NULL,'logs_poller');
			}
			
			$sql_host = 'select ip,items.value_type,feriados,horario,prevorgvalue,lastclock,delta,snmp_community,snmp_port,unix_timestamp(now()) as utime,items.delay,items.type,snmp_oid,itemid,hosts.hostid,macaddress,plan,host,dns,description,lastvalue,prevvalue from hosts,items,hosts_profiles,hosts_groups,bsw_config where hosts_profiles.hostid=hosts.hostid and hosts.status=0 and hosts.hostid=items.hostid and description like "%.bsw" and nextcheck < unix_timestamp(now()) and items.status=0 and items.error <> "" and items.type=4 and hosts.hostid=hosts_groups.hostid and bsw_config.groupid=hosts_groups.groupid and lastlogsize=0 order by nextcheck LIMIT '.$LIMIT;
						
			$result_host = $this->conexion->queryFetchAllAssoc($sql_host);

			$totalHijos = 	count($result_host);
			
			$this->logs->debug("Cargando un total de $totalHijos hilos",NULL,'logs_poller');
			
			$base = $this->parametro->get("URL_BASE");
			
			$urlInicial = $base.'poller/process';
			
			foreach ($result_host as $key => $host) {				
				$post = array();
				$post['pidPadre'] = $this->pid;
				$post['typePid'] = 'indexPollerRetry';
				$post['ip'] = $host['ip'];
				$post['itemid'] = $host['itemid'];
				$post['utime'] = $host['utime'];
				$post['snmp_community'] = $host['snmp_community'];
				$post['snmp_port'] = $host['snmp_port'];
				$post['snmp_oid'] = $host['snmp_oid'];
				$post['host'] = $host['host'];
				$post['description'] = $host['description'];
				$post['value_type'] = $host['value_type'];
				$post['delta'] = $host['delta'];
				$post['dns'] = $host['dns'];
				$post['lastvalue'] = $host['lastvalue'];
				
				$this->curl->cargar($urlInicial,$host['itemid'],$post);	
			}

			$this->curl->ejecutar();
		}
		
		public function process(){
				
			$getParam = (object)$_POST;
			
			$this->validaPoller($getParam->typePid,'hijo',$getParam->pidPadre);
					
			$ip = $getParam->ip.":".$getParam->snmp_port;
			
			
			switch ($getParam->value_type) {
				case '0':
					$tablehistory="history";
 	       			$tabletrends="trends";	
				break;
				
				case '1':
					$tablehistory="history_str";
 	       			$tabletrends="";
				break;
				
				case '3':
					$tablehistory="history_uint";
 	       			$tabletrends="trends_uint";	
				break;
				
				case '4':
					$tablehistory="history_text";
 	       			$tabletrends="";
				break;
								
				default:
					$tablehistory="history";
					$tabletrends="trends";	
				break;
			}
			
			$query="update items set nextcheck=" . $getParam->utime . "+delay,lastlogsize=1 where itemid=" . $getParam->itemid;
			$this->conexion->query($query);
			
			//Cargando parametros
			
			$timeout = $this->parametro->get("SNMP_TIMEOUT",1000000);
			$retry = $this->parametro->get("SNMP_RETRY",3);
			$version = $this->parametro->get("SNMP_VERSION",'2c');
			
			//Validamos si el modulo o paquete php_snmp se encuentra activo
			if (function_exists('snmp2_get')) {
				//Via modulo Inicio
				$result = @snmp2_get($ip,$getParam->snmp_community,$getParam->snmp_oid,$timeout,$retry);
				if($result){
					$this->logs->debug("La consulta snmp -> host: [$getParam->host] ip: [$ip] objetid: [$getParam->snmp_oid]  , respondio: ",$result,'logs_poller');
					list($tipoValorSnmp, $valorSnmp) = explode(": ", $result);
					
					//validaciones 
					
					if(($tipoValorSnmp == 'STRING') && (!preg_match('/"/i',$valorSnmp)) ) {
						$valorSnmp = '"'.$valorSnmp.'"';
					}
					
					$filtroObligatorio = false;
					if(($tipoValorSnmp == 'STRING') && ($getParam->value_type == 0) ) {
						$this->logs->error("Revisar: Se esperaba un valor float y se recibio un string $valorSnmp", NULL,'logs_poller');
						$filtroObligatorio = true;	
					}
					
					$this->logs->debug("Buscando filtro especial", NULL,'logs_poller');
					
					$filterStatus = $this->filter($getParam->description,$getParam->dns,$valorSnmp);
					
					if($filterStatus) {
						$valorSnmp = $this->snmpFilterValue;
					}
									
					if(($filterStatus == false) && ($filtroObligatorio)) {
						$this->logs->error("Revisar: Se esperaba un valor float y se recibio un string y no tiene filtro", NULL,'logs_poller');
						exit;
					}
					
					$query="insert into $tablehistory (clock,itemid,value) values ( $getParam->utime, $getParam->itemid, $valorSnmp )";
					$this->conexion->query($query);
					
					if ($tabletrends != "") {
						$query="insert into " . $tabletrends . "(clock,itemid,value_avg,value_min,value_max,num) values (" . $getParam->utime . "," . $getParam->itemid . "," . $valorSnmp . "," . $valorSnmp . "," . $valorSnmp . ",3)";
						$this->conexion->query($query);
					}
					
					if(empty($getParam->lastvalue)) {
						$getParam->lastvalue = "0";
					}
					
					$query_item="UPDATE `items` ".
								"SET error='',lastlogsize=0,prevorgvalue='" . $getParam->lastvalue . "',lastclock=" . $getParam->utime . ",prevvalue=prevorgvalue,lastvalue=" . $valorSnmp . ",nextcheck=" . $getParam->utime . " + delay ".
								"WHERE itemid=" . $getParam->itemid;
										
					$this->conexion->query($query_item);					
					 
				} else {
					$this->logs->error("Timeout al consultar via snmp al host: $getParam->host ",NULL,'logs_poller');
				
					if (($getParam->description == "Availability-ForTrap.bsw") || ($getParam->description=="Availability.bsw")) {
						$q="update items set error='Router no responde',lastlogsize=0,prevorgvalue=,lastclock=" . $getParam->utime . ",prevvalue=prevorgvalue,lastvalue=0,nextcheck=" . $getParam->utime . " + delay where itemid=" . $getParam->itemid;
					}
								
				}
				//Via modulo Fin
			} else {
				//Via comando Inicio
				$this->logs->error("El modulo snmp no existe por lo cual se intenta via comando",NULL,'logs_poller');
				$retval = shell_exec("whereis snmpget | wc -w");
				if($retval == 1) {
					$this->logs->error("Error el comando snmpget no existe",NULL,'logs_poller');
					exit;
				}
				$snmpget_cmd="snmpget -t10 -Oqv -v$version -c$getParam->snmp_community $ip $getParam->snmp_oid";
				$snmpget=shell_exec($snmpget_cmd);
				$this->logs->info("El comando snmp: ",$snmpget_cmd ." Respondio: ". $snmpget,'logs_poller');
				//Via comando Fin
			}
		}
		
		private function filter($description, $dns, $valor)
		{
			switch ($description) {
				case 'Availability.bsw':
					if(preg_match("/$dns/i",$valor)){
						$this->logs->debug("Filtro  encontrado, se tranforma valor inicial en 1, $dns ->  $valor",NULL,'logs_poller');
						$this->snmpFilterValue = '1';
						return true;
					} else {
						$this->logs->debug("Filtro  encontrado, se tranforma valor inicial en 0, $dns ->  $valor",NULL,'logs_poller');
						$this->snmpFilterValue = '0';
						return true;
					}
				break;

				case 'Availability-ForTrap.bsw':
					if(preg_match("/$dns/i",$valor)){
						$this->logs->debug("Filtro  encontrado, se tranforma valor inicial en 1, $dns ->  $valor",NULL,'logs_poller');
						$this->snmpFilterValue = '1';
						return true;
					} else {
						$this->logs->debug("Filtro  encontrado, se tranforma valor inicial en 0, $dns ->  $valor",NULL,'logs_poller');
						$this->snmpFilterValue = '0';
						return true;
					}
				break;
					
				default:
					return false;
				break;
			}	
		}
		
		function __destruct() {
			if($this->tipo == 'padre'){
				$campo = 'pid_padre';
			} else {
				$campo = 'pid_hijo';
			}
			$update = " UPDATE `bsw_poller` SET `fecha_fin` = NOW() WHERE `$campo` = '$this->pid'";
			$result_update = $this->conexion->query($update);
			if(!$result_update) {
				$this->logs->error("Error al actualizar estado del poller con el pid",$this->pid,'logs_poller');
			}
   		}
	}
?>