<?php

class Qos extends Control
{
    public function index()
    {
        $valida = $this->protect->access_page('QOS-Module');

        $this->plantilla->loadSec("qos/index", $valida);

        $section = new Section();

        $tps_index_control['menu'] = $section->menu('QOS-Module');
        $tps_index_control['header'] = $section->header();
        $tps_index_control['footer'] = $section->footer();

        $tabs[] = array(
            'title' => $this->language->REPORT,
            'urlBase' => true,
            'href' => 'qos/report',
            'protec' => 'QOS_TAB_REPORT'
        );

        /*$tabs[] = array(
         'title' => 'Cumplimiento',
         'urlBase' => true,
         'href' => 'qos/accomplishment',
         'protec' => 'QOS_TAB_REPORT'
         );*/

        $tabs[] = array(
            'title' => $this->language->CONFIGURATION,
            'urlBase' => true,
            'href' => 'qos/config',
            'protec' => 'QOS_TAB_CONFIG'
        );

        $tabs[] = array(
            'title' => $this->language->CHARTS,
            'urlBase' => true,
            'href' => 'qos/graph',
            'protec' => 'QOE_TAB_CHARTS'
        );

        $tps_index_control['tabs'] = $this->bmonitor->getTabs($tabs);

        $this->plantilla->set($tps_index_control);

        $this->plantilla->getCache();
    }

    public function graph()
    {

        $valida = $this->protect->access_page('QOS-GRAPH');

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

        $this->plantilla->getCache();
    }

    public function report()
    {

        $valida = $this->protect->access_page('QOS-Module');

        $this->plantilla->loadSec("qos/report", $valida, false);

        $tps_index_control['option_groups'] = $this->bmonitor->getAllGroupsHost('QoS', 'option', '-first-');

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

        $tps_index_control['MONTH'] = $this->language->MONTH;
        $tps_index_control['GROUP'] = $this->language->GROUP;
        $tps_index_control['SCALE'] = $this->language->SCALE;
        $tps_index_control['YEAR'] = $this->language->YEAR;
        
        $this->plantilla->set($tps_index_control);

        $this->plantilla->getCache();
    }

    public function accomplishment()
    {

    }

