<?php

	class Charts {
		public $separator = true;
		public $defaultDay = 2;
		public $limitDefault = 10000;
		public $markTime = false;
		public $graphNullDisplay = true;
		private $dbNameCore = 'bsw_bi';
		private $conexion;
		private $parametro;
		private $logs;

		public function __construct( $parametro, $conexion, $logs ) {
			$this->parametro = $parametro;
			$this->conexion = $conexion;
			$this->logs = $logs;
		}

		public function getIDHistory($month)
		{
			$monthNow = date('n');
			$yearNow = date('Y');
			$dateNow = date('Y-m-j');
			$newDate = strtotime ('-'.$month.' months' , strtotime ( $dateNow ) ) ;
			
			$monthPrevious = date ( 'n' , $newDate );
			$yearPrevious = date ( 'Y' , $newDate );
			//$this->logs->error("mes: $month");
			//$this->logs->error("year now: $yearNow, yearPrevio: $yearPrevious, mes: $monthNow, mes anterior: $monthPrevious");
			
			//Cambio mes anterior. BUSG012
			/*if($month < 2) {
				$monthSelect = $monthPrevious+1;
			} else {
				$monthSelect = $monthPrevious;
			}*///BUGS012
			$monthSelect = $monthPrevious;
			
			if($yearNow == $yearPrevious){
				$yearSelect = $yearNow;
			} else {
				$yearSelect = $yearPrevious;
			}
			//$this->logs->error("year seleccionado: $yearSelect, mes seleleccionado: $monthSelect");			
			$urlBase = explode('/', URL_BASE_FULL);

			$urlBase = $urlBase[0];
						
			$getIDServerSQL = "SELECT T.`idHistory`  FROM `" . $this->dbNameCore . "`.`bi_server` S
				LEFT JOIN `" . $this->dbNameCore . "`.`bi_tick` T ON S.`idServer`=T.`idServer`
				WHERE S.`domain` = '$urlBase' 
						AND T.`type`='start' AND T.`date`= '$yearSelect-$monthSelect';";
			
			//$this->logs->error("SQL idHistory para bsw_bi: ".$getIDServerSQL);
			
			$getIDServerRESULT = $this->conexion->queryFetch($getIDServerSQL);
			
			if($getIDServerRESULT) {			
				$idHistory = $getIDServerRESULT[0]['idHistory'];
				
				if(is_numeric($idHistory) && $idHistory > 0){
					return $idHistory;
				} else {
					return false;
				}
				
			} else {
				return false;
			}
		}
	
		public function getIdItemForGraphid( $graphid ) {
			if(is_numeric($graphid)) {

				$getIdItemForGraphidSQL = 'SELECT `id_item` FROM  `bm_graphs_items` WHERE `id_graph` = ' .$graphid;

				$getIdItemForGraphidRESULT = $this->conexion->queryFetch($getIdItemForGraphidSQL, 'logs_charts');

				if($getIdItemForGraphidRESULT) {

					foreach ($getIdItemForGraphidRESULT as $key => $value) {
						$idItem[] = (int)$value['id_item'];
					}

					return $idItem;

				} else {
					return false;
				}

			} else {
				return false;
			}
		}

		public function getGraph( $hostid, $itemid, $start = false, $end = false, $limit = false , $dataGrouping = true) {
			if($start && ! preg_match('/^[0-9]+$/', $start)) {
				return false;
			} elseif( ! $start) {

				if( ! is_numeric($this->defaultDay)) {
					return false;
				}
				
				switch ($limit) {
					case '0':
						$month = 1;
						$start = (time() - ( 31 * 24 * 3600 ) );
						break;
					case '1':
						$month = 1;
						$start = (time() - ( 1 * 24 * 3600 ) );
						break;
					case '2':
						$month = 1;
						$start = (time() - ( 2 * 24 * 3600 ) );
						break;
					case '3':
						$month = 1;
						$start = (time() - ( 3 * 24 * 3600 ) );
						break;
					case '500':
						$month = 1;
						$start = (time() - ( 31 * 24 * 3600 ) );
						break;
					case '15000':
						$month = 3;
						$start = (time() - ( 93 * 24 * 3600 ) );
						break;
					case '25000':
						$month = 6;
						$start = (time() - ( 186 * 24 * 3600 ) );
						break;
					case '60000':
						$month = 12;
						$start = (time() - ( 372 * 24 * 3600 ) );
						break;
					default:
						$month = 1;
						$start = (time() - ( 31 * 24 * 3600 ) );
						break;
				}
			}

			if($end && ! preg_match('/^[0-9]+$/', $end)) {
				return false;
			}

			if( ! $end) {
				$this->logs->debug("Activando el marcador de tiempo final", NULL, 'logs_charts');
				$this->markTime = true;
				$end = time();
			}

			$range = $end - $start;

			if($this->separator == true) {

				if($range <= 2 * 24 * 3600 * 1000) {//2 Days
					$separator = 'minutes';
				} elseif($range < 31 * 24 * 3600 * 1000) {//1 Month
					$separator = 'hour';
				} elseif($range < 2 * 31 * 24 * 3600 * 1000) {//2 Month
					$separator = 'day';
				} elseif($range < 4 * 31 * 24 * 3600 * 1000) {//4 Month
					$separator = 'week';
				} else {
					$separator = 'month';
				}

			} else {
				$separator = false;
			}

			if($limit !== false && is_numeric($limit) && $limit > 10) {
				$this->limitDefault = $limit;
			}

			if(is_array($hostid) && is_array($itemid)) {
				return false;
			}

			if(is_array($hostid)) {
				$selection = "AH";
			} elseif(is_array($itemid)) {
				$selection = "AI";
			} else {
				$selection = "SINGLE";
			}

			//return $this->generate($hostid, $itemid, $start, $end, $separator, $selection, $dataGrouping);
			
			$IDHistory = $this->getIDHistory($month);
			
			return $this->generateNew($hostid, $itemid, $start, $end, $separator, $dataGrouping,$IDHistory);

		}
		
		public function generateNew( $hostid, $itemid, $start, $end, $separator, $dataGrouping,$IDHistory) {
			$startTime = gmstrftime('%Y-%m-%d %H:%M:%S', $start );
			$endTime = gmstrftime('%Y-%m-%d %H:%M:%S', $end);

			$this->logs->debug("Generado grafico: ", "Start [$startTime] => End [$endTime]", 'logs_charts');
			
			$tree = 'host';
			$tree='';
			$filter='';
			
			if(is_array($hostid)){
				//$filter = 'H.`id_host` IN (' .join(',', $hostid) .')'; 	esto se saca para realizar la consulta correcta mas abajo por cada sonda y sea mas rapida la consulta
				$tree = 'host';
				// se quita esto ya que se optimiza la query al consultar en tablas xyz_000item 
				//if(is_array($itemid)){
				//	$filter .= ' AND H.`id_item` IN (' .join(',', $itemid) .')';
				//	$tree = 'item';
				//} else {
				//	$filter .= " AND H.`id_item` = $itemid";  
				//}			
			} else {  //cuando es 1 sonda queda la consulta antigua solo para Graficos Simples muchas sondas 1 monitot (item)
				$filter = "H.`id_host` = $hostid";
				if(is_array($itemid)){
					$filter .= ' AND H.`id_item` IN (' .join(',', $itemid) .')';
					$tree = 'item';
				} else {
					$filter .= " AND H.`id_item` = $itemid"; 
				}
			}	
						
/*			if(is_array($itemid)){
				$filter .= ' AND H.`id_item` IN (' .join(',', $itemid) .')';
				$tree = 'item';
			} else {
				$filter .= " AND H.`id_item` = $itemid"; 
			}
*/
			/*if($IDHistory !== false){
				$filter .= " AND H.`id_history` >= $IDHistory";
			} else {
				$filter .= '';
			}*/
								
			if($tree == 'host'){
				$orderBY = ' H.`id_host`, H.`clock` ';
			} else {
				$orderBY = ' H.`id_item`, H.`clock` ';
			}
			//cambio BGS010 se cae por mxa data pero se aumento memoria php en /etc/vim php.ini  ,  y memory limit= 1G          en vez  de 128 M	 		
			// se revivio el mismo BUGS010 ya que ahora por mas que se aumente memoria la consulta se mora mas de 8 min en traer data 
			// cambio o mejora a bmonitor2.5 BGS022 ticket007
			$getHistoriRESULT=array();
			if(is_array($hostid)){
				//cuando se consulta por todas las sondas y 1 monitor(item)  Graficos Simples
				foreach ($hostid as $key => $IDvalue) { 
					//$this->logs->error("consulta del host(id_host): ".$IDvalue);
					$table='xyz_' . str_pad($itemid, 10, "0", STR_PAD_LEFT);
					$getHistorySQL = "SELECT H.`clock`, H.`value`, HO.`host`, I.`descriptionLong`, I.`unit`,
	                H.`id_host`, I.`delay`
	                FROM `".$table."` H
	                LEFT JOIN `bm_host` HO USING(`id_host`)
	                LEFT JOIN `bm_items` I ON I.`id_item`= $itemid
	                WHERE  
	                H.`clock` >= ".$start." AND
	                H.`clock`  <= ".$end." AND H.`id_host`= $IDvalue $filter 
	                ORDER BY $orderBY ASC";
			/*		$getHistorySQL = "SELECT H.`id_history`,H.`clock`, H.`value`, HO.`host`, I.`descriptionLong`, I.`unit`,
	                H.`id_host`,H.`id_item` , I.`delay`
	                FROM `bm_history` H
	                LEFT JOIN `bm_host` HO USING(`id_host`)
	                LEFT JOIN `bm_items` I ON H.`id_item`=I.`id_item`
	                WHERE  
	                H.`clock` >= ".$start." AND
	                H.`clock`  <= ".$end." AND H.`id_host`= $IDvalue $filter 
	                ORDER BY $orderBY ASC";
			 */
					//$this->logs->error("la CONSULTA PRINCIPAL q realiza para data a graficar muchas sondas por 1 monitor o (item) Graf. Simples.: ".$getHistorySQL);
					$getHistoriRESULT_1 = $this->conexion->queryFetch($getHistorySQL, 'logs_charts');
					//$this->logs->error("OK consulta se hizo");
					if($getHistoriRESULT_1!=null || $getHistoriRESULT_1!=''){
						//$this->logs->error("entre empezarea guardar");
						$getHistoriRESULT=array_merge($getHistoriRESULT,$getHistoriRESULT_1);
						//$this->logs->error("Se guardo OK la data host=$IDvalue");						
					}
					//$this->logs->error("El array que almacena lleva count= ".count($getHistoriRESULT)."y el nuevo array sumo= ".count($getHistoriRESULT_1));
				}				
			}else{ //cuando se consulta por 1 agente				
				//para Graficos Agrupados. Se consulta por 1 agente pero varios items,  
				//tb se demora una eternidad por lo que se extiende el cambio del BUGS010 para graficos Agrupados pero consultando en las tablas xyz_000..item.  
				if(is_array($itemid)){//cuando se consulta por varios items de 1 categoria en 1 sonda.	
					$filter = " AND H.`id_host` = $hostid";   //necesito solo 1 host para el filter nada mas.   
					$orderBY = 'H.`clock` ';//' H.`id_item`, H.`clock` ';	ya no existe en tablas xyz_ el item_id como campo		
					foreach ($itemid as $key => $IDvalue) { 
						//$this->logs->error("consulta del host(id_host): ".$IDvalue); 
						$table='xyz_' . str_pad($IDvalue, 10, "0", STR_PAD_LEFT);
						$getHistorySQL = "SELECT H.`clock`, H.`value`, HO.`host`, I.`descriptionLong`, I.`unit`,
		                H.`id_host`, I.`delay`
		                FROM `".$table."` H
		                LEFT JOIN `bm_host` HO USING(`id_host`)
		                LEFT JOIN `bm_items` I ON I.`id_item`= $IDvalue 
		                WHERE  
		                H.`clock` >= ".$start." AND
		                H.`clock`  <= ".$end." $filter 
		                ORDER BY $orderBY ASC";
				/*		$getHistorySQL = "SELECT H.`id_history`,H.`clock`, H.`value`, HO.`host`, I.`descriptionLong`, I.`unit`,
		                H.`id_host`,H.`id_item` , I.`delay`
		                FROM `bm_history` H
		                LEFT JOIN `bm_host` HO USING(`id_host`)
		                LEFT JOIN `bm_items` I ON H.`id_item`=I.`id_item`
		                WHERE   
		                H.`clock` >= ".$start." AND
		                H.`clock`  <= ".$end." AND $filter 
		                ORDER BY $orderBY ASC";
				 */
						//$this->logs->error("la CONSULTA PRINCIPAL q realiza para data a graficar 1 sonda muchos items Graf. Agrupados: ".$getHistorySQL); 
						$getHistoriRESULT_1 = $this->conexion->queryFetch($getHistorySQL, 'logs_charts');
						//$this->logs->error("OK consulta se hizo");
						if($getHistoriRESULT_1!=null || $getHistoriRESULT_1!=''){
							//$this->logs->error("entre empezarea guardar");
							$getHistoriRESULT=array_merge($getHistoriRESULT,$getHistoriRESULT_1);
							//$this->logs->error("Se guardo OK la data host=$IDvalue");						
						}
					//$this->logs->error("El array que almacena lleva count= ".count($getHistoriRESULT)."y el nuevo array sumo= ".count($getHistoriRESULT_1));
					}			
				}else{
					//cuando se consulta en graficos simples pero 1 sonda y 1 monitor(item).
					$getHistorySQL = "SELECT H.`id_history`,H.`clock`, H.`value`, HO.`host`, I.`descriptionLong`, I.`unit`,
	                H.`id_host`,H.`id_item` , I.`delay`
	                FROM `bm_history` H
	                LEFT JOIN `bm_host` HO USING(`id_host`)
	                LEFT JOIN `bm_items` I ON H.`id_item`=I.`id_item`
	                WHERE  
	                H.`clock` >= ".$start." AND
	                H.`clock`  <= ".$end." AND $filter 
	                ORDER BY $orderBY ASC";						
					//$this->logs->error("la CONSULTA PRINCIPAL q realiza para data a graficar: con solo 1 host 1 monitor(item) Graf. Simples".$getHistorySQL);  
					$getHistoriRESULT = $this->conexion->queryFetch($getHistorySQL, 'logs_charts');
				}
			}
			
			$history = array();
			$unitName = array();
			$unitSet = false;
			if($getHistoriRESULT) {		
				if($tree == 'host'){		
					foreach ($getHistoriRESULT as $key => $value) {
						$history[$value['host']][$value['clock']] = $value['value'];
						if(!isset($firstClock)){
							$firstClock = $value['clock'];
							$maxClock = $value['clock'];
						} else {
							if($value['clock'] < $firstClock) {
								$firstClock = $value['clock'];
							}
							if($value['clock'] > $maxClock) {
								$maxClock = $value['clock'];
							}		
						}
						$unitName[$value['host']] = $value['unit'];
					}
					//$this->logs->error(" es host ultima funcion ");
				} else {
					foreach ($getHistoriRESULT as $key => $value) {
						$history[$value['descriptionLong']][$value['clock']] = $value['value'];
						if(!isset($firstClock)){
							$firstClock = $value['clock'];
							$maxClock = $value['clock'];
						} else {
							if($value['clock'] < $firstClock) {
								$firstClock = $value['clock'];
							}
							if($value['clock'] > $maxClock) {
								$maxClock = $value['clock'];
							}			
						}
						$unitName[$value['descriptionLong']] = $value['unit'];
					}			
				}
			} else {
				return "[{}]";
				//$this->logs->error("salida sin data pero correcta ");

			}
		
			$series = array();		
			//$this->logs->error("el array de hystory lleva n_elementos: ".count($history));
			//$i12=0;	
			foreach ($history as $keyHistory => $valueHistory) {
				//$this->logs->error("entre OK al forech history y voy en el i: $i12");					
				$lastClock = 0;
				$mark = false;
				$delaySensitized = array();
				///$this->logs->error("el array de valuehistory lleva n_elementos: ".count($valueHistory)); 
				//$z12=0;
				foreach ($valueHistory as $key => $value) {					
					if($mark == false){
						//$this->logs->error("entre en el foreach valuehistory  mark=false,  z: $z12");
						if($firstClock != $key){
							$series[$keyHistory][] = "[" .$firstClock ."000,null]";
						}
						$mark = true;
					}					
					if($lastClock == 0){
						//$this->logs->error("entre en el foreach valuehistory  lastClock=0,  z: $z12");
						$series[$keyHistory][] = "[" .$key ."000," .round($value, 2) ."]";
					} else {
						//$this->logs->error("entre en el foreach valuehistory  mark<>0 else,  z: $z12");							
						if($this->graphNullDisplay == true)	{
							//$this->logs->error("es this->graphNullDisplay == true,  z: $z12");							
							if(!isset($delay)){
								//$this->logs->error("es !isset(delay),  z: $z12");
								$delay = $key - $lastClock;
							} else {
								//$this->logs->error("es delay=1800,  z: $z12");
								$delay = 1800;
							}							
							//$this->logs->error("el tam de delaySensitized antes es: ".count($delaySensitized));
							$delaySensitized[] = $this->roundNumber($key - $lastClock,100);
							//$this->logs->error("el tam de delaySensitized despues es: ".count($delaySensitized));
							if(($lastClock+($delay*2)) < $key){
								//$this->logs->error("es lastClock+(delay*2)) < key,  z: $z12");
								$newClock = round((($lastClock+($delay*2))+$key)/2);
								//$this->logs->error("el tam de series[] antes es: ".count($series));																
								$series[$keyHistory][] = "[" .$newClock ."000,null]";
								//$this->logs->error("el tam de series[] despues es: ".count($series));
							}							
							//$this->logs->error("el tam de series[] al final antes es: ".count($series));
							$series[$keyHistory][] = "[" .$key ."000," .round($value, 2) ."]";
							//$this->logs->error("el tam de series[] al final despues es: ".count($series));							
						} else {
							//$this->logs->error("es this->graphNullDisplay == true,  z: $z12");
							//$this->logs->error("el tam de series[] antes es: ".count($series));
							$series[$keyHistory][] = "[" .$key ."000," .round($value, 2) ."]";
							//$this->logs->error("el tam de series[] despues es: ".count($series));
						}							
					}
					//$this->logs->error("OKKK i: $i12, z: $z12");
					$lastClock = $key;
					//$z12++;
				}
				//$this->logs->error("OKKK i: $i12, z: $z12");
				$delayModa = $this->modaArray($delaySensitized);
				if($delayModa == '' || !is_numeric($delayModa)){
					$delayModa = 3600;
				}
			
				if($maxClock != $lastClock){
					$series[$keyHistory][] = "[" .$maxClock ."000,null]";
				}
				
				$presentClock = time();
				if(($presentClock-$maxClock) > $delayModa) {
					$maxClockTick = $maxClock;
					for ($i=0; $i < 20; $i++) { 
						$maxClockTick = $maxClockTick+$delayModa;
						if($maxClockTick < $presentClock){
							$series[$keyHistory][] = "[" .$maxClockTick ."000,null]";
						}
					}
					
					if(($maxClockTick + $delayModa) < $presentClock){
						$series[$keyHistory][] = "[" .$presentClock ."000,null]";
					}
					
				}		
				$mark = false;
				//$i12++;	
			}
			
			$result = array();
			
			$pointStart = $firstClock*1000;
			
			if($dataGrouping == false){
				$option = ', marker: { enabled : true, radius: 3}, dataGrouping: { enabled: false } , pointInterval: '.$delayModa.' * 1000';
			} else {
				$option = ', marker: { enabled : true, radius: 3}, dataGrouping: { enabled: true, forced: true  }, pointInterval: '.$delayModa.' * 1000 , pointStart:'.$pointStart;
			}
			
			$count = 1;
			foreach ($series as $key => $serie) {
				if(isset($unitName[$key])){
					//$key = $key."(".$unitName[$key].")";
				}
				$result[] = '{"id" : '.$count.', "name" : \'' .$key .'\', "data" :' ."[" .join(",", $serie) ."]$option}";
				$count++;
			}
			
			return "[" .join(",\n", $result) ."]";
		}

		public function generate( $hostid, $itemid, $start, $end, $separator, $selection, $dataGrouping) {

			$startTime = gmstrftime('%Y-%m-%d %H:%M:%S', $start / 1000);
			$endTime = gmstrftime('%Y-%m-%d %H:%M:%S', $end / 1000);

			$this->logs->debug("Generado grafico: ", "Start [$startTime] => End [$endTime]", 'logs_charts');

			if($selection == "AH") {
				$filter = 'H.`id_host` IN (' .join(',', $hostid) .") AND H.`id_item` =" .$itemid;
			} elseif($selection == "AI") {
				$filter = 'H.`id_host` = ' .$hostid;
			} else {
				$filter = 'H.`id_host` = ' .$hostid ." AND H.`id_item` =" .$itemid;
			}

			if($selection == "AI") {

				foreach ($itemid as $key => $value) {

					$array[] = "(SELECT H.`id_history`,H.`clock`, H.`value`, HO.`host`, I.`descriptionLong`,  I.`unit` ,
                        H.`id_host`,H.`id_item` , I.`delay`
                        FROM `bm_history` H
                        LEFT JOIN `bm_host` HO USING(`id_host`)
                        LEFT JOIN `bm_items` I ON H.`id_item`=I.`id_item`
                        WHERE $filter AND H.`id_item` IN ($value) AND H.`clock` >= " .($start / 1000) ." AND
                        H.`clock`  <= " .($end / 1000) ."
                        ORDER BY H.`clock` DESC LIMIT " .$this->limitDefault .")";

					$getHistoriSQL = ' ' .join(' UNION ', $array) .'  ORDER BY  `clock` ASC';
				}
			} else {

				$getHistoriSQL = "SELECT H.`id_history`,H.`clock`, H.`value`, HO.`host`, I.`descriptionLong`, I.`unit`,
                    H.`id_host`,H.`id_item` , I.`delay`
                    FROM `bm_history` H
                    LEFT JOIN `bm_host` HO USING(`id_host`)
                    LEFT JOIN `bm_items` I ON H.`id_item`=I.`id_item`
                    WHERE $filter AND H.`clock` >= " .($start / 1000) ." AND
                    H.`clock`  <= " .($end / 1000) ."
                    ORDER BY H.`clock` DESC LIMIT " .$this->limitDefault;

			}

			$getHistoriRESULT = $this->conexion->queryFetch($getHistoriSQL, 'logs_charts');
			
			if($selection !== "AI") {
			    $getHistoriRESULT = array_reverse($getHistoriRESULT);
			}

			if($getHistoriRESULT) {

				$data = array();

				$GRAPH_NULL_DISPLAY = $this->graphNullDisplay;

				//$GRAPH_NULL_DISPLAY_LIMIT = $this->parametro->get("GRAPH_NULL_DISPLAY_LIMIT", 10);
				$GRAPH_NULL_DISPLAY_LIMIT = 1000;
                $countDelay = 0;
				foreach ($getHistoriRESULT as $key => $value) {

					if($selection == "AH") {
						// $idData = $value['id_host'];
						$idData = $value['host'];
					} elseif($selection == "AI") {
						//$idData = $value['id_item'];
						$idData = $value['descriptionLong']."(".$value['unit'].")";
					} else {
						//$idData = $value['id_host'];
						$idData = $value['host']."(".$value['unit'].")";
					}

					if($GRAPH_NULL_DISPLAY == true) {
						if( ! isset($lastclock[$idData])) {
							$lastclock[$idData] = $value['clock'];
						} else {
							
							if($countDelay < 20) {			
								$delayArry[] = 	$value['clock'] - $lastclock[$idData];
								$countDelay++;
							} elseif ($countDelay == 20) {
								$delayAvg = (int)round(array_sum($delayArry) / count($delayArry));
								$countDelay = 0;
							}
							
							if(isset($delayAvg)) {
								$value['delay'] = $delayAvg;
							}
									
							if(($lastclock[$idData] + ( $value['delay'] * 2)) < $value['clock']) {

								$cicle = true;
								$count = 0;

								while ($cicle == true) {
			
			
									$lastclock[$idData] = $lastclock[$idData] + $value['delay'];
									
									$delay[$idData] = $value['delay'];
															

									if((($lastclock[$idData]+$value['delay']) > $value['clock']) || ($count >= $GRAPH_NULL_DISPLAY_LIMIT)) {
										$cicle = false;
										//$lastclock[$idData] = $value['clock'];
									} else {
										$data[$idData][] = "[" .$lastclock[$idData] ."000,null]";
										$count++;
									}
								
								}
								
							} else {
								$delay[$idData] = $value['delay'];
								$lastclock[$idData] = $value['clock'];
							}

						}

					} else {
						$delay[$idData] = $value['delay'];
						$lastclock[$idData] = $value['clock'];
					}

					$data[$idData][] = "[" .$value['clock'] ."000," .round($value['value'], 2) ."]";
					//$data[$idData][] = array((integer)$value['clock'],number_format((float)$value['value'],
					// 4, '.', ''));

					if( ! isset($idHistoryStart) || ! isset($idHistoryEnd)) {
						$idHistoryStart = $value['id_history'];
						$idHistoryEnd = $value['id_history'];
					} else {
						if($idHistoryStart > $value['id_history']) {
							$idHistoryStart = $value['id_history'];
						}

						if($idHistoryEnd < $value['id_history']) {
							$idHistoryEnd = $value['id_history'];
						}
					}
				}

				//return json_encode($data);

				$option = ", marker : { enabled : true, radius : 3 },
                shadow : true, tooltip : { valueDecimals : 2 }";
				
				if($dataGrouping == false){
					$option .= ', dataGrouping: { enabled: false }';
				} else {
					$option .= ', dataGrouping: { enabled: true }, pointInterval: 3600 * 1000';
				}

				foreach ($data as $id => $value) {
					if($this->markTime == true) {

						$cicle = true;
						$count = 0;
						$endTick = $end / 1000;

						$this->logs->debug("Marcando el idHost o idItem: [$id] ", " Comparando $lastclock[$id]000 es menor a $end ? ", 'logs_charts');

						while ($cicle == true) {

							$endTick = $endTick - $delay[$id];

							if((($lastclock[$id]) > $endTick) || ($count >= $GRAPH_NULL_DISPLAY_LIMIT)) {
								$cicle = false;
							} else {
								$this->logs->debug("Valor: ", $endTick, 'logs_charts');
								$markTime[] = $endTick;
								$count++;
							}

						}

						// sort($markTime);

						foreach ($markTime as $timeClock) {
							//$value[] = "[" . $timeClock . "000,null]";
						}

					}

					$result[] = '{"name" : \'' .$id .'\', "data" :' ."[" .join(",", $value) ."] $option}";
				}

				//echo "/* [ $idHistoryStart ] =>  [ $idHistoryEnd ]  AND Start [$startTime] => End [$endTime] */";
				return "[" .join(",\n", $result) ."]";

			} else {
				return false;
			}

		}

		 public function modaArray($array=array())
		 {
			 $return=false;
			 $arryCount= array();
			 if(is_array($array)){
			 	$arryCount= array();
			 	/*
			 	$arryCount = array_count_values($array);
				$maxKey = 0;
				foreach ($arryCount as $key => $value) {
					if($value > $maxKey) {
						$maxKey = $value;
						$return = $key;
					}
				}*/
				foreach ($array as $key => $value) {
					$arryCount[$value] =  (isset($arryCount[$value])) ? $arryCount[$value]+1 : 1 ;
				}
				$maxKey = 0;
				foreach ($arryCount as $key => $value) {
					if($value > $maxKey) {
						$maxKey = $value;
						$return = $key;
					}
				}
				return $return;
			 } else {
			 	return false;
			 }
		 }


		public function roundNumber($number = 0, $significance = 1)
		{
			if(!is_numeric($number) || $number < 0){
				return 0;
			}
			
			if($significance == 'auto'){
				if($number < 1000){
					$significance = 100;
				} elseif ($number < 10000) {
					$significance = 1000;
				}	elseif ($number < 100000) {
					$significance = 10000;
				} else {
					$significance = 1;
				}
			}

			$cal = $number/ $significance;
			$cal = round($cal);
			$cal = $cal*$significance;
			if(is_numeric($number) || $number > 0){
				return $cal;
			} else {
				return 0;
			}
		}
		
		public function __destruct( ) {
			unset($this->parametro);
			unset($this->conexion);
			unset($this->logs);
		}

	}
?>