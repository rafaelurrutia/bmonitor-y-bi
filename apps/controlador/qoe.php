<?php

class Qoe extends Control {
	public function index() {
		$valida = $this->protect->access_page('QOE', true, 'QoE', 'Section QoE');

		$this->plantilla->loadSec("qos/index", $valida, 360000);

		$section = new Section();

		$tps_index_control['menu'] = $section->menu('QOE');
		$tps_index_control['header'] = $section->header();
		$tps_index_control['footer'] = $section->footer();

		$tabs[] = array(
			'title' => $this->language->REPORT,
			'urlBase' => true,
			'href' => 'qoe/report',
			'protec' => array(
				"code" => 'QOS_REPORT',
				"title" => 'QoE',
				"description" => "Show Report"
			)
		);

		$tabs[] = array(
			'title' => "Speedtest",
			'urlBase' => true,
			'href' => 'qoe/speedtest',
			'protec' => array(
				"code" => 'QOS_SPEED',
				"title" => 'QoE',
				"description" => "Show Speedtest"
			)
		);

		$tabs[] = array(
			'title' => "Speedtest - " . $this->language->GROUPED,
			'urlBase' => true,
			'href' => 'qoe/minticGroups',
			'protec' => array(
				"code" => 'QOS_SPEEDGROUPS',
				"title" => 'QoE',
				"description" => "Show Speedtest Grouped"
			)
		);

		$tabs[] = array(
			'title' => "Mintic",
			'urlBase' => true,
			'href' => 'qoe/mintic',
			'protec' => array(
				"code" => 'QOS_MINTIC',
				"title" => 'QoE',
				"description" => "Show Mintic"
			)
		);

		$tabs[] = array(
			'title' => $this->language->CONFIGURATION,
			'urlBase' => true,
			'href' => 'qoe/config',
			'protec' => array(
				"code" => 'QOS_CONFIG',
				"title" => 'QoE',
				"description" => "Show Config"
			)
		);
		/*
		 $tabs[] = array(
		 'title' => $this->language->LOCATION,
		 'urlBase' => true,
		 'href' => 'qoe/locations',
		 'protec' => array(
		 "code" => 'QOS_LOCATION',
		 "title" => 'QoE',
		 "description" => "Show Location"
		 )
		 );*/

		$tabs[] = array(
			'title' => $this->language->EXPORT,
			'urlBase' => true,
			'href' => 'qoe/exportItems',
			'protec' => array(
				"code" => 'QOS_EXPORTITEMS',
				"title" => 'QoE',
				"description" => "Show Export"
			)
		);

		/*
		 $tabs[] = array(
		 'title' => $this->language->MAP,
		 'urlBase' => true,
		 'href' => 'qoe/getMaps',
		 'protec' => array(
		 "code" => 'QOS_MAPS',
		 "title" => 'QoE',
		 "description" => "Show Maps"
		 )
		 );*/
		 


		$tps_index_control['tabs'] = $this->bmonitor->getTabs($tabs);

		$this->plantilla->set($tps_index_control);

		$this->plantilla->finalize();
	}

	public function graph() {

		$valida = $this->protect->access_page('QOE_CHARTS', true, 'QoE', 'Show Chart');

		$this->plantilla->loadSec("qos/reportGraph", $valida);

		//Get menu:

		$get_menu_value = 'SELECT `id_report`,`report`,`category` FROM `report` LEFT JOIN `report_category` USING(`id_category`) ORDER BY `id_category`;';

		$get_menu_result = $this->conexion->queryFetch($get_menu_value);

		if ($get_menu_result) {
			$menuR = '';
			foreach ($get_menu_result as $key => $menu) {

				if (isset($category) && ($category !== $menu['category'])) {
					$menuR .= '</ul></div>' . "\n";
					unset($category);
				}

				if (!isset($category)) {
					$menuR .= '<h3><a href="#"> ' . $menu['category'] . '</a></h3>' . "\n" . "<div><ul>\n";
					$category = $menu['category'];
				}

				$menuR .= '<li> <a href="#' . $menu['id_report'] . '"> ' . $menu['report'] . '</a></li>' . "\n";

			}

			$menuR .= '</ul></div>' . "\n";

			$tps_index_control['menu'] = utf8_encode($menuR);
		}

		$tps_index_control['REPORT'] = $this->language->REPORT;

		$this->plantilla->set($tps_index_control);

		$this->plantilla->finalize();
	}

	public function report() {

		$valida = $this->protect->access_page('QOS_REPORT', true, 'QoE', 'Show Report');

		$this->plantilla->loadSec("qos/report", $valida);

		$tps_index_control['option_groups'] = $this->bmonitor->getAllGroups()->formatOption();

		$getMeses = $this->language->MESES;
		$optionM = '';
		$mesActual = date('n');
		foreach ($getMeses as $key => $meses) {
			if ($mesActual == $key) {
				$selected = 'selected';
			} else {
				$selected = '';
			}
			$optionM .= '<option ' . $selected . ' value="' . $key . '">' . $meses . '</option>';
		}

		$tps_index_control['option_meses'] = $optionM;

		for ($i = date("Y"); $i >= date("Y") - 10; $i--) {
			$year[] = '<option value="' . $i . '">' . $i . '</option>';
		}

		$tps_index_control['option_anos'] = join('', $year);
		$tps_index_control['option_escalas'] = $this->basic->getOptionValue('escalas', '1024');

		$this->plantilla->set($tps_index_control);

		$this->plantilla->finalize();
	}

	public function accomplishment() {

	}

	public function getTableReport() {
		$getParam = (object)$_POST;

		if (!isset($getParam->grupo)) {
			return false;
		}

		if (!isset($getParam->meses)) {
			return false;
		} else {
			if ((is_numeric($getParam->meses)) && ($getParam->meses < 10)) {
				$getParam->meses = '0' . $getParam->meses;
			}
		}

		if (!isset($getParam->ano)) {
			return false;
		}

		if (isset($getParam->escala)) {
			$factor = $getParam->escala;
		} else {
			return false;
		}

		$clock = $getParam->ano . "/" . $getParam->meses;

		$PERCENTILE_REPORT_QOE = json_decode($this->parametro->get('PERCENTILE_REPORT_QOE', '[]'));

		//Datos de la tabla

		$tableResult = $this->getReport($clock, $getParam->grupo, $factor);
		$table = '<div class="column">';
		if ($tableResult) {
			foreach ($tableResult as $keyGroup => $group) {
				$table .= '<div class="portlet"><div class="portlet-header">' . $group['title'] . '</div><div class="portlet-content">';

				$table .= '<table id="tableResumenConsFDT_int" class="basic ui-grid-content ui-widget-content display">';

				$table .= '<thead><tr>
                    <th class="ui-state-default"></th>
                    <th class="ui-state-default">Plan</th>
                    <th class="ui-state-default">' . $this->language->MONTH . '</th>
                    <th class="ui-state-default">Q</th>
                    <th class="ui-state-default">' . $this->language->SUCCESSFUL . '</th>
                    <th class="ui-state-default">' . $this->language->FAILED . '</th>
                    <th class="ui-state-default">' . $this->language->NOMINAL_OVERFLOW . '</th>
                    <th class="ui-state-default">Nominal</th>
                    <th class="ui-state-default">' . $this->language->WARNING . '</th>
                    <th class="ui-state-default">' . $this->language->CRITICAL . '</th>
                    <th class="ui-state-default">Prom</th>
                    <th class="ui-state-default">Mín</th>
                    <th class="ui-state-default">Máx</th>
                    <th class="ui-state-default">Desv</th>';

				foreach ($PERCENTILE_REPORT_QOE as $keyPer => $percentile) {
					$table .= '<th class="ui-state-default">Per ' . $percentile . '</th>';
				}

				$table .= '</tr></thead>';
				$tableLocation = '<table class="ui-grid-content ui-widget-content display" style="width: 88.5%;float: right;"><thead><tr>
                    <th class="ui-state-default">' . $this->language->LOCATION . '</th>
                    <th class="ui-state-default">Q</th>
                    <th class="ui-state-default">' . $this->language->SUCCESSFUL . '</th>
                    <th class="ui-state-default">' . $this->language->FAILED . '</th>
                    <th class="ui-state-default">' . $this->language->NOMINAL_OVERFLOW . '</th>
                    <th class="ui-state-default">Nominal</th>
                    <th class="ui-state-default">' . $this->language->WARNING . '</th>
                    <th class="ui-state-default">' . $this->language->CRITICAL . '</th>
                    <th class="ui-state-default">Prom</th>
                    <th class="ui-state-default">Mín</th>
                    <th class="ui-state-default">Máx</th>
                    <th class="ui-state-default">Desv</th>';

				foreach ($PERCENTILE_REPORT_QOE as $keyPer => $percentile) {
					$tableLocation .= '<th class="ui-state-default">Per ' . $percentile . '</th>';
				}

				$tableLocation .= '</tr></thead>';

				$table .= '<tbody>';
				foreach ($group['data'] as $key => $value) {
					$table .= '<tr class="gradeA odd">';
					if (isset($group['location'][$value['idPlan']])) {
						$table .= '<td class="ui-widget-content sorting" style="width: 41px;"><img id="table_' . $value['idPlan'] . '" src="/sitio/img/details_open.png"></td>';
					} else {
						$table .= '<td class="ui-widget-content" style="width: 41px;"><img id="table_' . $value['idPlan'] . '" src="/sitio/img/details_close.png"></td>';
					}
					$table .= '<td class="">' . $value['plan'] . '</td>';
					$table .= '<td class="">' . $value['mes'] . '</td>';
					$table .= '<td class="">' . $value['total'] . '</td>';
					$table .= '<td class="format1">' . $value['exitosa'] . '</td>';
					$table .= '<td class="format1">' . $value['fallida'] . '</td>';
					$table .= '<td class="format2">' . $value['nominalD'] . '</td>';
					$table .= '<td class="format2">' . $value['nominal'] . '</td>';
					$table .= '<td class="format2">' . $value['warning'] . '</td>';
					$table .= '<td class="format2">' . $value['critical'] . '</td>';
					$table .= '<td class="">' . $value['promedio'] . '</td>';
					$table .= '<td class="">' . $value['min'] . '</td>';
					$table .= '<td class="">' . $value['max'] . '</td>';
					$table .= '<td class="">' . $value['std'] . '</td>';
					foreach ($PERCENTILE_REPORT_QOE as $keyPer => $percentile) {
						$table .= '<td class="">' . $value['percentil' . $percentile] . '</td>';
					}
					$table .= '</tr>';
					if (isset($group['location'][$value['idPlan']])) {
						$table .= '<tr>';
						$table .= '<td id="table_' . $value['idPlan'] . '" style="display: none;" colspan="17" class="details">';
						$table .= $tableLocation . '<tbody>';

						foreach ($group['location'][$value['idPlan']] as $keyLocation => $location) {
							$table .= '<tr class="gradeA odd"><td class="ui-widget-content">' . $location['locationName'] . '</td>';

							$table .= '<td class="">' . $location['total'] . '</td>';
							$table .= '<td class="format1">' . $location['exitosa'] . '</td>';
							$table .= '<td class="format1">' . $location['fallida'] . '</td>';
							$table .= '<td class="format2">' . $location['nominalD'] . '</td>';
							$table .= '<td class="format2">' . $location['nominal'] . '</td>';
							$table .= '<td class="format2">' . $location['warning'] . '</td>';
							$table .= '<td class="format2">' . $location['critical'] . '</td>';
							$table .= '<td class="">' . $location['promedio'] . '</td>';
							$table .= '<td class="">' . $location['min'] . '</td>';
							$table .= '<td class="">' . $location['max'] . '</td>';
							$table .= '<td class="">' . $location['std'] . '</td>';
							foreach ($PERCENTILE_REPORT_QOE as $keyPer => $percentile) {
								$table .= '<td class="">' . $location['percentil' . $percentile] . '</td>';
							}
							$table .= '</tr>';
						}

						$table .= '</tbody></table></td></tr>';
					}
				}

				$table .= '</tbody></table></div></div>';
			}

		}
		$table .= '</div>';

		$datos['status'] = true;
		$datos['tablas'] = $table;
		$this->basic->jsonEncode($datos);
		exit ;
	}

	public function getReportDebug() {
		$this->getReport('2014/04', 23, 1024, true, true, true);
	}

