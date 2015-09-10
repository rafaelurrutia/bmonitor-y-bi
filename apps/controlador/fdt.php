<?php
class fdt extends Control
{

    var $consolidado = array();

    public function index()
    {
        $valida = $this->protect->access_page('FDT');

        $this->plantilla->loadSec("fdt/fdt", $valida);

        $section = new Section();

        $tps_index_control['menu'] = $section->menu('FDT');
        $tps_index_control['header'] = $section->header();
        $tps_index_control['footer'] = $section->footer();

        $this->plantilla->set($tps_index_control);

        $this->plantilla->getCache();
    }

    private function getTableColum($table)
    {

        $getColumn = "select hf.`feature`, hf.`display`, ht.`width`, ht.`sortable`, ht.`align` 
						FROM `bm_host_table` ht JOIN `bm_host_feature` hf USING(`id_feature`)
						WHERE ht.`view_table` = 'true' AND ht.`table`='$table' 
						ORDER BY ht.`orden` asc;";
        $Columns = $this->conexion->queryFetch($getColumn);
        $option = array();
        foreach ($Columns as $key => $Colum) {

            if ($Colum['width'] == 'none') {
                $width = '';
            } else {
                $width = ", width : '" . $Colum['width'] . "'";
            }

            $option[] = "{display: '" . $Colum['display'] . "', name : '" . $Colum['feature'] . "' " . $width . "  , sortable : " . $Colum['sortable'] . ", align: '" . $Colum['align'] . "'}";
        }
        $optionF = join(',', $option);
        return $optionF;
    }

    private function getOptionAnios($adelanta = 0)
    {

        $option = '';

        for ($i = date("Y"); $i >= date("Y") - 10; $i--) {
            $anio = $i + $adelanta;
            $option .= '<option value="' . $anio . '">' . $anio . '</option>';
        }

        return $option;
    }

    public function getInformeDisponibilidad()
    {
        $valida = $this->protect->access_page('FDT_INFORME_DISPONIBILIDAD', FALSE);

        if ($valida->redirect == TRUE) {
            header("HTTP/1.1 302 Found");
            echo "Session close";
            exit ;
        }

        if (isset($_POST['groupid']) && is_numeric($_POST['groupid'])) {
            $groupid = (int)$_POST['groupid'];
            if ($groupid == 0) {
                echo $this->bmonitor->getHostForGroup('ENLACES', 'option');
                exit ;
            } else {
                echo $this->bmonitor->getHostForGroup($groupid, 'option');
                exit ;
            }
        }

        $this->plantilla->cacheJSON('getInformeDisponibilidad', 3600);
        $this->plantilla->load_sec("fdt/fdt_disponibilidad", "denegado", $valida->access);

        $tps_index_control['table_column'] = $this->getTableColum('disponibilidad');

        $tps_index_control['disp_fmFrom'] = date("Y-m-d");
        $tps_index_control['disp_fmTo'] = date("Y-m-d");

        $tps_index_control['option_groups'] = $this->bmonitor->getAllGroupsHost('ENLACES', 'option', 'all');
        $tps_index_control['option_escuela'] = $this->bmonitor->getHostForGroup('ENLACES', 'option', true);

        $tps_index_control['option_estados'] = $this->basic->getOptionValue('fdt_estado', 4);
        $tps_index_control['option_desde'] = $this->basic->getOptionValue('fdt_desde', 2);

        $this->plantilla->set($tps_index_control);
        echo $this->plantilla->get();
        $this->plantilla->cacheClose();
    }

    public function getOption($type, $all = false)
    {
        if (!empty($type)) {
            $optionValues = $this->conexion->queryFetch("SELECT * FROM `bm_option` WHERE `option_group`='$type' ORDER BY `orden` ASC");
            if ($all) {
                $option = '<option value="all">'.$this->language->ALL.'</option>';
            } else {
                $option = '';
            }

            foreach ($optionValues as $key => $optionValue) {

                if ($optionValue['selected'] == 'true') {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }

                $option .= '<option ' . $selected . ' value="' . $optionValue['id_option'] . '">' . $optionValue['option'] . '</option>';

            }

            return $option;
        } else {
            return false;
        }
    }