    public function getTableReport()
    {
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

        $this->plantilla->cacheJSON($clock . '_' . $getParam->grupo . '_' . $factor, 10);

        //Datos de la tabla

        $tableResult = $this->getReport($clock, $getParam->grupo, $factor);
        $table = '';
        if ($tableResult) {
            foreach ($tableResult as $keyGroup => $group) {
                $table .= '<div id="tableResumenConsFDT" style="width:934px" class="dataTables_wrapper">
        <div class="fg-toolbar ui-toolbar ui-widget-header ui-corner-tl ui-corner-tr ui-helper-clearfix">' . $group['title'] . '</div>';

                $table .= '<table id="tableResumenConsFDT_int" class="ui-grid-content ui-widget-content display">';

                $table .= '<thead><tr>
                    <th class="ui-state-default">Plan</th>
                    <th class="ui-state-default">Mes</th>
                    <th class="ui-state-default">Q</th>
                    <th class="ui-state-default">Exitosos</th>
                    <th class="ui-state-default">Fallidos</th>
                    <th class="ui-state-default">Tasa </br> de </br> Fallas</th>
                    <th class="ui-state-default">Error </br> de la </br> prueba</th>
                    <th class="ui-state-default">Prom</th>
                    <th class="ui-state-default">Mín</th>
                    <th class="ui-state-default">Máx</th>
                    <th class="ui-state-default">Desv</th>
                    <th class="ui-state-default">Per 5</th>
                    <th class="ui-state-default">Per 80</th>
                    <th class="ui-state-default">Per 95</th>
                    <th class="ui-state-default">Confiabilidad Medición</th>
                    <th class="ui-state-default">Error Medición</br>(máx 5)</th>
                </tr>
            </thead>';

                $table .= '<tbody>';
                foreach ($group['data'] as $key => $value) {
                    $table .= '<tr class="gradeA odd">';

                    $table .= '<td class="ui-widget-content">' . $value['plan'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['mes'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['total'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['exitosa'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['fallida'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['tasaFalla'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['errorPrueba'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['promedio'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['min'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['max'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['std'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['percentil5'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['percentil95'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['percentil80'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['confMedicion'] . '</td>';
                    $table .= '<td class="ui-widget-content">' . $value['errorMedicion'] . '</td>';

                    $table .= '</tr>';
                }

                $table .= '</tbody></table></div>';
            }

        }

        $datos['status'] = true;
        $datos['tablas'] = $table;

        echo json_encode($datos);
        exit ;
    }

    private function getReport($clock = false, $groupid, $escala = false)
    {

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

        $getStats_sql = "SELECT INF.*,PL.`plan`,PL.`nacD`, PL.`nacU`, PL.`locD`, PL.`locU`, PL.`intD`, PL.`intU`,IT.`descriptionLong`
FROM `bm_inform` INF
    LEFT JOIN `bm_plan` PL ON PL.`id_plan`=INF.`idPlan`
    LEFT JOIN `bm_items` IT ON IT.`id_item`=INF.`idItem` 
WHERE INF.`clock` = '$clock' AND INF.`groupid` = $groupid ORDER BY INF.`groupid`, INF.`idPlan`,INF.`idItem`";

        $getStats_result = $this->conexion->queryFetch($getStats_sql);

        if ($getStats_result) {
            foreach ($getStats_result as $key => $qos_data) {
                $section = $qos_data['groupid'] . "_" . $qos_data['idPlan'] . "_" . $qos_data['idItem'];
                $dateInform[$section]['groupid'] = $qos_data['groupid'];
                $dateInform[$section]['idPlan'] = $qos_data['idPlan'];
                $dateInform[$section]['idItem'] = $qos_data['idItem'];

                $dateInform[$section]['plan'] = $qos_data['plan'];
                $dateInform[$section]['nacD'] = $qos_data['nacD'];
                $dateInform[$section]['nacU'] = $qos_data['idItem'];
                $dateInform[$section]['locD'] = $qos_data['locD'];
                $dateInform[$section]['locU'] = $qos_data['locU'];
                $dateInform[$section]['locD'] = $qos_data['locD'];
                $dateInform[$section]['intD'] = $qos_data['intD'];
                $dateInform[$section]['intU'] = $qos_data['intU'];
                $dateInform[$section]['descriptionLong'] = $qos_data['descriptionLong'];
                $dateInform[$section][$qos_data['type']] = $qos_data['value'];
            }
        } else {
            return false;
        }

        //Calculando datos

        foreach ($dateInform as $key => $value) {

            $result['title'] = $value['descriptionLong'];
            $result['plan'] = $value['plan'];
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

            $result['errorPrueba'] = $color . number_format($e, 4, ",", ".") . "%</FONT>";

            $result['promedio'] = number_format($value['AVG'] / $factor, 0, ",", ".");
            $result['min'] = number_format($value['MIN'] / $factor, 0, ",", ".");
            $result['max'] = number_format($value['MAX'] / $factor, 0, ",", ".");

            $result['max'] = number_format($value['MAX'] / $factor, 0, ",", ".");

            $result['std'] = number_format($value['STD'] / $factor, 0, ",", ".");

            $result['percentil5'] = number_format($value['PERCENTIL_5'] / $factor, 0, ",", ".");
            $result['percentil95'] = number_format($value['PERCENTIL_95'] / $factor, 0, ",", ".");
            $result['percentil80'] = number_format($value['PERCENTIL_80'] / $factor, 0, ",", ".");

            $result['confMedicion'] = number_format($value['INTERVALO'] / $factor, 0, ",", ".");
            ;
            $result['errorMedicion'] = 0;

            $inform[$value['idItem']]['data'][] = $result;
            $inform[$value['idItem']]['title'] = $value['descriptionLong'];
        }

        return $inform;

    }

    //Config Section

    public function config()
    {
        $valida = $this->protect->access_page('QOS-Module');

        $this->plantilla->loadSec("qos/config", $valida, false);
 
        $tps_index_control['MONITOR'] = $this->language->MONITOR;
        $tps_index_control['NOMINAL'] = $this->language->NOMINAL;
        $tps_index_control['NEW'] = $this->language->NEW;
        $tps_index_control['EDIT'] = $this->language->EDIT;
        $tps_index_control['DELETE'] = $this->language->DELETE;
        $tps_index_control['PERCENTILE'] = $this->language->PERCENTILE;
        $tps_index_control['ADD'] = $this->language->ADD;
        
        $this->plantilla->set($tps_index_control);

        $this->plantilla->getCache();
    }

    public function getThreshold()
    {
        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $var = $this->basic->setParamTable($_POST, 'id_item');

        //Parametros Table

        $data = array();

        $data['page'] = $var->page;

        $data['rows'] = array();

        $WHERE = " IG.`groupid` IN " . $this->bmonitor->getAllGroupsHost(false, 'sql');

        //Total Threshold
        $getCountThresholdSQL = "SELECT COUNT(*) AS Total FROM `bm_threshold` ";
        // T LEFT JOIN `bm_items_groups` IG ON T.`id_item`=IG.`id_item` WHERE ".$WHERE." GROUP BY `id_threshold`";

        $getCountThresholdRESULT = $this->conexion->queryFetch($getCountThresholdSQL);

        if ($getCountThresholdRESULT) {
            $data['total'] = $getCountThresholdRESULT[0]['Total'];
        } else {
            $data['total'] = 0;
        }

        //Threshold

        $getThresholdSQL = "SELECT * FROM `bm_threshold` ";
        // T LEFT JOIN `bm_items_groups` IG ON T.`id_item`=IG.`id_item` WHERE ".$WHERE." GROUP BY `id_threshold`";

        $getThresholdRESULT = $this->conexion->queryFetch($getThresholdSQL);

        if ($getThresholdRESULT) {
            foreach ($getThresholdRESULT as $key => $value) {
                $data['rows'][] = array(
                    'id' => $value['id_threshold'],
                    'cell'=>array(
                        'idThreshold'=>$value['id_threshold'],
                        'item'=>$value['id_item'],
                        'nominal'=>$value['nominal'],
                        'warning'=>$value['warning'],
                        'critical'=>$value['critical']
                    )
                );
            }
        }

        echo $this->basic->jsonEncode($data);
        exit ;
    }

}
?>