	private function getReport($clock = false, $groupid, $escala = false, $opColor = true, $unit = true, $debug = false) {

		if ($clock === false) {

			$month = date("n", strtotime("yesterday"));
			$year = date("Y", strtotime("yesterday"));
			if ($month < 10) {
				$month = "0" . $month;
			}
			$clock = $year . "/" . $month;

		} else {
			$clock = str_replace("-", "/", $clock);
		}

		if ($escala != false && is_numeric($escala)) {
			$factor = $escala;
		} else {
			$factor = 1;
		}

		$getStats_sql = "SELECT INF.*,IT.`unit`,PL.`plan`,PL.`nacD`, PL.`nacU`, PL.`locD`, PL.`locU`, PL.`intD`, PL.`intU`,IT.`descriptionLong`,TH.`type` As 'typeTH'
FROM `bm_inform` INF
    LEFT JOIN `bm_plan` PL ON PL.`id_plan`=INF.`idPlan`
    LEFT JOIN `bm_items` IT ON IT.`id_item`=INF.`idItem` 
    INNER JOIN `bm_threshold` TH ON TH.`id_item`=INF.`idItem`
WHERE INF.`clock` = '$clock' AND INF.`groupid` = $groupid  AND `report` = 'true'
ORDER BY INF.`idItem`,INF.`idPlan`,INF.`location`";

		$getStats_result = $this->conexion->queryFetch($getStats_sql);

		if ($getStats_result) {
			foreach ($getStats_result as $key => $qos_data) {

				if (strlen($qos_data['location']) > 3) {
					list($region, $city) = explode('|', $qos_data['location']);

					if (!is_numeric($city)) {
						$locationName = $city;
					} elseif (is_numeric($city) && $city != 0) {
						$locationValue = $this->basic->getLocationValue($city);
						$locationName = $locationValue->name;
					} elseif (is_numeric($region) && $region != 0) {
						$locationValue = $this->basic->getLocationValue($region);
						$locationName = $locationValue->name;
					} else {
						$locationName = $city;
					}

				} elseif (strlen($qos_data['location']) > 2) {
					$locationName = $this->language->OTHERS;
				} else {
					$locationName = $qos_data['location'];
				}

				$section = $qos_data['groupid'] . "_" . $qos_data['idPlan'] . "_" . $qos_data['idItem'] . "_" . $qos_data['location'];
				$dateInform[$section]['groupid'] = $qos_data['groupid'];
				$dateInform[$section]['idPlan'] = $qos_data['idPlan'];
				$dateInform[$section]['locationName'] = $locationName;
				$dateInform[$section]['location'] = $qos_data['location'];
				$dateInform[$section]['idItem'] = $qos_data['idItem'];
				$dateInform[$section]['plan'] = $qos_data['plan'];
				$dateInform[$section]['nacD'] = $qos_data['nacD'];
				$dateInform[$section]['nacU'] = $qos_data['idItem'];
				$dateInform[$section]['locD'] = $qos_data['locD'];
				$dateInform[$section]['locU'] = $qos_data['locU'];
				$dateInform[$section]['locD'] = $qos_data['locD'];
				$dateInform[$section]['intD'] = $qos_data['intD'];
				$dateInform[$section]['intU'] = $qos_data['intU'];
				$dateInform[$section]['typeTH'] = $qos_data['typeTH'];
				$dateInform[$section]['unit'] = $qos_data['unit'];
				$dateInform[$section]['descriptionLong'] = $qos_data['descriptionLong'];
				$dateInform[$section][$qos_data['type']] = $qos_data['value'];

			}

		} else {
			return false;
		}

		if ($debug == true) {
			var_dump($dateInform);
			exit ;
		}

		//Calculando datos

		$PERCENTILE_REPORT_QOE = json_decode($this->parametro->get('PERCENTILE_REPORT_QOE', '[]'));

		foreach ($dateInform as $key => $value) {

			if ($value['typeTH'] == 'personalized') {
				$factor = 1;
			} else {
				if ($escala != false && is_numeric($escala)) {
					$factor = $escala;

					if ($escala == 1024 && $value['unit'] == 'bps') {
						$value['unit'] = 'Kbps';
					} elseif ($escala == 1048576 && $value['unit'] == 'bps') {
						$value['unit'] = 'Mbps';
					} elseif ($escala == 1073741824 && $value['unit'] == 'bps') {
						$value['unit'] = 'Gbps';
					}

				} else {
					$factor = 1;
				}
			}

			$result['title'] = $value['descriptionLong'];
			$result['plan'] = $value['plan'];
			$result['idPlan'] = $value['idPlan'];
			$result['location'] = $value['location'];

			$result['locationName'] = $value['locationName'];

			$result['mes'] = $clock;
			$result['total'] = $value['EXITOSA'] + $value['FALLIDA'];
			$result['exitosa'] = $value['EXITOSA'];
			$result['fallida'] = $value['FALLIDA'];
			$result['tasaFalla'] = number_format($value['FALLIDA'] / ($value['EXITOSA'] + $value['FALLIDA']) * 100, 2, ",", ".") . "%";

			//Calculando error de la prueba

			$e = 0;
			if (isset($value['FALLIDA']) && $value['FALLIDA'] > 0) {
				$p = $value['FALLIDA'] / $value['EXITOSA'];
				$np = $value['FALLIDA'];
				$q = 1 - $np;
				$d2 = $np * $q;
				$cnt = $value['EXITOSA'] + $value['FALLIDA'];
				if ($value['FALLIDA'] == 0) {
					$e = 0;
				} else {

					if ($d2 < 9) {
						$e = 1.96 * sqrt(($p * (1 - $p) / $cnt)) * 100;
					} else {
						$lp = "SELECT * from bsw_poisson where h=" . $value['FALLIDA'];
						$lpq = $this->conexion->queryFetch($lp);
						foreach ($lpq as $key => $value) {
							$k = $value["k"];
						}
						$e = $k / $cnt * 100;
					}
				}

			}

			if ($e > 5) {
				$color = '<FONT COLOR="red">';
			} else {
				$color = '<FONT COLOR="green">';
			}

			if ($opColor === true) {
				$result['errorPrueba'] = $color . number_format($e, 4, ",", ".") . "%</FONT>";
			} else {
				$result['errorPrueba'] = number_format($e, 4, ",", ".");
			}

			$result['nominalD'] = number_format($value['nominalD'], 0, ",", ".");
			$result['nominal'] = number_format($value['nominal'], 0, ",", ".");

			if ($value['warning'] > 0) {
				$color = '<FONT COLOR="red">';
			} else {
				$color = '<FONT COLOR="green">';
			}

			if ($opColor === true) {
				$result['warning'] = $color . number_format($value['warning'], 0, ",", ".") . "</FONT>";
			} else {
				$result['warning'] = number_format($value['warning'], 0, ",", ".");
			}

			if ($value['critical'] > 0) {
				$color = '<FONT COLOR="red">';
			} else {
				$color = '<FONT COLOR="green">';
			}

			if ($opColor === true) {
				$result['critical'] = $color . number_format($value['critical'], 0, ",", ".") . "</FONT>";
			} else {
				$result['critical'] = number_format($value['critical'], 0, ",", ".");
			}

			if ($unit === true) {
				$unitD = $value['unit'];
			} else {
				$unitD = '';
			}

			$result['promedio'] = number_format($value['AVG'] / $factor, 0, ",", ".") . " " . $unitD;
			$result['min'] = number_format($value['MIN'] / $factor, 0, ",", ".") . " " . $unitD;
			$result['max'] = number_format($value['MAX'] / $factor, 0, ",", ".") . " " . $unitD;
			$result['std'] = number_format($value['STD'] / $factor, 0, ",", ".");

			foreach ($PERCENTILE_REPORT_QOE as $keyper => $vpercentile) {
				$result['percentil' . $vpercentile] = number_format($value['PERCENTIL_' . $vpercentile] / $factor, 0, ",", ".") . " " . $unitD;
			}

			$result['confMedicion'] = number_format($value['INTERVALO'] / $factor, 0, ",", ".");
			;
			$result['errorMedicion'] = 0;

			if ($value['location'] == 0 && strlen($value['location']) == 1) {
				$inform[$value['idItem']]['data'][] = $result;
			} else {
				$inform[$value['idItem']]['location'][$value['idPlan']][] = $result;
			}

			$inform[$value['idItem']]['title'] = $value['descriptionLong'];
		}
		return $inform;
	}

	//Config Section

	public function getConfigItems($groupid) {
		$return['status'] = false;
		if (is_numeric($groupid)) {

			$getItemsSql = "SELECT IG.`groupid`,I.`id_item`, `name`, `descriptionLong`  
                    FROM `bm_items` I 
                        INNER JOIN `bm_profiles_values`  PV ON PV.`id_monitor`=I.`id_item`              
                        LEFT OUTER JOIN `bm_threshold` T ON I.`id_item`=T.`id_item`
                        LEFT OUTER JOIN `bm_items_groups` IG ON IG.`id_item`=I.`id_item`
                    WHERE 
                        IG.`groupid` = $groupid AND
                        T.`id_threshold` IS NULL AND
                        PV.`id_monitor` > 0
                        ORDER BY I.`descriptionLong`";

			$getItemsRESULT = $this->conexion->queryFetch($getItemsSql);

			if ($getItemsRESULT) {
				foreach ($getItemsRESULT as $key => $item) {
					$option[] = '<option value="' . $item['id_item'] . '">' . $item['descriptionLong'] . '</option>';
				}

				$return['status'] = true;
				$return['result'] = join("\n", $option);
			}
		}
		$this->basic->jsonEncode($return);
		exit ;
	}

	public function config() {
		$valida = $this->protect->access_page('QOS_CONFIG', true, 'QoE', 'Show Config');

		$this->plantilla->loadSec("qos/config", $valida, false);

		$groups = $this->bmonitor->getAllGroups();
		$groupsFirstID = $groups->firstKey();
		$getItemsSql = "SELECT IG.`groupid`,I.`id_item`, `name`, `descriptionLong`  
                FROM `bm_items` I 
                    INNER JOIN `bm_profiles_values`  PV ON PV.`id_monitor`=I.`id_item`              
                    LEFT OUTER JOIN `bm_threshold` T ON I.`id_item`=T.`id_item`
                    LEFT OUTER JOIN `bm_items_groups` IG ON IG.`id_item`=I.`id_item`
                WHERE 
                    IG.`groupid` = $groupsFirstID AND
                    T.`id_threshold` IS NULL AND
                    PV.`id_monitor` > 0
                    ORDER BY I.`descriptionLong`";

		$items = $this->conexion->queryFetch($getItemsSql);

		if ($items) {
			$varForm["optionMonitors"] = '';
			foreach ($items as $key => $item) {
				$varForm["optionMonitors"] .= '<option value="' . $item['id_item'] . '">' . $item['descriptionLong'] . '</option>';
			}
		}

		$varForm['optionGroups'] = $groups->formatOption();

		$varForm['formID'] = 'modalQoeConfigForm';

		$varForm['optionBiDashboard'] = $this->basic->getOptionValue('bi_dashboard');

		$vars['formNewMonitor'] = $this->plantilla->getOne("qos/configForm", $varForm);

		$PERCENTILE_TYPE_REPORT_QOE = $this->parametro->get('PERCENTILE_TYPE_REPORT_QOE', 1);

		if ($PERCENTILE_TYPE_REPORT_QOE == 0) {
			$vars['checked1'] = 'checked="checked"';
		} else {
			$vars['checked2'] = 'checked="checked"';
		}

		$PERCENTILE_TYPE_REPORT_QOE = json_decode($this->parametro->get('PERCENTILE_REPORT_QOE', '[]'));
		$vars['buttonPercentile'] = '';
		foreach ($PERCENTILE_TYPE_REPORT_QOE as $key => $percentile) {
			$vars['buttonPercentile'] .= "<button id='$percentile'>P$percentile</button>";
		}
		$vars['optionGroups'] = $groups->formatOption();
		$this->plantilla->set($vars);

		$this->plantilla->finalize();
	}

	public function getThreshold() {
		$getParam = (object)$_POST;

		if (isset($getParam->groupid) && is_numeric($getParam->groupid)) {
			$groupid = $getParam->groupid;
		} else {
			$groupid = 0;
		}

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'id_item');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		//$WHERE = " IG.`groupid` IN " .$this->bmonitor->getAllGroupsHost(false, 'sql');

