<?php
class graphc extends Control {

	var $graphDisplay = '';

	public function getHost() {
		$groupid = $_POST['groupid'];
		$filter = false;
		if (isset($_POST['valueFilter'])) {
			$filter = (int)$_POST['valueFilter'];

			if ($filter == 2) {

				if (isset($_POST['valuePlan']) && is_numeric($_POST['valuePlan']) && ($_POST['valuePlan'] > 0)) {
					$idplan = $_POST['valuePlan'];
				} else {
					$idplan = false;
				}

			} else {
				$idplan = false;
			}
		} else {
			$idplan = false;
		}

		$host = $this->bmonitor->getHostForGroup($groupid, false, true, $idplan);

		if ($host) {
			$host_option = array();

			if ($filter !== false) {
				$host_option[] = '<option value="0">' . $this->language->ALL . '</option>';
			}

			foreach ($host as $key => $value) {
				$host_option[] = '<option value="' . $value['id_host'] . '">' . $value['host'] . '</option>';
			}
			$host_option = join(',', $host_option);
		} else {
			$host_option = '<option value="0">' . $this->language->NO_RESULTS . '</option>';
		}

		echo json_encode($host_option);
	}

	public function getPlan() {
		$groupid = $_POST['groupid'];
		$this->bmonitor->getPlanes($groupid, true, true);
	}

	public function getClass($groupid = false) {
		
		if(isset($_POST['groupid'])){
			$groupid = $_POST['groupid'];
		}
		
		$getMonitoresSQL = "SELECT IT.`id_item`,  PCG.`id_categories`, PCG.`category` , SUBSTRING_INDEX(IT.`descriptionLong`,'-',-2) as 'items' , IT.`descriptionLong` 
				FROM `bm_items` IT 
					INNER JOIN `bm_items_groups`  IG ON IT.`id_item` =IG.`id_item` 
					INNER JOIN `bm_profiles_values` PV ON PV.`id_monitor`=IT.`id_item`
					INNER JOIN `bm_profiles_category` PC ON PV.`id_category`=PC.`id_category`
					INNER JOIN `bm_profiles_categories` PCG ON PCG.`id_categories`=PC.`id_categories`
			WHERE IG.`groupid` = $groupid  AND PC.`view` = 'true'
			GROUP BY PCG.`id_categories`
			ORDER BY PCG.`id_categories`";

		$getMonitoresRESULT = $this->conexion->queryFetch($getMonitoresSQL);

		if ($getMonitoresRESULT) {
			$host_option['datos'] = $this->basic->getOption($getMonitoresRESULT, 'id_categories', 'category');
		} else {
			$host_option['datos'] = '<option value="0">' . $this->language->NO_RESULTS . '</option>';
		}		
		
		$this->basic->jsonEncode($host_option);
	}

	public function getMonitores($groupid = false, $class = false) {
			
		if(isset($_POST['groupid'])){
			$groupid = $_POST['groupid'];
		}
		
		if(isset($_POST['class']))  {
			$class = ' AND PCG.`id_categories` = "'.$_POST['class'].'"';
		} else {
			$class = '';
		}
		//GetMonitores

		$getMonitoresSQL = "SELECT IT.`id_item`,  PCG.`id_categories`, PCG.`category` , REPLACE(IT.`descriptionLong`, CONCAT(PCG.`category`,' -'), '')  as 'items' , IT.`descriptionLong` 
				FROM `bm_items` IT 
					INNER JOIN `bm_items_groups`  IG ON IT.`id_item` =IG.`id_item` 
					INNER JOIN `bm_profiles_values` PV ON PV.`id_monitor`=IT.`id_item`
					INNER JOIN `bm_profiles_category` PC ON PV.`id_category`=PC.`id_category`
					INNER JOIN `bm_profiles_categories` PCG ON PCG.`id_categories`=PC.`id_categories`
					INNER JOIN `bm_profiles_item` PI ON PI.`id_item`=PV.`id_item`
			WHERE IG.`groupid` = $groupid AND PC.`view` = 'true' AND PI.`type_result` IN  ('float','string','text') $class 
			GROUP BY IT.`id_item`
			ORDER BY PCG.`category`,items";

		$getMonitoresRESULT = $this->conexion->queryFetch($getMonitoresSQL);

		if ($getMonitoresRESULT) {
			/*
			$idItemEnd = end($getMonitoresRESULT);
			$idItemEnd = $idItemEnd['id_item'];
				
			foreach ($getMonitoresRESULT as $key => $value) {
							
				if(!isset($optgroup)){
					$option[] = '<optgroup label="'.$value['category'].'">';
					$optgroup = $value['category'];
				}

				if ($value['category'] != $optgroup) {
					$option[] = '</optgroup>';
					$option[] = '<optgroup label="'.$value['category'].'">';
					$optgroup = $value['category'];
				}
				
				$option[] = '<option value="' . $value['id_item'] . '">' . $value['items'] . '</option>';

				if($idItemEnd === $value['id_item']) {
					$option[] = '</optgroup>';
				}
			}*/
			
			//$host_option['datos'] = join("\n", $option);
			
			$host_option['datos'] = $this->basic->getOption($getMonitoresRESULT, 'id_item', 'items');
				
		} else {
			$host_option['datos'] = '<option value="0">' . $this->language->NO_RESULTS . '</option>';
		}

		$this->basic->jsonEncode($host_option);
	}

