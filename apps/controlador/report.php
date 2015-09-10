<?php
class report extends Control
{

    private $AttrGraph;
    private $idCompany;
    private $memcache = false;

    public function getReport($idReport = false, $singleGraph = false, $json = true, $company = false)
    {
        $secure = true;   
        if($json === false){
            $_POST['type'] = "table";
            if($company !== false) {
                $this->idCompany = $company;
                $secure = false;
            }
        } else {
            $this->idCompany = $_SESSION['idcompany'];
        }
        
        if($singleGraph === false){
            $singleGraph_id = 'mono';
        } else {
            $singleGraph_id = 'perro';
        }
                
        $identifactor = join('_', $_POST) . '_' . $idReport . '_' . $singleGraph_id . '_' . $this->idCompany;
        
        if (class_exists('Memcached')) {
           $this->memcache = new Memcached;
           $this->memcache->addServer('127.0.0.1', 11211);
        }
                
        $this->plantilla->cacheJSON($identifactor, 86000);
        $continue = $this->getAttrGraph($idReport);

        if ($continue) {
            if ($this->AttrGraph->type_graph == 'column') {
                $result['html'] = utf8_encode($this->graphColumn($idReport, $_POST, $singleGraph,$secure));
            } elseif ($this->AttrGraph->type_graph == 'pie') {
                $result['html'] = utf8_encode($this->graphPie($idReport, $_POST, $singleGraph,$secure));
            }
        }
        $this->basic->jsonEncode($result);
        $this->plantilla->cacheClose();
    }

    private function getAttrGraph($idReport)
    {
        $getAttrGraph_sql = 'SELECT `id_graph`, R.`id_report`, `type_graph`, `width`, `height`, `yaxis_title`, `xaxis`, `tooltips`, `showInLegend`, `showLabels`, `report`, `id_category`
								FROM  `report_graph`  RG
									LEFT JOIN `report` R ON R.`id_report`=RG.`id_report`
								WHERE RG.`id_report` = ' . $idReport . ' LIMIT 1;';
        $getAttrGraph_result = $this->conexion->queryFetch($getAttrGraph_sql);

        if ($getAttrGraph_result) {

            $this->AttrGraph = (object)$getAttrGraph_result[0];

            return TRUE;

        } else {
            return FALSE;
        }
    }