    public function getDisponibilidadTable()
    {
        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $var = $this->basic->setParamTable($_POST, 'id');

        //Parametros Table

        $data = array();

        $data['page'] = $var->page;

        $data['rows'] = array();

        unset($_SESSION['SET_FDT_DISPONIBILIDAD']);

        //Filtros

        if ((!empty($getParam->disp_fmFrom)) && (!empty($getParam->disp_fmTo))) {
            $from = "$getParam->disp_fmFrom 00:00:00";
            $to = "$getParam->disp_fmTo 23:59:59";
        } else {
            $today = date("Y-m-d");
            $from = "$today 00:00:00";
            $tohour = date("Y-m-d H:m:s");
            $to = "$tohour";
        }

        if (isset($getParam->disp_fmHost)) {
            if (is_numeric($getParam->disp_fmHost)) {
                if ($getParam->disp_fmHost == 0) {
                    $WHERE = '';
                } else {
                    $WHERE = ' AND HI.id_host=' . $getParam->disp_fmHost;
                }
            } else {
                $WHERE = '';
            }
        } else {
            return false;
        }

        $GROUP_BY = '';
        $COUNT = 'COUNT(*)';

        $group_host = false;

        switch ($getParam->disp_fmEstados) {
        case 'all' :
            break;

        case 0 :
            $WHERE .= ' AND HI.`value` = 0';
            break;
        case 1 :
            $WHERE .= ' AND HI.`value` = 1';
            break;
        case 2 :
            $WHERE .= ' AND CLOSE.`close` =  "RCSEE" AND HI.`value` = 0';
            break;
        case 3 :
            $WHERE .= ' AND CLOSE.`id_history` IS NULL AND HI.`value` = 0';
            break;
        case 4 :
            $WHERE .= ' AND CLOSE.`id_history` IS NULL AND HI.`value` = 0 ';
            $GROUP_BY = 'GROUP  BY H.`id_host`';
            $COUNT = 'COUNT(DISTINCT 1)';
            $group_host = true;
            break;
        }

        $LEFT_CLOSE = 'LEFT OUTER JOIN `bm_close` CLOSE ON HI.`id_history`=CLOSE.`id_history`';

        //Total de Registros

        $groupid = $getParam->disp_fmGrupo;

        $host_sql_total = "SELECT $COUNT As Total , GROUP_CONCAT(DISTINCT H.`id_host`) As Host
								FROM `bm_host` H
								LEFT JOIN `bm_history` HI ON HI.`id_host`=H.`id_host`
								LEFT OUTER JOIN `bm_close` CLOSE ON HI.`id_history`=CLOSE.`id_history`
							WHERE 
								HI.`id_item`=1 AND H.`groupid`=$groupid  AND H.`borrado`=0 AND
								HI.`clock` >= unix_timestamp('$from') AND  HI.`clock`  <= unix_timestamp('$to') + 3600*24
							$WHERE $GROUP_BY";

        $host_totales = $this->conexion->queryFetch($host_sql_total, 'logs_fdt');

        if ($host_totales && (int)$host_totales[0]['Total'] > 0) {
            $data['total'] = $host_totales[0]['Total'];
            $host_filter = explode(',', $host_totales[0]['Host']);
        } else {
            $data['total'] = 0;
            echo json_encode($data);
            exit ;
        }

        //Campos de la tabla

        $selecl_sql_feature = "SELECT HF.`feature`
							FROM `bm_host_table`  HT
							LEFT JOIN `bm_host_feature` HF USING(`id_feature`)
							WHERE `table`='disponibilidad' ORDER BY HT.`orden` ASC;";

        $select_result_feature = $this->conexion->queryFetch($selecl_sql_feature, 'logs_fdt');

        // Detalle de los host

        $select_sql_host_detalle = "SELECT   H.`id_host`, H.`host`, P.`plan`,  HF.`feature`,HD.`value`
								FROM `bm_host` H
												LEFT JOIN `bm_host_detalle` HD USING(`id_host`)
												LEFT JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature`
												LEFT JOIN `bm_host_table` HT ON HT.`id_feature`=HD.`id_feature`
												LEFT JOIN `bm_plan` P ON P.`id_plan` = H.`id_plan`
										
								WHERE 
												HT.`table`='disponibilidad' AND
												HF.`feature_type` = 'other' AND  
												H.`id_host` IN " . $this->conexion->arrayToIN($host_filter);

        $select_result_host_detalle = $this->conexion->queryFetch($select_sql_host_detalle, 'logs_fdt');

        foreach ($select_result_host_detalle as $key => $feature) {
            $datos[$feature['id_host']][$feature['feature']] = $feature['value'];
        }

        //Get Host

        $select_sql_host = "SELECT HI.id_host,H.`host`,H.`ip_wan`, HI.`clock`,HI.`id_history`, HI.`value`,CLOSE.`id_history` AS close
								FROM `bm_host` H
								LEFT JOIN `bm_history` HI ON HI.`id_host`=H.`id_host`
								LEFT OUTER JOIN `bm_close` CLOSE ON HI.`id_history`=CLOSE.`id_history`
							WHERE 
								HI.`id_item`=1 AND H.`groupid`=$groupid  AND H.`borrado`=0 AND HI.id_host IN " . $this->conexion->arrayToIN($host_filter) . " AND
								HI.`clock` >= unix_timestamp('$from') AND  HI.`clock`  <= unix_timestamp('$to') + 3600*24
							$WHERE ORDER BY HI.id_host, HI.`clock` ASC $var->limitSql";

        $select_result_host = $this->conexion->queryFetch($select_sql_host, 'logs_fdt');

        $host_id = 0;
        $anterior = 0;
        $anterior_host = 0;

        foreach ($select_result_host as $key => $host) {

            if ($group_host) {
                $id_array = $host['id_host'];
            } else {
                $id_array = $host['id_host'] . '_' . $host['id_history'];
            }

            $this->logs->debug('Array Resultante Host: ' . $host_id . '-', $host['id_host'], 'logs_fdt');

            if ($group_host) {
                if ($host['id_host'] != $host_id) {
                    $datos_host[$id_array]['clock_inicial'] = $host['clock'];
                } else {
                    $datos_host[$id_array]['clock_inicial'] = $datos_host[$id_array]['clock_inicial'];
                    $datos_host[$id_array]['clock_final'] = $host['clock']; ;
                }
            } else {
                $datos_host[$id_array]['clock_inicial'] = $host['clock'];
            }
            $datos_host[$id_array]['id_host'] = $host['id_host'];
            $datos_host[$id_array]['ip_wan'] = $host['ip_wan'];
            $datos_host[$id_array]['host'] = $host['host'];
            $datos_host[$id_array]['value'] = $host['value'];
            $datos_host[$id_array]['close'] = $host['close'];
            $datos_host[$id_array]['id_history'][] = $host['id_history'];

            $host_id = $host['id_host'];
        }

        $this->logs->debug('Array Resultante: ', $datos_host, 'logs_fdt');

        $_SESSION['SET_FDT_DISPONIBILIDAD'] = $datos_host;

        $data['rows'] = array();

        foreach ($datos_host as $key => $rows) {
            $rows = $rows + $datos[$rows['id_host']];

            $rows['region'] = $this->basic->getLocalidad('region', $rows['region']);
            $rows['comuna'] = $this->basic->getLocalidad('comuna', $rows['comuna']);

            if ($group_host) {
                $rows['clock'] = date("Y-M-d H:i:s", $rows['clock_inicial']) . '/' . date("Y-M-d H:i:s", $rows['clock_final']) . ' (' . count($rows['id_history']) . ')';
            } else {
                $rows['clock'] = date("Y-M-d H:i:s", $rows['clock_inicial']);
            }

            if ($rows['value'] == 1) {
                $rows['cumple'] = "Cumple";
                $rows['fen'] = '';
                $rows['contacto'] = '';
                $rows['cumple_nocumple'] = '';
            } elseif (($rows['value'] == 0) && ($rows['close'] == NULL)) {
                $rows['cumple'] = "No Cumple";
                $rows['fen'] = '<input type="text" size="10" name="FEN_' . $key . '" value="">';
                $rows['contacto'] = '<input type="text" size="30" maxlength="254" name="CONTAC_' . $key . '" value="">';
                $rows['cumple_nocumple'] = '<select name="CB_' . $key . '">' . $this->basic->getOptionValue('fdt_cierre', 'EUO') . '</select>';
            } else {
                $rows['cumple'] = "No Cumple";
                $rows['fen'] = '';
                $rows['contacto'] = '';
                $rows['cumple_nocumple'] = '';
            }

            $datosTable = array();
            foreach ($select_result_feature as $table) {
                if (isset($rows[$table['feature']])) {
                    $datosTable[] = $rows[$table['feature']];
                } else {
                    $datosTable[] = '';
                }
            }

            $data['rows'][] = array(
                'id' => $rows['id_host'],
                'cell' => $datosTable
            );

            //$this->logs->debug('Array Resultante: ',$rows,'logs_fdt');
        }
        $this->basic->jsonEncode($data);
        exit ;
    }

    /*
     public function getDisponibilidadTable2()
     {
     $getParam = (object)$_POST;

     //Valores iniciales , libreria flexigrid
     $page = 1;
     $sortname = 'id';
     $sortorder = 'asc';
     $qtype = '';
     $query = '';
     $rp = 15;

     // Validaciones de los parametros enviados por la libreria flexigrid
     if (isset($_POST['page'])) {
     $page = $_POST['page'];
     }
     if (isset($_POST['sortname']) && ($_POST['sortname']  != 'undefined') &&
     ($_POST['sortname']  != '')) {
     $sortname = $_POST['sortname'];
     }
     if (isset($_POST['sortorder']) && ($_POST['sortorder']  != 'undefined') &&
     ($_POST['sortorder']  != '')) {
     $sortorder = $_POST['sortorder'];
     }
     if (isset($_POST['qtype'])) {
     $qtype = $_POST['qtype'];
     }
     if (isset($_POST['query'])) {
     $query = $_POST['query'];
     }
     if (isset($_POST['rp'])) {
     $rp = $_POST['rp'];
     }

     //Parametros Table

     $data = array();
     $data['page'] = $page;

     $pageStart = ($page-1)*$rp;

     $limitSql = " LIMIT $pageStart, $rp";

     //Filtros

     if(!isset($getParam->disp_fmGrupo)) {
     return false;
     }

     if((!empty($getParam->disp_fmFrom)) && (!empty($getParam->disp_fmTo))) {
     $from = "$getParam->disp_fmFrom 00:00:00";
     $to = "$getParam->disp_fmTo 23:59:59";
     } else {
     $today = date("Y-m-d");
     $from = "$today 00:00:00";
     $tohour = date("Y-m-d H:m:s");
     $to = "$tohour";
     }

     if(isset($getParam->disp_fmHost)) {
     if(is_numeric($getParam->disp_fmHost)) {
     if($getParam->disp_fmHost == 0) {
     $WHERE = '';
     } else {
     $WHERE = ' AND HI.id_host='.$getParam->disp_fmHost;
     }
     } else {
     $WHERE = '';
     }
     } else {
     return false;
     }
     $GROUP_BY = 'HI.`id_history`';
     if(isset($getParam->disp_fmEstados)) {
     if($getParam->disp_fmEstados == 'all') {
     $LEFT_CLOSE = 'LEFT OUTER JOIN `bm_close` CLOSE ON
     HI.`id_history`=CLOSE.`id_history`';
     $WHERE .= '';
     } elseif ($getParam->disp_fmEstados == 0) {
     $LEFT_CLOSE = 'LEFT OUTER JOIN `bm_close` CLOSE ON
     HI.`id_history`=CLOSE.`id_history`';
     $WHERE .= ' AND HI.`value` = 0';
     } elseif ($getParam->disp_fmEstados == 1) {
     $LEFT_CLOSE = 'LEFT OUTER JOIN `bm_close` CLOSE ON
     HI.`id_history`=CLOSE.`id_history`';
     $WHERE .= ' AND HI.`value` = 1';
     } elseif ($getParam->disp_fmEstados == 2) {
     $LEFT_CLOSE = 'LEFT OUTER JOIN `bm_close` CLOSE ON
     HI.`id_history`=CLOSE.`id_history`';
     $WHERE .= ' AND CLOSE.`close` =  "RCSEE" AND HI.`value` = 0';
     } elseif ($getParam->disp_fmEstados == 3) {
     $LEFT_CLOSE = 'LEFT OUTER JOIN `bm_close` CLOSE ON
     HI.`id_history`=CLOSE.`id_history`';
     $WHERE .= ' AND CLOSE.`id_history` IS NULL AND HI.`value` = 0';
     } elseif ($getParam->disp_fmEstados == 4) {
     $LEFT_CLOSE = 'LEFT OUTER JOIN `bm_close` CLOSE ON
     HI.`id_history`=CLOSE.`id_history`';
     $WHERE .= ' AND CLOSE.`id_history` IS NULL AND HI.`value` = 0 ';
     $GROUP_BY = 'H.`id_host`';
     }
     }

     if(!isset($getParam->disp_fmDesde)) {
     return false;
     }

     //Host totales

     $host_sql_total = "SELECT COUNT(1) As Total
     FROM `bm_history`  HI
     LEFT OUTER JOIN `bm_close` CLOSE ON HI.`id_history`=CLOSE.`id_history`
     WHERE HI.`id_item`=1 AND
     HI.`clock` >= unix_timestamp('$from') AND  HI.`clock`  <=
     unix_timestamp('$to') + 3600*24
     $WHERE";

     $host_totales = $this->conexion->queryFetch($host_sql_total,'logs_fdt');

     if($host_totales) {
     $data['total'] = $host_totales[0]['Total'];
     } else {
     $data['total'] = 0;
     }

     //Features

     $query_feature = "SELECT HF.`feature`
     FROM `bm_host_table`  HT
     LEFT JOIN `bm_host_feature` HF USING(`id_feature`)
     WHERE `table`='disponibilidad' ORDER BY HT.`orden` ASC;";

     $featuresTable = $this->conexion->queryFetch($query_feature,'logs_fdt');

     //Host a desplegar

     $query_host = "SELECT H.`host`,HI.id_host,H.`ip_wan`,P.`plan`,
     HI.`clock`,HI.`id_history`,  min(HI.`id_history`) as mixHistory,
     max(HI.`id_history`) as maxHistory, HI.`value`,CLOSE.`id_history` AS close
     ,GROUP_CONCAT(HF.`feature`)  AS feature,
     GROUP_CONCAT(HD.`value`) AS valueFeature
     FROM `bm_host` H
     LEFT JOIN `bm_host_detalle` HD USING(`id_host`)
     LEFT JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature`
     LEFT JOIN `bm_host_table` HT ON HT.`id_feature`=HD.`id_feature`
     LEFT JOIN `bm_plan` P ON P.`id_plan`=H.`id_plan`
     LEFT JOIN `bm_history` HI ON HI.`id_host`=H.`id_host`
     $LEFT_CLOSE
     WHERE
     H.`groupid`=$getParam->disp_fmGrupo AND H.`borrado`=0 AND
     HI.`clock` >= unix_timestamp('$from') and  HI.`clock`  <=
     unix_timestamp('$to') + 3600*24 AND
     HT.`table`='disponibilidad' AND
     HF.`feature_type` = 'other' AND
     HI.`id_item`=1 $WHERE
     GROUP  BY $GROUP_BY
     ORDER BY  HI.`id_history` ASC,HT.`orden`  ASC ".$limitSql;

     $hostValues = $this->conexion->queryFetch($query_host,'logs_fdt');

     $data['rows'] = array();

     foreach ($hostValues as $key => $value) {

     $valores = array();

     $valores['host'] = $value['host'];
     $valores['ip_wan'] = $value['ip_wan'];
     $valores['plan'] = $value['plan'];
     $valores['clock'] = date("Y-M-d H:i:s",$value['clock']);

     if($value['value'] == 1) {
     $valores['cumple'] = "Cumple";
     $valores['fen'] = '';
     $valores['contacto'] = '';
     $valores['cumple_nocumple'] = '';
     } elseif (($value['value'] == 0) && ($value['close'] == NULL)) {
     $valores['cumple'] = "No Cumple";
     $valores['fen'] = '<input type="text" size="10"
     name="FEN_'.$value['id_host'].'_'.$value['mixHistory'].'_'.$value['maxHistory'].'"
     value="">';
     $valores['contacto'] = '<input type="text" size="30" maxlength="254"
     name="CONTAC_'.$value['id_host'].'_'.$value['mixHistory'].'_'.$value['maxHistory'].'"
     value="">';
     $valores['cumple_nocumple'] = '<select
     name="CB_'.$value['id_host'].'_'.$value['mixHistory'].'_'.$value['maxHistory'].'">'.$this->getOption('fdt_cierre').'</select>';
     } else {
     $valores['cumple'] = "No Cumple";
     $valores['fen'] = '';
     $valores['contacto'] = '';
     $valores['cumple_nocumple'] = '';
     }

     $features=explode(',',$value['feature']);
     $valoresFeature=explode(',',$value['valueFeature']);

     $features_final = $features[0];
     $count = 0;
     foreach ($features as $keyF => $feature) {
     if( ($count > 0) && ($features_final == $feature) ) {
     break;
     }
     $valores[$feature] = $valoresFeature[$keyF];
     $count++;
     }

     $datos = array();

     foreach ($featuresTable as $table) {
     if(isset($valores[$table['feature']])) {
     $datos[] = $valores[$table['feature']];
     } else {
     $datos[] = '';
     }

     }

     $data['rows'][] = array(
     'id' => $key,
     'cell' => $datos
     );
     }

     echo json_encode($data);
     }*/