		$getCountThresholdSQL = "SELECT COUNT(*) AS Total FROM `bm_threshold` T 
				LEFT OUTER JOIN `bm_items_groups` IG ON T.`id_item`=IG.`id_item` 
				WHERE IG.`groupid`=$groupid ";

		$getCountThresholdRESULT = $this->conexion->queryFetch($getCountThresholdSQL);

		if ($getCountThresholdRESULT) {
			$data['total'] = $getCountThresholdRESULT[0]['Total'];
		} else {
			$data['total'] = 0;
		}

		//Threshold

		$getThresholdSQL = "SELECT T.`id_threshold` , T.`id_item` , T.`active`, I.`descriptionLong`, T.`nominal`, T.`warning`, T.`critical`
                                FROM `bm_threshold` T
                                        LEFT JOIN `bm_items` I ON  I.`id_item`=T.`id_item`
                                        LEFT OUTER JOIN `bm_items_groups` IG ON T.`id_item`=IG.`id_item` WHERE IG.`groupid`=$groupid";

		$getThresholdRESULT = $this->conexion->queryFetch($getThresholdSQL);

		if ($getThresholdRESULT) {
			foreach ($getThresholdRESULT as $key => $value) {

				if ($value['active'] == 'true') {
					$availability = '<a onclick="statusThreshold(' . $value['id_threshold'] . ',true)" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" aria-disabled="false" title="Button on/off"> <span class="ui-button-icon-primary ui-icon ui-icon-power"></span><span class="ui-button-text">' . $this->language->ENABLED . '</span> </a>';
				} else {
					$availability = '<a onclick="statusThreshold(' . $value['id_threshold'] . ',false)" class="ui-button ui-widget ui-state-error ui-corner-all ui-button-icon-only" role="button" aria-disabled="false" title="Button on/off"> <span class="ui-button-icon-primary ui-icon ui-icon-power"></span><span class="ui-button-text">' . $this->language->DISABLED . '</span> </a>';
				}

				if ($value['nominal'] == '-1') {
					$nominal = '(Plan) Download';
				} elseif ($value['nominal'] == '-2') {
					$nominal = '(Plan) Upload';
				} else {
					$nominal = $value['nominal'];
				}

				$data['rows'][] = array(
					'id' => $value['id_threshold'],
					'cell' => array(
						'id_item' => $value['id_item'],
						'descriptionLong' => $value['descriptionLong'],
						'nominal' => $nominal,
						'warning' => $value['warning'],
						'critical' => $value['critical'],
						'status' => $availability,
						'option' => '<button id="' . $value['id_threshold'] . '" name="editThreshold">' . $this->language->EDIT . '</button>'
					)
				);
			}
		}