    private function graphColumn($idReport, $filter, $singleGraph, $secure = true)
    {
        if($secure === true) {
            $this->plantilla->load_sec("report/column", 'REPORT_DISPLAY');
        } else {
            $this->plantilla->load("report/column");
        }
       
        //Generando array de exportacion [1]

        $reportSource = array();

        $reportSource['idreport'] = $idReport;

        $reportSource['graphTitle'] = $this->AttrGraph->report;

        //Array de exportacion [1]

        $tps_index_control['graphTitle'] = $this->AttrGraph->report;

        if (strpos($this->AttrGraph->tooltips, 'this.') !== FALSE) {
            $tps_index_control['tooltips'] = $this->AttrGraph->tooltips;
        } else {
            $tps_index_control['tooltips'] = "'" . $this->AttrGraph->tooltips . "'";
        }

        $tps_index_control['yAxis_title'] = $this->AttrGraph->yaxis_title;

        // Label

        $tps_index_control['showInLegend'] = $this->AttrGraph->showInLegend;

        if ($this->AttrGraph->showLabels == 'complete') {
            $tps_index_control['showLabels_enabled'] = 'true';
            $tps_index_control['showLabels_detail'] = "return '<b>'+ this.point.name +'</b>: '+ Highcharts.numberFormat(this.percentage,2) +' %'";
        } elseif ($this->AttrGraph->showLabels == 'simple') {
            $tps_index_control['showLabels_enabled'] = 'true';
            $tps_index_control['showLabels_detail'] = "return Highcharts.numberFormat(this.percentage,2) +' %'";
        } elseif ($this->AttrGraph->showLabels == 'none') {
            $tps_index_control['showLabels_enabled'] = 'false';
            $tps_index_control['showLabels_detail'] = "return '<b>'+ this.point.name +'</b>: '+ Highcharts.numberFormat(this.percentage,2) +' %'";
        } else {
            $tps_index_control['showLabels_enabled'] = 'true';
            $tps_index_control['showLabels_detail'] = "return '<b>'+ this.point.name +'</b>: '+ Highcharts.numberFormat(this.percentage,2) +' %'";
        }

        if ($this->AttrGraph->xaxis == 'month') {
            $serieValue = array('01' => 0, '02' => 0, '03' => 0, '04' => 0, '05' => 0, '06' => 0, '07' => 0, '08' => 0, '09' => 0, '10' => 0, '11' => 0, '12' => 0);
            $tps_index_control['category'] = "'Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'";
            $category_x = TRUE;
        } else {
            $category_x = FALSE;
        }

        ///Series

        $getFilter_sql = 'SELECT * FROM `report_input`  WHERE `id_graph` = ' . $this->AttrGraph->id_graph;

        $getFilter_result = $this->conexion->queryFetch($getFilter_sql);

        $filerHTML = '';

        if ($getFilter_result) {
            foreach ($getFilter_result as $key => $value) {
                if (isset($filter[$value['attr_name']])) {
                    $attr = $filter[$value['attr_name']];
                } else {
                    if ($value['attr_name'] == 'COMPANY') {
                        
                        $attr = $this->idCompany;
                    } else {
                        $attr = $value['default_value'];
                    }
                }

                if ($value['format'] != '') {
                    $filterVALUE[$value['attr_name']] = str_replace('{VALUE}', $attr, $value['format']);
                } else {
                    $filterVALUE[$value['attr_name']] = $attr;
                }

                if ($singleGraph === FALSE) {

                    if ($value['type'] == 'select') {

                        $filerHTML .= '<label for="' . $value['attr_name'] . '" accesskey="m">' . $value['attr_label'] . ': </label><select name="' . $value['attr_name'] . '" id="' . $value['attr_name'] . '">';

                        if ($value['module'] == 'modYear') {

                            $thisYear = date('Y');

                            $startYear = ($thisYear - 10);

                            foreach (range($thisYear, $startYear) as $year) {
                                $selected = '';
                                if ($year == $attr) {
                                    $selected = " selected";
                                }

                                $filerHTML .= '<option' . $selected . '>' . $year . '</option>';
                            }
                        }

                        if ($value['module'] == 'modMonth') {

                            $MONTH_LIST = $this->language->MONTH_LIST;
                            
                            $MONTH_LIST = explode(',', $MONTH_LIST);

                            foreach ($MONTH_LIST as $key => $month) {
                                $selected = '';
                                $month_now = date("n");
                                
                                if ($key+1 == $attr) {
                                    $selected = " selected";
                                } elseif (($attr == '' && $key+1 == $month_now) || ($attr == 'present' && $key+1 == $month_now)) {
                                    $selected = " selected";
                                }
                                
                                $num = $key+1;
                                
                                if($num < 10) {
                                    $num = "0".$num;
                                }
                                
                                $month = trim(str_replace('\"', '', $month));
                     
                                $filerHTML .= '<option' . $selected . ' value="'.$num.'">' . $month . '</option>';
                            }
                        }

                        $filerHTML .= '</select>';
                    }
                }
            }

            $reportSource['filter'] = join('_', $filterVALUE);
        }

        $getSource_sql = 'SELECT * FROM `report_source`  WHERE `id_graph` = ' . $this->AttrGraph->id_graph;

        $getSource_result = $this->conexion->queryFetch($getSource_sql);

        $series = array();

        if ($getSource_result) {

            foreach ($getSource_result as $key => $value) {

                $querySource = $value['source'];

                foreach ($filterVALUE as $keyFilter => $valueFilter) {
                    $querySource = str_replace('{' . $keyFilter . '}', $valueFilter, $querySource);
                }

                $reportSource['sources'][] = $querySource;

                $querySource_result = $this->conexion->queryFetch($querySource);

                if ($querySource_result) {
                    $category_x_value = array();
                    foreach ($querySource_result as $keySource => $valueSource) {
                        //if($this->AttrGraph->xaxis == 'month'){
                        $serieValue[$valueSource[$value['s_xaxis']]] = $valueSource[$value['s_yaxis']];

                        if ((strpos($valueSource[$value['s_xaxis']], '\u') === false) || (strpos($valueSource[$value['s_xaxis']], '?') === false)) {
                            $category_x_value[] = substr($valueSource[$value['s_xaxis']], 0, 30);
                        } else {
                            $category_x_value[] = "CategoryError";
                        }

                        //$category_x_value[] = substr($valueSource[$value['s_xaxis']], 0, 30);
                        //}
                    }
                    $series[] = "{name: '" . $value['name'] . "', data: [" . join(',', $serieValue) . "]}";
                } else {
                    $series[] = "{name: '" . $value['name'] . "', data: []}";
                }
            }

            if ($category_x == FALSE) {
                if(isset($category_x_value)) {
                    $tps_index_control['category'] = "'" . join("','", $category_x_value) . "'";
                } else {
                    $tps_index_control['category'] = "'none'";
                }
                
            }

        }

        $tps_index_control['series'] = join(',', $series);

        ///Fin Series

        if ($singleGraph === FALSE) {
            $tps_index_control['filter'] = $filerHTML;
        }
        
        if($this->memcache != false){
            $this->memcache->set('SOURCE_DATA',$reportSource,0);
        }

        $_SESSION['SOURCE_DATA'] = $reportSource;

        $this->plantilla->set($tps_index_control);

        return $this->plantilla->get();
    }