    public function setDisponibilidad()
    {
        $getParam = (object)$_POST;

        $array_id = $_SESSION['SET_FDT_DISPONIBILIDAD'];

        $result['status'] = true;
        $result['msg'] = $this->language->SAVE_OK;

        foreach ($getParam as $key => $value) {
            if (preg_match("/^CONTAC/i", $key)) {

                $ArrayContac = explode("_", $key, 2);

                $KEY = $ArrayContac[1];

                $value_FEN = "FEN_" . $KEY;
                $value_FEN = $getParam->$value_FEN;
                $value_CONTAC = "CONTAC_" . $KEY;
                $value_CONTAC = $getParam->$value_CONTAC;
                $value_CB = "CB_" . $KEY;
                $value_CB = $getParam->$value_CB;

                $id_host = $array_id[$KEY]['id_host'];

                $array_id_history = $array_id[$KEY]['id_history'];

                if ((!empty($value_CONTAC)) && ($value_CB != 'EUO')) {

                    $insert_close_value = array();
                    $insert_close_sql = "INSERT INTO `bm_close` (`id_item`, `id_host`, `id_history`, `msg`, `close`, `fen`) 
										VALUES ";

                    foreach ($array_id_history as $key_history => $id_history) {

                        if (is_numeric($id_history)) {
                            $insert_close_value[] = "('1','$id_host', '$id_history', '$value_CONTAC', '$value_CB', '$value_FEN')";
                        }
                    }

                    $closeInsert = $this->conexion->query($insert_close_sql . join(',', $insert_close_value));

                    if (!$closeInsert) {
                        $result['msg'] = $this->language->ITERNAL_ERROR;
                        $result['status'] = false;
                    }

                }/* FIN IF Campo vacio */
                else {
                    $result['status'] = false;
                    $result['msg'] = $this->language->ERROR_PARAM_EMPTY;
                }
            } /* IF Buscar Todos los CONTAC */
        }/* Recorrer las variables */
        $callback = $_GET['callback'];

        echo $callback . "(" . json_encode($result) . ')';
    }// Fin Funcion  setDisponibilidad

    public function setDisponibilidad2()
    {
        $getParam = (object)$_POST;

        foreach ($getParam as $key => $value) {
            if (preg_match("/^CONTAC/i", $key)) {

                $ArrayContac = explode("_", $key);
                $id_host = $ArrayContac[1];
                $historyMin = $ArrayContac[2];
                $historyMax = $ArrayContac[3];

                $keyFDT = "FEN_" . $id_host . "_" . $historyMin . "_" . $historyMax;
                $valueFDT = $getParam->$keyFDT;
                $valueContac = $value;
                $keyCumple = "CB_" . $id_host . "_" . $historyMin . "_" . $historyMax;
                $valueCUMPLE = $getParam->$keyCumple;

                if ((!empty($valueContac)) && ($valueCUMPLE != 'EUO')) {

                    if ($historyMin == $historyMax) {

                        $insert_close = "INSERT INTO `bm_close` (`id_item`, `id_host`, `id_history`, `msg`, `close`, `fen`) 
											VALUES ('1','$id_host', '$historyMin', '$valueContac', '$valueCUMPLE', '$valueFDT');";

                        $closeInsert = $this->conexion->query($insert_close);

                        if ($closeInsert) {
                            $status = true;
                        }

                    } else {
                        $group_history_query = "SELECT `id_history` FROM `bm_history` 
								WHERE `id_history` >= $historyMin AND `id_history` <= $historyMax AND `value` = 0 AND `id_host`=$id_host AND `id_item`=1;";

                        $getGroupHistory = $this->conexion->queryFetch($group_history_query);

                        $insertClose = 'INSERT INTO `bm_close` (`id_item`, `id_host`, `id_history`, `msg`, `close`, `fen`) VALUES ';

                        $value = array();
                        foreach ($getGroupHistory as $key => $history) {
                            $value[] = "( 1, " . $id_host . "," . $history['id_history'] . ", '" . $valueContac . "', '" . $valueCUMPLE . "', '" . $valueFDT . "')";
                        }
                        $valueAll = join(',', $value);

                        $insertClose = $insertClose . $valueAll;
                        echo $insertClose;
                        $closeInsert = $this->conexion->query($insertClose);
                    }

                }
            }
        }
    }

    public function getInformeVelocidad()
    {
        $valida = $this->protect->access_page('FDT_INFORME_VELOCIDAD', FALSE);

        if ($valida->redirect == TRUE) {
            header("HTTP/1.1 302 Found");
            echo "Session close";
            exit ;
        }

        $this->plantilla->cacheJSON('getInformeVelocidad', 3600);

        $this->plantilla->load_sec("fdt/bandwidth", "denegado", $valida->access);

        if (isset($_POST['groupid']) && is_numeric($_POST['groupid'])) {
            $groupid = (int)$_POST['groupid'];
            if ($groupid == 0) {
                echo $this->bmonitor->getHostForGroup('ENLACES', 'option', true);
                exit ;
            } else {
                echo $this->bmonitor->getHostForGroup($groupid, 'option', true);
                exit ;
            }
        }

        $tps_index_control['datepicker_fmFrom'] = date("Y-m-d");
        $tps_index_control['datepicker_fmTo'] = date("Y-m-d");

        $tps_index_control['table_column'] = $this->getTableColum('bandwidth');
        $tps_index_control['option_groups'] = $this->bmonitor->getAllGroupsHost('ENLACES', 'option', 'all');
        $tps_index_control['option_escuela'] = $this->bmonitor->getHostForGroup('ENLACES', 'option', true);

        $tps_index_control['option_estados'] = $this->basic->getOptionValue('fdt_estados_bandwidth', 3);
        $tps_index_control['option_medicion'] = $this->basic->getOptionValue('fdt_medicion_bandwidth', 38);

        $this->plantilla->set($tps_index_control);
        echo $this->plantilla->get();
        $this->plantilla->cacheClose();
    }

