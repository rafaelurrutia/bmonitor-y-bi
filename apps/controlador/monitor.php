<?php

class monitor extends Control
{
	// se ocupa
	public function nEstadoSistema(){
		
		$tps_index_control['date'] = date("H:i:s");
		
		//grupos disponibles
		$grupos_disponibles = $this->bmonitor->getGroupsHost(true, true);
		
		//sondas disponibles
		$sondas_diponibles = $this->bmonitor->getAllHost(true, true);
		
		if($grupos_disponibles == false || $sondas_diponibles == false || $grupos_disponibles == ""){
			return false;
		}
		/* cambio de consulta ya que se rehizo crontab de disponibilidad
		$nSQL = "SELECT BHG.`name`, ".
			"SUM(IF((BA.`STATUS_SONDA`=1), 1, 0)) AS AVAILABILITY, ".
			"SUM(IF((BA.`STATUS_SONDA`=0), 1, 0)) AS NO_AVAILABILITY, ".
			"BA.`groupid` FROM bm_availability BA LEFT JOIN bm_host_groups BHG USING(`groupid`) ".
			"WHERE BA.`groupid` IN $grupos_disponibles AND BA.`id_host` IN $sondas_diponibles GROUP BY BA.`groupid`";
		*/
		//nueva consulta para rescatar sondas disponibles.
		$nSQL = "SELECT BD.name_group as `name`, SUM(IF((BD.`status`='true'), 1, 0)) AS AVAILABILITY, SUM(IF((BD.`status`='false'), 1, 0)) AS NO_AVAILABILITY, BD.id_group as `groupid` from bm_disponibilidad BD
		WHERE BD.id_group IN $grupos_disponibles 
		AND BD.id_host IN $sondas_diponibles
		GROUP BY BD.id_group";

		//$this->logs->error("_ consulta de sondas siponibles y no disponibles: $nSQL");     
		$resultado = $this->conexion->queryFetch($nSQL);
		
		$aResult = array();
		
		if($resultado){
			foreach ($resultado as $key => $value) {
				$aRegistro = array();
				$aRegistro["id_group"] = $value["groupid"];
				$aRegistro["name"] = $value["name"];
				$aRegistro["available"] = $value["AVAILABILITY"];
				$aRegistro["no_available"] = $value["NO_AVAILABILITY"];
				
				/*$nDetail = "SELECT * FROM `bm_availability` AV 
				LEFT JOIN `bm_host_groups` G ON G.`groupid`=AV.`groupid`
				WHERE AV.`STATUS_SONDA` = 0  AND AV.`groupid` IN (" . $value["groupid"] . ") LIMIT 20";*/
				
				//$nDetail = "SELECT * FROM `bm_disponibilidad` AV 
				//bug problema de edit nombre de sonda. Jura tarea BMC-107, https://bakingsoftware.atlassian.net/browse/BMC-107
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
						//$aDetail["nextcheck"] = $dvalue["nextcheck"];
						$aDetail["lastcheck"] = $dvalue["ultima_fecha"];
						$aDetail["agent_code"] = $dvalue["id_host"];
						
						$allDetail[] = $aDetail;
					}
				}
				
				$aRegistro["details"] = $allDetail;
				
				$aResult["data"][] = $aRegistro;
			}
		}
		
		$this->basic->jsonEncode($aResult);
		exit;
	}


    public function dashboard()
    {
        $this->plantilla->load("monitor/dashboard",864000);
        $this->plantilla->finalize();
    }

	public function alert()
	{
        $this->plantilla->load("monitor/tableAlert",864000);
        $grupos = $this->bmonitor->getAllGroups();
        $tps_index_control['option_group'] = $grupos->formatOption().'<option value="0">' .$this->language->ALL .'</option>';
        $this->plantilla->set($tps_index_control);
        $this->plantilla->finalize();		
	}
	