    private function graphPie($idReport, $filter, $singleGraph,$secure = true)
    {
        if($secure === true) {
            $this->plantilla->load_sec("report/pie", 'REPORT_DISPLAY');
        } else {
            $this->plantilla->load("report/pie");
        }

        //Generando array de exportacion [1]

        $reportSource = array();

        $reportSource['idreport'] = $idReport;

        $reportSource['graphTitle'] = $this->AttrGraph->report;

        //Array de exportacion [1]

        $tps_index_control['graphTitle'] = $this->AttrGraph->report;

        $tps_index_control['tooltips'] = $this->AttrGraph->tooltips;

        $tps_index_control['yAxis_title'] = $this->AttrGraph->yaxis_title;

        // Label

        $tps_index_control['showInLegend'] = $this->AttrGraph->showInLegend;

        if ($this->AttrGraph->showLabels == 'complete') {
            $tps_index_control['showLabels_enabled'] = 'true';
            $tps_index_control['showLabels_detail'] = "return '<b>'+ this.point.name +'</b>: '+ Highcharts.numberFormat(this.percentage,2) +' %'";
        } elseif ($this->AttrGraph->showLabels == 'simple') {
            $tps_index_control['showLabels_enabled'] = 'true';
            $tps_index_control['showLabels_detail'] = "return Highcharts.numberFormat(this.percentage,2) +' %'";
        } elseif ($this->AttrGraph->showLabels == 'none') {
            $tps_index_control['showLabels_enabled'] = 'false';
            $tps_index_control['showLabels_detail'] = "return '<b>'+ this.point.name +'</b>: '+ Highcharts.numberFormat(this.percentage,2) +' %'";
        } else {
            $tps_index_control['showLabels_enabled'] = 'true';
            $tps_index_control['showLabels_detail'] = "return '<b>'+ this.point.name +'</b>: '+ Highcharts.numberFormat(this.percentage,2) +' %'";
        }

        ///Series

        $getFilter_sql = 'SELECT * FROM `report_input`  WHERE `id_graph` = ' . $this->AttrGraph->id_graph;

        $getFilter_result = $this->conexion->queryFetch($getFilter_sql);

        $filerHTML = '';

        if ($getFilter_result) {
            foreach ($getFilter_result as $key => $value) {
                if (isset($filter[$value['attr_name']])) {
                    $attr = $filter[$value['attr_name']];
                } else {
                    if ($value['attr_name'] == 'COMPANY') {
                        $attr = $this->idCompany;
                    } elseif ($value['default_value'] == 'year') {
                        if ($value['attr_name'] == 'FECHAHORA_START') {
                            $attr = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d"), date("Y") - 1));
                        } elseif ($value['attr_name'] == 'FECHAHORA_END') {
                            $attr = date("Y-m-d");
                        } else {
                            $attr = date("Y-m-d");
                        }
                    } else {
                        $attr = $value['default_value'];
                    }
                }

                if ($value['format'] != '') {
                    $filterVALUE[$value['attr_name']] = str_replace('{VALUE}', $attr, $value['format']);
                } else {
                    $filterVALUE[$value['attr_name']] = $attr;
                }

                if ($singleGraph === FALSE) {

                    if ($value['type'] != 'option') {

                        if ($value['module'] == 'modYear') {

                            $filerHTML .= '<label for="' . $value['attr_name'] . '" accesskey="m">' . $value['attr_label'] . ': </label><select name="' . $value['attr_name'] . '" id="' . $value['attr_name'] . '">';

                            $thisYear = date('Y');

                            $startYear = ($thisYear - 10);

                            foreach (range($thisYear, $startYear) as $year) {
                                $selected = '';
                                if ($year == $attr) {
                                    $selected = " selected";
                                }

                                $filerHTML .= '<option' . $selected . '>' . $year . '</option>';
                            }

                            $filerHTML .= '</select>';

                        }

                        if ($value['module'] == 'datepicker') {

                            $filerHTML .= '<label for="' . $value['attr_name'] . '" accesskey="d">' . $value['attr_label'] . ': </label><input name="' . $value['attr_name'] . '" value="' . $attr . '" id="' . $value['attr_name'] . '"/>';

                        }

                    }
                }
            }

            $reportSource['filter'] = join('_', $filterVALUE);

        }

        $getSource_sql = 'SELECT * FROM `report_source`  WHERE `id_graph` = ' . $this->AttrGraph->id_graph;

        $getSource_result = $this->conexion->queryFetch($getSource_sql);

        $series = array();

        if ($getSource_result) {

            foreach ($getSource_result as $key => $value) {

                $querySource = $value['source'];

                foreach ($filterVALUE as $keyFilter => $valueFilter) {
                    $querySource = str_replace('{' . $keyFilter . '}', $valueFilter, $querySource);
                }

                $reportSource['sources'][] = $querySource;

                $querySource_result = $this->conexion->queryFetch($querySource);

                $total = 0;

                if ($querySource_result) {
                    foreach ($querySource_result as $keySource => $valueSource) {
                        $namePie = $valueSource[$value['s_xaxis']];
                        if ((strpos($namePie, '\u') === false) || (strpos($namePie, '?') === false)) {
                            $namePie = str_replace("\\'", "", substr($namePie, 0, 30));
                        } else {
                            $namePie = "NameError_" . $keySource;
                        }

                        $serieValue[] = "['" . $namePie . "'," . $valueSource[$value['s_yaxis']] . "]";
                        $total = $total + $valueSource[$value['s_yaxis']];
                    }
                } else {
                    $serieValue = array();
                }

                $series[] = "{ type: 'pie', name: '" . $value['name'] . "', data: [" . join(',', $serieValue) . "]}";
            }
        }

        $tps_index_control['graphTitle'] = $tps_index_control['graphTitle'] . ' Total : ' . $total;

        $tps_index_control['series'] = join(',', $series);

        ///Fin Series

        if ($singleGraph === FALSE) {
            $tps_index_control['filter'] = $filerHTML;
        }

        if($this->memcache != false){
            $this->memcache->set('SOURCE_DATA',$reportSource,0);
        }
        
        $_SESSION['SOURCE_DATA'] = $reportSource;

        $this->plantilla->set($tps_index_control);

        return $this->plantilla->get();

    }