    public function getBandwidthTable()
    {
        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $var = $this->basic->setParamTable($_POST, 'id');

        //Parametros Table

        $data = array();

        $data['page'] = $var->page;

        $data['rows'] = array();

        unset($_SESSION['SET_FDT_BANDWIDTH']);

        //Filtros

        if (!isset($getParam->fmMedicion_bandwidth)) {
            return false;
        } elseif ($getParam->fmMedicion_bandwidth == 38) {
            $WHERE_OPTION = ' if(HI.`value`>=P.`locD`*1024,100,HI.`value`/P.`locD`/1024*100) as cumple, ';

            if ($getParam->fmEstados_bandwidth == 1) {
                $WHERE_ESTADO = ' AND HI.`value` >= P.`locD`*1024 ';
            } elseif ($getParam->fmEstados_bandwidth != 'all') {
                $WHERE_ESTADO = ' AND HI.`value` < P.`locD`*1024 ';
            }

        } elseif ($getParam->fmMedicion_bandwidth == 42) {
            $WHERE_OPTION = ' if(HI.`value`>=P.`locU`*1024,100,HI.`value`/P.`locU`/1024*100) as cumple, ';
            if ($getParam->fmEstados_bandwidth == 1) {
                $WHERE_ESTADO = ' AND HI.`value` >= P.`locU`*1024 ';
            } elseif ($getParam->fmEstados_bandwidth != 'all') {
                $WHERE_ESTADO = ' AND HI.`value` < P.`locU`*1024 ';
            }
        }

        if ((!empty($getParam->fmFrom_bandwidth)) && (!empty($getParam->fmTo_bandwidth))) {
            $from = "$getParam->fmFrom_bandwidth 00:00:00";
            $to = "$getParam->fmTo_bandwidth 23:59:59";
        } else {
            $today = date("Y-m-d");
            $from = "$today 00:00:00";
            $tohour = date("Y-m-d H:m:s");
            $to = "$tohour";
        }

        if (isset($getParam->fmHost_bandwidth)) {
            if (is_numeric($getParam->fmHost_bandwidth)) {
                if ($getParam->fmHost_bandwidth == 0) {
                    $WHERE = '';
                } else {
                    $WHERE = ' AND HI.id_host=' . $getParam->fmHost_bandwidth;
                }
            } else {
                $WHERE = '';
            }
        } else {
            return false;
        }

        $GROUP_BY = '';
        $COUNT = 'COUNT(*)';

        $group_host = false;

        $groupid = $getParam->fmGrupo_bandwidth;

        switch ($getParam->fmEstados_bandwidth) {
        case 'all' :
            break;

        case 0 :
            $WHERE .= ' AND CLOSE.`id_history` IS NOT NULL ';
            break;
        case 1 :
            break;
        case 2 :
            $WHERE .= ' AND CLOSE.`id_history` IS NULL ';
            break;
        case 3 :
            $WHERE .= ' AND CLOSE.`id_history` IS NULL ';
            $GROUP_BY = 'GROUP  BY H.`id_host`';
            $COUNT = 'COUNT(DISTINCT 1)';
            $group_host = true;
            break;
        }

        $LEFT_CLOSE = 'LEFT OUTER JOIN `bm_close` CLOSE ON HI.`id_history`=CLOSE.`id_history`';

        //Total de Registros

        $host_sql_total = "SELECT $COUNT As Total , GROUP_CONCAT(DISTINCT H.`id_host`) As Host
								FROM `bm_host` H
								LEFT JOIN `bm_history` HI ON HI.`id_host`=H.`id_host`
								LEFT JOIN `bm_plan` P ON P.`id_plan` = H.`id_plan`
								LEFT OUTER JOIN `bm_close` CLOSE ON HI.`id_history`=CLOSE.`id_history`
							WHERE 
								HI.`id_item`=$getParam->fmMedicion_bandwidth AND H.`groupid`=$groupid  AND H.`borrado`=0 AND
								HI.`clock` >= unix_timestamp('$from') AND  HI.`clock`  <= unix_timestamp('$to') + 3600*24
							$WHERE $WHERE_ESTADO $GROUP_BY";

        $host_totales = $this->conexion->queryFetch($host_sql_total, 'logs_fdt');

        if ($host_totales && ($host_totales[0]['Total'] > 0)) {
            $data['total'] = $host_totales[0]['Total'];
            $host_filter = explode(',', $host_totales[0]['Host']);
        } else {
            $data['total'] = 0;
            echo json_encode($data);
            exit ;
        }

        //Campos de la tabla

        $selecl_sql_feature = "SELECT HF.`feature`
							FROM `bm_host_table`  HT
							LEFT JOIN `bm_host_feature` HF USING(`id_feature`)
							WHERE `table`='bandwidth' ORDER BY HT.`orden` ASC;";

        $select_result_feature = $this->conexion->queryFetch($selecl_sql_feature, 'logs_fdt');

        // Detalle de los host

        $select_sql_host_detalle = "SELECT H.`id_host`, H.`host`, P.`plan`,  HF.`feature`,HD.`value`
								FROM `bm_host` H
												LEFT JOIN `bm_host_detalle` HD USING(`id_host`)
												LEFT JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature`
												LEFT JOIN `bm_host_table` HT ON HT.`id_feature`=HD.`id_feature`
												LEFT JOIN `bm_plan` P ON P.`id_plan` = H.`id_plan`
										
								WHERE 
												HT.`table`='bandwidth' AND
												HF.`feature_type` = 'other' AND  
												H.`id_host` IN " . $this->conexion->arrayToIN($host_filter);

        $select_result_host_detalle = $this->conexion->queryFetch($select_sql_host_detalle, 'logs_fdt');

        foreach ($select_result_host_detalle as $key => $feature) {
            $datos[$feature['id_host']][$feature['feature']] = $feature['value'];
        }

        //Get Host

        $select_sql_host = "SELECT $WHERE_OPTION P.`locD`, P.`locU`, HI.id_host,H.`host`,H.`ip_wan`, HI.`clock`,HI.`id_history`, HI.`value`,CLOSE.`id_history` AS close,CLOSE.`msg`
								FROM `bm_host` H
								LEFT JOIN `bm_history` HI ON HI.`id_host`=H.`id_host`
								LEFT JOIN `bm_plan` P ON P.`id_plan` = H.`id_plan`
								LEFT OUTER JOIN `bm_close` CLOSE ON HI.`id_history`=CLOSE.`id_history`
							WHERE 
								HI.`id_item`=$getParam->fmMedicion_bandwidth AND H.`groupid`=$groupid  AND H.`borrado`=0 AND HI.id_host IN " . $this->conexion->arrayToIN($host_filter) . " AND
								HI.`clock` >= unix_timestamp('$from') AND  HI.`clock`  <= unix_timestamp('$to') + 3600*24
							$WHERE  $WHERE_ESTADO ORDER BY HI.id_host, HI.`clock` ASC $var->limitSql";

        $select_result_host = $this->conexion->queryFetch($select_sql_host, 'logs_fdt');

        $host_id = 0;
        $anterior = 0;
        $anterior_host = 0;
        $datos_host = array();

        foreach ($select_result_host as $key => $host) {

            if ($group_host) {
                $id_array = $host['id_host'];
            } else {
                $id_array = $host['id_host'] . '_' . $host['id_history'];
            }

            $this->logs->debug('Array Resultante Host: ' . $host_id . '-', $host['id_host'], 'logs_fdt');

            if ($group_host) {
                if ($host['id_host'] != $host_id) {
                    $datos_host[$id_array]['clock_inicial'] = $host['clock'];
                } else {
                    $datos_host[$id_array]['clock_inicial'] = $datos_host[$id_array]['clock_inicial'];
                    $datos_host[$id_array]['clock_final'] = $host['clock']; ;
                }
            } else {
                $datos_host[$id_array]['clock_inicial'] = $host['clock'];
            }
            $datos_host[$id_array]['id_host'] = $host['id_host'];
            $datos_host[$id_array]['ip_wan'] = $host['ip_wan'];
            $datos_host[$id_array]['host'] = $host['host'];

            if ($getParam->fmMedicion_bandwidth == 38) {
                $datos_host[$id_array]['vel_comp'] = $this->bytes($host["locD"] * 1024, NULL, '%01.0f %s', false);
            } elseif ($getParam->fmMedicion_bandwidth == 42) {
                $datos_host[$id_array]['vel_comp'] = $this->bytes($host["locU"] * 1024, NULL, '%01.0f %s', false);
            }

            $datos_host[$id_array]['vel_medida'] = $this->bytes($host["value"], NULL, NULL, false);

            $datos_host[$id_array]['vel_cumple'] = number_format($host["cumple"], 2, ",", ".") . "%";

            $datos_host[$id_array]['value'] = $host['value'];
            $datos_host[$id_array]['cumple'] = $host['cumple'];

            $datos_host[$id_array]['close'] = $host['close'];
            $datos_host[$id_array]['msg_close'] = $host['msg'];
            $datos_host[$id_array]['id_history'][] = $host['id_history'];
            $datos_host[$id_array]['id_item'] = $getParam->fmMedicion_bandwidth;

            $host_id = $host['id_host'];
        }

        if ($datos_host) {

            $this->logs->debug('Array Resultante: ', $datos_host, 'logs_fdt');

            $_SESSION['SET_FDT_BANDWIDTH'] = $datos_host;

            $data['rows'] = array();

            foreach ($datos_host as $key => $rows) {
                $rows = $rows + $datos[$rows['id_host']];

                $rows['region'] = $this->basic->getLocalidad('region', $rows['region']);
                $rows['comuna'] = $this->basic->getLocalidad('comuna', $rows['comuna']);

                if ($group_host) {
                    $rows['clock'] = date("Y-M-d H:i:s", $rows['clock_inicial']) . '/' . date("Y-M-d H:i:s", $rows['clock_final']) . ' (' . count($rows['id_history']) . ')';
                } else {
                    $rows['clock'] = date("Y-M-d H:i:s", $rows['clock_inicial']);
                }

                if (($rows["cumple"] < 100) && ($rows['close'] == NULL)) {
                    $rows['contacto'] = '<input type="text" id="contac" name="CONTAC_' . $key . '">';
                } else {
                    $rows['contacto'] = '<input type="text" id="contac" name="CONTAC_' . $key . '" value="' . $rows['msg_close'] . '">';
                }

                $rows['tipo_medic'] = 'Medicion realizada en el router';

                $datosTable = array();
                foreach ($select_result_feature as $table) {
                    if (isset($rows[$table['feature']])) {
                        $datosTable[] = $rows[$table['feature']];
                    } else {
                        $datosTable[] = '';
                    }
                }

                $data['rows'][] = array(
                    'id' => $rows['id_host'],
                    'cell' => $datosTable
                );

                //$this->logs->debug('Array Resultante: ',$rows,'logs_fdt');
            }

        }

        echo json_encode($data);
    }

    public static function bytes($bytes, $force_unit = NULL, $format = NULL, $si = true)
    {
        $format = ($format === NULL) ? '%01.2f %s' : (string)$format;

        if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE) {
            $units = array(
                'bps',
                'kbps',
                'Mbps',
                'Gbps'
            );
            $mod = 1024;
        }
        // SI prefixes (decimal)
        else {
            $units = array(
                'Bps',
                'kB/s',
                'Mb/s',
                'Gbps'
            );
            $mod = 1000;
        }

        if (($power = array_search((string)$force_unit, $units)) === FALSE) {
            $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
        }

        return sprintf($format, $bytes / pow($mod, $power), $units[$power]);
    }

    /*
     public function getBandwidthTable2()
     {
     $getParam = (object)$_POST;

     //Valores iniciales , libreria flexigrid
     $page = 1;
     $sortname = 'id';
     $sortorder = 'asc';
     $qtype = '';
     $query = '';
     $rp = 15;

     // Validaciones de los parametros enviados por la libreria flexigrid
     if (isset($_POST['page'])) {
     $page = $_POST['page'];
     }
     if (isset($_POST['sortname']) && ($_POST['sortname']  != 'undefined') &&
     ($_POST['sortname']  != '')) {
     $sortname = $_POST['sortname'];
     }
     if (isset($_POST['sortorder']) && ($_POST['sortorder']  != 'undefined') &&
     ($_POST['sortorder']  != '')) {
     $sortorder = $_POST['sortorder'];
     }
     if (isset($_POST['qtype'])) {
     $qtype = $_POST['qtype'];
     }
     if (isset($_POST['query'])) {
     $query = $_POST['query'];
     }
     if (isset($_POST['rp'])) {
     $rp = $_POST['rp'];
     }

     //Parametros Table

     $data = array();
     $data['page'] = $page;

     $pageStart = ($page-1)*$rp;

     $limitSql = " LIMIT $pageStart, $rp";

     //Filtros

     $WHERE_ESTADO = '';

     if(!isset($getParam->fmMedicion_bandwidth)) {
     return false;
     } elseif ($getParam->fmMedicion_bandwidth == 38) {
     $WHERE_OPTION = '
     if(HI.`value`>=P.`locD`*1024,100,HI.`value`/P.`locD`/1024*100) as cumple, ';

     if($getParam->fmEstados_bandwidth == 1) {
     $WHERE_ESTADO = ' AND HI.`value` >= P.`locD`*1024 ';
     } elseif ($getParam->fmEstados_bandwidth != 'all') {
     $WHERE_ESTADO = ' AND HI.`value` < P.`locD`*1024 ';
     }

     } elseif ($getParam->fmMedicion_bandwidth == 42) {
     $WHERE_OPTION = '
     if(HI.`value`>=P.`locU`*1024,100,HI.`value`/P.`locU`/1024*100) as cumple, ';
     if($getParam->fmEstados_bandwidth == 1) {
     $WHERE_ESTADO = ' AND HI.`value` >= P.`locU`*1024 ';
     } elseif ($getParam->fmEstados_bandwidth != 'all') {
     $WHERE_ESTADO = ' AND HI.`value` < P.`locU`*1024 ';
     }
     }

     if(!isset($getParam->fmGrupo_bandwidth)) {
     return false;
     }

     if((!empty($getParam->fmFrom_bandwidth)) &&
     (!empty($getParam->fmTo_bandwidth))) {
     $from = "$getParam->fmFrom_bandwidth 00:00:00";
     $to = "$getParam->fmTo_bandwidth 23:59:59";
     } else {
     $today = date("Y-m-d");
     $from = "$today 00:00:00";
     $tohour = date("Y-m-d H:m:s");
     $to = "$tohour";
     }

     if(isset($getParam->fmHost_bandwidth)) {
     if(is_numeric($getParam->fmHost_bandwidth)) {
     if($getParam->fmHost_bandwidth == 0) {
     $WHERE = '';
     } else {
     $WHERE = ' AND HI.id_host='.$getParam->fmHost_bandwidth;
     }
     } else {
     $WHERE = '';
     }
     } else {
     return false;
     }

     $GROUP_BY = 'HI.`id_history`';

     if(isset($getParam->fmEstados_bandwidth)) {
     if($getParam->fmEstados_bandwidth == 'all') {
     $WHERE .= '';
     } elseif ($getParam->fmEstados_bandwidth == 0) {
     $WHERE .= ' AND CLOSE.`id_history` IS NOT NULL ';
     } elseif ($getParam->fmEstados_bandwidth == 1) {

     } elseif ($getParam->fmEstados_bandwidth == 2) {
     $WHERE .= ' AND CLOSE.`id_history` IS NULL ';
     } elseif ($getParam->fmEstados_bandwidth == 3) {
     $WHERE .= ' AND CLOSE.`id_history` IS NULL ';
     $GROUP_BY = 'H.`id_host`';
     }
     }

     //Host totales

     $host_sql_total = "SELECT COUNT(1) As Total
     FROM `bm_history`  HI
     LEFT JOIN `bm_host` H ON H.`id_host`=HI.`id_host`
     LEFT OUTER JOIN `bm_close` CLOSE ON HI.`id_history`=CLOSE.`id_history`
     LEFT JOIN `bm_plan` P ON P.`id_plan`=H.`id_plan`
     WHERE HI.`id_item`=$getParam->fmMedicion_bandwidth AND
     HI.`clock` >= unix_timestamp('$from') AND  HI.`clock`  <=
     unix_timestamp('$to') + 3600*24
     $WHERE $WHERE_ESTADO";

     $host_totales = $this->conexion->queryFetch($host_sql_total);

     if($host_totales) {
     $data['total'] = $host_totales[0]['Total'];
     } else {
     $data['total'] = 0;
     }

     //Features

     $query_feature = "SELECT HF.`feature`
     FROM `bm_host_table`  HT
     LEFT JOIN `bm_host_feature` HF USING(`id_feature`)
     WHERE `table`='bandwidth' ORDER BY HT.`orden` ASC;";

     $featuresTable = $this->conexion->queryFetch($query_feature);

     $this->conexion->query("SET SESSION group_concat_max_len = 20000");

     //Host a desplegar

     $query_host = "SELECT $WHERE_OPTION H.`host`,HI.id_host,H.`ip_wan`,P.`plan`,
     P.`nacD`, HI.`clock`,HI.`id_history`,  min(HI.`id_history`) as mixHistory,
     max(HI.`id_history`) as maxHistory,round(HI.`value`/1024/1024,2) as
     value,CLOSE.`id_history` AS close ,GROUP_CONCAT(HF.`feature`)  AS feature,
     GROUP_CONCAT(HD.`value`) AS valueFeature, GROUP_CONCAT(HI.`id_history`) AS
     idHistory
     FROM `bm_host` H
     LEFT JOIN `bm_host_detalle` HD USING(`id_host`)
     LEFT JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature`
     LEFT JOIN `bm_host_table` HT ON HT.`id_feature`=HD.`id_feature`
     LEFT JOIN `bm_plan` P ON P.`id_plan`=H.`id_plan`
     LEFT JOIN `bm_history` HI ON HI.`id_host`=H.`id_host`
     LEFT OUTER JOIN `bm_close` CLOSE ON HI.`id_history`=CLOSE.`id_history`
     WHERE
     H.`groupid`=$getParam->fmGrupo_bandwidth AND H.`borrado`=0 AND
     HI.`clock` >= unix_timestamp('$from') and  HI.`clock`  <=
     unix_timestamp('$to') + 3600*24 AND
     HT.`table`='bandwidth' AND
     HF.`feature_type` = 'other' AND
     HI.`id_item`=$getParam->fmMedicion_bandwidth $WHERE $WHERE_ESTADO
     GROUP  BY $GROUP_BY
     ORDER BY  HI.`id_history` ASC,HT.`orden`  ASC ".$limitSql;

     $hostValues = $this->conexion->queryFetch($query_host);

     $data['rows'] = array();

     foreach ($hostValues as $key => $value) {

     $valores = array();

     $valores['host'] = $value['host'];
     $valores['ip_wan'] = $value['ip_wan'];
     $valores['plan'] = $value['plan'];

     $valores['vel_comp'] = $value["nacD"]/1024 . " Mbps";

     $valores['vel_medida'] =  number_format($value["value"],2,",","."). " Mbps";

     $valores['vel_cumple'] =  number_format($value["cumple"],2,",","."). "%";

     $valores['clock'] = date("Y-M-d H:i:s",$value['clock']);

     $valores['tipo_medic'] = 'Medicion realizada en el router';

     if(( $value["cumple"] < 100) && ($value['close'] == NULL)) {

     $groupIDHistory = explode(",",$value['idHistory']);
     $groupIDHistory = array_unique($groupIDHistory);
     $groupIDHistory = join(',', $groupIDHistory);

     $nameINPUT =
     'CONTAC_'.$value['id_host'].'_'.$getParam->fmMedicion_bandwidth.'_'.$value['mixHistory'].'_'.$value['maxHistory'];

     $_SESSION[$nameINPUT] = $groupIDHistory;

     $valores['contacto'] = '<input type="text" size="30" maxlength="254"
     name="'.$nameINPUT.'" value="">';
     } else {
     $valores['contacto'] = '';
     }

     $features=explode(',',$value['feature']);
     $valoresFeature=explode(',',$value['valueFeature']);

     $features_final = $features[0];
     $count = 0;
     foreach ($features as $keyF => $feature) {
     if( ($count > 0) && ($features_final == $feature) ) {
     break;
     }
     $valores[$feature] = $valoresFeature[$keyF];
     $count++;
     }

     $datos = array();

     foreach ($featuresTable as $table) {
     if(isset($valores[$table['feature']])) {
     $datos[] = $valores[$table['feature']];
     } else {
     $datos[] = '';
     }
     }

     $data['rows'][] = array(
     'id' => $key,
     'cell' => $datos
     );
     }
     header("Content-type: application/json");
     echo json_encode($data);
     } */

