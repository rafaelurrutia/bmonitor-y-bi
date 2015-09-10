<?php

class tablas extends Control
{
//Función de zabbix que era usada en bmonitor1, pero no se encontraba en esta versión.      	
private function zbx_date2age($start_date, $end_date = 0, $utime = false)
{
   if(!$utime){
          $start_date=date('U',$start_date);
          if($end_date)
                 $end_date=date('U',$end_date);
          else
                 $end_date = time();
   }

   $time = abs($end_date-$start_date);
//SDI($start_date.' - '.$end_date.' = '.$time);
   $years = (int) ($time / (365*86400));
   $time -= $years*365*86400;

   $months = (int ) ($time / (30*86400));
   $time -= $months*30*86400;
    
   $days = (int) ($time / 86400);
   $time -= $days*86400;
   
   $hours = (int) ($time / 3600);
   $time -= $hours*3600;
   
   $minutes = (int) ($time / 60);
   $time -= $minutes*60;
   
   if($time >= 1){
          $seconds = round($time,2);
          $ms = 0;
   }
   else{
          $seconds = 0;
          $ms = round($time,3) * 1000;
   }
   
   $str =  (($years)?$years.'y ':'').
                 (($months)?$months.'m ':'').
                 (($days)?$days.'d ':'').
                 (($hours && !$years)?$hours.'h ':'').
                 (($minutes && !$years && !$months)?$minutes.'m ':'').
                 ((!$years && !$months && !$days && (!$ms || $seconds))?$seconds.'s ':'').
                 (($ms && !$years && !$months && !$days && !$hours)?$ms.'ms':'');
	return $str;
}
		

    private function date2str($format, $timestamp)
    {
        if ($timestamp == 0) {
            return $this->language->NEVER;
        } else {
            return date($format, $timestamp);
        }
    }

    function convert_units($value, $units)
    {
        // Special processing for unix timestamps
        if ($units == "unixtime") {
            $ret = date("Y.m.d H:i:s", $value);
            return $ret;
        }
        //Special processing of uptime
        if ($units == "uptime") {
            $ret = "";
            $days = floor($value / (24 * 3600));
            if ($days > 0) {
                $value = $value - $days * (24 * 3600);
            }
            $hours = floor($value / (3600));
            if ($hours > 0) {
                $value = $value - $hours * 3600;
            }
            $min = floor($value / (60));
            if ($min > 0) {
                $value = $value - $min * (60);
            }
            if ($days == 0) {
                $ret = sprintf("%02d:%02d:%02d", $hours, $min, $value);
            } else {
                $ret = sprintf("%d days, %02d:%02d:%02d", $days, $hours, $min, $value);
            }
            return $ret;
        }
        // Special processing for seconds   
        if ($units == "s") {
            return $this->zbx_date2age(0, $value, true);			
        }

        $u = "";

        // Special processing for bits (kilo=1000, not 1024 for bits)
        $kilo = 1024;
        if (($units == "b") || ($units == "bps")) {
            $abs = abs($value);

            if ($abs < $kilo) {
                $u = "";
            } else if ($abs < $kilo * $kilo) {
                $u = "K";
                $value = $value / $kilo;
            } else if ($abs < $kilo * $kilo * $kilo) {
                $u = "M";
                $value = $value / ($kilo * $kilo);
            } else {
                $u = "G";
                $value = $value / ($kilo * $kilo * $kilo);
            }

            if (round($value) == round($value, 2)) {
                $s = sprintf("%.0f", $value);
            } else {
                $s = sprintf("%.2f", $value);
            }

            return "$s $u$units";
        }

        if ($units == "") {
            if (round($value) == round($value, 2)) {
                return sprintf("%.0f", $value);
            } else {
                return sprintf("%.2f", $value);
            }
        }

        $abs = abs($value);
        // * MODIFIED BY BSW
        // * changed all 1024 for variable $munit = 1000
        // * This will fix error on show values on graph.
        $munit = 1000;
        if ($abs < $munit) {
            $u = "";
        } else if ($abs < $munit * $munit) {
            $u = "K";
            $value = $value / $munit;
        } else if ($abs < $munit * $munit * $munit) {
            $u = "M";
            $value = $value / ($munit * $munit);
        } else if ($abs < $munit * $munit * $munit * $munit) {
            $u = "G";
            $value = $value / ($munit * $munit * $munit);
        } else {
            $u = "T";
            $value = $value / ($munit * $munit * $munit * $munit);
        }
        // ** END MODIFICATION BY BSW

        if (round($value) == round($value, 2)) {
            $s = sprintf("%.0f", $value);
        } else {
            $s = sprintf("%.2f", $value);
        }

        return "$s $u$units";
    }