    public function export($idReport = FALSE)
    {
        $result['status'] = TRUE;
        
        if ((isset($_SESSION['SOURCE_DATA']) && ($_SESSION['SOURCE_DATA']['idreport'] == $idReport)) || (isset($_SESSION['SOURCE_DATA']) && ($idReport === FALSE))) {
           $report = (object)$_SESSION['SOURCE_DATA']; 
        } else {
           if($this->memcache != false) {
               $report = $this->memcache->get('SOURCE_DATA');
               if(!is_array($report)) {
                    $result['status'] = FALSE;
                    $result['msg'] = "Session data not found";
                    echo json_encode($result);
                    exit; 
               }
           } else {
                $result['status'] = FALSE;
                $result['msg'] = "Session data not found";
                echo json_encode($result);
                exit;               
           }
        }

        include APPS . "plugins/PHPExcel.php";

        $objPHPExcel = new PHPExcel();

        $objPHPExcel->getProperties()->setCreator("Baking software")->setLastModifiedBy("bPC-Fix")->setTitle(utf8_encode($report->graphTitle))->setSubject("Export")->setDescription("Documento generado por report manager de bPC-Fix")->setKeywords("report")->setCategory("bPCFix");

        $page = 0;

        foreach ($report->sources as $keySource => $source) {
            if ($page > 0) {
                $objPHPExcel->createSheet();
            }

            $objPHPExcel->setActiveSheetIndex($page);

            $objPHPExcel->getActiveSheet()->setTitle(utf8_encode($report->graphTitle));

            $getSourceData = $this->conexion->queryFetch($source);

            if ($getSourceData) {

                $line = 2;
                foreach ($getSourceData as $keyData => $SourceData) {

                    $letter = 65;
                    foreach ($SourceData as $key => $value) {
                        $letterCount = chr($letter);
                        if ($line == 2) {
                            $objPHPExcel->setActiveSheetIndex($page)->setCellValue($letterCount . 1, utf8_encode($key));
                        }
                        $objPHPExcel->setActiveSheetIndex($page)->setCellValue($letterCount . $line, utf8_encode($value));
                        $letter++;
                    }
                    $line++;
                }

            } else {
                $result['msg'] = "Source data not found";
                $result['status'] = FALSE;
            }

            $page++;
        }
       

        if ($result['status'] === TRUE) {
            $objPHPExcel->setActiveSheetIndex(0);
            $excel_file = 'repor_pcfix_' . $report->idreport . "_" . $report->filter . "_" . date("Ymd") . '.xlsx';
            $path = site_path . '/upload/';

            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            $objWriter->save($path . $excel_file);

            $result['excel_file'] = $excel_file;
        }

        echo json_encode($result);
    }