    public function setBandwidth()
    {
        $getParam = (object)$_POST;

        $array_id = $_SESSION['SET_FDT_BANDWIDTH'];

        $result['status'] = true;
        $result['msg'] = $this->language->SAVE_OK;

        foreach ($getParam as $key => $value) {
            if (preg_match("/^CONTAC/i", $key)) {

                $ArrayContac = explode("_", $key, 2);

                $KEY = $ArrayContac[1];

                $value_CONTAC = "CONTAC_" . $KEY;
                $value_CONTAC = $getParam->$value_CONTAC;

                $id_host = $array_id[$KEY]['id_host'];

                $array_id_history = $array_id[$KEY]['id_history'];
                $array_id_item = $array_id[$KEY]['id_item'];

                if (!empty($value_CONTAC)) {

                    $insert_close_sql = 'INSERT INTO `bm_close` (`id_item`, `id_host`, `id_history`, `msg`, `close`, `fen`) 
										VALUES ';
                    $insert_close_values = array();

                    foreach ($array_id_history as $key_history => $id_history) {

                        if (is_numeric($id_history)) {

                            $insert_close_values[] = "('$array_id_item','$id_host', '$id_history', '$value_CONTAC', '', '')";

                        }
                    }

                    $closeInsert = $this->conexion->query($insert_close_sql . join(",", $insert_close_values));

                    if (!$closeInsert) {
                        $result['msg'] = $this->language->ITERNAL_ERROR;
                        $result['status'] = false;
                    }

                }/* FIN IF Campo vacio */
                else {
                    $result['status'] = false;
                    $result['msg'] = $this->language->ERROR_PARAM_EMPTY;
                }
            } /* IF Buscar Todos los CONTAC */
        }/* Recorrer las variables */
        $callback = $_GET['callback'];

        echo $callback . "(" . json_encode($result) . ')';
    }// Fin Funcion  setDisponibilidad

