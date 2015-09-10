<?php 

class crontab extends Control {
    
    private static $retryPing = 3;

	public function fdt($mes = FALSE, $ano = FALSE)
	{
		//Validar que no se encuentre activo un cron de FDT
		$valida_crontab = $this->_crontab("fdt_crontab","start",TRUE,10800);
		
		if($valida_crontab) {
			$this->logs->info("[FDT] Starting OK",NULL,'logs_poller');
		} else {
			$this->logs->error("[FDT] Starting NOK",NULL,'logs_poller');
			exit;
		}
			
		//Generado variables
		
		$datos = array();
		
		$host_act = array();
		
		if($mes === FALSE) {
			$mes=date("m");
		} else {
			if(is_numeric($mes)) {
				$mes = (int)$mes;
			} else {
				$mes=date("m");
			}
		}
		
		if($ano === FALSE) {
			$y=date("Y");
		} else {
			$y=$ano;
		}
			
		//Cargando Sondas Disponibiles
		$get_host_sql = "SELECT H.`id_host`, H.`groupid` ,H.`id_plan`,HD.`value` as RDB, CO.`tarifa` as Tarifa
						FROM `bm_host` H
							LEFT JOIN `bm_host_groups` HG ON  HG.`groupid`=H.`groupid`
							LEFT JOIN `bm_host_detalle` HD ON HD.`id_host`=H.`id_host`
							LEFT JOIN `bm_colegios` CO ON CO.`rdb`=HD.`value`
						WHERE 
							HG.`type`='ENLACES' AND 
							HD.`id_feature` = 1 AND
							H.`borrado` = 0 
						GROUP BY `id_host`";
		
		$get_host_result = $this->conexion->queryFetch($get_host_sql);
		
		foreach ($get_host_result as $keyHost => $host) {	
			$host_act[] = $host['id_host'];
			$datos[$host['id_host']]['id_host'] = $host['id_host'];
			$datos[$host['id_host']]['groupid'] = $host['groupid'];
			$datos[$host['id_host']]['id_plan'] = $host['id_plan'];
			$datos[$host['id_host']]['avg_availability'] = 0;
			$datos[$host['id_host']]['avg_locD'] = 0;
			$datos[$host['id_host']]['avg_locU'] = 0;
			$datos[$host['id_host']]['avg_vel'] = 0;
			$datos[$host['id_host']]['locD'] = 0;
			$datos[$host['id_host']]['locU'] = 0;
			$datos[$host['id_host']]['dst_availability'] = 0;
			$datos[$host['id_host']]['dst_vel'] = 0;
			$datos[$host['id_host']]['tarifa_inicial'] = $host['Tarifa'];;
			
		}
		
		//Fix FDT
		
		//Tabla de fix valid 10
		
		$table_tmp_1_sql = "CREATE TEMPORARY TABLE vfix SELECT `id_host`, `id_item`, UNIX_TIMESTAMP(SUBSTRING( FROM_UNIXTIME(`clock`), 1, 13 )) as clockHOUR , `id_history`
								FROM `bm_history` 
								WHERE  `id_item` IN (38,42) AND 
									`clock` between unix_timestamp('$y-$mes-01') and unix_timestamp(adddate('$y-$mes-01', interval 1 month))-1 AND
                                    `id_host` IN ". $this->conexion->arrayToIN($host_act)." AND `valid` = 10";
									
		$this->conexion->query($table_tmp_1_sql);
		
		//Tabla de fix disp
		
		$table_tmp_2_sql = "CREATE TEMPORARY TABLE dfix SELECT 
								HI.`id_host`,
								HI.`id_item`, 
								UNIX_TIMESTAMP(SUBSTRING( FROM_UNIXTIME(HI.`clock`), 1, 13 )) as clockHOUR,
								COUNT(*) As dipHOUR ,
								IF(C.`close` != 'OPERADOR', 1,0) as valid
							FROM   `bm_close` C
							LEFT JOIN `bm_history` HI ON C.`id_history` =HI.`id_history` 
							WHERE HI.`clock` between unix_timestamp('$y-$mes-01') and unix_timestamp(adddate('$y-$mes-01', interval 1 month))-1 AND HI.`id_item` =1 AND
							 HI.`id_host` IN ". $this->conexion->arrayToIN($host_act)."
							GROUP BY HI.`id_host`,HI.`id_item`,clockHOUR
							
							UNION
							SELECT `id_host`,`id_item`, UNIX_TIMESTAMP(SUBSTRING( FROM_UNIXTIME(`clock`), 1, 13 )) as clockHOUR , COUNT(*) As dipHOUR, '1' as valid FROM `bm_history` 
							WHERE  `id_item` IN (1) AND 
							`clock` between unix_timestamp('$y-$mes-01') and unix_timestamp(adddate('$y-$mes-01', interval 1 month))-1 AND
							               `id_host` IN ".$this->conexion->arrayToIN($host_act)." AND `value` = 1
							                GROUP BY `id_host`,`id_item`,clockHOUR";
																						   
		$this->conexion->query($table_tmp_2_sql);
														   
		//Creando tabla temporal de estado de disponiblidad agregadas por el fix
		
		$table_tmp_3_sql = "CREATE TEMPORARY TABLE fixFDT SELECT `id_host`,vfix.`id_item`,`id_history`, clockHOUR ,dipHOUR, valid
								FROM  vfix  
									LEFT JOIN  dfix USING(`id_host`,clockHOUR)
								WHERE valid IS NOT NULL";
		
		$this->conexion->query($table_tmp_3_sql);
		
		//Cargando Disponibilidad
		
		$get_dip_host_sql = "SELECT 
								DATE_FORMAT(from_unixtime(HI.clock), '%Y/%m') as clock, HI.`id_host`,HI.`id_item`,
								avg(if(HI.`value`=0 and (C.`close`='OPERADOR' or C.`close` is null or C.`close`=''),0,1))*100 as avg_availability
							FROM  `bm_history` HI 
								LEFT OUTER JOIN `bm_close` C USING(`id_history`)
							WHERE 
								HI.`id_item`=1 AND
								HI.`valid` <> 3  AND 
								HI.`clock` between unix_timestamp('$y-$mes-01') and unix_timestamp(adddate('$y-$mes-01', interval 1 month))-1 AND
								HI.`id_host` IN ". $this->conexion->arrayToIN($host_act).
							" GROUP BY HI.`id_host`,DATE_FORMAT(from_unixtime(clock), '%Y/%m')";

		$get_dip_host_result = $this->conexion->queryFetchAllAssoc($get_dip_host_sql);
		
		if($get_dip_host_result) {			
			foreach ($get_dip_host_result as $key => $availability) {
				$datos[$availability['id_host']]['avg_availability'] =  $availability['avg_availability'];
			}
		}
		
		//Cargando Local Download

		$get_loc_down_sql= "SELECT 
								DATE_FORMAT(from_unixtime(HI.`clock`), '%Y/%m') as clock, 
								HI.`id_host`,
								HI.`id_item`,
							    avg(
							    	 CASE WHEN (HI.`value`/(P.`locD`*1024)*100<100 && HI.valid != 10) THEN ( HI.`value`/(P.`locD`*1024)*100 )
							    	 	  WHEN (HI.`value`/(P.`locD`*1024)*100>100 && HI.valid != 10) THEN 100
							    	 	  WHEN (HI.valid = 10 && FIX.valid = 1 ) THEN 100
							    	 	  WHEN (HI.valid = 10 && FIX.valid = 0  && FIX.dipHOUR = 0) THEN 0
							    	 	  WHEN (HI.valid = 10 && FIX.valid IS NULL ) THEN 0
							    	 	  WHEN (HI.valid < 0) THEN 100
							    	 END
							    ) as avg_locD,
							    P.`locD`
							FROM  `bm_history` HI 
								LEFT JOIN `bm_host` H ON HI.`id_host`=H.`id_host`
                                LEFT JOIN `bm_plan` P ON H.`id_plan`=P.`id_plan`
                                LEFT JOIN `fixFDT` FIX ON FIX.`id_history`=HI.`id_history`
							WHERE 
								HI.`id_item`=38 AND 
								HI.`valid` <> 3  AND 
								HI.`clock` between unix_timestamp('$y-$mes-01') and unix_timestamp(adddate('$y-$mes-01', interval 1 month))-1 AND
								HI.`id_host` IN ". $this->conexion->arrayToIN($host_act).
							" GROUP BY HI.`id_host`,DATE_FORMAT(from_unixtime(clock), '%Y/%m') ;";
							
		$get_loc_down_result = $this->conexion->queryFetchAllAssoc($get_loc_down_sql);
			
		if($get_loc_down_result){
			foreach ($get_loc_down_result as $key => $locDown) {
				$datos[$locDown['id_host']]['avg_locD'] =  $locDown['avg_locD'];
				$datos[$locDown['id_host']]['locD'] =  $locDown['locD'];
			}				
		}

		//Cargando Local Bajada

		$get_loc_upload_sql= "SELECT
								DATE_FORMAT(from_unixtime(HI.`clock`), '%Y/%m') as clock, 
								HI.`id_host`,
								HI.`id_item`,
							    avg(
							    	 CASE WHEN (HI.`value`/(P.`locU`*1024)*100<100 && HI.valid != 10) THEN ( HI.`value`/(P.`locU`*1024)*100 )
							    	 	  WHEN (HI.`value`/(P.`locU`*1024)*100>100 && HI.valid != 10) THEN 100
							    	 	  WHEN (HI.valid = 10 && FIX.valid = 1 ) THEN 100
							    	 	  WHEN (HI.valid = 10 && FIX.valid = 0  && FIX.dipHOUR = 0) THEN 0
							    	 	  WHEN (HI.valid = 10 && FIX.valid IS NULL ) THEN 0
							    	 	  WHEN (HI.valid < 0) THEN 100
							    	 END
							    ) as avg_locU,
							    P.`locU`
							FROM  `bm_history` HI
								LEFT JOIN `bm_host` H ON HI.`id_host`=H.`id_host`
                               	LEFT JOIN `bm_plan` P ON H.`id_plan`=P.`id_plan`
                                LEFT JOIN `fixFDT` FIX ON FIX.`id_history`=HI.`id_history`
							WHERE 
								HI.`id_item`=42 AND 
								HI.`valid` <> 3  AND 
								HI.`clock` between unix_timestamp('$y-$mes-01') and unix_timestamp(adddate('$y-$mes-01', interval 1 month))-1 AND
								HI.`id_host` IN ". $this->conexion->arrayToIN($host_act).
							" GROUP BY HI.`id_host`,DATE_FORMAT(from_unixtime(clock), '%Y/%m') ;";
							
		$get_loc_upload_result = $this->conexion->queryFetchAllAssoc($get_loc_upload_sql);
			
		if($get_loc_upload_result){
			foreach ($get_loc_upload_result as $key => $locUpload) {
				$datos[$locUpload['id_host']]['avg_locU'] =  $locUpload['avg_locU'];
				$datos[$locUpload['id_host']]['locU'] =  $locUpload['locU'];
			}				
		}
		
		//Insertando o actualizando valores
		
		$insert_sql = "INSERT INTO `bm_enlace` (`fecha`, `id_host`, `groupid`, `tarifa_inicial`, `id_plan`, `locD`, `locU`, `avg_availability`, `avg_locD`, `avg_locU`, `avg_vel`) VALUES "; 
		
		$values = array();
		foreach ($datos as $key => $host_close) {
			$values[] = "('$y-$mes-00', '".$host_close['id_host']."', 
						'".$host_close['groupid']."', 
						'".$host_close['tarifa_inicial']."', 
						'".$host_close['id_plan']."', 
						'".$host_close['locD']."', 
						'".$host_close['locU']."', 
						'".$host_close['avg_availability']."', 
						'".$host_close['avg_locD']."', 
						'".$host_close['avg_locU']."', 
						'".$host_close['avg_vel']."')";
		}