    private function getHostStatus()
    {

        //Borrar datos Anteriores

        $this->conexion->query("/* BSW */ TRUNCATE TABLE `bm_availability`;");

        //Ingresando nuevos valores (Status de las sondas)
        $set_status_host_sql = "SELECT 
                        NOW() as fechahora ,H.`id_host`,H.`groupid`,H.`host`,
                        SUM(IF((IP.status='ok') && (IP.`nextcheck` >= DATE_ADD(NOW(), INTERVAL - (I.`delay` * 3) SECOND)) && (IP.`id_item` = 1),1,0)) AS OK_SNMP,
                        SUM(IF((IP.status='error') && (IP.`id_item` = 1),1,0)) AS NOK_SNMP,
                        SUM(IF((IP.`prevvalue`=1) && (IP.`nextcheck` >= DATE_ADD(NOW(), INTERVAL - (I.`delay` * 3) SECOND)) && (IP.`id_item` = 26),1,0)) AS OK_SONDA,
                        SUM(IF(((IP.`prevvalue`=0) || (IP.`prevvalue`=NULL) ) && (IP.`id_item` = 26),1,0)) AS NOK_SONDA,
                        FROM_UNIXTIME(SUM(CASE WHEN IP.`id_item`= 1  THEN IP.`lastclock` END)) AS updateSNMP,
                        IF(IP.`id_item`= 1,IP.`okcheck`,0) as updateSNMPOK,
                        FROM_UNIXTIME(SUM(CASE WHEN IP.`id_item`= 26  THEN IP.`lastclock` END)) AS updateSONDA,
                        H.`dns`
                        FROM `bm_host`  H
                            LEFT OUTER JOIN `bm_item_profile` IP USING(`id_host`)
                            LEFT JOIN `bm_items` I ON IP.`id_item`=I.`id_item`
                            WHERE H.`status`=1 AND H.`borrado`=0 AND (IP.`id_item` IN (1,26) OR IP.`id_item` is NULL) GROUP BY H.`id_host`";

        $set_status_host = $this->conexion->queryFetch($set_status_host_sql);

        if ($set_status_host) {
            $insert = 'INSERT INTO `bm_availability` (`fechahora`, `id_host`, `groupid`, `host`, `OK_SNMP`, `OK_SONDA`, `NOK_SONDA`, `NOK_SNMP`,`updateSNMP`, `updateSNMPOK`,`updateSONDA`,`dns`) VALUES ';
            $values = array();
            foreach ($set_status_host as $key => $host) {

                if ($host['OK_SNMP'] == 0) {
                    $snmp_nok = 1;
                    $snmp_ok = 0;
                } elseif ($host['OK_SNMP'] == 1) {
                    $snmp_nok = 0;
                    $snmp_ok = 1;
                } else {
                    $snmp_nok = 1;
                    $snmp_ok = 0;
                }

                if ($host['OK_SONDA'] == 0) {
                    $sonda_nok = 1;
                    $sonda_ok = 0;
                } elseif ($host['OK_SONDA'] == 1) {
                    $sonda_nok = 0;
                    $sonda_ok = 1;
                } else {
                    $sonda_nok = 1;
                    $sonda_ok = 0;
                }

                if (($host['updateSNMP'] == 'NULL') || ($host['updateSNMP'] == '')) {
                    $updateSNMP = '0000-00-00 00:00:00';
                } else {
                    $updateSNMP = $host['updateSNMP'];
                }

                if (($host['updateSONDA'] == 'NULL') || ($host['updateSONDA'] == '')) {
                    $updateSONDA = '0000-00-00 00:00:00';
                } else {
                    $updateSONDA = $host['updateSONDA'];
                }

                if (($host['updateSNMPOK'] == 'NULL') || ($host['updateSNMPOK'] == '') OR ($host['updateSNMPOK'] == 0)) {
                    $updateSNMPOK = '0000-00-00 00:00:00';
                } else {
                    $updateSNMPOK = $host['updateSNMPOK'];
                }

                $values[] = '(NOW(),' . $host['id_host'] . ',' . $host['groupid'] . ",'" . $host['host'] . "',$snmp_ok, $sonda_ok, $sonda_nok, $snmp_nok,'" . $updateSNMP . "','" . $updateSNMPOK . "','" . $updateSONDA . "','" . $host['dns'] . "')";
            }

            $insert_final = $insert . join(',', $values);

            $result = $this->conexion->query($insert_final);

            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function estadoSistema()
    {
        $this->plantilla->cacheJSON('estadoSistema');

        //Generado Tabla Status
        $this->getHostStatus();

        //Generado grupo de host permitidos por el usuario

        $available_groups = $this->bmonitor->getGroupsHost(true, true);
        $available_hosts = $this->bmonitor->getAllHost(true, true);

        if (empty($_POST['rp'])) {
            $rp = 15;
        } else {
            $rp = $_POST['rp'];
        }

        if (empty($_POST['page'])) {
            $page = 15;
        } else {
            $page = $_POST['page'];
        }

        $sql = "SELECT HG.`name`,
						SUM(IF((AV.`NOK_SONDA`=1),1,0)) AS CRITICAL,
						SUM(IF(( (AV.`NOK_SNMP`=1) || (AV.`NOK_SNMP`=0)  ) && (HG.`snmp_monitor`=true) && (AV.`NOK_SONDA`=0),1,0)) AS WARNING
						FROM `bm_availability` AV
						LEFT JOIN `bm_host_groups` HG USING(`groupid`)
						WHERE AV.`groupid` IN " . $available_groups . " AND AV.`id_host` IN " . $available_hosts . " GROUP BY AV.`groupid`;";

        $result = $this->conexion->queryFetch($sql);

        $data['page'] = 1;
        $data['total'] = count($result);
        $data['rows'] = array();

        if ($result) {
            foreach ($result as $key => $group) {

                $data['rows'][] = array(
                    'id' => $key,
                    'cell' => array(
                        $group['name'],
                        $group['CRITICAL'],
                        $group['WARNING']
                    )
                );

            }
        }
        echo json_encode($data);
        $this->plantilla->cacheClose();
    }

    public function estadoBsw()
    {
        $status = $this->get_status();

        $data = array();
        $data['page'] = 1;
        $data['total'] = 4;
        $data['rows'] = array();

        //Inicio Numero de host
        $data['rows'][] = array(
            'id' => 1,
            'cell' => array(
                $this->language->S_NUMBER_OF_HOSTS,
                $status['hosts_count'],
                $status['hosts_count_monitored'] . " / " . $status['hosts_count_not_monitored'] . " / " . $status['hosts_count_deleted']
            )
        );
        //Fin Numero de host

        //Inicio Numero de item
        $data['rows'][] = array(
            'id' => 1,
            'cell' => array(
                $this->language->S_NUMBER_OF_ITEMS,
                $status['items_count'],
                $status['items_count_monitored'] . " / " . $status['items_count_disabled'] . " / " . $status['items_count_snmp'] . " / " . $status['items_count_bsw']
            )
        );
        //Fin Numero de item

        /*
         //Inicio Numero de Triger
         $data['rows'][] = array(
         'id' => 1,
         'cell' => array(
         $this->language->S_NUMBER_OF_TRIGGERS,
         $status['triggers_count'],
         $status['triggers_count_enabled']." / ".
         $status['triggers_count_disabled']." [ ".
         $status['triggers_count_on']." / ".
         $status['triggers_count_unknown']." / ".
         $status['triggers_count_off']." ] "
         )
         );
         //Fin Numero de Triger*/

        //Inicio Numero de Usuarios
        $data['rows'][] = array(
            'id' => 1,
            'cell' => array(
                $this->language->S_NUMBER_OF_USERS,
                $status['users_count'],
                $status['users_online']
            )
        );
        //Fin Numero de Usuarios

        echo json_encode($data);

    }

    /*
     public function ultimos20problemas()
     {
     $available_hosts =
    array(10055,10094,10123,10049,10073,10122,10124,10120,10054,10017,10072,10001,10016,10007,10004,10116);
     $alertas_query = 'SELECT * from qos_availability where hostid IN ' . $this->conexion->arrayToIN($available_hosts) .
    '  order by host';
     $alertas = $this->conexion->queryFetch($alertas_query);

     $rp = $_POST['rp'];
     $page = $_POST['page'];

     $pageStart = ($page-1)*$rp;

     $total = count($alertas);

     $data['page'] = $page;
     $data['total'] = $total;
     $data['rows'] = array();

     foreach ($alertas as $key => $alerta) {

     if ($alerta["prevvalue"] == 0 && $alerta["lastvalue"] == 1) $tt= "Equipo en recuperación";
     if ($alerta["prevvalue"] == 0 && $alerta["lastvalue"] == 0) $tt= "Equipo no responde";
     if ($alerta["prevvalue"] == 1 && $alerta["lastvalue"] == 0) $tt= "Equipo con posible falla";

     $data['rows'][] = array(
     'id' => $key,
     'cell' => array(
     $alerta["host"],
     $tt,
     $alerta["lastclock"],
     $alerta["nextchecka"],
     $alerta["dns"]
     )
     );

     }

     echo json_encode($data);
     }
     */

    public function ultimos20problemas()
    {

        //Generado grupo de host permitidos por el usuario

        $this->plantilla->cacheJSON('ultimos20problemas');

        $available_groups = $this->bmonitor->getGroupsHost(true, true);
        $available_hosts = $this->bmonitor->getAllHost(true, true);

        if (empty($_POST['rp'])) {
            $rp = 15;
        } else {
            $rp = $_POST['rp'];
        }

        if (empty($_POST['page'])) {
            $page = 15;
        } else {
            $page = $_POST['page'];
        }

        $data['page'] = 1;

        $sql = "SELECT * FROM `bm_availability` AV WHERE `OK_SNMP` != 1  AND `OK_SONDA` != 1 AND AV.`groupid` IN " . $available_groups . " AND AV.`id_host` IN " . $available_hosts;

        $result = $this->conexion->queryFetch($sql);

        $data['total'] = count($result);

        $data['rows'] = array();

        foreach ($result as $key => $alerta) {
            if ($alerta["OK_SNMP"] == 0 && $alerta["OK_SONDA"] == 1)
                $tt = "Equipo no responde solicitudes SNMP";
            if ($alerta["OK_SNMP"] == 0 && $alerta["OK_SONDA"] == 0)
                $tt = "Equipo no responde";
            if ($alerta["OK_SNMP"] == 1 && $alerta["OK_SONDA"] == 0)
                $tt = "Equipo no registra actividad";

            $data['rows'][] = array(
                'id' => $key,
                'cell' => array(
                    $alerta["host"],
                    $tt,
                    $alerta["updateSNMP"],
                    $alerta["updateSONDA"],
                    $alerta["dns"]
                )
            );

        }

        echo json_encode($data);
        $this->plantilla->cacheClose();
    }

    public function get_status()
    {
        $status = array();

        $urlHost = $this->parametro->get('URL_BASE');

        $urlHost = $urlHost . "/bmonitor/ping";

        $this->curl->cargar($urlHost, 1);

        $this->curl->ejecutar();

        $content = $this->curl->getContent(1);

        if ($content == 'OK') {
            $status["bsw_server"] = true;
        } else {
            $status["bsw_server"] = false;
        }
        /*
         $sql = 'SELECT COUNT(DISTINCT t.triggerid) as cnt '.
         ' FROM triggers t, functions f, items i, hosts h'.
         ' WHERE t.triggerid=f.triggerid '.
         ' AND f.itemid=i.itemid '.
         ' AND i.status='. $this->parametro->get('ITEM_STATUS_ACTIVE',0).
         ' AND i.hostid=h.hostid '.
         ' AND h.status='. $this->parametro->get('HOST_STATUS_MONITORED',0);

         $results = $this->conexion->queryFetch($sql);

         $status['triggers_count']=$results[0]['cnt'];

         $row= $this->conexion->queryFetch($sql.' and t.status=0');
         $status['triggers_count_enabled']=$row[0]['cnt'];

         $row= $this->conexion->queryFetch($sql.' and t.status=1');
         $status['triggers_count_disabled']=$row[0]['cnt'];

         $row= $this->conexion->queryFetch($sql.' and t.status=0 and t.value=0');
         $status['triggers_count_off']=$row[0]['cnt'];

         $row= $this->conexion->queryFetch($sql.' and t.status=0 and t.value=1');
         $status['triggers_count_on']=$row[0]['cnt'];

         $row= $this->conexion->queryFetch($sql.' and t.status=0 and t.value=2');
         $status['triggers_count_unknown']=$row[0]['cnt'];
         */
        $sql = 'SELECT COUNT(DISTINCT i.`id_item`) as cnt  FROM `bm_items` i LEFT JOIN `bm_items_groups` g USING(`id_item`)';

        $row = $this->conexion->queryFetch($sql);
        $status['items_count'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch($sql . ' WHERE g.status=1');
        $status['items_count_monitored'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch($sql . ' WHERE g.status=0');
        $status['items_count_disabled'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch($sql . " WHERE i.type_poller='snmp'");
        $status['items_count_snmp'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch($sql . " WHERE i.type_poller='bsw_agent'");
        $status['items_count_bsw'] = $row[0]['cnt'];

        $sql = 'SELECT COUNT(id_host) as cnt ' . ' FROM bm_host ';

        $row = $this->conexion->queryFetch($sql);
        $status['hosts_count'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch('SELECT COUNT(id_host) as cnt FROM bm_host WHERE status=' . $this->parametro->get('HOST_STATUS_MONITORED') . ' AND borrado=0');
        $status['hosts_count_monitored'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch('SELECT COUNT(id_host) as cnt FROM bm_host WHERE status=' . $this->parametro->get('HOST_STATUS_NOT_MONITORED') . ' AND borrado=0');
        $status['hosts_count_not_monitored'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch('SELECT COUNT(id_host) as cnt FROM bm_host WHERE borrado=1');
        $status['hosts_count_deleted'] = $row[0]['cnt'];

        // Usuarios
        $row = $this->conexion->queryFetch('SELECT COUNT(id_user) as cnt FROM ' . $this->protect->table_user);
        $status['users_count'] = $row[0]['cnt'];

        $sql = 'SELECT COUNT(DISTINCT u.`id_user`) AS count  FROM ' . $this->protect->table_user . ' u  WHERE u.`attempt_clock` > ADDDATE(NOW(), INTERVAL - `lifetime` SECOND)';

        $row_user_1 = $this->conexion->queryFetch($sql);

        $status['users_online'] = $row_user_1[0]['count'];

        return $status;

        /*

         $result=$this->conexion->queryFetch('SELECT count(*)/i.delay as qps '.
         ' FROM items i,hosts h '.
         ' WHERE i.status='.$this->parametro->get('ITEM_STATUS_ACTIVE').
         ' AND i.hostid=h.hostid '.
         ' AND h.status='.$this->parametro->get('HOST_STATUS_MONITORED').
         ' GROUP BY i.type,i.delay '.
         ' ORDER BY i.type, i.delay');
         if($result){
         foreach ($result as $key => $value) {
         $qps_total[] =  $value['qps'];
         }

         $status['qps_total']=array_sum($qps_total);
         return $status;
         } else {
         return false;
         }

         */
    }

    public function ultimafecha($fmGrupo = false,$fmEquipo = false)
    {

        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $var = $this->basic->setParamTable($_POST, '`bm_items`.`id_item`','asc');

        //Parametros Table

        $data = array();

        $data['page'] = $var->page;

        $data['rows'] = array();
                  
        if($fmGrupo == false && isset($_POST['fmGrupo']) && $_POST['fmGrupo'] > 0) {
            $fmGrupo = $_POST['fmGrupo'];
        } elseif (!is_numeric($fmGrupo)) {
            $fmGrupo = false;
        }
        
        if($fmEquipo == false && isset($_POST['fmEquipo']) && $_POST['fmEquipo'] > 0) {
            $fmEquipo = $_POST['fmEquipo'];
        } elseif (!is_numeric($fmEquipo)) {
            $fmEquipo = false;
        }

        if($_POST['fmSearch'] !== '') {
            $fmSearch = " AND `bm_items`.`descriptionLong` LIKE '%".$_POST['fmSearch']."%' ";
        } else {
            $fmSearch = "";
        }

        if($_POST['fmFilter'] == 1) {
            $fmFilter = " AND p.`lastvalue` IS NULL ";
        } elseif ($_POST['fmFilter'] == 2) {
            $fmFilter = " AND p.`lastvalue`  IS NOT NULL ";
        } else {
            $fmFilter = "";
        }
				       
        if ($fmGrupo && $fmEquipo) {

            //Valida el total de items
            $getCountItemSQL = 'SELECT count(p.`id_item_profile`) as total FROM `bm_items` 
								JOIN `bm_item_profile` p USING(`id_item`)
								JOIN `bm_host` ON `bm_host`.`id_host`=p.`id_host`
								JOIN bm_profiles_values PV ON PV.id_monitor=p.id_item
								WHERE `bm_host`.`groupid`=' . $fmGrupo . ' AND `bm_host`.`id_host`=' . $fmEquipo .
            ' AND p.`active`=' . $this->parametro->get('ITEM_STATUS_ACTIVE', 1) . $fmFilter . $fmSearch;

            $getCountItemRESULT = $this->conexion->queryFetch($getCountItemSQL);
			
			$data['queryCount'] = $getCountItemSQL;
			//$this->logs->error("Total de Items SQL: $getCountItemSQL"); //<--- sacar despues  
            if ($getCountItemRESULT && count($getCountItemRESULT) > 0) {
                $data['total'] = $getCountItemRESULT[0]['total'];
            } else {
                $data['total'] = 0;
                $data['rows'] = array();
                $this->basic->jsonEncode($data);
                exit ;
            }
			//$this->logs->error("La data[Total]: ".$data['total']);  //<--- sacar despues 
			//hace la consulta de los items datos
            $getItemsSQL = "SELECT `bm_host`.`id_host`,`bm_host`.`host`,  `bm_items`.`descriptionLong`,  `bm_items`.`id_item` ,`bm_items`.`type_item` , `bm_items`.`display`, `bm_items`.`unit`,
									p.`lastvalue`,p.`prevvalue`,p.`lastclock`
									FROM `bm_items` 
									JOIN `bm_item_profile` p USING(`id_item`)
									JOIN `bm_host` ON `bm_host`.`id_host`=p.`id_host`
									JOIN bm_profiles_values PV ON PV.id_monitor=p.id_item 
							WHERE `bm_host`.`groupid`=  $fmGrupo  AND `bm_host`.`id_host`=  $fmEquipo $fmSearch $fmFilter $var->sortSql $var->limitSql";

            $items = $this->conexion->queryFetch($getItemsSQL);
			//$this->logs->error("Consulta de data que trae la data para ULTIMNA FECHA ES:  $getItemsSQL"); ///<--- sacar despues 
            $data['rows'] = array();

            if ($items) {
                foreach ($items as $key => $tem) {

                    if (isset($tem["lastvalue"])) {
                        if ($tem["type_item"] == $this->parametro->get('ITEM_VALUE_TYPE_FLOAT', 'float')) {
                            $lastvalue = $this->convert_units($tem["lastvalue"], $tem["unit"]);
                        } else if ($tem["type_item"] == (string)$this->parametro->get('ITEM_VALUE_TYPE_UINT64', 'unit')) {
                            $lastvalue = $this->convert_units($app_value["lastvalue"], $app_value["units"]);
                        } else if ($tem["type_item"] == (string)$this->parametro->get('ITEM_VALUE_TYPE_TEXT', 'text')) {
                            $lastvalue = "...";
                        } else if ($tem["type_item"] == (string)$this->parametro->get('ITEM_VALUE_TYPE_STR', 'string')) {
                            $lastvalue = substr($tem["lastvalue"], 0, 20);
                            if (strlen($tem["lastvalue"]) > 20) {
                                $lastvalue .= " ...";
                            }
                        } else {
                            $lastvalue = "Unknown value type";
                        }

                    } else {
                        $lastvalue = "-";
                    }

                    $change = '';
                    if (isset($tem['lastvalue']) && isset($tem['prevvalue']) && ($tem['type_item'] == $this->parametro->get('ITEM_VALUE_TYPE_FLOAT', 'float') || $tem['type_item'] == $this->parametro->get('ITEM_VALUE_TYPE_UINT64', 'unit')) && ($tem['lastvalue'] - $tem['prevvalue'] != 0)) {
                        if ($tem['lastvalue'] - $tem['prevvalue'] < 0) {
                            $change = $this->convert_units($tem['lastvalue'] - $tem['prevvalue'], $tem['unit']);
                        } else {
                            $change = '+' . $this->convert_units($tem['lastvalue'] - $tem['prevvalue'], $tem['unit']);
                        }
                    }

                    //$url = '<a
                    // onclick="$(\'#graph_ultimafecha\').load(\'history/graph?group='.$_POST['fmGrupo'].'&host='.$_POST['fmEquipo'].'\');"
                    // class="button_graph"></a>';

                    if (($tem['type_item'] == 'float' && $tem['display'] == 'none') || ($tem['display'] == 'graph')) {
                        $url = '<a onclick="graph_display(' . $tem['id_item'] . ',' . $fmEquipo . ',' . $fmGrupo . ');" class="button_graph"></a>';
                    } else {
                        $url = '<a onclick="table_display(' . $tem['id_item'] . ',' . $fmEquipo . ',\'' . $tem['type_item'] . '\');" class="button_table"></a>';
                    }

                    $data['rows'][] = array(
                        'id' => $key,
                        'cell' => array(
                            'host' => $tem['host'],
                            'descriptionLong' => $tem['descriptionLong'],
                            'lastclock' => $this->date2str($this->parametro->get('DATE_FORMAT', 'd M Y H:i:s'), $tem['lastclock']),
                            'lastvalue' => utf8_encode($lastvalue),
                            'prevvaluediff' => $change,
                            'historical' => $url
                        )
                    );
                }
            }
            $this->basic->jsonEncode($data);
            exit ;
        } else {
            $data['total'] = 0;
            $data['rows'] = array();
            $this->basic->jsonEncode($data);
            exit ;
        }
    }

}
?>