    public function setBandwidth2()
    {
        $getParam = (object)$_POST;

        foreach ($getParam as $key => $value) {
            if (preg_match("/^CONTAC/i", $key)) {

                $ArrayContac = explode("_", $key);
                $id_host = $ArrayContac[1];
                $id_item = $ArrayContac[2];
                $historyMin = $ArrayContac[3];
                $historyMax = $ArrayContac[4];

                $valueContac = $value;

                $datos = array();
                $datos['status'] = false;

                if (!empty($valueContac)) {

                    if ($historyMin == $historyMax) {

                        $insert_close = "INSERT INTO `bm_close` (`id_item`, `id_host`, `id_history`, `msg`, `close`, `fen`) 
											VALUES ('$id_item','$id_host', '$historyMin', '$valueContac', '', '');";

                        $closeInsert = $this->conexion->query($insert_close);

                        if ($closeInsert) {
                            $datos['status'] = true;
                        }

                    } else {

                        $nameINPUT = 'CONTAC_' . $id_host . '_' . $id_item . '_' . $historyMin . '_' . $historyMax;

                        $GroupIDHistory = $_SESSION[$nameINPUT];

                        $GroupIDHistory = explode(",", $GroupIDHistory);

                        $insertClose = 'INSERT INTO `bm_close` (`id_item`, `id_host`, `id_history`, `msg`, `close`, `fen`) VALUES ';

                        $value = array();
                        foreach ($GroupIDHistory as $key => $history) {
                            $value[] = "( $id_item, " . $id_host . "," . $history . ", '" . $valueContac . "', '', '')";
                        }
                        $valueAll = join(',', $value);

                        $insertClose = $insertClose . $valueAll;
                        $closeInsert = $this->conexion->query($insertClose);
                        if ($closeInsert) {
                            $datos['status'] = true;
                        }
                    }
                }
                header("Content-type: application/json");
                echo json_encode($datos);
            }
        }
    }

    public function getInformeConsolidado()
    {

        $valida = $this->protect->access_page('FDT_INFORME_CONSOLIDADO', FALSE);

        if ($valida->redirect == TRUE) {
            header("HTTP/1.1 302 Found");
            echo "Session close";
            exit ;
        }

        $this->plantilla->load_sec("fdt/fdt_consolidado", "denegado", $valida->access);

        //Option Grupos
        $sql_option = "SELECT `groupid`,`name` FROM `bm_host_groups` WHERE `type` = 'ENLACES'";

        $optionsGroups = $this->conexion->queryFetch($sql_option);

        $optionG = '';
        foreach ($optionsGroups as $key => $optionsGroup) {
            $optionG .= '<option value="' . $optionsGroup['groupid'] . '">' . $optionsGroup['name'] . '</option>';
        }

        $tps_index_control['option_groups'] = $optionG;

        //Fin Option Grupos

        //Option meses

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

        $tps_index_control['table_column'] = $this->getTableColum('fdt_consolidado');

        $tps_index_control['option_meses'] = $optionM;
        $tps_index_control['option_anos'] = $this->getOptionAnios();

        $this->plantilla->set($tps_index_control);

        echo $this->plantilla->get();
    }

    public function getConsolidadoTable()
    {
        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $var = $this->basic->setParamTable($_POST, 'H.`id_host`', 'DESC');

        //Parametros Table

        $data = array();

        $data['page'] = $var->page;

        $data['rows'] = array();

        //Filtros

        if (!isset($getParam->fmGrupo_consolidado)) {
            return false;
        }

        if (!isset($getParam->fmMeses_consolidado)) {
            return false;
        } else {
            if ($getParam->fmMeses_consolidado < 10) {
                $getParam->fmMeses_consolidado = "0" . $getParam->fmMeses_consolidado;
            }
        }

        if (!isset($getParam->fmAno_consolidado)) {
            return false;
        }

        $fecha = $getParam->fmAno_consolidado . "-" . $getParam->fmMeses_consolidado . "-00";

        $total_report_sql = "SELECT count(*) As Total FROM `bm_enlace` WHERE `groupid` = $getParam->fmGrupo_consolidado AND `fecha` = '$fecha'";

        $report_totales = $this->conexion->queryFetch($total_report_sql);

        if ($report_totales) {
            $data['total'] = $report_totales[0]['Total'];
        } else {
            $data['total'] = 0;
        }

        $get_consolidado_sql = "SELECT  E.*,H.`host`,P.`plan`,
									GROUP_CONCAT(HT.`id_feature` ORDER BY HT.`orden` ASC) AS id_feature , GROUP_CONCAT(HD.`value` ORDER BY HT.`orden` ASC) AS value
									FROM `bm_enlace` E
									LEFT JOIN `bm_host` H USING(`id_host`)
									LEFT OUTER JOIN `bm_host_detalle` HD ON HD.`id_host`= H.`id_host`
									LEFT OUTER JOIN `bm_host_table` HT ON HT.`id_feature` = HD.`id_feature` 
									LEFT JOIN `bm_plan` P ON P.`id_plan`=E.`id_plan`
									WHERE 
										E.`groupid` = $getParam->fmGrupo_consolidado AND 
										E.`fecha` = '$fecha' AND
										HT.`table`='fdt_consolidado'
									GROUP BY H.`id_host`
									$var->sortSql 
									$var->limitSql;";

        $get_consolidado = $this->conexion->queryFetch($get_consolidado_sql);
        $trent = 0;
        $tpagar = 0;
        $_SESSION['tarifa_inicial'] = 0;
        $_SESSION['apagar'] = 0;
        $_SESSION['escuelas'] = count($get_consolidado);

        foreach ($get_consolidado as $key => $value) {

            $feature = array();
            $feature_id = explode(",", $value['id_feature']);
            $feature_value = explode(",", $value['value']);

            foreach ($feature_id as $feature_id_key => $feature_id_v) {
                $feature[$feature_id_v] = $feature_value[$feature_id_key];
            }

            if (isset($feature[23]) && ($feature[23] != '')) {
                $comuna = $this->basic->getLocalidad('comuna', $feature[23]);
            } else {
                $comuna = $this->language->SIN_COMUNA;
            }

            if (isset($feature[24]) && ($feature[24] != '')) {
                $region = $this->basic->getLocalidad('region', $feature[24]);
            } else {
                $region = $this->language->SIN_REGION;
            }

            if (isset($feature[1]) && ($feature[1] != '')) {
                $rdb = $feature[1];
            } else {
                $rdb = '';
            }

            if (isset($feature[21]) && ($feature[21] != '')) {
                $rut = $feature[21];
            } else {
                $rut = '';
            }

            if (isset($feature[25]) && ($feature[25] != '')) {
                $idservicio = $feature[25];
            } else {
                $idservicio = '';
            }

            if (isset($feature[2]) && ($feature[2] != '')) {
                $id_vivienda = $feature[2];
            } else {
                $id_vivienda = '';
            }

            if (isset($feature[22]) && ($feature[22] != '')) {
                $direccion = $feature[22];
            } else {
                $direccion = '';
            }

            $data['rows'][] = array(
                'id' => $value['id_host'],
                'cell' => array(
                    $value['fecha'],
                    $rdb,
                    $rut,
                    $idservicio,
                    $id_vivienda,
                    $value['host'],
                    $direccion,
                    $comuna,
                    $region,
                    $value['plan'],
                    "$ " . number_format($value['tarifa_inicial'], 0, ",", "."),
                    number_format($value['avg_locD'], 2, ",", ".") . "%",
                    number_format($value['avg_locU'], 2, ",", ".") . "%",
                    number_format($value["avg_vel"], 2, ",", ".") . "%",
                    number_format(100 - $value['dst_vel'], 2, ".", ".") . "%",
                    number_format($value['avg_availability'], 2, ",", ".") . "%",
                    number_format(100 - $value['dst_availability'], 2, ".", ".") . "%",
                    "$ " . number_format($value['apagar'], 0, ".", ".")
                )
            );

            $trent = $trent + $value["tarifa_inicial"];
            $tpagar = $tpagar + $value["apagar"];
        }

        $_SESSION['tarifa_inicial'] = $trent;
        $_SESSION['apagar'] = $tpagar;

        $this->basic->jsonEncode($data);
    }

    public function getResumenFDTConsolidado()
    {

        $data['result'] = json_encode($this->consolidado);

        $data['status'] = true;

        $data['escuelas'] = $_SESSION['escuelas'];

        $data['tarifaTotal'] = "$" . number_format($_SESSION['tarifa_inicial'], 0, ",", ".");

        $data['tarifaAPagar'] = "$" . number_format($_SESSION['apagar'], 0, ",", ".");

        $data['descuentos'] = "$" . number_format($_SESSION['tarifa_inicial'] - $_SESSION['apagar'], 0, ",", ".");

        $data['porcentaje'] = number_format((100 * ($_SESSION['tarifa_inicial'] - $_SESSION['apagar'])) / $_SESSION['tarifa_inicial'], 0, ".", ".") . "%";

        echo json_encode($data);
    }