	public function getChartsItems() {
		$groupid = $_POST['groupid'];

		$getItemGraphSQL = 'SELECT `id_graph`, `name` FROM `bm_graphs` WHERE `groupid` = ' . $groupid . " ORDER BY `name`";

		$getItemGraphRESULT = $this->conexion->queryFetch($getItemGraphSQL);

		if ($getItemGraphRESULT) {
			$return['status'] = true;
			$return['option'] = $this->basic->getOption($getItemGraphRESULT, 'id_graph', 'name');
		} else {
			$return['status'] = false;
			$return['option'] = '<option selected="" value="0">' . $this->language->SELECT_A_GROUP . '</option>';
			$return['error'] = 'Error get graph database';
		}

		$this->basic->jsonEncode($return);
		exit ;
	}

	public function getScreenItems() {
		$groupid = $_POST['groupid'];

		$getItemGraphRESULT = $this->graph->getScreenList($groupid);

		if ($groupid > 0 && $getItemGraphRESULT) {
			$return['status'] = true;
			$return['option'] = $this->basic->getOption($getItemGraphRESULT, 'screenid', 'name');
		} else {
			$return['status'] = false;
			$return['option'] = '<option selected="" value="0">' . $this->language->SELECT_A_GROUP . '</option>';
			$return['error'] = 'Error get screen database';
		}

		$this->basic->jsonEncode($return);
		exit ;
	}

	public function getGraphPage() {
		$valida = $this->protect->access_page('INDEX_GRAPH', false);

		if ($valida->redirect == TRUE) {
			header("HTTP/1.1 302 Found");
			echo "Session close";
			exit ;
		}

		$this->plantilla->loadSec("monitor/chartGroupedIndex", $valida->access, 3600);

		$groups_arry = $this->bmonitor->getGroupsHost();

		$groups_option = $this->basic->getOption($groups_arry, 'groupid', 'name');

		$tps_index_control['option_groups'] = $groups_option;

		$tps_index_control['option_graph'] = '<option selected="" value="0">' . $this->language->SELECT_A_GROUP . '</option>';

		$tps_index_control['option_equipos'] = '<option selected="" value="0">' . $this->language->SELECT_A_GROUP . '</option>';

		$this->plantilla->set($tps_index_control);

		$this->plantilla->finalize();
	}

	public function getGraphPageNew() {
		$valida = $this->protect->access_page('INDEX_GRAPH', false);

		if ($valida->redirect == TRUE) {
			header("HTTP/1.1 302 Found");
			echo "Session close";
			exit ;
		}

		$this->plantilla->loadSec("monitor/graphFilterIndex", $valida->access, 3600);

		//Grupos

		$groups_arry = $this->bmonitor->getGroupsHost();

		$groups_option = $this->basic->getOption($groups_arry, 'groupid', 'name');

		//GRaficos
		$list_graph_arry = $this->graph->getGraphList();

		$list_graph_option = $this->basic->getOption($list_graph_arry, 'id_graph', 'name');

		//Filtros

		$listOptionFilter = $this->basic->getOptionValue("filterGraph", 1);

		//Asignar valores a la plantilla
		$tps_index_control['option_filter'] = $listOptionFilter;

		$tps_index_control['option_groups'] = $groups_option;

		$tps_index_control['option_plan'] = '<option selected="" value="0">' . $this->language->ALL . '</option>';
		$tps_index_control['option_monitor'] = '<option selected="" value="0">' . $this->language->SELECT_A_GROUP . '</option>';
		$tps_index_control['option_equipos'] = '<option selected="" value="0">' . $this->language->SELECT_A_GROUP . '</option>';

		$this->plantilla->set($tps_index_control);

		$this->plantilla->finalize();
	}

	public function getGraphCompare() {
		$valida = $this->protect->access_page('INDEX_GRAPH', false);

		if ($valida->redirect == TRUE) {
			header("HTTP/1.1 302 Found");
			echo "Session close";
			exit ;
		}

		$this->plantilla->loadSec("monitor/graph_compare", $valida->access, 3600);

		$groups_arry = $this->bmonitor->getGroupsHost();

		$groups_option = $this->basic->getOption($groups_arry, 'groupid', 'name');

		$list_graph_arry = $this->graph->getGraphList();

		$list_graph_option = $this->basic->getOption($list_graph_arry, 'id_graph', 'name');

		$tps_index_control['option_groups'] = $groups_option;

		$tps_index_control['option_graph'] = $list_graph_option;

		$this->plantilla->set($tps_index_control);

		$this->plantilla->finalize();
	}