/*	public function getAlertTable() {
		$getParam = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'id');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();
		
		if(isset($_POST['fmGrupo_tableAlert'])){
			
			if($_POST['fmGrupo_tableAlert'] > 0){
				
				$groupid = '('.$_POST['fmGrupo_tableAlert'].')';
				
			} else {
				$groupid = $this->bmonitor->getAllGroups();
				$groupid = $groupid->formatINI();
			}	
		} else {
			$groupid = $this->bmonitor->getAllGroups();
			$groupid = $groupid->formatINI();
		}
		
		if(isset($_POST['fmStatus_tableAlert'])){
			if($_POST['fmStatus_tableAlert'] == '1') {
				$filter = ' AND  A.`valueOk` = 0';
			} elseif ($_POST['fmStatus_tableAlert'] == '2') {
				$filter = ' AND  A.`valueOk` != 0';
			} else {
				$filter = '';
			}
			
		} else {
			$filter = '';
		}
		
		if(isset($_POST['fmGroupBy_tableAlert'])){
			if($_POST['fmGroupBy_tableAlert'] == '1') {
				$GroupBy = ' GROUP BY  PC.`id_category` ';
			}  else {
				$GroupBy = '';
			}	
		} else {
			$GroupBy = '';
		}		
		

		//Total rows

		$getTotalRows_sql = "SELECT count(DISTINCT A.`id`) as count FROM `bm_alert` A 
LEFT JOIN `bm_items_groups` IG ON A.`idItemHost`=IG.`id_item`
WHERE `valueFail` > 0 AND `datetime` > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND IG.`groupid` IN $groupid $filter";

		//echo $getTotalRows_sql;

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

		if ($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['count'];
		else {
			$data['total'] = 0;
		}

		//Rows

		$getRows_sql = "SELECT DISTINCT A.`id`, H.`host`,H.`codigosonda`, A.`valueFail`, A.`valueOk`,PCC.`category`,PC.`display`
				FROM `bm_alert` A
					INNER JOIN `bm_host` H ON A.`idHost`= H.`id_host` 
					INNER JOIN `bm_profiles_values` PV ON (PV.`id_item` =A.`idItemProfile` AND PV.`id_monitor`=A.`idItemHost`)  
					INNER JOIN `bm_profiles_category` PC ON PC.`id_category`=PV.`id_category`
					INNER JOIN `bm_profiles_categories` PCC ON PCC.`id_categories`=PC.`id_categories`
					INNER JOIN `bm_items_groups` IG ON A.`idItemHost`=IG.`id_item`
				WHERE `valueFail` > 0 $filter AND `datetime` > DATE_SUB(NOW(), INTERVAL 42 HOUR)  AND H.`borrado` = '0' AND H.`status`='1'
					AND IG.`groupid` IN $groupid $GroupBy $var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);

		if ($getRows_result) {
			foreach ($getRows_result as $key => $row) {

				$data['rows'][] = array(
					'id' => $row['id'],
					'cell' => array(
						$row['host'],
						$row['codigosonda'],
						$row['valueFail'],
						$row['valueOk'],
						$row['category'],
						$row['display']			
					)
				);
			}
		}

		$this->basic->jsonEncode($data);
		exit ;
	}
*/
	
    public function getAlertTable() { 
		$getParam = (object)$_POST;
		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'id');
		//Parametros Table
		$data = array();
		$data['page'] = $var->page;
		$data['rows'] = array();
		
		if(isset($_POST['fmGrupo_tableAlert'])){			
			if($_POST['fmGrupo_tableAlert'] > 0){				
				$groupid = '('.$_POST['fmGrupo_tableAlert'].')';				
			} else {
				$groupid = $this->bmonitor->getAllGroups();
				$groupid = $groupid->formatINI();
			}	
		} else {
			$groupid = $this->bmonitor->getAllGroups();
			$groupid = $groupid->formatINI();
		}		
		if(isset($_POST['fmStatus_tableAlert'])){
			if($_POST['fmStatus_tableAlert'] == '1') {
				$filter = ' AND `fail_warning` = 1';
			} elseif ($_POST['fmStatus_tableAlert'] == '2') {
				$filter = ' AND `fail_critical` = 1';
			} else {
				$filter = '';
			}
			
		} else {
			$filter = '';
		}
		//Total rows  
		/* $getTotalRows_sql = "SELECT count(DISTINCT A.`id`) as count FROM `bm_alert` A 
LEFT JOIN `bm_items_groups` IG ON A.`idItemHost`=IG.`id_item`
WHERE `valueFail` > 0 AND `datetime` > DATE_SUB(NOW(), INTERVAL 1 HOUR) AND IG.`groupid` IN $groupid $filter"; */
		/*
		$getTotalRows_sql = "SELECT COUNT(*) as count FROM `bm_items_alert` WHERE DATE(`datetime`) = DATE(NOW()) AND `groupid` IN $groupid $filter";
		*/		
		$getTotalRows_sql = "SELECT * as count FROM `bm_items_alert` WHERE DATE(`datetime`) <= DATE(NOW()) AND DATE(`datetime`) >= DATE(DATE_SUB(`datetime`, INTERVAL 1 DAY)) AND `groupid` IN $groupid $filter";
		
		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);
		if ($getTotalRows_result)
			$data['total'] = count($getTotalRows_result);
		else {
			$data['total'] = 0;
		}
		//Rows
		/* $getRows_sql = "SELECT DISTINCT A.`id`, H.`host`,H.`codigosonda`, A.`valueFail`, A.`valueOk`, A.`datetime`,PCC.`category`,PC.`display`
				FROM `bm_alert` A
					INNER JOIN `bm_host` H ON A.`idHost`= H.`id_host` 
					INNER JOIN `bm_profiles_values` PV ON (PV.`id_item` =A.`idItemProfile` AND PV.`id_monitor`=A.`idItemHost`)  
					INNER JOIN `bm_profiles_category` PC ON PC.`id_category`=PV.`id_category`
					INNER JOIN `bm_profiles_categories` PCC ON PCC.`id_categories`=PC.`id_categories`
					INNER JOIN `bm_items_groups` IG ON A.`idItemHost`=IG.`id_item`
				WHERE `valueFail` > 0 $filter AND `datetime` > DATE_SUB(NOW(), INTERVAL 42 HOUR)  AND H.`borrado` = '0' AND H.`status`='1'
					AND IG.`groupid` IN $groupid $GroupBy $var->sortSql $var->limitSql";  */
		$getRows_sql = "SELECT `id`, `name_host`, `code_host`, `name_item`, `datetime`, `fail_warning`, `fail_critical`, `fail_repair`, `datetime_sendtrap`, `lastvalue`, `unit`, `averagevalue`, `nominal_value`, `warning_value`, `critical_value` FROM `bm_items_alert` WHERE DATE(`datetime`) <= DATE(NOW()) AND DATE(`datetime`) >= DATE(DATE_SUB(NOW(), INTERVAL 1 DAY)) $filter AND `groupid` IN $groupid $var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);
		if ($getRows_result) {
			foreach ($getRows_result as $key => $row) {
				$failType = "";				
				if($row["fail_warning"] == 1){
					$failType = "WARNING";
				}				
				if($row["fail_critical"] == 1){
					$failType = "CRITICAL";
				}				
				if($row["fail_repair"] == 1){
					$failType = "CLEAR";
				}				
				$failType .= " ".$row["datetime_sendtrap"];				
				$row["type_trap"] = $failType;
				$data['rows'][] = array(
					'id' => $row['id'],
					'cell' => array(
						$row['name_host'],
						$row['code_host'],
						$row['type_trap'],
						$row['name_item'],
						$row['datetime'],
						$row['lastvalue'],
						$row['unit'],
						$row['averagevalue'],
						$row['nominal_value'],
						$row['warning_value'],
						$row['critical_value']
					)
				);
			}
		}
		$this->basic->jsonEncode($data);
		exit ;
	}
        
    
    public function ultimafecha()
    {

        $valida = $this->protect->access_page('INDEX_ULTIMAFECHA', FALSE);

        if ($valida->redirect == TRUE) {
            $page = $_SERVER['PHP_SELF'];
            $sec = "10";
            header("Refresh: $sec; url=$page");
            echo "Session close";
            exit ;
        }
        
        $this->plantilla->loadSec("monitor/ultimafecha", $valida->access);

        $groupFilter = $this->bmonitor->getAllGroups();
        
        $option = '';
        if($groupFilter){
            
            $tps_index_control['select_group'] = $groupFilter->formatOption();
            
            if (isset($_POST['id'])) {
                $idGroup = $_POST['id'];
                $exit = true;
            } else {
                $idGroup = $groupFilter->firstKey();
                $exit = false;
            }
            
            $select_equipo = $this->bmonitor->getHostForGroup($idGroup, false, true);
            
            if ($select_equipo) {
                foreach ($select_equipo as $key => $equipo) {
                    $option .= '<option value="' . $equipo['id_host'] . '">' . $equipo['host'] . '</option>';
                }
            }  
        }

        if ($exit) {
            echo '<option selected value="0">Select</option>' . $option;
            exit ;
        }

        $tps_index_control['select_equipo'] = $option;

        $this->plantilla->set($tps_index_control);
        $this->plantilla->finalize();
    }

    public function estadoSistema($table = false)
    {

        //Generado grupo de host permitidos por el usuario

        $available_groups = $this->bmonitor->getGroupsHost(true, true);

        $available_hosts = $this->bmonitor->getAllHost(true, true);

        if ($available_groups == false || $available_hosts == false || $available_groups == '') {
            return false;
        }

        $sql = "SELECT HG.`name`,
					SUM(IF((AV.`STATUS_SONDA`=0),1,0)) AS CRITICAL,
					SUM(IF( ((AV.`STATUS_SNMP`=0) && (HG.`snmp_monitor`=true) && (AV.`STATUS_SONDA`=1) ) , 1 , 0)) AS WARNING,
					AV.`groupid`, HG.`snmp_monitor`
					FROM `bm_availability` AV
					LEFT JOIN `bm_host_groups` HG USING(`groupid`)
					WHERE AV.`groupid` IN " . $available_groups . " AND AV.`id_host` IN " . $available_hosts . " GROUP BY AV.`groupid`;";

        $result = $this->conexion->queryFetch($sql);

		
		$snmpDisplay = false;
		if($result){
			foreach ($result as $key => $value) {
				if($value['snmp_monitor'] == 'true'){
					$snmpDisplay = true;
				}
			}
		}
		
        $today = date("H:i:s");

        $portlet = '<div  class="portlet-m">
			<div class="portlet-header-m">' . $this->language->DASHBOARD_TITLE_STATUS_SYSTEM . " (" . $today . ')</div>
			<div class="portlet-content-m"><div id="table_status_system" class="ui-grid ui-widget ui-widget-content ui-corner-all">';

        $portlet_table = '<table class="ui-grid-content ui-widget-content"><tr>
				<th  style="min-width: 200px!important;" class="ui-state-default">' . $this->language->EQUIPMENT_GROUP . '</th>
				<th class="ui-state-default">' . $this->language->CRITICAL_MONITOR_AGENT . '</th>';
		if($snmpDisplay == true) {
			$portlet_table .= '<th class="ui-state-default">' . $this->language->WARNING_MONITOR_AGENT . '</th></tr>';
		}		

        if ($result) {
            $class = 'rowA';
            foreach ($result as $key => $group) {

                $portlet_table .= '<tr class="' . $class . '"><td class="ui-grid-content">' . $group['name'] . '</td>';
                //Lo se de pajero
                if ($group['CRITICAL'] > 0) {
                    $portlet_table .= '<td id="' . $group['groupid'] . '/CR" class="ui-grid-content color_red tooltips">' . $group['CRITICAL'] . '</td>';
                } else {
                    $portlet_table .= '<td class="ui-grid-content">' . $group['CRITICAL'] . '</td>';
                }

				if($snmpDisplay == true) {
	                if ($group['WARNING'] > 0) {
	                    $portlet_table .= '<td id="' . $group['groupid'] . '/WR" class="ui-grid-content color_yellow tooltips">' . $group['WARNING'] . '</td></tr>';
	                } else {
	                    $portlet_table .= '<td class="ui-grid-content">' . $group['WARNING'] . '</td></tr>';
	                }
                }
				
                if ($class == 'rowA') {
                    $class = 'rowB';
                } else {
                    $class = 'rowA';
                }
            }
        }
        $portlet_table .= '</table>';

        if ($table) {
            return $portlet_table;
        } else {
            return $portlet . $portlet_table . '</div></div></div>';

        }
    }

    public function get_status()
    {
        $valida = $this->protect->access_page('INDEX_STATUS', FALSE);

        if ($valida->redirect == TRUE) {
            $page = $_SERVER['PHP_SELF'];
            $sec = "10";
            header("Refresh: $sec; url=$page");
            echo "Session close";
            exit ;
        }

        $groupFilter = $this->bmonitor->getAllGroups()->formatINI();
     
        if($groupFilter == false) {
            return false;
        }
        
        $status = array();

        $sql = 'SELECT COUNT(DISTINCT i.`id_item`) as cnt  FROM `bm_items` i 
                            LEFT JOIN `bm_items_groups` g USING(`id_item`) 
                                WHERE g.groupid IN ' . $groupFilter;

        $row = $this->conexion->queryFetch($sql);
        $status['items_count'] = $row[0]['cnt'];
        
        $row = $this->conexion->queryFetch($sql . ' AND g.status=1');
        $status['items_count_monitored'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch($sql . ' AND g.status=0');
        $status['items_count_disabled'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch($sql . " AND i.type_poller='snmp'");
        $status['items_count_snmp'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch($sql . " AND i.type_poller='bsw_agent'");
        $status['items_count_bsw'] = $row[0]['cnt'];

        $sql = 'SELECT COUNT(id_host) as cnt ' . ' FROM bm_host WHERE groupid IN ' . $groupFilter;

        $row = $this->conexion->queryFetch($sql);
        $status['hosts_count'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch('SELECT COUNT(id_host) as cnt FROM bm_host WHERE status=' . $this->parametro->get('HOST_STATUS_MONITORED') . ' AND borrado=0 AND groupid IN ' . $groupFilter);
        $status['hosts_count_monitored'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch('SELECT COUNT(id_host) as cnt FROM bm_host WHERE status=' . $this->parametro->get('HOST_STATUS_NOT_MONITORED') . ' AND borrado=0 AND groupid IN ' . $groupFilter);
        $status['hosts_count_not_monitored'] = $row[0]['cnt'];

        $row = $this->conexion->queryFetch('SELECT COUNT(id_host) as cnt FROM bm_host WHERE borrado=1 AND groupid IN ' . $groupFilter);
        $status['hosts_count_deleted'] = $row[0]['cnt'];
        
        // Usuarios
        $row = $this->conexion->queryFetch('SELECT COUNT(id_user) as cnt FROM ' . $this->protect->table_user);
        $status['users_count'] = $row[0]['cnt'];

        $sql = 'SELECT count(`id`) as countSec , COUNT(DISTINCT `uid`) as countUser FROM `admin_sessions` WHERE `unixtimeactiviti` > (UNIX_TIMESTAMP()-600);';

        $row_user_1 = $this->conexion->queryFetch($sql);

        $status['users_online_sec'] = $row_user_1[0]['countSec'];
        $status['users_online_active'] = $row_user_1[0]['countUser'];

        return $status;

    }

    public function OLD_estadoBsw($table = false)
    {
        $status = $this->get_status();

        $today = date("H:i:s");

        $portlet = '<div class="portlet-m">
			<div class="portlet-header-m">' . $this->language->DASHBOARD_TITLE_STATUS_BSW . " (" . $today . ')</div>
			<div class="portlet-content-m"><div id="table_status_bsw" class="ui-grid ui-widget ui-widget-content ui-corner-all">';

        $portlet_table = '<table class="ui-grid-content ui-widget-content"><tr>
				<th class="ui-state-default">' . $this->language->PARAMETROS . '</th>
				<th class="ui-state-default">' . $this->language->STATUS . '</th>
				<th class="ui-state-default">' . $this->language->DETAILS . '</th></tr>';

        $portlet_table .= '<tr class="rowA"><td class="ui-grid-content">' . $this->language->S_NUMBER_OF_HOSTS . '</td>';
        $portlet_table .= '<td class="ui-grid-content">' . $status['hosts_count'] . '</td>';
        $portlet_table .= '<td class="ui-grid-content">' . $status['hosts_count_monitored'] . " / " . $status['hosts_count_not_monitored'] . " / " . $status['hosts_count_deleted'] . '</td></tr>';

        $portlet_table .= '<tr class="rowB"><td class="ui-grid-content">' . $this->language->S_NUMBER_OF_ITEMS . '</td>';
        $portlet_table .= '<td class="ui-grid-content">' . $status['items_count'] . '</td>';
        $portlet_table .= '<td class="ui-grid-content">' . $status['items_count_monitored'] . " / " . $status['items_count_disabled'] . " / " . $status['items_count_snmp'] . " / " . $status['items_count_bsw'] . '</td></tr>';

        $portlet_table .= '<tr class="rowA"><td class="ui-grid-content">' . $this->language->S_NUMBER_OF_USERS . '</td>';
        $portlet_table .= '<td class="ui-grid-content">' . $status['users_count'] ." ".$this->language->USER.'</td>';
        $portlet_table .= '<td class="ui-grid-content">' . $status['users_online_active'] ." / ". $status['users_online_sec'].'</td></tr>';

        $portlet_table .= '</table>';

        if ($table) {
            return $portlet_table;
        } else {
            return $portlet . $portlet_table . '</div></div></div>';
        }
    }

    public function estadoBsw()
    {
        $status = $this->get_status();

		$estadoBsw = array(
			'sondasState' 		=> $status['hosts_count'], 
			'sondasDetails' 	=> $status['hosts_count_monitored'] . " / " . $status['hosts_count_not_monitored'] . " / " . $status['hosts_count_deleted'],
			 
			'monitoresState' 	=> $status['items_count'],
			'monitoresDetails' 	=> $status['items_count_monitored'] . " / " . $status['items_count_disabled'] . " / " . $status['items_count_snmp'] . " / " . $status['items_count_bsw'], 
			
			'usuariosState'		=> $status['users_count'] ." ".$this->language->USER, 
			'usuariosDetails'	=> $status['users_online_active'] ." / ". $status['users_online_sec']
		);
		
		$aResult["data"][] = $estadoBsw;
		return $this->basic->jsonEncode($aResult);
		
    }

    public function OLD_ultimos20problemas($table = false, $groupid = false, $estado = false)
    {
        $continuous = TRUE;
        if ($groupid) {
            $available_groups = "($groupid)";
        } else {
            $available_groups = $this->bmonitor->getGroupsHost(true, true);
            if ($available_groups == FALSE) {
                $continuous = FALSE;
            }
        }

        $available_hosts = $this->bmonitor->getAllHost(true, true);

        if ($available_hosts == FALSE) {
            $continuous = FALSE;
        }

        $sql = "SELECT * FROM `bm_availability` AV 
				LEFT JOIN `bm_host_groups` G ON G.`groupid`=AV.`groupid`
				WHERE ( AV.`STATUS_SONDA` = 0  OR  ( AV.`STATUS_SNMP` = 0 AND G.`snmp_monitor`='true' ) )  AND AV.`groupid` IN " . $available_groups . " AND AV.`id_host` IN " . $available_hosts . " LIMIT 20";
		
		
        if ($continuous) {
            $result = $this->conexion->queryFetch($sql);
        } else {
            $result = FALSE;
        }

		$snmpDisplay = false;
		if($result){
			foreach ($result as $key => $value) {
				if($value['snmp_monitor'] == 'true'){
					$snmpDisplay = true;
				}
			}
		}
		
        $today = date("H:i:s");

        $portlet = '<div class="portlet-m">
		<div class="portlet-header-m">' . $this->language->DASHBOARD_TITLE_PROBLEM_LIST . ' (' . $today . ')</div>
		<div class="portlet-content-m"><div id="table_problem_list" class="ui-grid ui-widget ui-widget-content ui-corner-all">';

        $portlet_table = '<table class="ui-grid-content ui-widget-content"><tr>
			<th style="min-width: 150px!important;" class="ui-state-default">' . $this->language->EQUIPO_PLANTILLA . '</th>
			<th class="ui-state-default">' . $this->language->PROBLEM . '</th>
			<th class="ui-state-default">' . $this->language->NEXT_CONNECTION . '</th>';
		
		if($snmpDisplay == true){
			$portlet_table .= '<th class="ui-state-default">' . $this->language->LAST_CONNECTION . ' SNMP OK</th>';
		}
		
		$portlet_table .= '<th class="ui-state-default">' . $this->language->LAST_CONNECTION . ' SONDA OK</th>';
		$portlet_table .= '<th class="ui-state-default">' . $this->language->AGENT_CODE . '</th></tr>';

        if ($result) {
            $class = 'rowA';
            foreach ($result as $key => $alerta) {

                if ($alerta["STATUS_SONDA"] == 1 && $alerta["STATUS_SNMP"] == 0) {
                    $tt = $this->language->DASHBOARD_UNRESPONSIVE_SNMP;
                    if ($estado && ($estado !== 'WR'))
                        continue;
                }
                if ($alerta["STATUS_SONDA"] == 0 && $alerta["STATUS_SNMP"] == 1) {
                    $tt = $this->language->DASHBOARD_RESPONDS_SNMP_NOT_REPORT;
                    if ($estado && ($estado !== 'CR'))
                        continue;
                }
                if ($alerta["STATUS_SONDA"] == 0 && $alerta["STATUS_SNMP"] == 0) {
                    $tt = $this->language->DASHBOARD_AGENT_NOT_REPORT;
                    if ($estado && ($estado !== 'CR'))
                        continue;
                }

                $portlet_table .= '<tr class="' . $class . '"><td class="ui-grid-content">' . $alerta["host"] . '</td>';
                $portlet_table .= '<td class="ui-grid-content">' . $tt . '</td>';
                $portlet_table .= '<td class="ui-grid-content">' . $alerta["nextcheck"] . '</td>';
				if($snmpDisplay == true){
                	$portlet_table .= '<td class="ui-grid-content">' . $alerta["updateSNMP"] . '</td>';
				}
                $portlet_table .= '<td class="ui-grid-content">' . $alerta["updateSONDA"] . '</td>';
                $portlet_table .= '<td class="ui-grid-content">' . $alerta["dns"] . '</td></tr>';

                if ($class == 'rowA') {
                    $class = 'rowB';
                } else {
                    $class = 'rowA';
                }
            }
        }

        $portlet_table .= '</table>';

//		echo $portlet . $portlet_table . '</div></div></div>';
 //       if ($table) {
  //          return $portlet_table;
//        } else {
            return $portlet . $portlet_table . '</div></div></div>';
//        }
    }

	public function ultimos20problemas($table = false, $groupid = false, $estado = false)
    {
    	
        $continuous = TRUE;
        if ($groupid) {
            $available_groups = "($groupid)";
        } else {
            $available_groups = $this->bmonitor->getGroupsHost(true, true);
            if ($available_groups == FALSE) {
                $continuous = FALSE;
            }
        }

        $available_hosts = $this->bmonitor->getAllHost(true, true);

        if ($available_hosts == FALSE) {
            $continuous = FALSE;
        }
		/* cambio consulta ya que cambio disponibilidad posee otra estructura.
        $sql = "SELECT * FROM `bm_availability` AV 
				LEFT JOIN `bm_host_groups` G ON G.`groupid`=AV.`groupid`
				WHERE ( AV.`STATUS_SONDA` = 0  OR  ( AV.`STATUS_SNMP` = 0 AND G.`snmp_monitor`='true' ) )  AND AV.`groupid` IN " . $available_groups . " AND AV.`id_host` IN " . $available_hosts . " ORDER BY AV.`updateSONDA` DESC LIMIT 20";
		*/
		//$sql = "SELECT * FROM `bm_disponibilidad` AV   antes... 
		//BUG cambio de nombre en sonda. Jura tarea BMC-107, https://bakingsoftware.atlassian.net/browse/BMC-107
		$sql = "SELECT H.`host` AS name_host, AV.ultima_fecha, AV.name_group, AV.id_host, AV.`status` FROM `bm_disponibilidad` AV 
		RIGHT JOIN bm_host H ON AV.id_host=H.id_host 
		WHERE ( AV.`status` = 'false' )  
		AND AV.id_group IN $available_groups AND AV.id_host IN $available_hosts 
		ORDER BY AV.ultima_fecha DESC LIMIT 20";
		
		//$this->logs->error("consulta ultimo20 problemas: $sql"); 
		if ($continuous) {
            $result = $this->conexion->queryFetch($sql);
        } else {
            $result = FALSE;
        }
		
		$aResult = array();
		
		if($result){
			//$i12=1;
			foreach ($result as $key => $value) {
				//$this->logs->error("Hay data fiila: $i12");
				/*
				if ($value["STATUS_SONDA"] == 1 && $value["STATUS_SNMP"] == 0) {
                    $tt = $this->language->DASHBOARD_UNRESPONSIVE_SNMP;
                    if ($estado && ($estado !== 'WR'))
                        continue;
                }
                if ($value["STATUS_SONDA"] == 0 && $value["STATUS_SNMP"] == 1) {
                    $tt = $this->language->DASHBOARD_RESPONDS_SNMP_NOT_REPORT;
                    if ($estado && ($estado !== 'CR'))
                        continue;
                }
                if ($value["STATUS_SONDA"] == 0 && $value["STATUS_SNMP"] == 0) {
                    $tt = $this->language->DASHBOARD_AGENT_NOT_REPORT; 
                    if ($estado && ($estado !== 'CR'))
                        continue;
                }
				*/
				if ($value["status"] == 'false') {
                    $tt = $this->language->DASHBOARD_AGENT_NOT_REPORT;
                    if ($estado && ($estado !== 'CR'))
                        continue;
                }				
				//$value["nextcheck"]=substr($value["nextcheck"], 0, -3); 
				$value["ultima_fecha"]=substr($value["ultima_fecha"], 0, -3);
				
				$aRegistro = array();
				$aRegistro["name_group"] = $value["name_group"];
				$aRegistro["name_sonda"] = $value["name_host"];
				$aRegistro["problem"] = $tt;
				//$aRegistro["next_test"] = $value["nextcheck"];
				$aRegistro["last_conection"] = $value["ultima_fecha"];
				$aRegistro["code_sonda"] = $value["id_host"];
				/*foreach ($aRegistro as $key12 => $value12) {
					$this->logs->error(" $key12 = $value12");	
				}*/				
				$aResult["data"][] = $aRegistro;
				//$i12++;
			}
		}		
		$this->basic->jsonEncode($aResult);
		exit; 
    }
		
    public function statusNodos($table = false)
    {
    	/* corresponde a dashboard estado de los nodos */
        $sql = "SELECT `nodo`,`status`,`fechahora` FROM `bm_nodos`";

        $result = $this->conexion->queryFetch($sql);

        $url_master = $this->parametro->get('BSWMASTER') . ':' . $this->parametro->get('BSW_PORT_API');
        $url_slave = $this->parametro->get('BSWSLAVE') . ':' . $this->parametro->get('BSW_PORT_API');

        $nodos=array();
		
        foreach ($result as $key => $nodo) {

            if ($url_master === $nodo["nodo"]) {
                $type = 'Master';
                $ip = gethostbyname($this->parametro->get('BSWMASTER'));
            } else {
                $type = 'Slave';
                $ip = gethostbyname($this->parametro->get('BSWSLAVE'));
            }

			$nodos[] = array(
				'nodo' 			=> $nodo["nodo"] . ' (' . $ip . ')', 
				'estado' 		=> $nodo["status"], 
				'ultima_prueba' => $nodo["fechahora"],
				'tipo' 			=> $type
			);
			
        }

			$this->basic->jsonEncode($nodos);
			exit();
  
    }

    public function OLD_statusNodos($table = false)
    {
    	/* corresponde a dashboard estado de los nodos */
        $sql = "SELECT `nodo`,`status`,`fechahora` FROM `bm_nodos`";

        $result = $this->conexion->queryFetch($sql);

        $today = date("H:i:s");

        $portlet = '<div class="portlet-m">
		<div class="portlet-header-m">' . $this->language->DASHBOARD_TITLE_STATUS_LIST . ' (' . $today . ')</div>
		<div class="portlet-content-m"><div id="table_nodos_list" class="ui-grid ui-widget ui-widget-content ui-corner-all">';

        $portlet_table = '<table class="ui-grid-content ui-widget-content"><tr>
			<th class="ui-state-default">' . $this->language->NODE . '</th>
			<th class="ui-state-default">' . $this->language->STATUS . '</th>
			<th class="ui-state-default">' . $this->language->LAST_TEST . '</th>
			<th class="ui-state-default">' . $this->language->TYPE . '</th>
			</tr>';

        $url_master = $this->parametro->get('BSWMASTER') . ':' . $this->parametro->get('BSW_PORT_API');

        $url_slave = $this->parametro->get('BSWSLAVE') . ':' . $this->parametro->get('BSW_PORT_API');

        $class = 'rowA';
		
        foreach ($result as $key => $nodo) {

            if ($url_master === $nodo["nodo"]) {
                $type = 'Master';
                $ip = gethostbyname($this->parametro->get('BSWMASTER'));
            } else {
                $type = 'Slave';
                $ip = gethostbyname($this->parametro->get('BSWSLAVE'));
            }
            
            $portlet_table .= '<tr class="rowA"><td class="ui-grid-content">' . $nodo["nodo"] . ' (' . $ip . ')' . '</td>';
            $portlet_table .= '<td class="ui-grid-content" align="center">' . $nodo["status"] . '</td>';
            $portlet_table .= '<td class="ui-grid-content">' . $nodo["fechahora"] . '</td>';
            $portlet_table .= '<td class="ui-grid-content">' . $type . '</td></tr>';

            if ($class == 'rowA') {
                $class = 'rowB';
            } else {
                $class = 'rowA';
            }

        }

        	$this->basic->jsonEncode($nodos);
			exit();

        $portlet_table .= '</table>';
		


        if ($table) {
            return $portlet_table;
        } else {
            return $portlet . $portlet_table . '</div></div></div>';
        }
	

		
        
    }

	public function ru_informacionServidor(){
		
		// promedio load CPU
		$procesos = sys_getloadavg();
		$procesos = array(
			'server' => 'Master', 
			'promLoad1Min' => $procesos[0], 
			'promLoad5Min' => $procesos[1], 
			'promLoad15Min' => $procesos[2]
		);
		
		// espacio disco duro servidor
		$espacioTotal=disk_total_space("/");
		$espacioDisponible=disk_free_space("/");
		$espacioOcupado=$espacioTotal-$espacioDisponible;
		
		$espacioDisponiblePor=($espacioDisponible*100/$espacioTotal);
		$espacioOcupadoPor=($espacioOcupado*100/$espacioTotal);
		
		$discoDuro = array(
			'espacioTotal' => round($espacioTotal/ pow(1024,3),2).' GB', 
			'espacioOcupado' => round($espacioOcupado/ pow(1024,3),2).' GB ('.round($espacioOcupadoPor,2).'%)', 
			'espacioDisponible' => round($espacioDisponible/ pow(1024,3),2). ' GB ('.round($espacioDisponiblePor,2).'%)'
		);
		
		// memoria ram
		$memoria = array(
			'memTotal' 	=> shell_exec("cat /proc/meminfo | grep MemTotal | awk '{print $2}'").' kB', 
			'memFree' 	=> shell_exec("cat /proc/meminfo | grep MemFree | awk '{print $2}'").' kB', 
			'buffers' 	=> shell_exec("cat /proc/meminfo | grep Buffers | awk '{print $2}'").' kB',
			'cached' 	=> shell_exec("cat /proc/meminfo | grep ^Cached: | awk '{print $2}'").' kB', 
			'swapCached'=> shell_exec("cat /proc/meminfo | grep SwapCached | awk '{print $2}'").' kB', 
			'swapTotal'	=> shell_exec("cat /proc/meminfo | grep SwapTotal | awk '{print $2}'").' kB',
			'swapFree' 	=> shell_exec("cat /proc/meminfo | grep SwapFree | awk '{print $2}'").' kB'
		);
		
		$aResult["data"][] = array_merge($procesos,$discoDuro,$memoria);
		return $this->basic->jsonEncode($aResult);
		
	}
	
	public function ru_graphicsGroups(){
		
		$today = date("H:i:s");
		$procesos = sys_getloadavg();
		$espacioTotal=disk_total_space("/");
		$espacioDisponible=disk_free_space("/");
		$espacioOcupado=$espacioTotal-$espacioDisponible;
		
		$espacioDisponiblePor=($espacioDisponible*100/$espacioTotal);
		$espacioOcupadoPor=($espacioOcupado*100/$espacioTotal);		
		
		$memTotal=shell_exec("cat /proc/meminfo | grep MemTotal | awk '{print $2}'");	
		$memFree=shell_exec("cat /proc/meminfo | grep MemFree | awk '{print $2}'");
		$buffers=shell_exec("cat /proc/meminfo | grep Buffers | awk '{print $2}'");	
		$cached=shell_exec("cat /proc/meminfo | grep ^Cached: | awk '{print $2}'");
		$swapCached=shell_exec("cat /proc/meminfo | grep SwapCached | awk '{print $2}'");
		$swapTotal=shell_exec("cat /proc/meminfo | grep SwapTotal | awk '{print $2}'");
		$swapFree=shell_exec("cat /proc/meminfo | grep SwapFree | awk '{print $2}'");
		
		$html='
		<div class="portlet-m">
			<div class="portlet-header-m">Información del servidor ('.$today.')</div>
			<div class="portlet-content-m">
				<div id="table_nodos_list" class="ui-grid ui-widget ui-widget-content ui-corner-all">
					<table class="ui-grid-content ui-widget-content">
						<tr>
							<th class="ui-state-default">Servidor</th>
							<th class="ui-state-default">Prom load CPU 1 min.</th>
							<th class="ui-state-default">Prom load CPU 5 min.</th>
							<th class="ui-state-default">Prom load CPU 15 min.</th>
						</tr>
						
						<tr class="rowA">
							<td class="ui-grid-content">Master</td>
		            		<td class="ui-grid-content">'.$procesos[0].'</td>
							<td class="ui-grid-content">'.$procesos[1].'</td>
		           			<td class="ui-grid-content">'.$procesos[2].'</td>
		           		</tr>
		           		
		           	</table>
		           	
					<table class="ui-grid-content ui-widget-content">
						<tr>
							<th class="ui-state-default">Servidor</th>
							<th class="ui-state-default">Espacio total</th>
							<th class="ui-state-default">Espacio ocupado</th>
							<th class="ui-state-default">Espacio disponible</th>
						</tr>
						
						<tr class="rowA">
							<td class="ui-grid-content">Master</td>
		            		<td class="ui-grid-content">'.round($espacioTotal/ pow(1024,3),2).' GB</td>
							<td class="ui-grid-content">'.round($espacioOcupado/ pow(1024,3),2).' GB ('.round($espacioOcupadoPor,2).'%)'. ' </td>
							<td class="ui-grid-content">'.round($espacioDisponible/ pow(1024,3),2). ' GB ('.round($espacioDisponiblePor,2).'%)'. ' </td>
		           		</tr>
		           		
		           	</table>
		           	
					<table class="ui-grid-content ui-widget-content">
						<tr>
							<th class="ui-state-default">MemTotal</th>
							<th class="ui-state-default">MemFree</th>
							<th class="ui-state-default">Buffers </th>
							<th class="ui-state-default">Cached</th>
							<th class="ui-state-default">SwapCached</th>
							<th class="ui-state-default">SwapTotal</th>
							<th class="ui-state-default">SwapFree</th>
						</tr>
						
						<tr class="rowA">
							<td class="ui-grid-content">'.$memTotal.' kB</td>
		            		<td class="ui-grid-content">'.$memFree.' kB</td>
		            		<td class="ui-grid-content">'.$buffers.' kB</td>
		            		<td class="ui-grid-content">'.$cached.' kB</td>
		            		<td class="ui-grid-content">'.$swapCached.' kB</td>
		            		<td class="ui-grid-content">'.$swapTotal.' kB</td>
		            		<td class="ui-grid-content">'.$swapFree.' kB</td>
		           		</tr>
		           		
		           	</table>
		           	
				</div>
			</div>
		</div>';

		return $html;
//		return '';
    }

    public function getDashboard()
    {

        $this->plantilla->load("monitor/dashboard2",60);

/*               
        $status_system = $this->estadoSistema();

        $status_bsw = $this->estadoBsw();

        $problem_sonda = $this->ultimos20problemas();

        $status_nodos = $this->statusNodos();
/	
		$ru_graphicsGroups = $this->ru_graphicsGroups();

		$data['portlet'] = $this->basic->fixEncoding('<div class="column-m">'  . $status_system. $status_bsw. $problem_sonda . $status_nodos . $ru_graphicsGroups . '</div>');
*/
        $this->plantilla->set($data);

        $this->plantilla->finalize();
    }

    public function getDashboardReload()
    {
		/* corresponde a dashaboard y se encarga de refrescar el dashboard */    
        $table['table_nodos_list'] = $this->basic->fixEncoding($this->statusNodos(true));
       

        $table['table_status_system'] = $this->basic->fixEncoding($this->estadoSistema(true));

        $table['table_status_bsw'] = $this->basic->fixEncoding($this->estadoBsw(true));

        $table['table_problem_list'] = $this->basic->fixEncoding($this->ultimos20problemas(true));

        $this->basic->jsonEncode($table);

    }

    public function getDashboardTips($groupid, $estado = false)
    {
        $table['table'] = '<div class="ui-grid ui-widget ui-widget-content ui-corner-all">' . $this->ultimos20problemas(true, $groupid, $estado) . '</div>';
        $this->basic->jsonEncode($table);
    }

}