    public function getFDTExportar()
    {
    	ini_set("memory_limit","250M");

        $valida = $this->protect->access_page('FDT_EXPORT', FALSE);

        $this->plantilla->load_sec("fdt/fdt_exportar", "denegado", $valida->access);

        if ($valida->redirect == TRUE) {
            header("HTTP/1.1 302 Found");
            echo "Session close";
            exit ;
        }

        //Option Grupos
        $sql_option = "SELECT `groupid`,`name` FROM `bm_host_groups` WHERE `type` = 'ENLACES'";

        $optionsGroups = $this->conexion->queryFetch($sql_option);

        $optionG = $this->basic->getOption($optionsGroups, 'groupid', 'name', '-first-');

        $groupidInicial = $optionsGroups[0]['groupid'];

        $tps_index_control['option_groups'] = $optionG;

        //Fin Option Grupos

        //Option meses

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
        $tps_index_control['option_anos'] = $this->getOptionAnios();

        $tps_index_control['option_medicion'] = $this->basic->getOptionValue('fdt_export');

        $getHost = $this->bmonitor->getAllHost(false, false, $groupidInicial);

        $FDT_ALL_OPTION_SCHOOL = $this->parametro->get("FDT_ALL_OPTION_SCHOOL", FALSE);

        if ($FDT_ALL_OPTION_SCHOOL) {
            $tps_index_control['option_colegios'] = $this->basic->getOption($getHost, 'id_host', 'host', 'all');
        } else {
            $tps_index_control['option_colegios'] = $this->basic->getOption($getHost, 'id_host', 'host');
        }

        $FDT_CABECERA_EXPORT = $this->parametro->get("FDT_CABECERA_EXPORT", FALSE);

        if ($FDT_CABECERA_EXPORT) {
            $tps_index_control['cabecera'] = '<div style="width: 86px;float: left;height: 21px;" class="ui-widget ui-corner-all ui-input ui-checkbox ui-state-default"><span class="ui-icon ui-icon-none"></span><input type="checkbox" id="cabecera" value="true" style="margin-top: 4px;"> Cabecera</div>';
        }

        $FDT_EXCEL_EXPORT = $this->parametro->get("FDT_EXCEL_EXPORT", FALSE);

        if ($FDT_EXCEL_EXPORT || $_SESSION['secretadmin'] == TRUE) {
            $tps_index_control['excel'] = '<button style="float: left;" type="button" id="export-download-excel">Descargar Excel</button>';
        }

        $this->plantilla->set($tps_index_control);

        echo $this->plantilla->get();
    }

    public function upload()
    {
        if (isset($_GET['f'])) {
            $file_name = $_GET['f'];
            $file_path = 'upload/' . $file_name;
            if (file_exists($file_path) && is_readable($file_path) && (preg_match('/\.csv$/', $file_name) || preg_match('/\.xls$/', $file_name))) {

                header("Pragma: public");
                header("Content-Disposition: attachment; filename=" . $file_name . "\n\n");
                //header ("Content-Type: text/csv");
                header("Content-type: " . mime_content_type($file_path));
                header("Content-Length: " . filesize($file_path));
                readfile($file_path);
            } else {
                header("HTTP/1.0 404 Not Found");
                echo "<h1>Error 404: File Not Found: <br /><em>$file_name</em></h1>";
            }
        } else {
            header("HTTP/1.0 404 Not Found");
            echo "<h1>Error 404: File Not Found: <br /><em>$file_name</em></h1>";
        }
    }