		$values = join(",", $values);
		
		$insert_final = $insert_sql.$values." ON DUPLICATE KEY UPDATE 
										 	`fecha`='$y-$mes-00' , 
										 	`tarifa_inicial`=VALUES(`tarifa_inicial`),
										 	`id_plan`=VALUES(`id_plan`),
											`locD`=VALUES(`locD`),
											`locU`=VALUES(`locU`),
											`avg_availability`=VALUES(`avg_availability`),
											`avg_locD`=VALUES(`avg_locD`),
											`avg_locU`=VALUES(`avg_locU`)";
											
		$insert_final = trim(implode(' ', preg_split('/\s+/', $insert_final)));
											
		$insert_result = $this->conexion->query($insert_final);
		
		if($insert_result) {
			//Actualizando promedio vel 
			
			$update_avg_vel = "UPDATE `bm_enlace` SET `avg_vel` = (`avg_locU`+`avg_locD`)/2 WHERE `fecha` = '$y-$mes-00';";
			$this->conexion->query($update_avg_vel);
			
			//Calculando Descuentos availability
   
				$update_dst_av1 = "UPDATE `bm_enlace` SET `dst_availability` = (100-14.0) WHERE `fecha` = '$y-$mes-00' AND `avg_availability` < 90.0 ;";
			$this->conexion->query($update_dst_av1);
			$update_dst_av2 = "UPDATE `bm_enlace` SET `dst_availability` = (100-9.6) WHERE `fecha` = '$y-$mes-00' AND `avg_availability` >= 90.0  AND `avg_availability` <= 92.9 ;";
			$this->conexion->query($update_dst_av2);
			$update_dst_av3 = "UPDATE `bm_enlace` SET `dst_availability` = (100-8.7) WHERE `fecha` = '$y-$mes-00' AND `avg_availability` > 92.9  AND `avg_availability` <= 94.9 ;";
			$this->conexion->query($update_dst_av3);
			$update_dst_av4 = "UPDATE `bm_enlace` SET `dst_availability` = (100-0) WHERE `fecha` = '$y-$mes-00' AND `avg_availability` > 94.9;";
				$this->conexion->query($update_dst_av4);
   
				//Calculando Descuentos velo
			
			$update_dst_vel1 = "UPDATE `bm_enlace` SET `dst_vel` = (100-14.7) WHERE `fecha` = '$y-$mes-00' AND `avg_vel` < 70.0 ;";
			$this->conexion->query($update_dst_vel1);
			$update_dst_vel2 = "UPDATE `bm_enlace` SET `dst_vel` = (100-10.1) WHERE `fecha` = '$y-$mes-00' AND `avg_vel` >= 70.0  AND `avg_vel` <= 89.9";
			$this->conexion->query($update_dst_vel2);
			$update_dst_vel3 = "UPDATE `bm_enlace` SET `dst_vel` = (100-9.2) WHERE `fecha` = '$y-$mes-00' AND `avg_vel` > 89.9  AND `avg_vel` <= 99.9";
			$this->conexion->query($update_dst_vel3);
			$update_dst_vel4 = "UPDATE `bm_enlace` SET `dst_vel` = (100-0) WHERE `fecha` = '$y-$mes-00' AND `avg_vel` > 99.9 ;";
			$this->conexion->query($update_dst_vel4);
			
			//Calculando Pagos
			
			$update_pagos = "UPDATE `bm_enlace` SET `apagar` = `tarifa_inicial`-(`tarifa_inicial`*(100-`dst_availability`)/100) - (`tarifa_inicial`*(100-`dst_vel`)/100) WHERE `fecha` = '$y-$mes-00';";
			$this->conexion->query($update_pagos);

		}
					