	public function graph() {
		$id_graph = $_POST['graphid'];
		$id_host = $_POST['id_host'];
		$id_groups = $_POST['id_groups'];

		$graph = $_POST['graph'];

		$series = $this->conexion->queryFetch("SELECT  G.`name`, GI.`id_item`,I.`description`, I.`descriptionLong`,GI.`color`,I.`unit`
				FROM `bm_graphs_items` GI
				LEFT JOIN `bm_items` I USING(`id_item`)
				LEFT JOIN `bm_graphs` G ON G.`id_graph`=GI.`id_graph`
				WHERE G.`id_graph` = $id_graph
				ORDER BY GI.`sortorder`");

		$group_series = array();

		foreach ($series as $key => $value) {
			$group_series[] = "'" . $value['description'] . "'";
			$group_series_1[] = array(
				"id" => (int)$value['id_item'],
				"description" => (string)$value['descriptionLong']
			);
			$group_series_2[] = $value['description'];
		}

		$group_series = join(',', $group_series);
		$group_series_2 = join(',', $group_series_2);

		$GRAPH_UNIC_POST = $this->parametro->get('GRAPH_UNIC_POST', TRUE);

		if ($GRAPH_UNIC_POST) {
			$this->plantilla->load("monitor/graph_display2");
		} else {
			$this->plantilla->load("monitor/graph_display");
		}

		if (isset($_POST['limit'])) {
			$vars['limit'] = $_POST['limit'];
		}

		$vars['series'] = $group_series;
		$vars['series1'] = json_encode($group_series_1);
		$vars['series2'] = $group_series_2;
		$vars['id_host'] = $id_host;
		$vars['groupid'] = $id_groups;
		$vars['graphid'] = $id_graph;
		$vars['graph_type'] = $graph;

		$vars['lang'] = $this->protect->getLang();
		$vars['AVERAGE'] = $this->language->AVERAGE;

		$vars['title'] = $series[0]['name'];
		$vars['unit'] = $series[0]['unit'];

		$this->plantilla->set($vars);
		echo $this->plantilla->get();
	}

	public function graphFilter() {
		$this->plantilla->load("monitor/graph_displayFilter");
		$groupid = $_POST['groupid'];
		$filterid = $_POST['filterid'];
		$planid = $_POST['planid'];
		$hostid = $_POST['hostid'];
		$monitorid = $_POST['monitorid'];

		if (is_numeric($groupid) && ($groupid > 0)) {
			$WHERE = ' `groupid` = ' . $groupid;
		} else {
			exit ;
		}

		if (is_numeric($planid) && ($planid > 0)) {
			$WHERE .= ' AND `id_plan` = ' . $planid;
		}

		if (is_numeric($hostid) && ($hostid > 0)) {
			$WHERE .= ' AND `id_host` = ' . $hostid;
		}

		$series = $this->conexion->queryFetch("SELECT `id_host`, `host`  FROM `bm_host` WHERE " . $WHERE);

		$group_series = array();

		foreach ($series as $key => $value) {
			$group_series_1[] = array(
				"id" => (int)$value['id_host'],
				"description" => (string)$value['host']
			);
			$group_series_2[] = $value['id_host'];
		}

		$group_series_2 = join(',', $group_series_2);

		if (isset($_POST['limit'])) {
			$vars['limit'] = $_POST['limit'];
		}

		$vars['series1'] = json_encode($group_series_1);
		$vars['series2'] = $group_series_2;

		$vars['id_host'] = 'mono';
		$vars['groupid'] = $groupid;
		$vars['monitorid'] = $monitorid;
		$vars['planid'] = $planid;

		$vars['title'] = 'Beta';
		$vars['unit'] = 'Beta';

		$vars['lang'] = $this->protect->getLang();
		$vars['AVERAGE'] = $this->language->AVERAGE;

		$this->plantilla->set($vars);
		$this->plantilla->finalize();
	}

	public function graphCompare() {
		$this->plantilla->load("monitor/graph_compare_result");
		$id_graph = $_POST['graphid'];
		$id_host_1 = $_POST['id_host_1'];
		$id_host_2 = $_POST['id_host_2'];
		$id_groups = $_POST['id_groups'];

		$graph = $_POST['graph'];

		$series = $this->conexion->queryFetch("SELECT  G.`name`, GI.`id_item`,I.`description`,GI.`color`,I.`unit`
				FROM `bm_graphs_items` GI
				LEFT JOIN `bm_items` I USING(`id_item`)
				LEFT JOIN `bm_graphs` G ON G.`id_graph`=GI.`id_graph`
				WHERE G.`id_graph` = $id_graph
				ORDER BY GI.`sortorder`");

		$group_series = array();

		foreach ($series as $key => $value) {
			$group_series[] = "'" . $value['description'] . "'";
			$group_series_1[] = array(
				"id" => (int)$value['id_item'],
				"description" => (string)$value['description']
			);
			$group_series_2[] = $value['description'];
		}

		$group_series = join(',', $group_series);
		$group_series_2 = join(',', $group_series_2);

		if (isset($_POST['limit'])) {
			$vars['limit'] = $_POST['limit'];
		}

		$vars['series'] = $group_series;
		$vars['series1'] = json_encode($group_series_1);
		$vars['series2'] = $group_series_2;
		$vars['id_host_1'] = $id_host_1;
		$vars['id_host_2'] = $id_host_2;
		$vars['groupid'] = $id_groups;
		$vars['graphid'] = $id_graph;
		$vars['graph_type'] = $graph;

		$vars['title'] = $series[0]['name'];
		$vars['unit'] = $series[0]['unit'];

		$this->plantilla->set($vars);
		$this->plantilla->finalize();
	}

	public function graphUltimaFecha() {
		$this->plantilla->load("monitor/graph_display2");

		$iditem = $_POST['idItem'];
		$id_host = $_POST['host'];
		$id_groups = $_POST['groupId'];

		$nombre = $this->conexion->queryFetch("SELECT `description`, `descriptionLong`, `unit` FROM `bm_items` WHERE `id_item`=$iditem");

		$group_series_1[] = array(
			"id" => (int)$iditem,
			"description" => (string)$nombre[0]['descriptionLong']
		);
		$group_series_2[] = $nombre[0]['description'];

		$id_graph = '';

		$graph = 'unic';

		$group_series_2 = join(',', $group_series_2);

		if (isset($_POST['limit'])) {
			$vars['limit'] = $_POST['limit'];
		}

		$vars['series1'] = json_encode($group_series_1);
		$vars['series2'] = $group_series_2;
		$vars['id_host'] = $id_host;
		$vars['groupid'] = $id_groups;
		$vars['graphid'] = $id_graph;
		$vars['graph_type'] = $graph;

		$vars['title'] = $nombre[0]['descriptionLong'];
		$vars['unit'] = $nombre[0]['unit'];

		$vars['lang'] = $this->protect->getLang();
		$vars['AVERAGE'] = $this->language->AVERAGE;

		$this->plantilla->set($vars);
		$this->plantilla->finalize();
	}

	public function getGraph() {
		$idgroups = $_GET['idgroups'];
		$graphid = $_GET['graphid'];
		$host = $_GET['id_host'];
		$name = $_GET['name'];

		$limit = $_GET['limit'];

		$callback = $_GET['callback'];
		$callback = explode("&", $callback);
		$callback = $callback[0];

		$delay_query = "SELECT `delay`,`id_item` FROM `bm_items` WHERE `description`='" . $name . "'";

		$delay_result = $this->conexion->queryFetch($delay_query);
		$delay = $delay_result[0]['delay'];
		$iditem = $delay_result[0]['id_item'];

		if ((string)$name === (string)"hide") {
			$date = $this->graph->getGraph2("HIDE", "fila1", false, $limit);
		} else {
			$date = $this->graph->getGraph2($iditem, $host, "fila1", false, $limit);
		}

		header("Content-type: text/json");
		if ($date) {
			echo $callback . '(/* API Bmonitor */' . $date . ')';
		} else {
			echo $callback . '(/* API Bmonitor */  [[0,0.0000]])';
		}
	}

	public function getGraphOne() {

		$callback = $_GET['callback'];
		$callback = explode("&", $callback);
		$callback = $callback[0];

		if (!isset($_GET['limit']) || $_GET['limit'] == 0 || (!is_numeric($_GET['limit']))) {
			$limit = (int)$this->parametro->get('LIMIT_GRAPH', 1000);
		} else {
			$limit = (int)$_GET['limit'];
		}

		if (isset($_GET['id_host']) && (is_numeric($_GET['id_host'])) && $_GET['id_host'] > 0) {
			$host = $_GET['id_host'];
		} elseif (isset($_GET['id_host_1']) && (is_numeric($_GET['id_host_1'])) && $_GET['id_host_1'] > 0 && isset($_GET['id_host_2']) && (is_numeric($_GET['id_host_2'])) && $_GET['id_host_2'] > 0) {
			$host[] = $_GET['id_host_1'];
			$host[] = $_GET['id_host_2'];
		} else {
			echo $callback . '(/* API Bmonitor */  [[0,0.0000]])';
			exit ;
		}

		if (!isset($_GET['idgroups']) || $_GET['idgroups'] == 0 || (!is_numeric($_GET['idgroups']))) {

			$getGroupid_sql = "SELECT `groupid` FROM `bm_host` WHERE `id_host`= $host LIMIT 1";

			$getGroupid_result = $this->conexion->queryFetch($getGroupid_sql);

			if ($getGroupid_result) {
				$idgroups = $getGroupid_result[0]['groupid'];
			} else {
				echo $callback . '(/* API Bmonitor */  [[0,0.0000]])';
				exit ;
			}

		} else {
			$idgroups = $_GET['idgroups'];
		}

		if (!isset($_GET['name']) || $_GET['name'] == '') {
			echo $callback . '(/* API Bmonitor */  [[0,0.0000]])';
			exit ;
		} else {
			$name = $_GET['name'];
			$array_name = explode(',', $name);
		}

		if (!isset($_GET['graphid']) || $_GET['graphid'] == 0 || (!is_numeric($_GET['graphid']))) {

			if (count($array_name) == 1 && isset($_GET['idItem'])) {
				$graphid = $_GET['idItem'];
			} else {
				$graphid = md5($name);
			}

		} else {
			$graphid = $_GET['graphid'];
		}

		$array_name = $this->conexion->arrayIN($array_name);

		$delay_query = "SELECT `delay`,`id_item`,`description`,`type_poller` FROM `bm_items` WHERE `description` IN $array_name";

		$delay_result = $this->conexion->queryFetch($delay_query, 'logs_graph');

		$get_delay_group_sql = 'SELECT `delay`, `delay_bsw` FROM `bm_host_groups`  WHERE `groupid` = ' . $idgroups;

		$get_delay_group_result = $this->conexion->queryFetch($get_delay_group_sql, 'logs_graph');

		$date = false;

		if ($delay_result && $get_delay_group_result) {

			foreach ($delay_result as $key => $value) {
				$item[$value['description']]['delay'] = $value['delay'];
				$item[$value['description']]['id'] = $value['id_item'];
				$item[$value['id_item']]['delay'] = $value['delay'];
				$item[$value['id_item']]['description'] = $value['description'];
			}

			$idItem = $this->conexion->arrayIN($delay_result, 'id_item');

			$date = $this->graph->getGraph3($graphid, $host, $item, $idItem, false, $limit);
		} else {
			$this->logs->error("Error al obtener items", NULL, 'logs_graph');
		}

		header("Content-type: text/json");

		if ($date) {
			echo $callback . '(' . $date . ')';
		} else {
			echo $callback . '([[0,0.0000]])';
		}
	}

	public function getGraphOneFilter() {
		$callback = $_GET['callback'];
		$callback = explode("&", $callback);
		$callback = $callback[0];

		if (!isset($_GET['limit']) || $_GET['limit'] == 0 || (!is_numeric($_GET['limit']))) {
			$limit = (int)$this->parametro->get('LIMIT_GRAPH', 1000);
		} else {
			$limit = (int)$_GET['limit'];
		}

		if (isset($_GET['id_host'])) {
			$hostid = $_GET['id_host'];
		} else {
			echo $callback . '(/* API Bmonitor */  [[0,0.0000]])';
			exit ;
		}

		if (isset($_GET['idgroups']) && is_numeric($_GET['idgroups']) && ($_GET['idgroups'] > 0)) {
			$idgroups = $_GET['idgroups'];
		} else {
			echo $callback . '(/* API Bmonitor */  [[0,0.0000]])';
			exit ;
		}

		if (isset($_GET['monitorid']) && is_numeric($_GET['monitorid']) && ($_GET['monitorid'] > 0)) {
			$monitorid = $_GET['monitorid'];
		} else {
			echo $callback . '(/* API Bmonitor */  [[0,0.0000]])';
			exit ;
		}

		if (isset($_GET['planid']) && is_numeric($_GET['planid']) && ($_GET['planid'] > 0)) {
			$planid = $_GET['planid'];
		} else {
			$planid = $_GET['planid'];
		}

		$delay_query = "SELECT `delay`,`id_item`,`description`,`type_poller` FROM `bm_items` WHERE `id_item` = $monitorid";

		$delay_result = $this->conexion->queryFetch($delay_query, 'logs_graph');

		$date = false;

		if ($delay_result) {

			$item = $delay_result[0];
			$date = $this->graph->getGraphFilter($monitorid, $planid, $hostid, $item, false, $limit);
		} else {
			$this->logs->error("Error al obtener items", NULL, 'logs_graph');
		}

		header("Content-type: text/json");

		if ($date) {
			echo $callback . '(/* API Bmonitor */' . $date . ')';
		} else {
			echo $callback . '(/* API Bmonitor */  [[0,0.0000]])';
		}
	}

	public function getGraph_display() {
		$callback = $_GET['callback'];
		$callback = explode("&", $callback);
		$callback = $callback[0];
		$graphid = $_GET['graphid'];
		$name = $_GET['name'];
		echo $callback . $_SESSION['graph_display_' . $name . '_' . $graphid];
	}

	public function getGraphWindows() {

		$valida = $this->protect->access_page('INDEX_WINDOWS', false);

		$this->plantilla->loadSec("monitor/graph_screen", $valida, 3600);

		$groups_arry = $this->bmonitor->getGroupsHost();

		$groups_option = $this->basic->getOption($groups_arry, 'groupid', 'name');

		$tps_index_control['select_group'] = $groups_option;

		$tps_index_control['select_screen'] = '<option selected="" value="0">' . $this->language->SELECT_A_GROUP . '</option>';

		$this->plantilla->set($tps_index_control);

		$this->plantilla->finalize();
	}

	public function screenDisplay() {
		$getParam = (object)$_POST;

		if (is_numeric($getParam->screenid)) {
			$screenid = $getParam->screenid;
		} else {
			return false;
		}

		if (is_numeric($getParam->groupid)) {
			$groupid = $getParam->groupid;
		} else {
			return false;
		}

		if (is_numeric($getParam->hostid)) {
			$hostid = $getParam->hostid;
		} else {
			return false;
		}

		if (isset($getParam->hostName)) {
			$hostName = $getParam->hostName;
		} else {
			$hostName = '';
		}
		//Cargando graficos

		$select_sql = "SELECT `id_graph`, `name` FROM `bm_graphs`";

		$select_result_graph = $this->conexion->queryFetch($select_sql);

		$select_sql = "SELECT `hsize`,`vsize` FROM `bm_screens`  WHERE `screenid` = $screenid";

		$select_result_screen = $this->conexion->queryFetch($select_sql);

		$colum = $select_result_screen[0]['hsize'];
		$filas = $select_result_screen[0]['vsize'];

		$select_sql = "SELECT `id_graph`,`hsize`,`vsize`, `name` FROM `bm_screens_items` LEFT JOIN `bm_graphs` USING(`id_graph`) WHERE `screenid` = $screenid";

		$select_result_screen_items = $this->conexion->queryFetch($select_sql);

		$items = array();

		foreach ($select_result_screen_items as $key => $item) {
			$items[$item['hsize'] . '_' . $item['vsize']]['id'] = $item['id_graph'];
			$items[$item['hsize'] . '_' . $item['vsize']]['name'] = $item['name'];
		}

		$form = '<form id="config_screen_form_asig" class="form_sonda">';

		$width = 100 / $colum;

		for ($c = 1; $c <= $colum; $c++) {
			$form .= '<div class="column" style="width: ' . $width . '%">';

			for ($f = 1; $f <= $filas; $f++) {

				if (empty($items[$c . '_' . $f]['name'])) {
					$items[$c . '_' . $f]['name'] = 'Sin Asignar';
					$script = '';
				} else {
					$script = '<script type="text/javascript">' . "$('#graph_screen_" . $items[$c . '_' . $f]['id'] . "').load('graphc/getCharts',{graph:'screen', hostName:'$hostName' , graphid:" . $items[$c . '_' . $f]['id'] . ", hostid:" . $hostid . ", groupid:" . $groupid . '});</script>';
				}
				$form .= '<div class="portlet">';
				$form .= '<div class="portlet-header">' . $items[$c . '_' . $f]['name'] . '</div>';
				$form .= '<div class="portlet-content">';
				$form .= $script;
				$form .= '<div id="graph_screen_' . $items[$c . '_' . $f]['id'] . '"></div>';
				$form .= '</div>';
				$form .= '</div>';

			}

			$form .= '</div>';
		}

		$form .= '</form>';

		echo $form;
	}

	public function getCharts() {
		$param = (object)$_POST;

		$this->plantilla->load("monitor/chartGroupedDisplay");

		$vars['idGraph'] = $param->graphid;
		$vars['idHost'] = $param->hostid;
		$vars['idGroup'] = $param->groupid;
		$vars['title'] = $param->hostName;

		$getUnit = "SELECT I.`unit` FROM `bm_graphs` G 
LEFT JOIN `bm_graphs_items` GI ON G.`id_graph`=GI.`id_graph`
LEFT JOIN `bm_items` I ON I.`id_item`=GI.`id_item`
WHERE G.`id_graph` = $param->graphid AND I.`unit` IS NOT NULL  LIMIT 1";

		$getUnitRESULT = $this->conexion->queryFetch($getUnit);

		if ($getUnitRESULT) {
			$vars['unit'] = $getUnitRESULT[0]['unit'];
		}

		$vars['container'] = $param->graphid . $param->hostid . $param->groupid;

		if (isset($_POST['separation'])) {
			$vars['separation'] = "&separation=" . $_POST['separation'];
		}

		if (isset($_POST['limit'])) {
			$vars['limit'] = "&limit=" . $_POST['limit'];
		}

		switch ($param->dataGrouping) {
			case '0' :
				$vars['dataGrouping'] = "
                      enabled: false
                   ";
				break;
			case '1' :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 25,
                        units: [[
							'millisecond', // unit name
							[1, 2, 5, 10, 20, 25, 50, 100, 200, 500] // allowed multiples
						], [
							'second',
							[1, 2, 5, 10, 15, 30]
						], [
							'minute',
							[1, 2, 5, 10, 15, 30]
						], [
							'hour',
							[1, 2, 3, 4, 6, 8, 12]
						], [
							'day',
							[1]
						], [
							'week',
							[1]
						], [
							'month',
							[1, 3, 6]
						], [
							'year',
							null
						]]
                   ";
				break;
			case 'hour' :
				$vars['dataGrouping'] = "
                        enabled: true,
                        forced: true,
                        units: [[ 'hour', [1] ]]                   
                   ";
				break;
			case 'day' :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 10,
                        forced: true,
                        units: [[ 'day', [1] ]]                   
                    ";
				break;
			case 'week' :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 10,
                        forced: true,
                        units: [[ 'week', [1] ]]                   
                    ";
				break;
			case 'month' :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 10,
                        forced: true,
                        units: [[ 'month', [1] ]]                   
                    ";
				break;
			default :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 25,
                        units: [[
                            'millisecond', 
                            [1, 2, 5, 10, 20, 25, 50, 100, 200, 500] 
                        ], [
                            'second',
                            [1, 2, 5, 10, 15, 30]
                        ], [
                            'minute',
                            [1, 2, 5, 10, 15, 30]
                        ], [
                            'hour',
                            [1, 2, 3, 4, 6, 8, 12]
                        ], [
                            'day',
                            [1]
                        ], [
                            'week',
                            [1]
                        ], [
                            'month',
                            [1, 3, 6]
                        ], [
                            'year',
                            null
                        ]]
                   ";
				break;
		}

		$this->plantilla->set($vars);
		$this->plantilla->finalize();

		//echo $this->charts->getGraph(100,437,$start,$end);
	}

	public function getChartData($start = false, $end = false) {
		$param = (object)$_GET;

		$idItem = $this->charts->getIdItemForGraphid($param->idGraph);

		if (isset($param->separation)) {
			if ((int)$param->separation == 1) {
				$this->charts->graphNullDisplay = true;
			} else {
				$this->charts->graphNullDisplay = false;
			}
		}

		$result = $this->charts->getGraph($param->idHost, $idItem, $start, $end);

		$callback = explode("&", $param->callback);
		$callback = $callback[0];

		header("Content-type: text/json");

		echo $callback . '(/* API Bmonitor */' . $result . ')';
	}

	public function getChartsFilter() {
		$param = (object)$_POST;

		$this->plantilla->load("monitor/chartDisplay2");

		$vars['idFilter'] = $param->filterid;
		$vars['idHost'] = $param->hostid;
		$vars['idGroup'] = $param->groupid;
		$vars['idPlan'] = $param->planid;
		$vars['idItem'] = $param->monitorid;

		$getUnit = "SELECT I.`unit`, I.`descriptionLong` FROM `bm_items` I 
			WHERE I.`id_item` = $param->monitorid AND I.`unit` IS NOT NULL  LIMIT 1";

		$getUnitRESULT = $this->conexion->queryFetch($getUnit);

		if ($getUnitRESULT) {
			$vars['unit'] = $getUnitRESULT[0]['unit'];
			$vars['title'] = $getUnitRESULT[0]['descriptionLong'];
		}
		
		if($param->dataGrouping == '0') {
			$vars['dataGroupingActive'] = 'false';
		} else {
			$vars['dataGroupingActive'] = 'true';
		}

		switch ($param->dataGrouping) {
			case '0' :
				$vars['dataGrouping'] = "
                      enabled: false,
                      groupPixelWidth: 25,
                      units: [
                        ['day', [1]],
                        ['week', [1]],
                        ['month', [1, 3, 6]],
                        ['year', null]
                      ]
                   ";
				break;
			case '1' :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 25,
                        units: [[
                            'millisecond', 
                            [1, 2, 5, 10, 20, 25, 50, 100, 200, 500] 
                        ], [
                            'second',
                            [1, 2, 5, 10, 15, 30]
                        ], [
                            'minute',
                            [1, 2, 5, 10, 15, 30]
                        ], [
                            'hour',
                            [1, 2, 3, 4, 6, 8, 12]
                        ], [
                            'day',
                            [1]
                        ], [
                            'week',
                            [1]
                        ], [
                            'month',
                            [1, 3, 6]
                        ], [
                            'year',
                            null
                        ]]
                   ";
				break;
			case 'hour' :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 10,
                        forced: true,
                        units: [[ 'hour', [1, 2, 3, 4, 6, 8, 12] ]]                   
                   ";
				break;
			case 'day' :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 10,
                        forced: true,
                        units: [[ 'day', [1] ]]                   
                    ";
				break;
			case 'week' :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 10,
                        forced: true,
                        units: [[ 'week', [1] ]]                   
                    ";
				break;
			case 'month' :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 10,
                        forced: true,
                        units: [[ 'month', [1] ]]                   
                    ";
				break;
			default :
				$vars['dataGrouping'] = "
                        enabled: true,
                        groupPixelWidth: 25,
                        units: [[
                            'millisecond', 
                            [1, 2, 5, 10, 20, 25, 50, 100, 200, 500] 
                        ], [
                            'second',
                            [1, 2, 5, 10, 15, 30]
                        ], [
                            'minute',
                            [1, 2, 5, 10, 15, 30]
                        ], [
                            'hour',
                            [1, 2, 3, 4, 6, 8, 12]
                        ], [
                            'day',
                            [1]
                        ], [
                            'week',
                            [1]
                        ], [
                            'month',
                            [1, 3, 6]
                        ], [
                            'year',
                            null
                        ]]
                   ";
				break;
		}

		$vars['container'] = $param->filterid . $param->hostid . $param->groupid . $param->planid . $param->monitorid;

		if (isset($_POST['limit'])) {
			$vars['limit'] = "&limit=" . $_POST['limit'];
		}

		if (isset($_POST['separation'])) {
			$vars['separation'] = "&separation=" . $_POST['separation'];
		}

		$this->plantilla->set($vars);
		$this->plantilla->finalize();

		//echo $this->charts->getGraph(100,437,$start,$end);
	}

	public function getChartFilterData($start = false, $end = false) {
		$param = (object)$_GET;
		//$this->logs->error("los parametros q entran a getChartFilterData(): ");
		//foreach ($param as $key => $value) {
		//	$this->logs->error(" $key : ".$value);	
		//}
		
		if ((int)$param->idHost == 0) {
			$WHERE = '';

			if ($param->idFilter == 2) {
				$WHERE = ' AND `id_plan` = ' . $param->idPlan;
			}

			$getIdHostSQL = "SELECT `id_host`  FROM `bm_host` WHERE `groupid` = $param->idGroup " . $WHERE;
			//$this->logs->error("consulta sql a bm_host por todos los host(sondas): ".$getIdHostSQL);
			$getIdHostRESULT = $this->conexion->queryFetch($getIdHostSQL);

			if ($getIdHostRESULT) {
				foreach ($getIdHostRESULT as $key => $value) {
					$idHost[] = $value['id_host'];
				}
				//$this->logs->error("los host rescatados: ");
				//foreach ($idHost as $key => $value) {
					//$this->logs->error(" $key : ".$value);	
				//}
			} else {
				//$this->logs->error("se fue a exit.");
				exit ;
			}
			
		} else {
			//$this->logs->error("se metio a solo 1 host y es:".$param->idHost);
			$idHost = $param->idHost;
		}
		
		if (isset($param->dataGrouping) && ($param->dataGrouping == 'false')) {
			$dataGrouping = false;
		} else {
			$dataGrouping = true;
		}
		
		if (isset($param->separation)) {
			if ((int)$param->separation == 1) {
				$this->charts->graphNullDisplay = true;
			} else {
				$this->charts->graphNullDisplay = false;
			}
		}
		
		if (isset($param->limit)) {
			$limit = $param->limit;
		} else {
			$limit = false;
		}
		
		//los valores q se van son...
		//$this->logs->error("los host q se grafican son: uno= ".$idHost);
		//foreach ($idHost as $key => $value) {
			//$this->logs->error(" $key : ".$value);	
		//}
		//$this->logs->error("parametro->item: ".$param->idItem);
		//$this->logs->error("start: ".$start);
		//$this->logs->error("end: ".$end);
		//$this->logs->error("limit: ".$limit);
		//$this->logs->error("agrupado: ".$dataGrouping);
		
		$result = $this->charts->getGraph($idHost, $param->idItem, $start, $end, $limit, $dataGrouping);
		//$this->logs->error("volvio ok del getGraph");
		$callback = explode("&", $param->callback);
		$callback = $callback[0];
		//$this->logs->error("callback: ".$callback);
		header("Content-type: text/json");

		echo $callback . '(/* API Bmonitor */' . $result . ')';
		//$this->logs->error("echoalfinal: ".$callback . '(/* API Bmonitor */' . $result . ')');
	}

	public function getPoint() {
		$post = (object)$_POST;
		
		//$post->x;//Clock
		//$post->y;//Value
		//$post->idItem;
		//$post->idHost;
		//$post->name;
		
		if(!is_numeric($post->idHost) || $post->idHost < 1){
			$getIDHostSQL = "SELECT `id_host` FROM `bm_host` WHERE `host` = '".$post->name."';";
			$getIDHostRESULT = $this->conexion->queryFetch($getIDHostSQL);
			if($getIDHostRESULT){
				$post->idHost = $getIDHostRESULT[0]['id_host'];
			} else {
				$result['status'] = false;
				$result['error'] = "Error get host id, $getIDHostSQL";
				echo json_encode($result);
				exit;
			}
		}
		
		$clock = $post->x/1000;
		
		$getTextSQL = "SELECT `value`,`id_history` FROM `bm_history_str` WHERE `clock` = '".$clock."' AND `id_host` = ".$post->idHost." AND `id_item` = (
			SELECT  PV.`id_monitor`
			FROM `bm_profiles_item` PI 
				INNER JOIN `bm_profiles_values` PV ON  PV.`id_item`=PI.`id_item`
			WHERE PV.`id_category` = (SELECT `id_category` 
			FROM `bm_profiles_values` 
			WHERE `id_monitor` = 1821)  AND PI.`type_result` = 'comment')";

		$getTextRESULT = $this->conexion->queryFetch($getTextSQL);
		
		if($getTextRESULT){
			$textPoint = $getTextRESULT[0]['value'];
			$textPoint = str_replace(";;", "</br>", $textPoint);
			$vars['textPoint'] = $textPoint;
			$result['height'] = 700;
		} else {
			$vars['textPoint'] = '';
			$result['height'] = 250;
		}
					
		$createTableIFnoExist = $this->conexion->query("CREATE TABLE IF NOT EXISTS `bm_history_comment` (
		  `id_item` int(11) unsigned NOT NULL DEFAULT '0',
		  `id_host` int(11) unsigned NOT NULL,
		  `clock` int(11) unsigned NOT NULL DEFAULT '0',
		  `value` longtext NOT NULL,
		  PRIMARY KEY (`id_item`,`id_host`,`clock`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;");
		
		$getCommentSQL = "SELECT  `value` FROM `bm_history_comment` WHERE `id_item` = $post->idItem AND `id_host` = $post->idHost AND `clock` = $clock";
		$getCommentRESULT = $this->conexion->queryFetch($getCommentSQL);
		
		if($getCommentRESULT){
			$comment = $getCommentRESULT[0]['value'];
			$vars['comment'] = $comment;
		} else {
			$vars['comment'] = '';
		}
						
		$this->plantilla->load("monitor/pointModal");
		$vars['clock'] = $clock;
		$vars['idHost'] = $post->idHost;
		$this->plantilla->set($vars);
		$result['data'] =  $this->plantilla->get();
		$result['status'] = true;
		$result['name'] = $post->name;
		$result['clock'] = $clock;
		
		echo json_encode($result);
		exit;
	}
	
	public function setCommentPoint()
	{
		$var = (object)$_POST;
		
		if(is_numeric($var->idItem) && is_numeric($var->idHost) && is_numeric($var->clock)){
				
			$insertCommnetSQL = "REPLACE INTO `bm_history_comment` (`id_item`, `id_host`, `clock`, `value`)
					VALUES
						($var->idItem, $var->idHost, $var->clock, '$var->comment');";
						
			$insertCommnetRESULT = $this->conexion->queryFetch($insertCommnetSQL);
			
			if($insertCommnetRESULT){
				$result['status'] = true;
			} else {
				$result['status'] = false;
				$result['error'] = 'Error Insert';
			}

		} else {
			$result['status'] = false;
			$result['error'] = 'Invalid Param';
		}
		
		echo json_encode($result);
		exit;
	}
	
	public function deletePoint()
	{
		$var = (object)$_POST;
		
		if(is_numeric($var->idItem) && is_numeric($var->idHost) && is_numeric($var->clock)){
			$updateValidHistorySQL = "UPDATE `bm_history` SET `valid` = '0' WHERE `id_item` = '$var->idItem' AND  `id_host` = '$var->idHost' AND  `clock` = '$var->clock'";
						
			$updateValidHistoryResult = $this->conexion->queryFetch($updateValidHistorySQL);
			
			if($updateValidHistoryResult){
				$result['status'] = true;
			} else {
				$result['status'] = false;
				$result['error'] = 'Error update';
			}
		} else {
			$result['status'] = false;
			$result['error'] = 'Invalid Param';
		}
		
		echo json_encode($result);
		exit;
	}
		
}