    public function getExportFDT()
    {
    	ini_set("memory_limit","250M");
		
        $getParam = (object)$_POST;

        $medicion = (int)$_POST['fdt_export_medicion'];

        if ($medicion === 1) {
            $value = "if(HI.`value`=0 and (close is null or close='' or close ='OPERADOR'),0,1) as valor";
        } else {
            $value = "replace(replace(replace(format(HI.`value`,2),'.','#'),',','.'),'#',',') as valor";
        }

        if ($getParam->fmMeses_export < 10) {
            $mes = '0' . $getParam->fmMeses_export;
        } else {
            $mes = $getParam->fmMeses_export;
        }

        $clock = $getParam->fmAno_export . "/" . $mes;

        $medicion = $_POST["fdt_export_medicion"];

        $host = $_POST["fdt_export_colegio"];

        if ($host > 0) {
            $filter = ' AND H.`id_host`=' . $host;
        } else {
            $filter = '';
        }

        if (!isset($getParam->cabecera) || $getParam->cabecera == '0') {
            $cabecera = FALSE;
        } else {
            $cabecera = TRUE;
        }

        if (!isset($getParam->typefile)) {
            $typefile = "CSV";
        } else {
            $typefile = "EXCEL";
        }

        if (is_numeric($medicion) && $medicion > 0) {
            $filter .= ' AND HI.`id_item`=' . $medicion;
        } elseif ($medicion == 'all') {
            $get_id_medicion = "SELECT `id_option` FROM `bm_option` WHERE `option_group` = 'fdt_export'";

            $get_id_medicion_result = $this->conexion->queryFetch($get_id_medicion);

            if ($get_id_medicion_result) {
                $filter .= ' AND HI.`id_item` IN ' . $this->conexion->arrayIN($get_id_medicion_result, 'id_option', TRUE);
            }
        } else {
            $return['status'] = false;
            $return['msg'] = $this->language->ERROR_FDT_EXPORT;
            echo json_encode($return);
            exit ;
        }

        $get_export_sql = "SELECT H.`host`,replace(IT.`description`,'.sh','') as Descripcion , PL.`plan`,from_unixtime(HI.`clock`) as fecha,DATE_FORMAT(from_unixtime(HI.`clock`),'%Y/%m/%d %H') medicion,
				IT.`unit` as unidad, CL.`msg` as contacto, CL.`close` AS motivo,
				GROUP_CONCAT(HF.`feature` ORDER BY HD.`id_feature` DESC) AS nameF,
				GROUP_CONCAT(HD.`value` ORDER BY HD.`id_feature` DESC) AS valueF,
				$value
				FROM `bm_host` H
				LEFT JOIN `bm_history` HI ON ( H.`id_host`=HI.`id_host`)
				LEFT JOIN `bm_items` IT ON IT.`id_item`=HI.`id_item`
				LEFT JOIN `bm_plan` PL ON PL.`id_plan`=H.`id_plan`
				LEFT JOIN `bm_host_detalle` HD ON HD.`id_host`=H.`id_host`
				LEFT JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature`
				LEFT JOIN `bm_close` CL ON (HI.`id_history`=CL.`id_history`)
				WHERE 	H.`groupid`=$getParam->fmGrupo_export AND DATE_FORMAT(from_unixtime(HI.`clock`),'%Y/%m')='$clock'
						AND HF.`type_feature` IN ('ambos','ENLACES') AND H.`borrado` = 0 AND H.`status` = 1 AND HF.`id_feature` $filter
				GROUP BY HI.`id_history`
				ORDER BY H.`id_host`,Descripcion,HI.`clock`";

        $get_export_result = $this->conexion->queryFetch($get_export_sql, 'logs_fdt');

        if ($get_export_result) {

            $fdt_file = 'fdt_' . $getParam->fmAno_export . "_" . $mes . "_" . $host . "_" . $medicion . '.generated_' . date("Ymd") . '.csv';

            $fdt_path = site_path . '/upload/';

            $fp = fopen($fdt_path . $fdt_file, 'w');

            $FILTER_SCHEDULE_FDT = $this->parametro->get("FILTER_SCHEDULE_FDT", TRUE);

            if ($FILTER_SCHEDULE_FDT) {

                $get_exp_schedule = $this->conexion->queryFetch("SELECT `horario`,`feriados` FROM `bm_host_groups` WHERE `groupid` = " . $getParam->fmGrupo_export . " LIMIT 1");

                if ($get_exp_schedule) {

                    $horario = $get_exp_schedule[0]['horario'];
                    $feriados = $get_exp_schedule[0]['feriados'];

                } else {
                    $return['status'] = false;
                    $return['msg'] = $this->language->ERROR_FDT_EXPORT;
                    echo json_encode($return);
                    exit ;
                }

            }

            if ($cabecera) {

                $line[] = "Host";
                $line[] = "ID servicio";
                $line[] = "ID vivienda";
                $line[] = "RDB";
                $line[] = "RUT";
                $line[] = "Comuna";
                $line[] = "Direccion";
                $line[] = "Region";
                $line[] = "Descripcion";
                $line[] = "Plan";
                $line[] = "Fecha";
                $line[] = "Medicion";
                $line[] = "Valor";
                $line[] = "Unidad";
                $line[] = "Contacto";
                $line[] = "Motivo";

                fputcsv($fp, $line);
            }

            $countFilter = 0;

            foreach ($get_export_result as $fields) {

                if ($FILTER_SCHEDULE_FDT) {
                    $valid = $this->basic->cdateForDate($fields['fecha'], $horario, $feriados);
                    if ($valid === FALSE) {
                        $return['filter'] = TRUE;
                        $countFilter++;
                        continue;
                    }
                }

                $feature_name = explode(",", $fields['nameF']);
                $feature_value = explode(",", $fields['valueF']);

                foreach ($feature_name as $key => $value) {
                    $fields[$value] = $feature_value[$key];
                }

                $line = array();

                $line[] = (string)$fields['host'];
                $line[] = (isset($fields['idservicio']) ? (int)$fields['idservicio'] : 0);
                $line[] = (isset($fields['id_vivienda']) ? (int)$fields['id_vivienda'] : 0);
                $line[] = (isset($fields['rdb']) ? (int)$fields['rdb'] : 'sin_rdb');
                $line[] = (isset($fields['rut']) ? $fields['rut'] : 'sin_rut');
                $line[] = (string)$this->basic->getLocalidad('comuna', $fields['comuna']);
                $line[] = (string)$fields['direccion'];
                $line[] = (string)$this->basic->getLocalidad('region', $fields['region']);
                $line[] = (string)$fields['Descripcion'];
                $line[] = (string)$fields['plan'];
                $line[] = (string)$fields['fecha'];
                $line[] = (string)$fields['medicion'];
                $line[] = $fields['valor'];
                $line[] = (string)$fields['unidad'];
                $line[] = (string)$fields['contacto'];
                $line[] = (string)$fields['motivo'];

                fputcsv($fp, $line);
            }
            fclose($fp);
            $this->logs->error('[FDT-CSV] Cantidad de lineas filtradas por horario incorrecto: ', $countFilter, 'logs_fdt');
            $return['status'] = true;
            $return['url'] = $fdt_file;
        } else {
            $return['status'] = false;
            $return['msg'] = $this->language->ERROR_FDT_EXPORT;
        }

        echo json_encode($return);
    }

    public function getExportFDTNEW()
    {
        $getParam = (object)$_POST;

        //Validando parametros:

        $continue = TRUE;

        if ($getParam->fmMeses_export < 10) {
            $mes = '0' . $getParam->fmMeses_export;
        } else {
            $mes = $getParam->fmMeses_export;
        }

        $clock = $getParam->fmAno_export . "/" . $mes;

        if (is_numeric($getParam->fdt_export_medicion) && $getParam->fdt_export_medicion > 0) {

            $get_id_medicion = "SELECT `id_option`,  `option` FROM `bm_option` WHERE `option_group` = 'fdt_export' AND `id_option` = " . $getParam->fdt_export_medicion;

            $get_id_medicion_result = $this->conexion->queryFetch($get_id_medicion);

            if ($get_id_medicion_result) {
                $mediciones[$get_id_medicion_result[0]['id_option']] = $get_id_medicion_result[0]['option'];
            } else {
                $continue = FALSE;
            }
        } elseif ($getParam->fdt_export_medicion == 'all') {

            $get_id_medicion = "SELECT `id_option`,  `option` FROM `bm_option` WHERE `option_group` = 'fdt_export'";

            $get_id_medicion_result = $this->conexion->queryFetch($get_id_medicion);

            if ($get_id_medicion_result) {
                foreach ($get_id_medicion_result as $key => $value) {

                    if (is_numeric($value['id_option'])) {
                        $mediciones[$value['id_option']] = $value['option'];
                    }

                }
            } else {
                $continue = FALSE;
            }

        } else {
            $continue = FALSE;
        }

        if ($continue == FALSE) {
            $return['status'] = FALSE;
            $return['msg'] = $this->language->ERROR_FDT_EXPORT;
            echo json_encode($return);
            exit ;
        }

        $host = $getParam->fdt_export_colegio;

        if ($host > 0) {
            $filter = ' AND H.`id_host`=' . $host;
        } else {
            $filter = '';
        }

        if (!isset($getParam->cabecera) || $getParam->cabecera == '0') {
            $cabecera = FALSE;
        } else {
            $cabecera = TRUE;
        }

        $FILTER_SCHEDULE_FDT = $this->parametro->get("FILTER_SCHEDULE_FDT", TRUE);

        if ($FILTER_SCHEDULE_FDT) {

            $get_exp_schedule = $this->conexion->queryFetch("SELECT `horario`,`feriados` FROM `bm_host_groups` WHERE `groupid` = " . $getParam->fmGrupo_export . " LIMIT 1");

            if ($get_exp_schedule) {

                $horario = $get_exp_schedule[0]['horario'];
                $feriados = $get_exp_schedule[0]['feriados'];

            } else {
                $return['status'] = false;
                $return['msg'] = $this->language->ERROR_FDT_EXPORT;
                echo json_encode($return);
                exit ;
            }

        }

        //FIN de parametros

        include APPS . "plugins/PHPExcel.php";

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Baking software")->setLastModifiedBy("Bmonitor")->setTitle("Export FDT")->setSubject("Export")->setDescription("Documento con detalle de mediciones")->setKeywords("fdt")->setCategory("FDT");

        $page = 0;

        foreach ($mediciones as $key => $medicion) {

            if ($page > 0) {
                $objPHPExcel->createSheet();
            }

            $objPHPExcel->setActiveSheetIndex($page);

            $objPHPExcel->getActiveSheet()->setTitle($medicion);

            if ($medicion === $key) {
                $value = "if(HI.`value`=0 and (close is null or close='' or close ='OPERADOR'),0,1) as valor";
            } else {
                $value = "replace(replace(replace(format(HI.`value`,2),'.','#'),',','.'),'#',',') as valor";
            }

            $get_export_sql = "SELECT H.`host`,replace(IT.`description`,'.sh','') as Descripcion , PL.`plan`,from_unixtime(HI.`clock`) as fecha,DATE_FORMAT(from_unixtime(HI.`clock`),'%Y/%m/%d %H') medicion,
					IT.`unit` as unidad, CL.`msg` as contacto, CL.`close` AS motivo,
					GROUP_CONCAT(HF.`feature` ORDER BY HD.`id_feature` DESC) AS nameF,
					GROUP_CONCAT(HD.`value` ORDER BY HD.`id_feature` DESC) AS valueF,
					$value
					FROM `bm_host` H
					LEFT JOIN `bm_history` HI ON ( H.`id_host`=HI.`id_host`)
					LEFT JOIN `bm_items` IT ON IT.`id_item`=HI.`id_item`
					LEFT JOIN `bm_plan` PL ON PL.`id_plan`=H.`id_plan`
					LEFT JOIN `bm_host_detalle` HD ON HD.`id_host`=H.`id_host`
					LEFT JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature`
					LEFT JOIN `bm_close` CL ON (HI.`id_history`=CL.`id_history`)
					WHERE 	H.`groupid`=$getParam->fmGrupo_export AND DATE_FORMAT(from_unixtime(HI.`clock`),'%Y/%m')='$clock'
							AND HF.`type_feature` IN ('ambos','ENLACES') AND  H.`borrado` = 0 AND H.`status` = 1 AND HF.`id_feature` $filter  AND HI.`id_item`=$key
					GROUP BY HI.`id_history`
					ORDER BY H.`id_host`,HI.`id_history`;";

            $get_export_result = $this->conexion->queryFetch($get_export_sql, 'logs_fdt');

            if ($get_export_result) {

                if ($cabecera) {
                    $objPHPExcel->setActiveSheetIndex($page)->setCellValue('A1', "Host")->setCellValue('B1', "ID servicio")->setCellValue('C1', "ID vivienda")->setCellValue('D1', "RDB")->setCellValue('E1', "RUT")->setCellValue('F1', "Comuna")->setCellValue('G1', "Direccion")->setCellValue('H1', "Region")->setCellValue('I1', "Descripcion")->setCellValue('J1', "Plan")->setCellValue('K1', "Fecha")->setCellValue('L1', "Medicion")->setCellValue('M1', "Valor")->setCellValue('N1', "Unidad")->setCellValue('O1', "Contacto")->setCellValue('P1', "Motivo")->freezePane('A2');

                    /*$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setOutlineLevel(1)
                     ->setVisible(false)
                     ->setCollapsed(true);*/

                    //$objPHPExcel->getActiveSheet()->freezePane('A2');

                    $objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);

                }

                $countFilter = 0;

                if ($cabecera) {
                    $line = 2;
                } else {
                    $line = 1;
                }

                foreach ($get_export_result as $num => $fields) {

                    if ($FILTER_SCHEDULE_FDT) {
                        $valid = $this->basic->cdateForDate($fields['fecha'], $horario, $feriados);
                        if ($valid === FALSE) {
                            $return['filter'] = TRUE;
                            $countFilter++;
                            continue;
                        }
                    }

                    //Inicio de datos
                    $feature_name = explode(",", $fields['nameF']);
                    $feature_value = explode(",", $fields['valueF']);

                    foreach ($feature_name as $key => $value) {
                        $fields[$value] = $feature_value[$key];
                    }

                    $objPHPExcel->setActiveSheetIndex($page)->setCellValue('A' . $line, $fields['host'])->setCellValue('B' . $line, (isset($fields['idservicio']) ? (int)$fields['idservicio'] : 0))->setCellValue('C' . $line, (isset($fields['id_vivienda']) ? (int)$fields['id_vivienda'] : 0))->setCellValue('D' . $line, (isset($fields['rdb']) ? (int)$fields['rdb'] : 'sin_rdb'))->setCellValue('E' . $line, (isset($fields['rut']) ? $fields['rut'] : 'sin_rut'))->setCellValue('F' . $line, $this->basic->getLocalidad('comuna', $fields['comuna']))->setCellValue('G' . $line, $fields['direccion'])->setCellValue('H' . $line, $this->basic->getLocalidad('region', $fields['region']))->setCellValue('I' . $line, $fields['Descripcion'])->setCellValue('J' . $line, $fields['plan'])->setCellValue('K' . $line, $fields['fecha'])->setCellValue('L' . $line, $fields['medicion'])->setCellValue('M' . $line, $fields['valor'])->setCellValue('N' . $line, $fields['unidad'])->setCellValue('O' . $line, $fields['contacto'])->setCellValue('P' . $line, $fields['motivo']);

                    $line++;

                    //Fin de datos
                }
            }
            $page++;
        }

        $objPHPExcel->setActiveSheetIndex(0);

        $fdt_file = 'fdt_' . $getParam->fmAno_export . $mes . "_" . $getParam->fdt_export_medicion . "_" . $host . '_generated_' . date("Ymd") . '.xls';
        $fdt_path = site_path . '/upload/';

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save($fdt_path . $fdt_file);

        if ($continue) {
            $this->logs->error('[FDT-EXCEL] Cantidad de lineas filtradas por horario incorrecto: ', $countFilter, 'logs_fdt');
            $return['status'] = true;
            $return['url'] = $fdt_file;
        } else {
            $return['status'] = false;
            $return['msg'] = $this->language->ERROR_FDT_EXPORT;
        }

        header("Content-type: application/json");
        echo json_encode($return);
        exit ;
    }

}
?>