		$valida_crontab = $this->_crontab("fdt_crontab","finish");		
	}	
	
	public function ping()
	{
	    $transformMaster = false;
        
		$urlMaster = $this->parametro->get('BSWMASTER').':'.$this->parametro->get('BSW_PORT_API');
		
		$urlSlave = $this->parametro->get('BSWSLAVE').':'.$this->parametro->get('BSW_PORT_API');
		
		$urlActive = $this->parametro->get('BSW_SERVER').':'.$this->parametro->get('BSW_PORT_API');
		
        //Is Master Active
		if($urlActive === $urlMaster) {
			$isMasterActive = true;
		} elseif ($urlActive == $urlSlave) {
			$isMasterActive = false;
		} else {
		    $urlActive = $this->parametro->get('BSWMASTER');
		    $isMasterActive = true;
		}
        
        //I am the master
        if(URL_BASE_FULL == $this->parametro->get('BSWMASTER')."/"){
            $isMasterExec = true;
        }  else {
            $isMasterExec = false;
        }

        if($isMasterExec == true) {
            $urlTest = $urlSlave;
            $urlTestNotPort = $this->parametro->get('BSWSLAVE');
        } else {
            $urlTest = $urlMaster;
            $urlTestNotPort = $this->parametro->get('BSWMASTER');
        }
        
        //Send mail 
        if($isMasterExec == false && $isMasterActive == true) {
            $sendMail = true;
        } else {
            $sendMail = false;
        }
            
        //Clean Other server
        $this->conexion->query("DELETE FROM `bm_nodos` WHERE `nodo` NOT IN ('$urlMaster','$urlSlave');");
        
        if($urlMaster == $urlSlave) {
           $this->conexion->query("INSERT INTO `bm_nodos` (`nodo`, `status`, `fechahora`, `url_ping`,`mail`) VALUES ('$urlTest', 'active', NOW(),'$urlTest/api/ping','false') ON DUPLICATE KEY UPDATE `status` = 'active',  `fechahora` = NOW(), `mail` = 'false';");   
           exit; 
        } else {
           $this->conexion->query("INSERT INTO `bm_nodos` (`nodo`, `status`, `fechahora`, `url_ping`,`mail`) VALUES ('$urlTest', 'inactive', NOW(),'$urlTest/api/ping','false') ON DUPLICATE KEY UPDATE  `fechahora` = NOW(), `mail` = 'false';"); 
        }

        $retry = 0;
        $datos = 'None';
        
        $finished = false;  
        
        while ( ! $finished  ) {
                    
            $this->curl->cargar($urlTest.'/api/ping',1);
            
            $this->curl->ejecutar();
        
            $datos = $this->curl->getContent(1);
                        
            $retry++;
            
            if (( $retry >= self::$retryPing ) || ( $datos === 'Pong' )) {
                $finished = true;
            }
                        
            sleep(2);
           
        }
		
		if($datos !== 'Pong') {
			
            if($isMasterExec == true ){
                $this->logs->error("The slave server does not respond to ping: ",$urlTest,'logs_server');
            } else {
                $this->logs->error("The master server does not respond to ping: ",$urlTest,'logs_server');
            }
            
            $master = $this->parametro->get('BSWMASTER');
        
            $slave = $this->parametro->get('BSWSLAVE');
                           
            if($transformMaster == true  && $isMasterExec == false ) {

                //Me transformo en master :)
                
                $msg = "El servidor '$urlTest' no responde al ping por lo cual el esclavo pasa a maestro";
                
                $this->conexion->InicioTransaccion();
                
                $updateMaster = "UPDATE `Parametros` SET `valor` = '$slave' WHERE `nombre` = 'BSWMASTER';";
                
                $updateMaster_R = $this->conexion->query($updateMaster);
                
                $updateSlave = "UPDATE `Parametros` SET `valor` = '$master' WHERE `nombre` = 'BSWSLAVE';";
                
                $updateSlave_R = $this->conexion->query($updateSlave);

                $updateActive = "UPDATE `Parametros` SET `valor` = '$slave' WHERE `nombre` = 'BSW_SERVER';";
                
                $updateActive_R = $this->conexion->query($updateActive);
                                        
                if($updateMaster_R && $updateSlave_R && $updateActive_R) {
                    $this->conexion->commit();
                } else {
                    $this->conexion->rollBack();
                }
                                            
            }  else {
                
                $msg = "El servidor '$urlTest' no responde al ping";
                
                if($isMasterExec == true) {
                    $updateActive = "UPDATE `Parametros` SET `valor` = '$master' WHERE `nombre` = 'BSW_SERVER';";
                    
                    $updateActive_R = $this->conexion->query($updateActive);               
                } else {
                    $updateActive = "UPDATE `Parametros` SET `valor` = '$slave' WHERE `nombre` = 'BSW_SERVER';";
                    
                    $updateActive_R = $this->conexion->query($updateActive);                      
                }    
                
            }
 

            $to = $this->parametro->get('MAIL_TO_ERROR_REPORT','soporte@bsw.cl');
            $from_user = $this->parametro->get('MAIL_FROM_USER_REPORT','bMonitor');
            $from_email = $this->parametro->get('MAIL_FROM_MAIL_REPORT','carlos@bsw.cl');
                          

			$mail_send = $this->conexion->queryFetch("SELECT `mail` FROM `bm_nodos` WHERE `nodo`='$url'");
				
			if((!$mail_send  || ($mail_send && ($mail_send[0]['mail'] == 'true' ))) && $sendMail == true) {
				$this->basic->mail($to, $from_user, $from_email,"Error: Nodo inactivo",$msg);
			}
			
			$this->conexion->query("UPDATE `bm_nodos` SET `status` = 'inactive' WHERE `nodo` = '$urlTest';");
		} else {
			$this->conexion->query("UPDATE `bm_nodos` SET `status` = 'active' WHERE `nodo` = '$urlTest';");
		}
	}

	public function getHostStatus()
	{
		
		//Borrar datos Anteriores 
		
		$this->conexion->query("/* BSW */ DELETE  FROM `bm_availability` WHERE  `fechahora` < ADDDATE(NOW(), INTERVAL - 60 MINUTE);");
		
		$DASHBOARD_DELAY_MULTIPLO_MONITOR_SONDA = $this->parametro->get('DASHBOARD_DELAY_MULTIPLO_MONITOR_SONDA',2);
		
		//Ingresando nuevos valores (Status de las sondas)
		$set_status_host_snmp_sql = 'INSERT INTO `bm_availability` (`fechahora`, `id_host`, `groupid`, `host`, `STATUS_SNMP`,`updateSNMP`,`dns`,`nextcheck` , `STATUS_SONDA`) 
										(SELECT NOW() as "fechahora", H.`id_host`, H.`groupid`, H.`host`, IF(IP.`check_ok` > DATE_SUB(NOW(), INTERVAL 2100 SECOND),1,0) as "STATUS_SNMP", 
											IF(`check_ok` = "0000-00-00 00:00:00" OR `check_ok` IS NULL , "1960-01-01 00:00:00",`check_ok`) AS updateSNMP, H.`codigosonda` as "dns" , `nextcheck` , 0 as "STATUS_SONDA"
										FROM `bm_item_profile` IP
											LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host`
										WHERE IP.`id_item` =1 AND H.`status`=1  AND  H.`borrado` = 0 GROUP BY H.`id_host`)
										ON DUPLICATE KEY UPDATE  `host`=VALUES(`host`),`dns`=VALUES(`dns`),`groupid`=VALUES(`groupid`),`STATUS_SNMP`=VALUES(`STATUS_SNMP`),`updateSNMP`=VALUES(`updateSNMP`), `nextcheck`=VALUES(`nextcheck`), `fechahora`=VALUES(`fechahora`)';

		$set_status_host_snmp_result = $this->conexion->query($set_status_host_snmp_sql);
		
		if($set_status_host_snmp_result) {
				
			$itemStatus = $this->parametro->get("DASHBOARD_ITEM_MONITOR_SONDA",4);
			
			$DASHBOARD_DELAY_MULTIPLO_MONITOR_SONDA_BSW = $this->parametro->get('DASHBOARD_DELAY_MULTIPLO_MONITOR_SONDA_BSW',2);
			
			$set_status_host_sonda_sql = 'INSERT INTO `bm_availability` (`fechahora`, `id_host`, `groupid`, `host`, `STATUS_SONDA`,`updateSONDA`,`dns`, `STATUS_SNMP`) 
			( SELECT NOW() as "fechahora", H.`id_host`, H.`groupid`, H.`host`, IF(IP.`lastclock` > ( UNIX_TIMESTAMP(NOW()) - 2100 ),1,0) as "STATUS_SONDA", 
											IF(IP.`lastclock` IS NULL , "1970-01-01 00:00:00" ,FROM_UNIXTIME(IP.`lastclock`))  as "updateSONDA", H.`codigosonda` as "dns" , 0 as "STATUS_SNMP"
										FROM `bm_item_profile` IP
											LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host`
										WHERE IP.`id_item` =4 AND H.`status`=1  AND  H.`borrado` = 0 GROUP BY H.`id_host` )
			ON DUPLICATE KEY UPDATE `host`=VALUES(`host`),`dns`=VALUES(`dns`),`groupid`=VALUES(`groupid`),`STATUS_SONDA`=VALUES(`STATUS_SONDA`),`updateSONDA`=VALUES(`updateSONDA`), `fechahora`=VALUES(`fechahora`)';
			
			$set_status_host_sonda_result = $this->conexion->query($set_status_host_sonda_sql);
			
			if(!$set_status_host_sonda_result) {
				return false;
			}	
		} else {
			return false;
		}
	}

	public function fixMonitores()
	{
		$getMonitores_error_sql = "SELECT H.`id_host`,IG.`id_item`,I.`snmp_community`, I.`snmp_oid`, I.`snmp_port`
					FROM `bm_host` H
					LEFT JOIN `bm_items_groups` IG USING(`groupid`) 
					LEFT JOIN `bm_items` I ON  I.`id_item`=IG.`id_item`
					LEFT OUTER JOIN `bm_item_profile` IP ON IP.`id_host`=H.`id_host` AND IP.`id_item`=IG.`id_item`
					WHERE IP.`id_item` IS NULL AND H.`borrado` = 0";
					
		$getMonitores_error_result = $this->conexion->queryFetch($getMonitores_error_sql);
		
		if($getMonitores_error_result) {
			
			$insert_monitor_profile = "INSERT INTO `bm_item_profile` (`id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, `nextcheck`) VALUES ";
			
			foreach ($getMonitores_error_result as $key => $host) {
				$insert_monitor_value[] = '('.$host['id_item'].','.$host['id_host'].',\''.$host['snmp_oid'].'\',\''.$host['snmp_port'].'\',\''.$host['snmp_community'].'\', NOW())';
			}
			
			$insert_monitor_profile_f = join(",", $insert_monitor_value);
			$insert_monitor_profile_f = $insert_monitor_profile.$insert_monitor_profile_f;
			$result_monitor_profile = $this->conexion->query($insert_monitor_profile_f);
			
		}
		
	}
	
	public function setStatus()
	{
	    /*
		$UPDATE_BLOCK_POLLER_SQL = "UPDATE `bm_item_profile` IP 
			LEFT JOIN `bm_items` I USING(`id_item`) 
			LEFT JOIN `bm_host`	H ON IP.`id_host`=H.`id_host`
			LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=H.`groupid` 
			SET IP.`status` = 'pendiente' WHERE  I.`type_poller` = 'snmp' AND IP.`status` IN ('process','processr') AND IP.`nextcheck` < DATE_SUB(NOW(), INTERVAL HG.`delay`*2 SECOND)";
		$this->conexion->query($UPDATE_BLOCK_POLLER_SQL);*/
		$this->getHostStatus();
	}
	
	public function limpiatraps()
	{
		$TRAPS_INSERT_TABLE = $this->parametro->get('TRAPS_INSERT_TABLE',false);
		
		if($TRAPS_INSERT_TABLE)	 {
			$TRAPS_INSERT_TABLE_HOST = $this->parametro->get('TRAPS_INSERT_TABLE_HOST','localhost');
			$TRAPS_INSERT_TABLE_BDNAME = $this->parametro->get('TRAPS_INSERT_TABLE_BDNAME','bswtraps');
			$TRAPS_INSERT_TABLE_USER = $this->parametro->get('TRAPS_INSERT_TABLE_USER','bswtraps');
			$TRAPS_INSERT_TABLE_PASS = $this->parametro->get('TRAPS_INSERT_TABLE_PASS','bswtraps321');
			
			$conn = $this->conexion->connectR('mysql', $TRAPS_INSERT_TABLE_HOST, $TRAPS_INSERT_TABLE_USER, $TRAPS_INSERT_TABLE_PASS, $TRAPS_INSERT_TABLE_BDNAME);
		
			if($conn) {
				$TRAPS_INSERT_TABLE_TABLE = $this->parametro->get('TRAPS_INSERT_TABLE_TABLE','traps');
				
				$insert_sql = "DELETE  FROM `$TRAPS_INSERT_TABLE_TABLE` WHERE  `FECHA_HORA` < ADDDATE(NOW(), INTERVAL - 10 MINUTE) AND `TIPO` != 'DISP'";

				$valid = $conn->query($insert_sql);
				
				if(!$valid) {
					$this->logs->error("Error al limpiar traps a base remota:",$conn->errorInfo(),"logs_trap");
				}
			} else {
				$this->logs->error("Error al limpiar traps a base remota:",NULL,"logs_trap");
			}
			
		}
		
	}
	
	public function traps()
	{
		
		$TRAPS_INSERT_TABLE = $this->parametro->get('TRAPS_INSERT_TABLE',false);
		
		if($TRAPS_INSERT_TABLE)	 {
			$TRAPS_INSERT_TABLE_HOST = $this->parametro->get('TRAPS_INSERT_TABLE_HOST','127.0.0.1');
			$TRAPS_INSERT_TABLE_BDNAME = $this->parametro->get('TRAPS_INSERT_TABLE_BDNAME','bswtraps');
			$TRAPS_INSERT_TABLE_USER = $this->parametro->get('TRAPS_INSERT_TABLE_USER','bswtraps');
			$TRAPS_INSERT_TABLE_PASS = $this->parametro->get('TRAPS_INSERT_TABLE_PASS','bswtraps321');
			
			$conn = $this->conexion->connectR('mysql', $TRAPS_INSERT_TABLE_HOST, $TRAPS_INSERT_TABLE_USER, $TRAPS_INSERT_TABLE_PASS, $TRAPS_INSERT_TABLE_BDNAME);
		
			if($conn) {
					
				$TRAPS_INSERT_TABLE_TABLE = $this->parametro->get('TRAPS_INSERT_TABLE_TABLE','traps');
							
				$getHost_sql = "SELECT H.`host`,G.`name`,H.`dns`,P.plan, G.`trapd`,
									AV.`STATUS_SNMP`, AV.`STATUS_SONDA`,
									GROUP_CONCAT(HD.`value` ORDER BY HD.`id_feature` ASC ) AS value_feature , 
									GROUP_CONCAT(HD.`id_feature` ORDER BY HD.`id_feature` ASC) AS id_feature
								FROM `bm_availability` AV
								LEFT JOIN `bm_host` H ON H.`id_host`=AV.`id_host`
								LEFT JOIN `bm_plan` P ON P.`id_plan`=H.`id_plan`
								LEFT JOIN `bm_host_groups` G ON G.`groupid`=AV.`groupid`
								LEFT JOIN `bm_host_detalle` HD ON HD.`id_host`=AV.`id_host`
								WHERE ( AV.`STATUS_SONDA` = 0  OR  ( AV.`STATUS_SNMP` = 0 AND G.`snmp_monitor`='true' ) )  AND H.`borrado`=0 AND H.`status`=1
									AND HD.`id_feature` IN (1,23,26,27) GROUP BY AV.`id_host`";
	
				$getHost = $this->conexion->queryFetch($getHost_sql,"logs_trap");
	
				if($getHost) {
					
					//Borrando Host
					
					$getHost_Array = $this->conexion->arrayToIN($getHost,'dns',FALSE,TRUE);
					
					$delete_sql = "DELETE  FROM `$TRAPS_INSERT_TABLE_TABLE` WHERE  `TIPO` = 'DISP' AND `DNS`  NOT IN ".$getHost_Array;

					$valid = $conn->query($delete_sql);
					
					if(!$valid) {
						$this->logs->error("Error al limpiar traps a base remota , QUERY: $delete_sql ERROR:",$conn->errorInfo(),"logs_trap");
					} else {
						$this->logs->debug("Limpiar traps a base remota  OK, QUERY: ",$delete_sql,"logs_trap");
					}
					
					foreach ($getHost as $key => $value) {
						
						//Value
						
						$ENCABEZADO = str_replace("#",$value['name'],$this->parametro->get("BSWTRAP"));
						
						$comuna="";
						$nodo="N00";
						$cuadrante="C00";
						$rut="0-0";
						$rdb="0";
						
						if($value['STATUS_SONDA'] == 0) {
							$status = 'CRITICAL';
						} elseif ($value['STATUS_SONDA'] == 1  && $value['STATUS_SNMP'] == 0) {
							$status = 'WARNING';
						}
						
						$feature_value = explode(',', $value['value_feature']);
						$feature_id = explode(',', $value['id_feature']);
						
						for ($i=0; $i < count($feature_id); $i++) { 
							if($feature_id[$i] == 1) {
								$rdb = $feature_value[$i];
							} elseif ($feature_id[$i] == 23) {
								$comuna = $feature_value[$i];
							} elseif ($feature_id[$i] == 26) {
								$nodo = $feature_value[$i];
							} elseif ($feature_id[$i] == 27) {
								$cuadrante = $feature_value[$i];
							}
						}
						
						if ($nodo == "") {
							$nodo="N00" ;
						}
						
						if ($cuadrante == "") {
							$cuadrante="C00";
						}
						
						if ($comuna == "") {
							$comuna="SIN COMUNA";
						}
					
						if ($rdb == "") {
							$rdb="SIN RDB";
						}
						
						$insert_sql = "INSERT INTO `$TRAPS_INSERT_TABLE_TABLE` (`FECHA_HORA`, `ENCABEZADO`, `TRAPCRIT`, `RDB`, `RUT`, `NODO`, `CUADRANTE`, `COMUNA` , `HOST`, `DNS`, `GROUP`, `PLAN`, `OID`, `TIPO`, `VALORESPERADO`, `VALOROBTENIDO`, `LOGIN`) VALUES ";
					
						$insert_sql .= sprintf("(NOW(),'%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s') ON DUPLICATE KEY UPDATE `FECHA_HORA_UPDATE`=NOW()",
							$ENCABEZADO,
							$status,
							$rdb,
							$rut,
							$nodo,
							$cuadrante,
							$comuna,
							$value['host'],
							$value['dns'],
							$value['name'],
							$value['plan'],
							$value['trapd'],
							'DISP',
							'1',
							'0',
							'MJ'
						);						
						
						$valid = $conn->query($insert_sql);
						
						if(!$valid) {
							$this->logs->error("Error al insertar traps a base remota , QUERY: $insert_sql ERROR:",$conn->errorInfo(),"logs_trap");
						}
						
					}
				} else {
					$this->logs->error("Error al insertar traps a base remota:",$this->conexion->errorInfo(),"logs_trap");
				}

			} else {
				$this->logs->error("Error al insertar traps a base remota:",NULL,"logs_trap");
			}
		}
	}
	
	public function fixHistory($minID = FALSE, $maxID = FALSE, $tableQ = FALSE)
	{
		$time_start = microtime(true);
			
		if($minID === FALSE && $maxID === FALSE &&  !is_numeric($minID)  && !is_numeric($maxID)) {
			
			$get_id = 'SELECT MAX(`id_history`) max , MIN(`id_history`) min FROM bm_q;';
			
			$get_id_result = $this->conexion->queryFetch($get_id);
			
			if($get_id_result) {
					
				$maxID = $get_id_result[0]['max'];
				$minID = $get_id_result[0]['min'];
				
			} else {
				exit;
			}
		
		}
		
		if($tableQ == 'true') {
			$table = 'bm_q';
		} else {
			$table = 'bm_history';
		}
		
		echo "Cambiando entre $minID y $maxID  ID \n";
		
		$get_item_sql = "SELECT `id_item`, `id_host`, `clock` ,`value` FROM $table 
								WHERE `id_item` IN (12,16,60,64,43,39,26,53,74)  AND value = 0 AND `id_history` >=  $minID AND `id_history` <= $maxID";
								
		$get_item_result =  $this->conexion->queryFetch($get_item_sql);
		
		if($get_item_result) {
			
			$insert_history="INSERT INTO `bm_history`  (`id_item`, `id_host`, `clock`, `value`, `valid`) VALUES ";
			
			foreach ($get_item_result as $key => $value) {
				
				if((int)$value['id_item'] == 12) {
					$value_int_insert[] = "(10, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(11, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(18, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(29, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
				} elseif ((int)$value['id_item'] == 16) {
					$value_int_insert[] = "(14, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(15, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(19, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(30, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";						
				} elseif ((int)$value['id_item'] == 60) {
					$value_int_insert[] = "(58, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(59, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(66, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(77, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";						
				} elseif ((int)$value['id_item'] == 64) {
					$value_int_insert[] = "(62, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(63, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(67, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(78, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";						
				} elseif ((int)$value['id_item'] == 43) {
					$value_int_insert[] = "(41, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(42, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(46, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(57, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";						
				} elseif ((int)$value['id_item'] == 39) {
					$value_int_insert[] = "(37, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(38, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(45, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(56, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";						
				} elseif ((int)$value['id_item'] == 26) {
					$value_int_insert[] = "(27, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(141, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";					
				} elseif ((int)$value['id_item'] == 53) {
					$value_int_insert[] = "(48, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(55, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";					
				} elseif ((int)$value['id_item'] == 74) {
					$value_int_insert[] = "(75, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";
					$value_int_insert[] = "(69, '".$value['id_host']."', '".$value['clock']."',".$value['value'].", 0)";					
				}
	
			}

			$sql_final = $insert_history.join(',',$value_int_insert).' ON DUPLICATE KEY UPDATE `valid`=VALUES(`valid`)';
			
			$resultInsert = $this->conexion->query($sql_final);
		}

		$time_end = microtime(true);
		$time = $time_end - $time_start;

		echo "Duro $time segundos\n";	
	}
}
?>