    public function download($file)
    {
        if (isset($file)) {
            $file_name = $file;
            $file_path = 'upload/' . $file_name;
            if (file_exists($file_path) && is_readable($file_path) && (preg_match('/\.csv$/', $file_name) || preg_match('/\.xlsx$/', $file_name) || preg_match('/\.xml$/', $file_name))) {

                header("Pragma: public");
                header("Content-Disposition: attachment; filename=" . $file_name . "\n\n");
                header("Content-Type: text/html");
                //header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                //header("Content-type: ".mime_content_type($file_path));
                header("Content-Length: " . filesize($file_path));
                header('Cache-Control: max-age=0');
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

    public function graphCache()
    {
        $getGraph_sql = 'select `id_report` from `report`';

        $getGraph_result = $this->conexion->queryFetch($getGraph_sql);
        
        $url = URL_BASE_FULL."report/getReport/";
        
        $getCompany_sql = 'select `id_empresa` from `Empresas`';
        $getCompany_result = $this->conexion->queryFetch($getCompany_sql);

        foreach ($getCompany_result as $companyKey => $company) {
            //sleep(5);
            foreach ($getGraph_result as $key => $value) {
                $urlPost = $url.$value['id_report']."/false/false/".$company['id_empresa'];
                $this->curl->cargar($urlPost,$value['id_report']);
            }
            
            $this->curl->ejecutar();
        
        }
    }

}
?>