		$this->basic->jsonEncode($data);
		exit ;
	}

	public function threshold($method) {
		$getParam = (object)$_POST;
		$return['status'] = false;
		if (($method == 'add' || $method == 'edit' || $method == 'delete')) {

			if ($getParam->thresholdType == 0) {
				$getParam->thresholdType = 'personalized';
			} elseif ($getParam->thresholdType == 1) {
				$getParam->thresholdType = 'download';
			} elseif ($getParam->thresholdType == 2) {
				$getParam->thresholdType = 'upload';
			}

			if (isset($getParam->qoeReport)) {
				$getParam->qoeReport = 'true';
			} else {
				$getParam->qoeReport = 'false';
			}

			if (isset($getParam->qoeAlert)) {
				$getParam->alert = 'true';
			} else {
				$getParam->alert = 'false';
			}
			
			if(is_numeric($getParam->cycle) === false){
				$getParam->cycle = 3;
			}
					
			if ($method == 'add') {
				$return = $this->thresholdNew($getParam);
			} elseif ($method == 'edit') {
				$return = $this->thresholdEdit($getParam);
			} elseif ($method == 'delete') {
				$return = $this->thresholdDelete($getParam);
			}

		} elseif ($method == 'form' && is_numeric($getParam->idThreshold)) {
			$this->thresholdForm($getParam->idThreshold);
		} else {
			$return['error'] = 'Invalid Param or method';
		}

		echo $this->basic->jsonEncode($return);
		exit ;
	}

	public function thresholdForm($idThreshold) {
		$this->plantilla->load("qos/configForm", false);

		//Get DATA Threshold

		$getDataThresholdSQL = "SELECT  IG.`groupid`, T.`id_item` As idItem, `type` as thresholdType, `nominal`, `warning`, `critical`, T.`cicle` as 'cycle',
                                                    `emailOptional` as optionalmail , `alert` , `report` ,`dashboard`
                                                FROM `bm_threshold` T LEFT OUTER JOIN `bm_items_groups` IG ON T.`id_item`=IG.`id_item`  WHERE `id_threshold` = $idThreshold LIMIT 1;";

		$getDataThresholdRESULT = $this->conexion->queryFetch($getDataThresholdSQL);

		if ($getDataThresholdRESULT) {

			$threshold = (object)$getDataThresholdRESULT[0];

			$getItemsSql = 'SELECT I.`id_item` AS idItem, `name`, `descriptionLong`  
                    FROM `bm_items` I LEFT OUTER JOIN `bm_threshold` T ON I.`id_item`=T.`id_item`
                    WHERE T.`id_threshold` IS NULL  OR T.`id_threshold` = ' . $idThreshold;

			$getItemsRESULT = $this->conexion->queryFetch($getItemsSql);

			if ($getItemsRESULT) {
				$vars["optionMonitors"] = $this->basic->getOption($getItemsRESULT, "idItem", "descriptionLong", $threshold->idItem);
			}

			$vars = array_merge($vars, $getDataThresholdRESULT[0]);

			if ($threshold->alert == 'true') {
				$vars["checkedAlert"] = 'checked';
			}
			if ($threshold->report == 'true') {
				$vars["checkedReport"] = 'checked';
			}

			if ($threshold->thresholdType == 'personalized') {
				$vars["selectedthresholdType0"] = 'selected';
			} elseif ($threshold->thresholdType == 'download') {
				$vars["selectedthresholdType1"] = 'selected';
			} elseif ($threshold->thresholdType == 'upload') {
				$vars["selectedthresholdType2"] = 'selected';
			}

		}

		$vars['formID'] = 'modalQoeConfigFormEdit';

		$vars['optionBiDashboard'] = $this->basic->getOptionValue('bi_dashboard', $threshold->dashboard);

		$vars['optionGroups'] = $this->bmonitor->getAllGroups()->formatOption($threshold->groupid);

		$this->plantilla->set($vars);

		$this->plantilla->finalize();
	}

	private function thresholdNew($var) {
		
		$insertThresholdSQL = 'INSERT INTO `bm_threshold` ( `id_item`, `nominal`, `warning`, `critical`, `alert`, 
                        `alertSend`, `cicle`, `restoreSend`, `type`, `emailOptional`, `active`, `report`, `dashboard`) VALUES ';

		$insertThresholdValueSQL = "( $var->item, $var->nominal, $var->warning, $var->critical, '$var->alert', 'critical', '$var->cycle', 'true', '$var->thresholdType', '$var->optionalmail', 'true', '$var->qoeReport', '$var->dashboard')";

		$insertThresholdRESULT = $this->conexion->query($insertThresholdSQL . $insertThresholdValueSQL);

		if ($insertThresholdRESULT) {
			$return['status'] = true;
		} else {
			$return['status'] = false;
			$return['error'] = 'Error insert database';
		}

		return $return;
	}

	private function thresholdEdit($var) {
		
		$updateThresholdSQL = "UPDATE `bm_threshold` SET 
                                    `nominal` = '$var->nominal', 
                                    `warning` = '$var->warning', 
                                    `critical` = '$var->critical', 
                                    `alert` = '$var->alert' , 
                                    `type` = '$var->thresholdType', 
                                    `emailOptional` = '$var->optionalmail', 
                                    `report` = '$var->qoeReport',
                                    `cicle` = '$var->cycle',
                                    `dashboard` = '$var->dashboard'
                                WHERE `id_threshold` = '$var->idThreshold';";

		$updateThresholdRESULT = $this->conexion->query($updateThresholdSQL);

		if ($updateThresholdRESULT) {
			$return['status'] = true;
		} else {
			$return['status'] = false;
			$return['error'] = 'Error update database';
		}

		return $return;
	}

	private function thresholdDelete($var) {
		$deleteThresholdSQL = "DELETE FROM `bm_threshold` WHERE `id_threshold` IN (" . join(',', $var->idThreshold) . ")";

		$deleteThresholdRESULT = $this->conexion->query($deleteThresholdSQL);

		if ($deleteThresholdRESULT) {
			$return['status'] = true;
		} else {
			$return['status'] = false;
			$return['error'] = 'Error delete database';
		}

		return $return;

	}

	public function percetilTypeChange() {
		$getParam = (object)$_POST;

		$return['status'] = false;
		if (isset($getParam->percetilChecked) && is_numeric($getParam->percetilChecked)) {

			$validUpdate = $this->conexion->query("UPDATE `Parametros` SET `valor` = '" . $getParam->percetilChecked . "' WHERE `nombre` = 'PERCENTILE_TYPE_REPORT_QOE'");

			if ($validUpdate) {
				$return['status'] = true;
			} else {
				$return['error'] = 'Error update database';
			}

		} else {
			$return['error'] = 'Invalid Param';
		}

		$this->basic->jsonEncode($return);
		exit ;
	}

	public function percetilNumberChange($method = 'delete') {
		$getParam = (object)$_POST;

		$PERCENTILE_REPORT_QOE = array_unique(json_decode($this->parametro->get('PERCENTILE_REPORT_QOE', '[]')));

		$return['status'] = false;
		if (isset($getParam->percetilID) && is_numeric($getParam->percetilID)) {

			if ($method == 'add') {

				if (array_search((int)$getParam->percetilID, $PERCENTILE_REPORT_QOE) == false) {

					$PERCENTILE_REPORT_QOE[] = (int)$getParam->percetilID;

					$validUpdate = $this->conexion->query("UPDATE `Parametros` SET `valor` = '" . json_encode($PERCENTILE_REPORT_QOE) . "' WHERE `nombre` = 'PERCENTILE_REPORT_QOE'");

					if ($validUpdate) {
						$return['status'] = true;
					} else {
						$return['error'] = 'Error update database';
					}

				} else {
					$return['error'] = 'Item exists in the database';
				}
			} elseif ($method == 'delete') {

				unset($PERCENTILE_REPORT_QOE[array_search((int)$getParam->percetilID, $PERCENTILE_REPORT_QOE)]);

				$PERCENTILE_REPORT_QOE = array_values($PERCENTILE_REPORT_QOE);

				$validUpdate = $this->conexion->query("UPDATE `Parametros` SET `valor` = '" . json_encode($PERCENTILE_REPORT_QOE) . "' WHERE `nombre` = 'PERCENTILE_REPORT_QOE'");

				if ($validUpdate) {
					$return['status'] = true;
				} else {
					$return['error'] = 'Error update database';
				}

			} else {
				$return['error'] = 'Method error';
			}

		} else {
			$return['error'] = 'Invalid Param';
		}

		$this->basic->jsonEncode($return);
		exit ;
	}

	public function getMaps() {
		$valida = $this->protect->access_page('QOS_MAPS');
		$this->plantilla->loadSec("qos/maps", $valida, 360000);
		$grupos = $this->bmonitor->getAllGroups()->formatINI();

		$getLatAndLongSQL = "SELECT `id_host`,`host`, HF.`feature`, HD.`id_feature`,`value` 
                                FROM `bm_host` H 
                                    LEFT JOIN `bm_host_detalle` HD USING(`id_host`)
                                    LEFT JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature` 
                                WHERE
                                    `groupid` IN $grupos AND
                                    HD.`id_feature` IN (67,68,69,70,71,72) AND
                                    H.`borrado` = 0 AND
                                    HD.`value` != ''
                                 ORDER BY `id_host` ASC
                                 LIMIT 6";

		$getLatAndLongRESULT = $this->conexion->queryFetch($getLatAndLongSQL);

		if ($getLatAndLongRESULT) {
			foreach ($getLatAndLongRESULT as $key => $value) {
				$LatLngvalue[$value['feature']] = $value['value'];
			}
			$vars['decimalLat'] = $this->basic->convertDMStoDEC($LatLngvalue['latitud_grados'], $LatLngvalue['latitud_minutos'], $LatLngvalue['latitud_segudos']);
			$vars['decimalLng'] = $this->basic->convertDMStoDEC($LatLngvalue['longitud_grados'], $LatLngvalue['longitud_minutos'], $LatLngvalue['longitud_segundos']);
		} else {
			$vars['decimalLat'] = '-33.46912';
			$vars['decimalLng'] = '-70.641997';
		}

		$this->plantilla->set($vars);
		$this->plantilla->finalize();
	}

	public function getMapsXml() {
		//Get groups access user
		$grupos = $this->bmonitor->getAllGroups()->formatINI();

		// Start XML file, create parent node
		$dom = new DOMDocument("1.0");
		$node = $dom->createElement("markers");
		$parnode = $dom->appendChild($node);

		$getHostFeature = "SELECT `id_host`,`host`, HF.`feature`, HD.`id_feature`,`value` 
                                FROM `bm_host` H
                                    LEFT JOIN `bm_host_detalle` HD USING(`id_host`)
                                    LEFT JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature` 
                                WHERE 
                                    `groupid` IN $grupos AND
                                    HD.`id_feature` IN (22,23,24,43,67,68,69,70,71,72) AND
                                    H.`borrado` = 0
                                ORDER BY `id_host` ASC";

		$getHostFeature_result = $this->conexion->queryFetch($getHostFeature);

		if ($getHostFeature_result) {
			foreach ($getHostFeature_result as $key => $value) {
				$host[$value['id_host']]['name'] = $value['host'];
				$host[$value['id_host']][$value['feature']] = $value['value'];
			}

			foreach ($host as $key => $row) {

				if (isset($row['direccion']) && isset($row['latitud_grados']) && is_numeric($row['latitud_grados'])) {

					$node = $dom->createElement("marker");
					$newnode = $parnode->appendChild($node);

					$newnode->setAttribute("name", $row['name']);
					$newnode->setAttribute("address", $row['direccion']);

					$getDecimalLat = $this->basic->convertDMStoDEC($row['latitud_grados'], $row['latitud_minutos'], $row['latitud_segudos']);
					$getDecimalLng = $this->basic->convertDMStoDEC($row['longitud_grados'], $row['longitud_minutos'], $row['longitud_segundos']);

					$newnode->setAttribute("lat", $getDecimalLat);
					$newnode->setAttribute("lng", $getDecimalLng);
					$newnode->setAttribute("type", 'sonda');

				}

			}
		}

		header("Content-type: text/xml");
		echo $dom->saveXML();
		exit ;
	}

	public function exportReportGeneral($typeResult = 'csv') {
		$param = (object)$_POST;
		$result['status'] = false;

		//Genere Un Array y cambie la funcion chr dado que cambia por la configuracion del servidor

		$arryLetter = array(
			65 => 'A',
			66 => 'B',
			67 => 'C',
			68 => 'D',
			69 => 'E',
			70 => 'F',
			71 => 'G',
			72 => 'H',
			73 => 'I',
			74 => 'J',
			75 => 'K',
			76 => 'L',
			77 => 'M',
			78 => 'N',
			79 => 'O',
			80 => 'P',
			81 => 'Q',
			82 => 'R',
			83 => 'S',
			84 => 'T',
			85 => 'U',
			86 => 'V',
			87 => 'W',
			88 => 'X',
			89 => 'Y',
			90 => 'Z',
		);

		if ($typeResult == 'csv' || $typeResult = 'excel') {

			if ((is_numeric($param->meses)) && ($param->meses < 10)) {
				$param->meses = '0' . $param->meses;
			}

			$clock = $param->ano . "/" . $param->meses;

			$PERCENTILE_REPORT_QOE = json_decode($this->parametro->get('PERCENTILE_REPORT_QOE', '[]'));

			$resultQoeInfo = $this->getReport($clock, $param->grupo, $param->escala, false, false);

			if ($typeResult == 'excel') {
				include APPS . "plugins/PHPExcel.php";

				$objPHPExcel = new PHPExcel();

				$objPHPExcel->getProperties()->setCreator("Baking software")->setLastModifiedBy("bMonitor")->setTitle($this->language->GENERAL_REPORT)->setSubject("Export")->setDescription("Documento generado por report manager de bMonitor")->setKeywords("report")->setCategory("bMonitor");

				$page = 0;

				$line = 2;

				foreach ($resultQoeInfo as $skey => $source) {

					if ($page > 0) {
						$objPHPExcel->createSheet();
					}

					$letter = 65;

					$objPHPExcel->setActiveSheetIndex($page);

					$objPHPExcel->getActiveSheet()->setTitle($this->language->GENERAL_REPORT);

					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $source['title']);

					$line++;

					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Plan");
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->MONTH);
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Q");
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->SUCCESSFUL);
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->FAILED);
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->NOMINAL_OVERFLOW);
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Nominal");
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->WARNING);
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->CRITICAL);
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Prom");
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Mín");
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Máx");
					$letter++;
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Desv");
					$letter++;
					foreach ($PERCENTILE_REPORT_QOE as $keyPer => $percentile) {
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Per" . $percentile);
						$letter++;
					}

					$styleCell = array(
						'font' => array('bold' => true),
						'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_JUSTIFY, ),
						'borders' => array('top' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
						'fill' => array(
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'rotation' => 90,
							'startcolor' => array('argb' => 'deedf7'),
							'endcolor' => array('argb' => 'deedf7')
						)
					);

					$objPHPExcel->getActiveSheet()->mergeCells(chr(65) . ($line - 1) . ":" . chr($letter) . ($line - 1));
					$objPHPExcel->setActiveSheetIndex($page)->getStyle(chr(65) . ($line - 1) . ":" . chr($letter) . ($line - 1))->applyFromArray($styleCell);
					$objPHPExcel->setActiveSheetIndex($page)->getStyle(chr(65) . $line . ":" . chr($letter) . $line)->applyFromArray($styleCell);

					$letter = 65;

					$line++;

					foreach ($source['data'] as $key => $value) {
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['plan']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['mes']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['total']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['exitosa']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['fallida']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['nominalD']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['nominal']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['warning']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['critical']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['promedio']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['min']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['max']);
						$letter++;
						$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['std']);
						$letter++;

						foreach ($PERCENTILE_REPORT_QOE as $keyPer => $percentile) {
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $value['percentil' . $percentile]);
							$letter++;
						}

						$letter = 65;

						$line++;

						if (isset($source['location'][$value['idPlan']])) {

							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->LOCATION);
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Q");
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->SUCCESSFUL);
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->FAILED);
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->NOMINAL_OVERFLOW);
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Nominal");
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->WARNING);
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $this->language->CRITICAL);
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Prom");
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Mín");
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Máx");
							$letter++;
							$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Desv");
							$letter++;

							foreach ($PERCENTILE_REPORT_QOE as $keyPer => $percentile) {
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, "Per" . $percentile);
								$letter++;
							}
							$objPHPExcel->setActiveSheetIndex($page)->getStyle(chr(66) . $line . ":" . chr($letter) . $line)->applyFromArray($styleCell);

							$line++;

							$letter = 65;

							foreach ($source['location'][$value['idPlan']] as $keyLocation => $location) {
								$letter = 65;
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['locationName']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['total']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['exitosa']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['fallida']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['nominalD']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['nominal']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['warning']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['critical']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['promedio']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['min']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['max']);
								$letter++;
								$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['std']);
								$letter++;

								foreach ($PERCENTILE_REPORT_QOE as $keyPer => $percentile) {
									$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $location['percentil' . $percentile]);
									$letter++;
								}

								$line++;
							}

						}

					}

					$line++;
					$line++;
				}

				$objPHPExcel->setActiveSheetIndex(0);

				$file = 'qoe_report_general_' . $param->ano . "_" . $param->meses . "_" . date("Ymd") . '.xlsx';

				$path = site_path . '/upload/';

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

				$objWriter->save($path . $file);

				$result['status'] = true;
				$result['file'] = $file;

			}

		} else {
			$result['error'] = $this->language->ERROR_INVALID_PARAM;
		}

		$this->basic->jsonEncode($result);
		exit ;
	}

	public function speedtest() {
		$this->plantilla->load("qos/speedtest");

		$tpsVar['option_groups'] = $this->bmonitor->getAllGroups()->formatOption();

		$tpsVar["dateNow"] = date('Y-m-d');
		$tpsVar["firstDay"] = date('Y-m-01');

		$this->plantilla->set($tpsVar);

		$this->plantilla->finalize();
	}

	public function getTableSpeedtest() {
		$param = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'id_item');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		if (!isset($param->percentStartDownload) || !is_numeric($param->percentStartDownload)) {
			$param->percentStartDownload = 95;
		}

		if (!isset($param->percentEndDownload) || !is_numeric($param->percentEndDownload)) {
			$param->percentEndDownload = 110;
		}

		if (!isset($param->percentStartUpload) || !is_numeric($param->percentStartUpload)) {
			$param->percentStartUpload = 95;
		}

		if (!isset($param->percentEndUpload) || !is_numeric($param->percentEndUpload)) {
			$param->percentEndUpload = 110;
		}

		//Where
		$where = " WHERE `groupid` = " . $param->grupo . " AND `testDate` BETWEEN '" . $param->from . " 00:00:00' AND '" . $param->to . " 23:59:59'" . " AND `pdownload` BETWEEN '" . $param->percentStartDownload . "' AND '" . $param->percentEndDownload . "'" . " AND `pupload` BETWEEN '" . $param->percentStartUpload . "' AND '" . $param->percentEndUpload . "'";

		$getCountSQL = "SELECT COUNT(`id_speedtest`) AS Total FROM `report_speedtest` $where ";

		$getCountRESULT = $this->conexion->queryFetch($getCountSQL);
		//$this->logs->error("consulta1_ reporteQoE: ".$getCountSQL);
		if ($getCountRESULT) {
			$data['total'] = $getCountRESULT[0]['Total'];
		} else {
			$data['total'] = 0;
		}

		$getSpeedtestSQL = "SELECT `id_speedtest`, `ip`, `city`, `region`, `country`, `isp`, `latitude`, 
                                        `longitude`, `testDate`, `serverName`, `download`, `upload`, 
                                        `latency`, `browser`, `operatingSystem`, `userAgent`, `pdownload`, `pupload`
                                FROM `report_speedtest` $where  $var->sortSql $var->limitSql";

		$getSpeedtestRESULT = $this->conexion->queryFetch($getSpeedtestSQL);

		if ($getSpeedtestRESULT) {
			foreach ($getSpeedtestRESULT as $key => $value) {
				$data['rows'][] = array(
					'id' => $value['id_speedtest'],
					'cell' => array(
						'ip' => $value['ip'],
						'city' => $value['city'],
						'region' => $value['region'],
						'country' => $value['country'],
						'isp' => $value['isp'],
						'latitude' => $value['latitude'],
						'longitude' => $value['longitude'],
						'testDate' => $value['testDate'],
						'serverName' => $value['serverName'],
						'download' => $value['download'] / 1024,
						'upload' => $value['upload'] / 1024,
						'latency' => $value['latency'],
						'browser' => $value['browser'],
						'operatingSystem' => $value['operatingSystem'],
						'userAgent' => $value['userAgent'],
						'pdownload' => $value['pdownload'] . "%",
						'pupload' => $value['pupload'] . "%"
					)
				);
			}
		}

		$this->basic->jsonEncode($data);
		exit ;
	}

	public function exportReportSpeedtest($method = false) {
		$param = (object)$_POST;

		$result['status'] = false;

		if (!isset($param->percentStartDownload) || !is_numeric($param->percentStartDownload)) {
			$param->percentStartDownload = 95;
		}

		if (!isset($param->percentEndDownload) || !is_numeric($param->percentEndDownload)) {
			$param->percentEndDownload = 110;
		}

		if (!isset($param->percentStartUpload) || !is_numeric($param->percentStartUpload)) {
			$param->percentStartUpload = 95;
		}

		if (!isset($param->percentEndUpload) || !is_numeric($param->percentEndUpload)) {
			$param->percentEndUpload = 110;
		}

		$where = " WHERE `groupid` = " . $param->grupo . " AND `testDate` BETWEEN '" . $param->from . " 00:00:00' AND '" . $param->to . " 23:59:59'" . " AND `pdownload` BETWEEN '" . $param->percentStartDownload . "' AND '" . $param->percentEndDownload . "'" . " AND `pupload` BETWEEN '" . $param->percentStartUpload . "' AND '" . $param->percentEndUpload . "'";

		$getSpeedtestSQL = "SELECT `ip`, `city`, `region`, `country`, `isp`, REPLACE(`latitude`,'.',',') AS 'latitude', 
                                        REPLACE(`longitude`,'.',',') AS 'longitude', DATE_FORMAT(`testDate`, '%d/%m/%Y %H:%i:%s') as 'testDate', `serverName`, ROUND(`download` / 1024) as 'download', ROUND(`upload` / 1024) as 'upload', 
                                        `latency`, `browser`, `operatingSystem`, `userAgent`
                                FROM `report_speedtest` $where  ORDER BY  `testDate`";

		$getSpeedtestRESULT = $this->conexion->queryFetch($getSpeedtestSQL);
		$this->logs->error("consulta data para excel Speedtest: ".$getSpeedtestSQL);
		if ($getSpeedtestRESULT) {
			$file = 'qoe_report_speedtest_' . $param->from . "_" . $param->to . "_" . date("Ymd") . '.csv';

			$path = site_path . '/upload/';

			$fp = fopen($path . $file, 'w');

			fputcsv($fp, array_keys($getSpeedtestRESULT[0]), ";", '"');

			foreach ($getSpeedtestRESULT as $key => $value) {
				foreach ($value as $ekey => $evalue) {
					$evalue = $this->basic->fixEncoding($evalue);
					$search = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,Á,É,Í,Ó,Ú");
					$replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,A,E,I,O,U");
					$string_limpio = str_replace($search, $replace, $evalue);
					$evalue = iconv('UTF-8', 'ASCII//TRANSLIT', $evalue);
					$pvalu[$ekey] = $string_limpio;
				}
				fputcsv($fp, $pvalu, ";", '"');
			}

			fclose($fp);
			$result['status'] = true;
			$result['file'] = $file;
		} else {
			$result['error'] = $this->language->ERROR_INVALID_PARAM;
		}

		$this->basic->jsonEncode($result);
		exit ;

	}

	public function mintic() {
		$this->plantilla->load("qos/minticExport");

		$tpsVar['option_groups'] = $this->bmonitor->getAllGroups()->formatOption();

		$tpsVar["firstDay"] = date('Y-m-01');
		$tpsVar["dateNow"] = date('Y-m-d');

		$this->plantilla->set($tpsVar);

		$this->plantilla->finalize();
	}

	public function minticGroups() {
		$this->plantilla->load("qos/minticGroups");

		$groups = $this->bmonitor->getAllGroups();

		$tpsVar['option_groups'] = $groups->formatOption();

		//Get Option Server
		$getServerSpeedSQL = "SELECT `serverName` FROM `report_speedtest` WHERE `groupid` = " . $groups->firstKey() . " GROUP BY `serverName`";

		$getServerSpeedRESULT = $this->conexion->queryFetch($getServerSpeedSQL);

		if ($getServerSpeedRESULT) {
			$tpsVar['option_speedServer'] = '<option value="0">' . $this->language->ALL . '</option>' . $this->basic->getOption($getServerSpeedRESULT, 'serverName', 'serverName', '-first-');
		}

		$tpsVar["firstDay"] = date('Y-m-01');
		$tpsVar["dateNow"] = date('Y-m-d');

		$this->plantilla->set($tpsVar);

		$this->plantilla->finalize();
	}

	public function exportItems() {
		$this->plantilla->load("qos/export");

		$groups = $this->bmonitor->getAllGroups();

		$tpsVar['option_measurement'] = '<option value="0">' . $this->language->ALL . '</option>' . $this->bmonitor->getAllItems($groups->firstKey())->formatOption();
		$tpsVar['option_planes'] = '<option value="0">' . $this->language->ALL . '</option>' . $this->bmonitor->getAllPlans($groups->firstKey())->formatOption();
		$tpsVar['option_sondas'] = $this->bmonitor->getAllAgents($groups->firstKey())->formatOption();
		$tpsVar['option_groups'] = $groups->formatOption();

		$tpsVar["dateNow"] = date('Y-m-d');

		$this->plantilla->set($tpsVar);

		$this->plantilla->finalize();
	}

	public function getServerSpeed($groupid) {
		$return['status'] = false;
		if (is_numeric($groupid)) {
			//Get Option Server
			$getServerSpeedSQL = "SELECT `serverName` FROM `report_speedtest` WHERE `groupid` = " . $groupid . " GROUP BY `serverName`";

			$getServerSpeedRESULT = $this->conexion->queryFetch($getServerSpeedSQL);

			if ($getServerSpeedRESULT) {
				$return['status'] = true;
				$return['option'] = '<option value="0">' . $this->language->ALL . '</option>' . $this->basic->getOption($getServerSpeedRESULT, 'serverName', 'serverName', '-first-');
			}
		}

		$this->basic->jsonEncode($return);
	}

	public function getExportItemsOption($groupid) {
		$return['status'] = false;
		if (is_numeric($groupid)) {
			//Get Option Server
			$return['status'] = true;
			$return['optionMeasurement'] = '<option value="0">' . $this->language->ALL . '</option>' . $this->bmonitor->getAllItems($groupid)->formatOption();
			$return['optionPlanes'] = '<option value="0">' . $this->language->ALL . '</option>' . $this->bmonitor->getAllPlans($groupid)->formatOption();
			$return['optionSondas'] = '<option value="0">' . $this->language->ALL . '</option>' . $this->bmonitor->getAllAgents($groupid)->formatOption();
		}
		$this->basic->jsonEncode($return);
	}

	public function getExportItems($method = false) {
		$param = (object)$_POST;

		$result['status'] = false;

		if (!isset($param->groupid) || !is_numeric($param->groupid)) {
			$this->basic->jsonEncode($result);
			exit ;
		}

		if (!isset($param->measurement) || $param->measurement == '0') {
			$param->measurement = '';
		} else {
			$param->measurement = ' AND H.`id_item` = ' . $param->measurement;
		}

		if (!isset($param->plan) || $param->plan == '0') {
			$param->plan = '';
		} else {
			$param->plan = '';
		}

		if (!isset($param->agent) || $param->agent == 0) {
			$param->agent = '';
		} else {
			$param->agent = ' AND H.`id_host` = ' . $param->agent;
		}

		$getSpeedtestSQL = "SELECT  H.`id_host` as 'idHost', HO.`host` , H.`clock` , FROM_UNIXTIME(H.`clock`) as 'timestamp' ,  I.`descriptionLong` , H.`value`
			    FROM `bm_history` H
			        LEFT JOIN `bm_items` I ON H.`id_item` = I.`id_item`
			        LEFT JOIN `bm_host` HO ON HO.`id_host`=H.`id_host` 
			    WHERE (H.`clock` BETWEEN UNIX_TIMESTAMP('" . $param->from . "') AND UNIX_TIMESTAMP(adddate('" . $param->to . "', interval 1 month))-1) AND HO.`groupid`=" . $param->groupid . $param->measurement . $param->agent . " ORDER BY `clock`";
		$getSpeedtestRESULT = $this->conexion->queryFetch($getSpeedtestSQL);

		if ($getSpeedtestRESULT) {
			$file = 'qoe_export_' . $param->from . "_" . $param->to . "_" . date("Ymd") . '.csv';

			$path = site_path . '/upload/';

			$fp = fopen($path . $file, 'w');

			fputcsv($fp, array_keys($getSpeedtestRESULT[0]), ";", '"');

			foreach ($getSpeedtestRESULT as $key => $value) {
				foreach ($value as $ekey => $evalue) {
					$pvalu[$ekey] = $evalue;
				}
				fputcsv($fp, $pvalu, ";", '"');
			}

			fclose($fp);
			$result['status'] = true;
			$result['file'] = $file;
		} else {
			$result['error'] = $this->language->ERROR_INVALID_PARAM;
		}

		$this->basic->jsonEncode($result);
		exit ;
	}

	public function getTableMintic() {
		$param = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'id_item');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		if (!isset($param->percentStartDownload) || !is_numeric($param->percentStartDownload)) {
			$param->percentStartDownload = 95;
		}

		if (!isset($param->percentEndDownload) || !is_numeric($param->percentEndDownload)) {
			$param->percentEndDownload = 110;
		}

		if (!isset($param->percentStartUpload) || !is_numeric($param->percentStartUpload)) {
			$param->percentStartUpload = 95;
		}

		if (!isset($param->percentEndUpload) || !is_numeric($param->percentEndUpload)) {
			$param->percentEndUpload = 110;
		}

		//Where
		$where = " WHERE `groupid` = " . $param->grupo . " AND `testDate` BETWEEN '" . $param->from . " 00:00:00' AND '" . $param->to . " 23:59:59'" . " AND `pdownload` BETWEEN '" . $param->percentStartDownload . "' AND '" . $param->percentEndDownload . "'" . " AND `pupload` BETWEEN '" . $param->percentStartUpload . "' AND '" . $param->percentEndUpload . "'";

		$getCountSQL = "SELECT COUNT(`id_speedtest`) AS Total FROM `report_speedtest` $where ";

		$getCountRESULT = $this->conexion->queryFetch($getCountSQL);

		if ($getCountRESULT) {
			$data['total'] = $getCountRESULT[0]['Total'];
		} else {
			$data['total'] = 0;
		}

		$getSpeedtestSQL = "SELECT 'id_speedtest',
                        '518' AS 'NUMEROCONTRATO', 
                        SUBSTRING(`date`,1,4) AS 'ANO', 
                        '0' AS 'NUMEROREFERENCIAPAGO', 
                        'NO ASIGNADO' AS 'IDBENEFECIARIO',
                         ROUND(`download` / 1024) AS 'VELOCIDADBAJADA',
                         ROUND(`upload` / 1024) AS 'VELOCIDADSUBIDA',
                         DATE_FORMAT(`testDate`, '%Y-%m-%d %H:%i:%s') AS 'FECHAMEDICION',
                         `daneDepartamento` AS 'DANEDEPARTAMENTO',
                         `daneMunicipio` AS 'DANEMUNICIPIO',
                         'NA' AS 'DANECODIGOCENTROPOBLADO',
                         '' AS 'MARCATIEMPO',
                         '' AS 'MARCATIEMPO2',
                         '0' AS 'CUENTA'
            FROM `report_speedtest` $where  $var->sortSql $var->limitSql";

		$getSpeedtestRESULT = $this->conexion->queryFetch($getSpeedtestSQL);

		if ($getSpeedtestRESULT) {
			foreach ($getSpeedtestRESULT as $key => $value) {
				$data['rows'][] = array(
					'id' => $value['id_speedtest'],
					'cell' => array(
						'NUMEROCONTRATO' => $value['NUMEROCONTRATO'],
						'ANO' => $value['ANO'],
						'NUMEROREFERENCIAPAGO' => $value['NUMEROREFERENCIAPAGO'],
						'IDBENEFECIARIO' => $value['IDBENEFECIARIO'],
						'VELOCIDADBAJADA' => $value['VELOCIDADBAJADA'],
						'VELOCIDADSUBIDA' => $value['VELOCIDADSUBIDA'],
						'FECHAMEDICION' => $value['FECHAMEDICION'],
						'DANEDEPARTAMENTO' => $value['DANEDEPARTAMENTO'],
						'DANEMUNICIPIO' => $value['DANEMUNICIPIO'],
						'DANECODIGOCENTROPOBLADO' => $value['DANECODIGOCENTROPOBLADO'],
						'MARCATIEMPO' => $value['MARCATIEMPO'],
						'MARCATIEMPO2' => $value['MARCATIEMPO2'],
						'CUENTA' => $value['CUENTA']
					)
				);
			}
		}

		$this->basic->jsonEncode($data);
		exit ;
	}

	public function getTableMinticGroups() {
		$param = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'id_item');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		if (!isset($param->percentStartDownload) || !is_numeric($param->percentStartDownload)) {
			$param->percentStartDownload = 95;
		}

		if (!isset($param->percentEndDownload) || !is_numeric($param->percentEndDownload)) {
			$param->percentEndDownload = 110;
		}

		if (!isset($param->percentStartUpload) || !is_numeric($param->percentStartUpload)) {
			$param->percentStartUpload = 95;
		}

		if (!isset($param->percentEndUpload) || !is_numeric($param->percentEndUpload)) {
			$param->percentEndUpload = 110;
		}

		if (!isset($param->percentValidDownload) || !is_numeric($param->percentValidDownload)) {
			$param->percentValidDownload = 100;
		}

		if (!isset($param->percentValidUpload) || !is_numeric($param->percentValidUpload)) {
			$param->percentValidUpload = 110;
		}

		if (!isset($param->server) || ($param->server == '0')) {
			$param->server = '';
		} else {
			$param->server = ' AND `serverName` = \'' . $param->server . "'";
		}

		if ($param->grupingData == 'city') {
			$grouopBy = 'L.`name`';
		} elseif ($param->grupingData == 'dmuni') {
			$grouopBy = 'RP.`daneMunicipio`';
		} elseif ($param->grupingData == 'ddepa') {
			$grouopBy = 'RP.`daneDepartamento`';
		} elseif ($param->grupingData == 'agent') {
			$grouopBy = 'L.`host`';
		}
	
		//Where
		$where = " WHERE RP.`groupid` = " . $param->grupo . " AND ( `pdownload` BETWEEN '" . $param->percentStartDownload . "' AND '" . $param->percentEndDownload . "' )" . " AND ( `pupload` BETWEEN '" . $param->percentStartUpload . "' AND '" . $param->percentEndUpload . "' )" . " AND ( `testDate` BETWEEN '" . $param->from . " 00:00:00' AND '" . $param->to . " 23:59:59')" . $param->server;
	
		$getCountSQL = "SELECT COUNT(DISTINCT $grouopBy) AS Total FROM `report_speedtest` RP LEFT JOIN `tableCacheLocation` L ON RP.`idHost`=L.`id_host` $where ";

		$getCountRESULT = $this->conexion->queryFetch($getCountSQL);

		if ($getCountRESULT) {
			$data['total'] = $getCountRESULT[0]['Total'];
		} else {
			$data['total'] = 0;
		}

		$getSpeedtestSQL = "SELECT `id_speedtest`, L.`host`,
					$grouopBy as 'groups', 
				 	SUM(IF(`pdownload` >= $param->percentValidDownload,1,0)) AS 'mDownloadOk',  
				 	SUM(IF(`pdownload` < $param->percentValidDownload,1,0)) AS 'mDownloadFallidas' ,  
				 	SUM(IF(`pupload` >= $param->percentValidUpload,1,0)) AS 'mUploadOk',  
				 	SUM(IF(`pupload` < $param->percentValidUpload,1,0)) AS 'mUploadFallidas', 
				 	ROUND(AVG(RP.`download`) / 1024)  AS 'vavgdown', 
				 	ROUND(AVG(RP.`upload`)  / 1024) AS 'vavgupload',
				 	'0' as 'cumplimientoDown',
				 	'0' as 'cumplimientoUpload',
				 	L.`additionalForm`, 
				 	L.`minTest`	 	
				FROM `report_speedtest` RP 
					INNER JOIN  tableCacheLocation L ON  L.`id_host`=RP.`idHost`
					$where GROUP BY groups $var->sortSql  $var->limitSql";

		$getSpeedtestRESULT = $this->conexion->queryFetch($getSpeedtestSQL);

		if ($getSpeedtestRESULT) {
			foreach ($getSpeedtestRESULT as $key => $value) {
					
				$detailJson = json_decode($value['additionalForm']);
				foreach ($detailJson as $keyJson => $valueJson) {
					$detail[$valueJson->key] = $valueJson->value;
				};
				
				//Download

				$cumplimientoDown = (100 * ($value['mDownloadOk'])) / $value['minTest'];
				$cumplimientoUpload = (100 * ($value['mUploadOk'])) / $value['minTest'];

				$data['rows'][] = array(
					'id' => $value['id_speedtest'],
					'cell' => array(
						'groups' => $value['groups'],
						'mEESperada' => $value['minTest'],
						'mDownloadOk' => $value['mDownloadOk'],
						'mDownloadFallidas' => $value['mDownloadFallidas'],
						'mUploadOk' => $value['mUploadOk'],
						'mUploadFallidas' => $value['mUploadFallidas'],
						'vavgdown' => (is_null($value['vavgdown'])) ? 0 : $value['vavgdown'],
						'vavgupload' => (is_null($value['vavgupload'])) ? 0 : $value['vavgupload'],
						'cumplimientoDown' => number_format($cumplimientoDown,0,',',''),
						'cumplimientoUpload' => number_format($cumplimientoUpload,0,',','')
					)
				);
			}
		}

		$this->basic->jsonEncode($data);
		exit ;
	}

	public function exportReportMintic($method = false) {
		$param = (object)$_POST;

		$result['status'] = false;

		if (!isset($param->percentStartDownload) || !is_numeric($param->percentStartDownload)) {
			$param->percentStartDownload = 95;
		}

		if (!isset($param->percentEndDownload) || !is_numeric($param->percentEndDownload)) {
			$param->percentEndDownload = 110;
		}

		if (!isset($param->percentStartUpload) || !is_numeric($param->percentStartUpload)) {
			$param->percentStartUpload = 95;
		}

		if (!isset($param->percentEndUpload) || !is_numeric($param->percentEndUpload)) {
			$param->percentEndUpload = 110;
		}

		$where = " WHERE `groupid` = " . $param->grupo . " AND `testDate` BETWEEN '" . $param->from . " 00:00:00' AND '" . $param->to . " 23:59:59'" . " AND `pdownload` BETWEEN '" . $param->percentStartDownload . "' AND '" . $param->percentEndDownload . "'" . " AND `pupload` BETWEEN '" . $param->percentStartUpload . "' AND '" . $param->percentEndUpload . "'";

		$getSpeedtestSQL = "SELECT 
                    '518' AS 'NUMEROCONTRATO', 
                    SUBSTRING(`date`,1,4) AS 'ANO', 
                    '0' AS 'NUMEROREFERENCIAPAGO', 
                    'NO ASIGNADO' AS 'IDBENEFECIARIO',
                     ROUND(`download` / 1024) AS 'VELOCIDADBAJADA',
                     ROUND(`upload` / 1024) AS 'VELOCIDADSUBIDA',
                     DATE_FORMAT(`testDate`, '%Y-%m-%d %H:%i:%s') AS 'FECHAMEDICION',
                     `daneDepartamento` AS 'DANEDEPARTAMENTO',
                     `daneMunicipio` AS 'DANEMUNICIPIO',
                     'NA' AS 'DANECODIGOCENTROPOBLADO',
                     '' AS 'MARCATIEMPO',
                     '' AS 'MARCATIEMPO2',
                     '0' AS 'CUENTA'
                        FROM `report_speedtest` $where  ORDER BY  `testDate`";

		$getSpeedtestRESULT = $this->conexion->queryFetch($getSpeedtestSQL);

		if ($getSpeedtestRESULT) {
			$file = 'qoe_report_mintic_' . $param->from . "_" . $param->to . "_" . date("Ymd") . '.csv';

			$path = site_path . '/upload/';

			$fp = fopen($path . $file, 'w');

			fputcsv($fp, array_keys($getSpeedtestRESULT[0]), ";", '"');

			foreach ($getSpeedtestRESULT as $key => $value) {
				foreach ($value as $ekey => $evalue) {
					$evalue = $this->basic->fixEncoding($evalue);
					$search = explode(",", "ç,æ,œ,á,é,í,ó,ú,à,è,ì,ò,ù,ä,ë,ï,ö,ü,ÿ,â,ê,î,ô,û,å,e,i,ø,u,Á,É,Í,Ó,Ú");
					$replace = explode(",", "c,ae,oe,a,e,i,o,u,a,e,i,o,u,a,e,i,o,u,y,a,e,i,o,u,a,e,i,o,u,A,E,I,O,U");
					$string_limpio = str_replace($search, $replace, $evalue);
					$evalue = iconv('UTF-8', 'ASCII//TRANSLIT', $evalue);
					$pvalu[$ekey] = $string_limpio;
				}
				fputcsv($fp, $pvalu, ";", '"');
			}

			fclose($fp);
			$result['status'] = true;
			$result['file'] = $file;
		} else {
			$result['error'] = $this->language->ERROR_INVALID_PARAM;
		}

		$this->basic->jsonEncode($result);
		exit ;

	}

	public function locations() {
		$valida = $this->protect->access_page('CONFIG_LOCATION', true, 'Configuration', 'Show Location');

		$this->plantilla->loadSec("qos/location", $valida, false);

		$varForm['formID'] = 'modalFormNewQoeLocation';

		$varForm['optionContinen'] = $this->basic->getLocation('continent');

		$PARAM_LOCATION_ADDITIONAL = json_decode($this->parametro->get('PARAM_LOCATION_ADDITIONAL', '[]'));

		if (count($PARAM_LOCATION_ADDITIONAL) > 0) {
			foreach ($PARAM_LOCATION_ADDITIONAL as $key => $value) {
				if (!isset($value->width)) {
					$value->width = 200;
				}
				if (isset($value->type) && $value->type == 'select') {
					$valueOption = explode(',', $value->option);
					$option = '';
					foreach ($valueOption as $keyOption => $valueOption) {
						if ("$valueOption" == "$value->value") {
							$selected = 'selected';
						} else {
							$selected = '';
						}
						$option .= "<option $selected>$valueOption</option>";
					}
					$rows[] = '<div class="row">
									<label for="additional_' . $value->key . '" class="col1">' . $value->label . '</label><span class="col2">
										<select name="additional_' . $value->key . '" id="additional_' . $value->key . '" class="select">
											' . $option . '
										</select>
									</span>
								</div>';
				} else {
					$rows[] = '<div class="row">
									<label for="additional_' . $value->key . '" class="col1">' . $value->label . '</label><span class="col2">
										<input type="text" style="width: ' . $value->width . 'px" name="additional_' . $value->key . '" id="additional_' . $value->key . '" value="" class="input ui-widget-content ui-corner-all">
									</span>
								</div>';
				}
			}

			$varForm['additionalInput'] = join("\n", $rows);
		}

		$vars['qoeLocationNewForm'] = $this->plantilla->getOne("qos/locationForm", $varForm);

		$this->plantilla->set($vars);

		$this->plantilla->finalize();
	}

	public function getTableLocation() {
		$param = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'idLocation');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		if ($param->city !== '') {
			$where = "WHERE `city` LIKE '%" . $param->city . "%'";
		} else {
			$where = '';
		}

		//Total
		$getCountSQL = "SELECT COUNT(*) as 'Total' FROM `bm_location` $where;";
		$getCountRESULT = $this->conexion->queryFetch($getCountSQL);

		if ($getCountRESULT) {
			$data['total'] = $getCountRESULT[0]['Total'];
		} else {
			$data['total'] = 0;
		}

		//Data
		$getDataSQL = "SELECT `idLocation`, `city`, `idState` FROM `bm_location` $where $var->sortSql  $var->limitSql";

		$getDataRESULT = $this->conexion->queryFetch($getDataSQL);

		if ($getDataRESULT) {
			foreach ($getDataRESULT as $key => $value) {
				$data['rows'][] = array(
					'id' => $value['idLocation'],
					'cell' => array(
						'idLocation' => $value['idLocation'],
						'idState' => $this->basic->getLocationValue('state', $value['idState']),
						'city' => $value['city'],
						'option' => '<button id="' . $value['idLocation'] . '" name="editLocation">' . $this->language->EDIT . '</button>'
					)
				);
			}
		}

		$this->basic->jsonEncode($data);
		exit ;
	}

	public function dbLocation($idLocation = false) {
		$getParam = (object)$_POST;

		$result['status'] = false;

		if (isset($getParam->city) && isset($getParam->state) && ($getParam->city != '')) {

			$city = $this->conexion->quote($getParam->city);
			$latitude = $this->conexion->quote($getParam->latitude);
			$longitude = $this->conexion->quote($getParam->longitude);

			if (is_numeric($getParam->minTest)) {

				foreach ($getParam as $key => $value) {
					if (preg_match("/^additional/i", $key)) {
						$keyName = explode("_", $key, 2);
						$additional[$keyName[1]] = $value;
					}
				}

				$PARAM_LOCATION_ADDITIONAL = json_decode($this->parametro->get('PARAM_LOCATION_ADDITIONAL', '[{}]'));

				foreach ($PARAM_LOCATION_ADDITIONAL as $key => $value) {
					$additionalInsert[$key]['key'] = $value->key;
					$additionalInsert[$key]['label'] = $value->label;
					$additionalInsert[$key]['value'] = (isset($additional[$value->key])) ? $additional[$value->key] : $value->value;
					$additionalInsert[$key]['width'] = (isset($value->width)) ? $value->width : 200;
					$additionalInsert[$key]['type'] = (isset($value->type)) ? $value->type : 'input';
					$additionalInsert[$key]['option'] = (isset($value->option)) ? $value->option : '';
				}

				$additionalInsert = json_encode($additionalInsert);

				if ($idLocation == false) {

					$insertSQL = "INSERT INTO `bm_location` (`idContinent`, `idCountry`, `idState`, `city`, `latitude`, `longitude`, `minTest`, `additionalForm`)
									VALUES (
											'$getParam->continent', 
											'$getParam->country', 
											'$getParam->state', $city, $latitude, $longitude, $getParam->minTest, '$additionalInsert');";
					$insertRESULT = $this->conexion->query($insertSQL);
				} elseif (is_numeric($idLocation)) {

					$insertSQL = "UPDATE `bm_location` 
										SET `idContinent` = '$getParam->continent', 
											`idCountry` = '$getParam->country', 
											`idState` = '$getParam->state', 
											`city` = $city, 
											`latitude` = $latitude, `longitude` = $longitude, `minTest` = '$getParam->minTest' , `additionalForm` = '$additionalInsert' WHERE `idLocation` = '$idLocation';";

					$insertRESULT = $this->conexion->query($insertSQL);
				} else {
					$insertRESULT = false;
				}

				if ($insertRESULT) {

					$lastInsertId = $this->conexion->lastInsertId();

					$result['status'] = true;

				} else {
					$result['error'] = $this->language->ITERNAL_ERROR;
				}

			} else {
				$result['error'] = $this->language->ERROR_INVALID_PARAM;
			}

		} else {
			$result['error'] = $this->language->ERROR_INVALID_PARAM;
		}

		$this->basic->jsonEncode($result);
	}

	public function deleteLocation() {
		$getParam = (object)$_POST;

		$result['status'] = false;

		if (isset($getParam->id) && ($getParam->id != '')) {

			if (is_array($getParam->id)) {

				$deleteSQL = "DELETE FROM `bm_location` WHERE `idLocation` IN ";

				$deleteValueSQL = $this->conexion->arrayIN($getParam->id, false, true);

				$deleteRESULT = $this->conexion->query($deleteSQL . $deleteValueSQL);

				if ($deleteRESULT) {
					$result['status'] = true;
				} else {
					$result['error'] = $this->language->ITERNAL_ERROR;
				}

			} else {
				$result['error'] = $this->language->ERROR_INVALID_PARAM;
			}

		} else {
			$result['error'] = $this->language->ERROR_INVALID_PARAM;
		}

		$this->basic->jsonEncode($result);
	}

	public function getLocationForm() {
		$var = (object)$_POST;

		if (is_numeric($var->idLocation)) {

			$valida = $this->protect->access_page('CONFIG_LOCATION', true, 'Configuration', 'Show Location -> edit');

			$this->plantilla->loadSec("qos/locationForm", $valida, false);

			//GetLocation
			$getLocationSQL = "SELECT `idContinent`, `idCountry`, `idState`, `city`, `latitude`, `longitude`, `minTest` ,`additionalForm` 
									FROM `bm_location` 
										WHERE `idLocation` = $var->idLocation LIMIT 1 ";

			$getLocationRESULT = $this->conexion->queryFetch($getLocationSQL);

			if ($getLocationRESULT) {
				$location = (object)$getLocationRESULT[0];

				$vars['optionContinen'] = $this->basic->getLocation('continent', false, $location->idContinent);
				$vars['optionCountry'] = $this->basic->getLocation('country', $location->idContinent, $location->idCountry);
				$vars['optionState'] = $this->basic->getLocation('state', $location->idCountry, $location->idState);
				$additional = json_decode($location->additionalForm);

				if (count($additional) > 0) {

					foreach ($additional as $key => $value) {
						if (isset($value->type) && $value->type == 'select') {
							$valueOption = explode(',', $value->option);
							$option = '';
							foreach ($valueOption as $keyOption => $valueOption) {
								if ("$valueOption" == "$value->value") {
									$selected = 'selected';
								} else {
									$selected = '';
								}

								$option .= "<option $selected>$valueOption</option>";
							}
							$rows[] = '<div class="row">
											<label for="additional_' . $value->key . '" class="col1">' . $value->label . '</label><span class="col2">
												<select name="additional_' . $value->key . '" id="additional_' . $value->key . '" class="select">
													' . $option . '
												</select>
											</span>
										</div>';
						} else {
							$rows[] = '<div class="row">
										<label for="additional_' . $value->key . '" class="col1">' . $value->label . '</label><span class="col2">
											<input type="text" style="width: ' . $value->width . 'px" name="additional_' . $value->key . '" id="additional_' . $value->key . '" value="' . $value->value . '" class="input ui-widget-content ui-corner-all">
										</span>
									</div>';
						}
					}

					$vars['additionalInput'] = join("\n", $rows);
				}

				$vars = array_merge((array)$vars, (array)$location);
			}

			$vars['formID'] = 'modalFormEditQoeLocation';

			$this->plantilla->set($vars);

			$this->plantilla->finalize();

		} else {
			echo $this->language->ERROR_INVALID_PARAM;
			exit ;
		}
	}

	public function getExportMintic() {

		$param = (object)$_POST;
		
		if (!isset($param->percentStartDownload) || !is_numeric($param->percentStartDownload)) {
			$param->percentStartDownload = 95;
		}

		if (!isset($param->percentEndDownload) || !is_numeric($param->percentEndDownload)) {
			$param->percentEndDownload = 110;
		}

		if (!isset($param->percentStartUpload) || !is_numeric($param->percentStartUpload)) {
			$param->percentStartUpload = 95;
		}

		if (!isset($param->percentEndUpload) || !is_numeric($param->percentEndUpload)) {
			$param->percentEndUpload = 110;
		}
		
		$where = "AND ( `pdownload` BETWEEN '" . $param->percentStartDownload . "' AND '" . $param->percentEndDownload . "' )" . " AND ( `pupload` BETWEEN '" . $param->percentStartUpload . "' AND '" . $param->percentEndUpload . "' )" . " AND ( `testDate` BETWEEN '" . $param->from . " 00:00:00' AND '" . $param->to . " 23:59:59')";
		

		$getSpeedtestSQL = "SELECT H.`id_host`, HD1.idLocation, 
							'100' as 'mEESperada',
							SUM(IF(RP.`pdownload` >= 100,1,0)) AS 'mDownloadOk',  
				 			SUM(IF(RP.`pdownload` < 100,1,0)) AS 'mDownloadFallidas' ,  
				 			SUM(IF(RP.`pupload` >= 100,1,0)) AS 'mUploadOk',  
				 			SUM(IF(RP.`pupload` < 100,1,0)) AS 'mUploadFallidas', 
				 			ROUND(AVG(RP.`download`) / 1024)  AS 'vavgdown', 
				 			ROUND(AVG(RP.`upload`)  / 1024) AS 'vavgupload',
							'0' as 'cumplimientoDown',
				 			'0' as 'cumplimientoUpload' , 
				 			L.`additionalForm` , 
				 			L.`minTest` , 
				 			CONCAT('SDS ', HD2.city ,' (', L.`city`,')') AS 'name'
							FROM `bm_host` H 
								LEFT OUTER JOIN (SELECT  `id_host`, `value` as 'idLocation' FROM `bm_host_detalle` WHERE `id_feature` = 78) HD1 ON ( H.`id_host`=HD1.`id_host`)
								LEFT OUTER JOIN (SELECT  `id_host`, `value` as 'city' FROM `bm_host_detalle` WHERE `id_feature` = 79) HD2 ON ( H.`id_host`=HD2.`id_host`)
								LEFT OUTER JOIN `bm_location` L ON L.`idLocation`=HD1.`idLocation`
								LEFT OUTER JOIN `report_speedtest` RP ON RP.`idHost`=H.`id_host` $where
						WHERE  H.`groupid` =  " . $param->groupid . " AND H.`borrado` = 0 AND  L.`additionalForm` IS NOT NULL  
						GROUP BY name";
		
		$getSpeedtestRESULT = $this->conexion->queryFetch($getSpeedtestSQL);
		
		
		if($getSpeedtestRESULT){
				
			foreach ($getSpeedtestRESULT as $key => $value) {
				$detailJson = json_decode($value['additionalForm']);
				foreach ($detailJson as $keyJson => $valueJson) {
					$detail[$valueJson->key] = $valueJson->value;
				};
				
				$cumplimientoDown = (100 * ($value['mDownloadOk'])) / $value['minTest'];
				$cumplimientoUpload = (100 * ($value['mUploadOk'])) / $value['minTest'];
					
				$pageData[$detail['division']][] = array(
						'name' => $value['name'],
						'mEESperada' => $value['minTest'],
						'mDownloadOk' => $value['mDownloadOk'],
						'mDownloadFallidas' => $value['mDownloadFallidas'],
						'mUploadOk' => $value['mUploadOk'],
						'mUploadFallidas' => $value['mUploadFallidas'],
						'vavgdown' => (is_null($value['vavgdown'])) ? 0 : $value['vavgdown'] ,
						'vavgupload' => (is_null($value['vavgupload'])) ? 0 : $value['vavgupload'],
						'cumplimientoDown' => number_format($cumplimientoDown,0,',',''),
						'cumplimientoUpload' => number_format($cumplimientoUpload,0,',','')
				);
			}	
		}
	
		$header = array(
			'SDS (Municipio)',
			'Muestras a Realizar',
			'Muestras Down Ok',
			'Muestras Down Fallidas',
			'Muestras Up Ok',
			'Muestras Up Fallidas',
			'Vel. prom. Down',
			'Vel. prom. Up',
			'Cumplimiento Cantidad Muestras Down OK',
			'Cumplimiento Cantidad Muestras Up OK'
		);
		
		include APPS . "plugins/PHPExcel.php";

		$objPHPExcel = new PHPExcel();

		$objPHPExcel->getProperties()->setCreator("Baking software")->setLastModifiedBy("bMonitor")->setTitle($this->language->GENERAL_REPORT)->setSubject("Export")->setDescription("Documento generado por report manager de bMonitor")->setKeywords("report")->setCategory("bMonitor");

	
		$page = 0;
		$lineStart = 38;

		$styleHeader = array(
			'font' => array('bold' => true, 'size' => '10'),
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,  'vertical' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
			'borders' => array('allborders' => array('style' => PHPExcel_Style_Border::BORDER_THIN)),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'rotation' => 90,
				'startcolor' => array('argb' => 'e3ae8c'),
				'endcolor' => array('argb' => 'e3ae8c')
			)
		);


		$styleCell = array(
			'font' => array('size' => '10'),
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'rotation' => 90,
				'startcolor' => array('argb' => 'a0e8bd'),
				'endcolor' => array('argb' => 'a0e8bd')
			)
		);

		$styleCellWarning = array(
			'font' => array('size' => '10'),
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array('argb' => 'F47C00'),
				'endcolor' => array('argb' => 'F47C00')
			)
		);

		$styleCellCritical = array(
			'font' => array('size' => '10'),
			'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
			'fill' => array(
				'type' => PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor' => array('argb' => 'D11600'),
				'endcolor' => array('argb' => 'D11600')
			)
		);
		
		//Llenado paginas
		
		foreach ($pageData as $key => $value) {
			$letter = 65;
			$line = $lineStart;
			if ($page > 0) {
				$objPHPExcel->createSheet();
			}
			
			$objPHPExcel->setActiveSheetIndex($page);

			$objPHPExcel->getActiveSheet()->setTitle($key);
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(6);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(6);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(14);
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(31);
			
			$objPHPExcel->getActiveSheet()->setCellValue('A1', 'Resultados Pruebas MinTic desde SDS - '.$key);
			
			$objPHPExcel->getActiveSheet()->mergeCells('A1:J1');
			$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				
			//Graficos
			
			$locationCount = count($value);

			$dataseriesLabels1 = array(
				new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$B$38', NULL, 1),	//	MuestrasARealizar
				new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$C$38', NULL, 1),	//	DownOK
				new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$D$38', NULL, 1),	//	DownFallidas
			);

			$dataseriesLabels2 = array(
				new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$B$38', NULL, 1),	//	MuestrasARealizar
				new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$E$38', NULL, 1),	//	DownOK
				new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$F$38', NULL, 1),	//	DownFallidas
			);
						
			$xAxisTickValues = array(
		        new PHPExcel_Chart_DataSeriesValues('String', 'Worksheet!$A$39:$A$' . (39+$locationCount), NULL, $locationCount)
			);
			
			$dataSeriesValues1 = array(
				new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$B$39:$B$' . (39+$locationCount), NULL, $locationCount),
				new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$C$39:$C$' . (39+$locationCount), NULL, $locationCount),
				new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$D$39:$D$' . (39+$locationCount), NULL, $locationCount),
			);

			$dataSeriesValues2 = array(
				new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$B$39:$B$' . (39+$locationCount), NULL, $locationCount),
				new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$E$39:$E$' . (39+$locationCount), NULL, $locationCount),
				new PHPExcel_Chart_DataSeriesValues('Number', 'Worksheet!$F$39:$F$' . (39+$locationCount), NULL, $locationCount),
			);
						
			$series1 = new PHPExcel_Chart_DataSeries(
				PHPExcel_Chart_DataSeries::TYPE_BARCHART,		// plotType
				PHPExcel_Chart_DataSeries::GROUPING_STACKED,	// plotGrouping
				range(0, count($dataSeriesValues1)-1),			// plotOrder
				$dataseriesLabels1,								// plotLabel
				$xAxisTickValues,								// plotCategory
				$dataSeriesValues1								// plotValues
			);

			$series2 = new PHPExcel_Chart_DataSeries(
				PHPExcel_Chart_DataSeries::TYPE_BARCHART,		// plotType
				PHPExcel_Chart_DataSeries::GROUPING_STACKED,	// plotGrouping
				range(0, count($dataSeriesValues2)-1),			// plotOrder
				$dataseriesLabels2,								// plotLabel
				$xAxisTickValues,								// plotCategory
				$dataSeriesValues2								// plotValues
			);

			$series1->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);
			$series2->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);
			
			$plotarea1 = new PHPExcel_Chart_PlotArea(NULL, array($series1));
			$plotarea2 = new PHPExcel_Chart_PlotArea(NULL, array($series2));
			
			$legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_BOTTOM, NULL, false);

			$title1 = new PHPExcel_Chart_Title('Muestras Down');
			$title2 = new PHPExcel_Chart_Title('Muestras UP');

			//	Create the chart
			$chart1 = new PHPExcel_Chart(
				'chart1',		// name
				$title1,			// title
				$legend,		// legend
				$plotarea1,		// plotArea
				true,			// plotVisibleOnly
				0,				// displayBlanksAs
				NULL,			// xAxisLabel
				NULL		// yAxisLabel
			);

			$chart2 = new PHPExcel_Chart(
				'chart2',		// name
				$title2,			// title
				$legend,		// legend
				$plotarea2,		// plotArea
				true,			// plotVisibleOnly
				0,				// displayBlanksAs
				NULL,			// xAxisLabel
				NULL		// yAxisLabel
			);
			
			$chart1->setTopLeftPosition('A12');
			$chart1->setBottomRightPosition('F31');

			$chart2->setTopLeftPosition('H12');
			$chart2->setBottomRightPosition('N31');
			
			$objPHPExcel->getActiveSheet()->addChart($chart1);
			$objPHPExcel->getActiveSheet()->addChart($chart2);
							
			//Tabla
				
			foreach ($header as $keyHeader => $valueHeader) {
				$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $valueHeader);
				$letter++;
			}
			$objPHPExcel->setActiveSheetIndex($page)->getStyle(chr(65) . ($line) . ":" . chr($letter-1) . ($line))->applyFromArray($styleHeader);
			$objPHPExcel->setActiveSheetIndex($page)->getStyle(chr(66) . ($line) . ":" . chr($letter-1) . ($line))->getAlignment()->setWrapText(true);
			$line++;
			
			foreach ($value as $keyLine => $valueLine) {
				$letter = 65;
				
				foreach ($valueLine as $celdaKey => $celdaValue) {
					if($celdaKey == 'cumplimientoDown' || $celdaKey == 'cumplimientoUpload'){
						$unit = '%';
					} else {
						$unit = '';
					}
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $celdaValue . $unit);
					$letter++;
				}
				
				$objPHPExcel->setActiveSheetIndex($page)->getStyle(chr(71) . ($line) . ":" . chr($letter-1) . ($line))->applyFromArray($styleCell);
				$objPHPExcel->setActiveSheetIndex($page)->getStyle(chr(66) . ($line) . ":" . chr($letter-1) . ($line))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
				
				if($valueLine['cumplimientoDown'] < 100 && $valueLine['cumplimientoDown'] > 0) {
					$objPHPExcel->setActiveSheetIndex($page)->getStyle(chr(73) . ($line) . ":" . chr($letter-1) . ($line))->applyFromArray($styleCellWarning);
				} elseif ($valueLine['cumplimientoDown'] == 0) {
					$objPHPExcel->setActiveSheetIndex($page)->getStyle(chr(71) . ($line) . ":" . chr($letter-1) . ($line))->applyFromArray($styleCellCritical);
				}
				
				$line++;
			}

			$page++;
		}

		
		$objPHPExcel->setActiveSheetIndex(0);

		$file = 'qoe_report_mintic_' . $param->from . "_" . $param->to . "_" . date("Ymd") . '.xlsx';

		$path = site_path . '/upload/';

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->setIncludeCharts(TRUE);

		$objWriter->save($path . $file);

		$result['status'] = true;
		$result['file'] = $file;
	
		$this->basic->jsonEncode($result)		;
		exit;
	}

	public function getExportMintic2() {

		$param = (object)$_POST;
		
		if (!isset($param->percentStartDownload) || !is_numeric($param->percentStartDownload)) {
			$param->percentStartDownload = 95;
		}

		if (!isset($param->percentEndDownload) || !is_numeric($param->percentEndDownload)) {
			$param->percentEndDownload = 110;
		}

		if (!isset($param->percentStartUpload) || !is_numeric($param->percentStartUpload)) {
			$param->percentStartUpload = 95;
		}

		if (!isset($param->percentEndUpload) || !is_numeric($param->percentEndUpload)) {
			$param->percentEndUpload = 110;
		}
		
		$where = "AND ( `pdownload` BETWEEN '" . $param->percentStartDownload . "' AND '" . $param->percentEndDownload . "' )" . " AND ( `pupload` BETWEEN '" . $param->percentStartUpload . "' AND '" . $param->percentEndUpload . "' )" . " AND ( `testDate` BETWEEN '" . $param->from . " 00:00:00' AND '" . $param->to . " 23:59:59')";
		

		$getSpeedtestSQL = "SELECT H.`id_host`, HD1.idLocation, 
							'100' as 'mEESperada',
							SUM(IF(RP.`pdownload` >= 100,1,0)) AS 'mDownloadOk',  
				 			SUM(IF(RP.`pdownload` < 100,1,0)) AS 'mDownloadFallidas' ,  
				 			SUM(IF(RP.`pupload` >= 100,1,0)) AS 'mUploadOk',  
				 			SUM(IF(RP.`pupload` < 100,1,0)) AS 'mUploadFallidas', 
				 			ROUND(AVG(RP.`download`) / 1024)  AS 'vavgdown', 
				 			ROUND(AVG(RP.`upload`)  / 1024) AS 'vavgupload',
							'0' as 'cumplimientoDown',
				 			'0' as 'cumplimientoUpload' , 
				 			L.`additionalForm` , 
				 			L.`minTest` , 
				 			CONCAT('SDS ', HD2.city ,' (', L.`city`,')') AS 'name'
							FROM `bm_host` H 
								LEFT OUTER JOIN (SELECT  `id_host`, `value` as 'idLocation' FROM `bm_host_detalle` WHERE `id_feature` = 78) HD1 ON ( H.`id_host`=HD1.`id_host`)
								LEFT OUTER JOIN (SELECT  `id_host`, `value` as 'city' FROM `bm_host_detalle` WHERE `id_feature` = 79) HD2 ON ( H.`id_host`=HD2.`id_host`)
								LEFT OUTER JOIN `bm_location` L ON L.`idLocation`=HD1.`idLocation`
								LEFT OUTER JOIN `report_speedtest` RP ON RP.`idHost`=H.`id_host` $where
						WHERE  H.`groupid` =  " . $param->groupid . " AND H.`borrado` = 0 AND  L.`additionalForm` IS NOT NULL  
						GROUP BY name";
		
		$getSpeedtestRESULT = $this->conexion->queryFetch($getSpeedtestSQL);
		
		
		if($getSpeedtestRESULT){
				
			foreach ($getSpeedtestRESULT as $key => $value) {
				$detailJson = json_decode($value['additionalForm']);
				foreach ($detailJson as $keyJson => $valueJson) {
					$detail[$valueJson->key] = $valueJson->value;
				};
				
				$cumplimientoDown = (100 * ($value['mDownloadOk'])) / $value['minTest'];
				$cumplimientoUpload = (100 * ($value['mUploadOk'])) / $value['minTest'];
					
				$pageData[$detail['division']][] = array(
						'name' => $value['name'],
						'mEESperada' => $value['minTest'],
						'mDownloadOk' => $value['mDownloadOk'],
						'mDownloadFallidas' => $value['mDownloadFallidas'],
						'mUploadOk' => $value['mUploadOk'],
						'mUploadFallidas' => $value['mUploadFallidas'],
						'vavgdown' => (is_null($value['vavgdown'])) ? 0 : $value['vavgdown'] ,
						'vavgupload' => (is_null($value['vavgupload'])) ? 0 : $value['vavgupload'],
						'cumplimientoDown' => number_format($cumplimientoDown,0,',',''),
						'cumplimientoUpload' => number_format($cumplimientoUpload,0,',','')
				);
			}	
		}
	
		$header = array(
			'SDS (Municipio)',
			'Muestras a Realizar',
			'Muestras Down Ok',
			'Muestras Down Fallidas',
			'Muestras Up Ok',
			'Muestras Up Fallidas',
			'Vel. prom. Down',
			'Vel. prom. Up',
			'Cumplimiento Cantidad Muestras Down OK',
			'Cumplimiento Cantidad Muestras Up OK'
		);
		
		include APPS . "plugins/PHPExcel.php";
	
		$objReader = PHPExcel_IOFactory::createReader("Excel2007");
		$objReader->setIncludeCharts(TRUE);
		$objPHPExcel = $objReader->load(site_path . "sitio/vista/qos/templateMintic.xlsx");

		$page = 0;
		$lineStart = 38;


		//Llenado paginas
		
		foreach ($pageData as $key => $value) {
			$letter = 65;
			$line = $lineStart;
			
			$objPHPExcel->setActiveSheetIndex($key);

			//$objPHPExcel->getActiveSheet()->setTitle($key);
											
			//Tabla
				
			foreach ($header as $keyHeader => $valueHeader) {
				$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $valueHeader);
				$letter++;
			}
			$line++;
			
			foreach ($value as $keyLine => $valueLine) {
				$letter = 65;
				
				foreach ($valueLine as $celdaKey => $celdaValue) {
					if($celdaKey == 'cumplimientoDown' || $celdaKey == 'cumplimientoUpload'){
						$unit = '%';
					} else {
						$unit = '';
					}
					$objPHPExcel->setActiveSheetIndex($page)->setCellValue(chr($letter) . $line, $celdaValue . $unit);
					$letter++;
				}	
				$line++;
			}

			$page++;
		}

		
		$objPHPExcel->setActiveSheetIndex(0);

		$file = 'qoe_report_mintic_' . $param->from . "_" . $param->to . "_" . date("Ymd") . '.xlsx';

		$path = site_path . '/upload/';

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$objWriter->setIncludeCharts(TRUE);

		$objWriter->save($path . $file);

		$result['status'] = true;
		$result['file'] = $file;
	
		$this->basic->jsonEncode($result)		;
		exit;
	}
}
?>
