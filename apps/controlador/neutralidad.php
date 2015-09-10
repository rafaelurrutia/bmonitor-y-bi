<?php

class Neutralidad extends Control
{

    public function index()
    {
        $valida = $this->protect->access_page('NEUTRALIDAD');

        $this->plantilla->loadSec("neutralidad/neutralidad", $valida,65333);

        $section = new Section();

        $tps_index_control['menu'] = $section->menu('NEUTRALIDAD');
        $tps_index_control['header'] = $section->header();
        $tps_index_control['footer'] = $section->footer();

        $tps_index_control["NEUTRALITY"] = $this->language->NEUTRALITY;
        $tps_index_control["RESUME"] = $this->language->RESUME;
        $tps_index_control["WEIGHING"] = $this->language->WEIGHING;
        $tps_index_control["EXPORT"] = $this->language->EXPORT;
        $tps_index_control["IMPORT"] = $this->language->IMPORT;

        $this->plantilla->set($tps_index_control);

        $this->plantilla->finalize();
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

    private function getOption($type, $all = false)
    {
        if (!empty($type)) {
            $optionValues = $this->conexion->queryFetch("SELECT * FROM `bm_option` WHERE `option_group`='$type' ORDER BY `orden` ASC");
            if ($all) {
                $option = '<option value="all">' . $this->language->ALL . '</option>';
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

    public function getNeutralidad()
    {

        $valida = $this->protect->access_page('NEUTRALIDAD');

        $this->plantilla->loadSec("neutralidad/neutralidad_index", $valida);

        $tps_index_control['option_groups'] = $this->bmonitor->getAllGroupsHost('QoS', 'option', '-first-');
      

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

        $optionM .= '<option value="Q1">Q1</option>';
        $optionM .= '<option value="Q2">Q2</option>';
        $optionM .= '<option value="Q3">Q3</option>';
        $optionM .= '<option value="Q4">Q4</option>';

        $tps_index_control['option_escalas'] = $this->getOption('escalas');
        $tps_index_control['option_meses'] = $optionM;
        $tps_index_control['option_anos'] = $this->getOptionAnios();

        $tps_index_control['WEIGHING'] = $this->language->WEIGHING;
        $tps_index_control['UNWEIGHTED'] = $this->language->UNWEIGHTED;
        $tps_index_control['WEIGHTED'] = $this->language->WEIGHTED;
        $tps_index_control['SCALE'] = $this->language->SCALE;
        $tps_index_control['GROUP'] = $this->language->GROUP;
        $tps_index_control['MONTH'] = $this->language->MONTH;
        $tps_index_control['YEAR'] = $this->language->YEAR;

        $this->plantilla->set($tps_index_control);

        echo $this->plantilla->get();//$this->plantilla->get();
        $this->plantilla->cacheClose();
    }

    public function getTableNeutralidad()
    {

        $getParam = (object)$_POST;
        
		/*$this->logs->error("Parametros son:");
		foreach ($getParam as $key => $value) {
			$this->logs->error("Param $key: $value .");
		}*/
		
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

        if (isset($getParam->pesos)) {
            if ((int)$getParam->pesos === 1) {
                $peso = false;
            } elseif ((int)$getParam->pesos === 2) {
                $peso = true;
            }
        } else {
            return false;
        }

        $clock = $getParam->ano . "/" . $getParam->meses;

        $SPEED = $this->language->SPEED;
        $SPEED_DOWNLOAD = $this->language->SPEED_DOWNLOAD;
        $SPEED_UPLOAD = $this->language->SPEED_UPLOAD;
		//$this->logs->error("valores iniciales: speed: $SPEED, speed_download: $SPEED_DOWNLOAD, grupo: $getParam->grupo, clock: $clock, factor: $factor, peso: $peso");
		//BGS016 OK
//        $table = $this->getTableNeutralidad_index1($getParam->grupo, $clock);
//        $table .= $this->getTableNeutralidad_index2($SPEED . ' NAC ' . $SPEED_DOWNLOAD, "vel", "NACBandwidthdown", $getParam->grupo, $clock, $factor, $peso);
//        $table .= $this->getTableNeutralidad_index2($SPEED . ' NAC ' . $SPEED_UPLOAD, "vel", "NACBandwidthup", $getParam->grupo, $clock, $factor, $peso);
//        $table .= $this->getTableNeutralidad_index2($SPEED . ' LOC ' . $SPEED_DOWNLOAD, "vel", "LOCBandwidthdown", $getParam->grupo, $clock, $factor, $peso);
//        $table .= $this->getTableNeutralidad_index2($SPEED . ' LOC ' . $SPEED_UPLOAD, "vel", "LOCBandwidthup", $getParam->grupo, $clock, $factor, $peso);
//        $table .= $this->getTableNeutralidad_index2($SPEED . ' INT ' . $SPEED_DOWNLOAD, "vel", "INTBandwidthdown", $getParam->grupo, $clock, $factor, $peso);
//        $table .= $this->getTableNeutralidad_index2($SPEED . ' INT ' . $SPEED_UPLOAD, "vel", "INTBandwidthup", $getParam->grupo, $clock, $factor, $peso);
//        $table .= $this->getTableNeutralidad_index2('Ping NAC', "ping", "NACPINGavg", $getParam->grupo, $clock, 1, $peso);    
//        $table .= $this->getTableNeutralidad_index2('Ping LOC', "ping", "LOCPINGavg", $getParam->grupo, $clock, 1, $peso);
//        $table .= $this->getTableNeutralidad_index2('Ping INT', "ping", "INTPINGavg", $getParam->grupo, $clock, 1, $peso);
//        $table .= $this->getTableNeutralidad_index2('Login', "login", "IPLOGINRENEW", $getParam->grupo, $clock, 1, $peso);
		$table = $this->getTableNeutralidad_index1($getParam->grupo, $clock);
        $table .= $this->getTableNeutralidad_index2($SPEED . ' NAC ' . $SPEED_DOWNLOAD, "vel", "Bandwidth - down - NAC", $getParam->grupo, $clock, $factor, $peso);
        $table .= $this->getTableNeutralidad_index2($SPEED . ' NAC ' . $SPEED_UPLOAD, "vel", "Bandwidth - up - NAC", $getParam->grupo, $clock, $factor, $peso);
        $table .= $this->getTableNeutralidad_index2($SPEED . ' LOC ' . $SPEED_DOWNLOAD, "vel", "Bandwidth - down - LOC", $getParam->grupo, $clock, $factor, $peso);
        $table .= $this->getTableNeutralidad_index2($SPEED . ' LOC ' . $SPEED_UPLOAD, "vel", "Bandwidth - up - LOC", $getParam->grupo, $clock, $factor, $peso);
        $table .= $this->getTableNeutralidad_index2($SPEED . ' INT ' . $SPEED_DOWNLOAD, "vel", "Bandwidth - down - INT", $getParam->grupo, $clock, $factor, $peso);
        $table .= $this->getTableNeutralidad_index2($SPEED . ' INT ' . $SPEED_UPLOAD, "vel", "Bandwidth - up - INT", $getParam->grupo, $clock, $factor, $peso);
        $table .= $this->getTableNeutralidad_index2('Ping NAC', "ping", "Ping - avg - NAC", $getParam->grupo, $clock, 1, $peso);
        $table .= $this->getTableNeutralidad_index2('Ping LOC', "ping", "Ping - avg - LOC", $getParam->grupo, $clock, 1, $peso);
        $table .= $this->getTableNeutralidad_index2('Ping INT', "ping", "Ping - avg - INT", $getParam->grupo, $clock, 1, $peso);
        $table .= $this->getTableNeutralidad_index2('Login', "login", "Login - renew - IP", $getParam->grupo, $clock, 1, $peso); 
				
        $datos['status'] = true;
        $datos['tablas'] = $table;
		$datos['clock'] = $clock;

        $this->basic->jsonEncode($datos);
        exit;
    }

    public function getTableNeutralidad_index1($fmGrupo_neutralidad, $clock)
    {
        //$this->logs->error("entran: fmGrupo_neutralidad: $fmGrupo_neutralidad, y clock: $clock"); 	
        //Tabla 1
        $getTable_sql = "SELECT  P.`id_plan`, P.`plan`,S.`exitosa`,S.`fallida`,S.`cumple`
                    FROM `bm_plan` P LEFT OUTER JOIN  bm_stat1 S USING(`id_plan`)
                    LEFT JOIN `bm_plan_groups` PG ON P.`id_plan`=PG.`id_plan`
                    LEFT JOIN `bm_host` H ON H.`id_plan` = P.`id_plan`
                    WHERE PG.`groupid`=$fmGrupo_neutralidad AND  ( S.`clock`='$clock' OR S.`clock` IS NULL) AND  H.`borrado` IN (1,0) GROUP BY P.`id_plan` HAVING `exitosa` IS NOT NULL ORDER BY nacD DESC";
		//$this->logs->error("SQL getTableNeutralidad_index1: ".$getTable_sql);
        $getTableValues = $this->conexion->queryFetch($getTable_sql);

        $table = '<div id="tableResumenConsFDT" class="ui-grid ui-widget ui-widget-content ui-corner-all">
		<div class="ui-grid-header ui-widget-header ui-corner-top">' . $this->language->RESUME . '</div><table id="tableResumenConsFDT_int" class="ui-grid-content ui-widget-content">';
        $table .= '<thead><tr>
					<th class="ui-state-default">' . $this->language->PLAN . '</th>
					<th class="ui-state-default">' . $this->language->MONTH . '</th>
					<th class="ui-state-default">' . $this->language->AVAILABILITY . '</th>
					<th class="ui-state-default">Q</th>
					<th class="ui-state-default">' . $this->language->SUCCESSFUL . '</th>
					<th class="ui-state-default">' . $this->language->FAILED . '</th>
				</tr>
			</thead><tbody>';

        if ($getTableValues) {

            foreach ($getTableValues as $key => $value) {

                $table .= '<tr>';

                $dispo = number_format($value['cumple'], 2, DEC_POINT, THOUSANDS_SEP);
                $com_q = number_format($value['exitosa'] + $value['fallida'], 0, DEC_POINT, THOUSANDS_SEP);
                $exitoso = number_format($value['exitosa'], 0, DEC_POINT, THOUSANDS_SEP);
                $fallida = number_format($value['fallida'], 0, DEC_POINT, THOUSANDS_SEP);

                $table .= '<td class="ui-widget-content">' . $value['plan'] . '</td>';
                $table .= '<td class="ui-widget-content">' . $clock . '</td>';
                $table .= '<td class="ui-widget-content">' . $dispo . '</td>';
                $table .= '<td class="ui-widget-content">' . $com_q . '</td>';
                $table .= '<td class="ui-widget-content">' . $exitoso . '</td>';
                $table .= '<td class="ui-widget-content">' . $fallida . '</td>';

                $table .= '</tr>';
            }

            $table .= '</tbody></table></div>';
            return $table;
        } else {
            return $table;
        }
    }

    public function getTableNeutralidad_index2($title, $type, $item, $grupo, $clock, $factor, $peso)
    {
		//$this->logs->error("entran: titulo: $title, tipo: $type:, item: $item, grupo: $grupo, clock: $clock, factor: $factor, peso: $peso");
        $table = '<div id="tableResumenConsNEUTRALIDAD" class="ui-grid ui-widget ui-widget-content ui-corner-all">
		<div class="ui-grid-header ui-widget-header ui-corner-top">' . $title . '</div><table id="tableResumenConsFDT_int" class="ui-grid-content ui-widget-content display">';
        $table .= '<thead><tr>
					<th class="ui-state-default">' . $this->language->PLAN . '</th>
					<th class="ui-state-default">' . $this->language->MONTH . '</th>
					<th class="ui-state-default">Q</th>
					<th class="ui-state-default">' . $this->language->SUCCESSFUL . '</th>
					<th class="ui-state-default">' . $this->language->FAILED . '</th>
					<th class="ui-state-default">' . $this->language->FAILURE_RATE . '</th>
					<th class="ui-state-default">' . $this->language->TEST_ERRORS . '</th>
					<th class="ui-state-default">Prom</th>
					<th class="ui-state-default">Mín</th>
					<th class="ui-state-default">Máx</th>
					<th class="ui-state-default">Desv</th>
					<th class="ui-state-default">Per 5</th>
					<th class="ui-state-default">Per 80</th>
					<th class="ui-state-default">Per 95</th>
					<th class="ui-state-default">' . $this->language->RELIABILITY_OF_MEASUREMENT . '</th>
					<th class="ui-state-default">' . $this->language->MEASUREMENT_ERROR . '(máx 5)</th>
				</tr>
			</thead><tbody>';

        $getStats_sql = "SELECT * from `bm_stat2` where `valid`=0 AND `groupid`='$grupo' and `clock`='$clock' and `description` = '$item'";
        //$this->logs->error("SQL getTableNeutralidad_index2_Q1: ".$getStats_sql);
        $getStats_result = $this->conexion->queryFetch($getStats_sql);

        if ($getStats_result) {
            foreach ($getStats_result as $key => $qos_data) {
                $fal[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["fallida"] * 1;
            }
        }
		//$this->logs->error("TIPO es: ".$type);
        switch ($type) {
        case "vel" :
            $getStats_sql = "SELECT * from `bm_stat2` where `valid`=1 AND `groupid`='$grupo' and `clock`='$clock' and `description` = '$item'";
            //$this->logs->error("VEL SQL getTableNeutralidad_index2_Q2: ".$getStats_sql);
            $getStats_result = $this->conexion->queryFetch($getStats_sql);
            foreach ($getStats_result as $key => $qos_data) {
                $avg[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["vavg"] * 1;
                $min[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["vmin"] * 1;
                $max[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["vmax"] * 1;
                $exi[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["exitosa"] * 1;
                $p5[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["p5"] * 1;
                $p80[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["p80"] * 1;
                $p95[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["p95"] * 1;
                $std[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["std_samp"] * 1;
                $err[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["error"] * 1;
                $int[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["intervalo"] * 1;
            }

            if ($peso) {
                $getStats_sql = "SELECT * FROM `bm_stat` WHERE `groupid`='$grupo' and `clock`='$clock' and `description` = '$item'";
				//$this->logs->error("VEL PESO: SQL getTableNeutralidad_index2_Q3: ".$getStats_sql);
                $getStats_result = $this->conexion->queryFetch($getStats_sql);
                foreach ($getStats_result as $key => $qos_data) {
                    $std[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["std1"] * 1;
                    $avg[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["avg"] * 1;
                    $err[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = 1.96 * $qos_data["std1"] / sqrt($qos_data["cnt"]);
                    $int[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = 2 * $qos_data["std1"] / sqrt($qos_data["cnt"]);
                }

            }
            break;
        case "ping" :
            $getStats_sql = "SELECT * from `bm_stat2` where `valid`=1 AND `groupid`='$grupo' and `clock`='$clock' and `description` = '$item'";
            //$this->logs->error("PING: SQL getTableNeutralidad_index2_Q4: ".$getStats_sql);
            $getStats_result = $this->conexion->queryFetch($getStats_sql);
            foreach ($getStats_result as $key => $qos_data) {
                $avg[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["vavg"] * 1 / 2;
                $min[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["vmin"] * 1 / 2;
                $max[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["vmax"] * 1 / 2;
                $exi[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["exitosa"] * 1;
                $p5[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["p5"] * 1 / 2;
                $p80[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["p80"] * 1 / 2;
                $p95[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["p95"] * 1 / 2;
                $std[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["std"] * 1 / 2;
                $err[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["error"] * 1 / 2;
                $int[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["intervalo"] * 1 / 2;
            }
			//$this->logs->error("ITEM es: ".$item);
            switch ($item) {
            case "Ping - avg - NAC" :
                $ndesc = "Ping - std - NAC";
                break;
            case "Ping - avg - LOC" :
                $ndesc = "Ping - std - LOC";
                break;
            case "Ping - avg - INT" :
                $ndesc = "Ping - std - INT";
                break;
            }

            $getStats_sql = "SELECT * from `bm_stat2` where `valid`=1 AND `groupid`='$grupo' and `clock`='$clock' and `description` = '$ndesc'";
            //$this->logs->error("PING SQL getTableNeutralidad_index2_Q5: ".$getStats_sql);
            $getStats_result = $this->conexion->queryFetch($getStats_sql);
            foreach ($getStats_result as $key => $qos_data) {
                $std[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["std"] * 1 / 2;
                $err[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["error"] * 1 / 2;
                $int[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["intervalo"] * 1 / 2;
            }

            $getStats_sql = "SELECT * from `bm_stat2` where `valid`=0 AND `groupid`='$grupo' and `clock`='$clock' and `description` = '$item'";
            //$this->logs->error("PING SQL getTableNeutralidad_index2_Q6: ".$getStats_sql);
            $getStats_result = $this->conexion->queryFetch($getStats_sql);
            foreach ($getStats_result as $key => $qos_data) {
                $fal[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["fallida"] * 1;
            }

            if ($peso) {
                $getStats_sql = "SELECT * FROM `bm_stat` WHERE `groupid`='$grupo' and `clock`='$clock' and `description` = '$ndesc'";
				//$this->logs->error("PING PESO SQL getTableNeutralidad_index2_Q7: ".$getStats_sql);
                $getStats_result = $this->conexion->queryFetch($getStats_sql);
                foreach ($getStats_result as $key => $qos_data) {
                    $std[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["std2"] / 2;
                    $err[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = 1.96 * $qos_data["std2"] / sqrt($qos_data["cnt"]) / 2;
                    $int[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = 2 * $qos_data["std2"] / sqrt($qos_data["cnt"]) / 2;
                }
                $getStats_sql = "SELECT * FROM `bm_stat` WHERE `groupid`='$grupo' and `clock`='$clock' and `description` = '$item'";
				//$this->logs->error("PING PESO2 SQL getTableNeutralidad_index2_Q8: ".$getStats_sql);
                $getStats_result = $this->conexion->queryFetch($getStats_sql);
                foreach ($getStats_result as $key => $qos_data) {
                    $avg[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["avg"] / 2;
                }

            }
            break;

        case "login" :
            $getStats_sql = "SELECT * from `bm_stat2` where `valid`=1 AND `groupid`='$grupo' and `clock`='$clock' and `description` = '$item'";
            //$this->logs->error("LOGIN SQL getTableNeutralidad_index2_Q9: ".$getStats_sql);
            $getStats_result = $this->conexion->queryFetch($getStats_sql);
            foreach ($getStats_result as $key => $qos_data) {
                $avg[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["vavg"] * 1;
                $min[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["vmin"] * 1;
                $max[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["vmax"] * 1;
                $exi[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["exitosa"] * 1;
                $p5[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["p5"] * 1;
                $p80[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["p80"] * 1;
                $p95[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["p95"] * 1;
                $std[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["std_samp"] * 1;
                $err[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["error"] * 1;
                $int[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["intervalo"] * 1;
            }

            if ($peso) {
                $getStats_sql = "SELECT * FROM `bm_stat` WHERE `groupid`='$grupo' and `clock`='$clock' and `description` = '$item'";
				//$this->logs->error("LOGIN PESO SQL getTableNeutralidad_index2_Q10: ".$getStats_sql);
                $getStats_result = $this->conexion->queryFetch($getStats_sql);
                foreach ($getStats_result as $key => $qos_data) {
                    $std[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["std1"];
                    $avg[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = $qos_data["avg"];
                    $err[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = 1.96 * $qos_data["std1"] / sqrt($qos_data["cnt"]);
                    $int[$qos_data["clock"] . "_" . $qos_data["id_plan"]] = 2 * $qos_data["std1"] / sqrt($qos_data["cnt"]);
                }

            }
            break;
        }

        /*
         foreach ($getStats_result as $key => $qos_data) {
         if(isset($qos_data["fallida"])){
         $fal[$qos_data["clock"]  . " " . $qos_data["id_plan"]] =
         $qos_data["fallida"]*1;
         } else {
         $fal[$qos_data["clock"]  . " " . $qos_data["id_plan"]] = 0;
         }
         }*/

        if ($factor == 1 && ($type != 'ping') && ($type != 'login')) {
            $dec = 0;
        } else {
            $dec = 2;
        }

        $getTable_sql = "SELECT  P.`id_plan`, P.`plan`,S.`exitosa`,S.`fallida`,S.`cumple`
                    FROM `bm_plan` P LEFT OUTER JOIN  bm_stat1 S USING(`id_plan`)
                    LEFT JOIN `bm_plan_groups` PG ON P.`id_plan`=PG.`id_plan`
                    LEFT JOIN `bm_host` H ON H.`id_plan` = P.`id_plan`
                    WHERE PG.`groupid`=$grupo AND  ( S.`clock`='$clock' OR S.`clock` IS NULL) AND  H.`borrado` IN (1,0) GROUP BY P.`id_plan` HAVING `exitosa` IS NOT NULL ORDER BY nacD DESC;";
		//$this->logs->error("SQL getTableNeutralidad_index2_Q11: ".$getTable_sql); 
        $getTableValues = $this->conexion->queryFetch($getTable_sql);

        $datos['rows'] = array();

        if ($getTableValues) {
            $datos['total'] = count($getTableValues);

            foreach ($getTableValues as $key => $data) {

                $e = 0;
                if (isset($fal[$clock . "_" . $data["id_plan"]]) && $fal[$clock . "_" . $data["id_plan"]] > 0) {
                    $p = $fal[$clock . "_" . $data["id_plan"]] / $exi[$clock . "_" . $data["id_plan"]];
                    $np = $fal[$clock . "_" . $data["id_plan"]];
                    $q = 1 - $np;
                    $d2 = $np * $q;
                    $cnt = $exi[$clock . "_" . $data["id_plan"]] + $fal[$clock . "_" . $data["id_plan"]];
                    if ($fal[$clock . "_" . $data["id_plan"]] == 0) {
                        $e = 0;
                    } else {

                        if ($d2 < 9) {
                            $e = 1.96 * sqrt(($p * (1 - $p) / $cnt)) * 100;
                        } else {
                            $lp = "select * from bsw_poisson where h=" . $fal[$clock . "_" . $data["id_plan"]];
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
                if (isset($exi[$clock . "_" . $data["id_plan"]]) && isset($fal[$clock . "_" . $data["id_plan"]])) {
                    $campo1 = number_format($exi[$clock . "_" . $data["id_plan"]] + $fal[$clock . "_" . $data["id_plan"]], 0, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo1 = '';
                }
                if (isset($exi[$clock . "_" . $data["id_plan"]])) {
                    $campo2 = number_format($exi[$clock . "_" . $data["id_plan"]], 0, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo2 = '';
                }
                if (isset($fal[$clock . "_" . $data["id_plan"]])) {
                    $campo3 = number_format($fal[$clock . "_" . $data["id_plan"]], 0, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo3 = '';
                }
                if (isset($fal[$clock . "_" . $data["id_plan"]]) && isset($exi[$clock . "_" . $data["id_plan"]])) {
                    $campo4 = number_format($fal[$clock . "_" . $data["id_plan"]] / ($exi[$clock . "_" . $data["id_plan"]] + $fal[$clock . "_" . $data["id_plan"]]) * 100, 2, DEC_POINT, THOUSANDS_SEP) . "%";
                } else {
                    $campo4 = '';
                }
                if (isset($fal[$clock . "_" . $data["id_plan"]])) {
                    $campo5 = $color . number_format($e, 4, DEC_POINT, THOUSANDS_SEP) . "%</FONT>";
                } else {
                    $campo5 = '';
                }
                if (isset($avg[$clock . "_" . $data["id_plan"]])) {
                    $campo6 = number_format($avg[$clock . "_" . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo6 = '';
                }
                if (isset($min[$clock . "_" . $data["id_plan"]])) {
                    $campo7 = number_format($min[$clock . "_" . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP) . "</td>";
                } else {
                    $campo7 = '';
                }
                if (isset($max[$clock . "_" . $data["id_plan"]]))
                    $campo8 = number_format($max[$clock . "_" . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                else {
                    $campo8 = '';
                }
                if (isset($std[$clock . "_" . $data["id_plan"]])) {
                    $campo9 = number_format($std[$clock . "_" . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo9 = '';
                }
                if (isset($p5[$clock . "_" . $data["id_plan"]])) {
                    $campo10 = number_format($p5[$clock . "_" . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo10 = '';
                }
                if (isset($p80[$clock . "_" . $data["id_plan"]])) {
                    $campo11 = number_format($p80[$clock . "_" . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo11 = '';
                }
                if (isset($p95[$clock . "_" . $data["id_plan"]])) {
                    $campo12 = number_format($p95[$clock . "_" . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo12 = '';
                }
                if (isset($int[$clock . "_" . $data["id_plan"]])) {
                    $campo13 = number_format($int[$clock . "_" . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo13 = '';
                }
                if (isset($int[$clock . "_" . $data["id_plan"]]) && isset($avg[$clock . "_" . $data["id_plan"]]) && $avg[$clock . "_" . $data["id_plan"]] > 0) {
                    if ($int[$clock . "_" . $data["id_plan"]] / $avg[$clock . "_" . $data["id_plan"]] * 100 > 5) {
                        $color = '<FONT COLOR="red">';
                    } else {
                        $color = '<FONT COLOR="green">';
                    }
                    $campo14 = $color . number_format($int[$clock . "_" . $data["id_plan"]] / $avg[$clock . "_" . $data["id_plan"]] * 100, 4, DEC_POINT, THOUSANDS_SEP) . "%</FONT>";
                } else {
                    $campo14 = '';
                }

                $table .= '<tr class="gradeA odd">';

                $table .= '<td class="ui-widget-content">' . $data['plan'] . '</td>';
                $table .= '<td class="ui-widget-content">' . $clock . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo1 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo2 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo3 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo4 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo5 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo6 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo7 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo8 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo9 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo10 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo11 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo12 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo13 . '</td>';
                $table .= '<td class="ui-widget-content">' . $campo14 . '</td>';

                $table .= '</tr>';

            }
            $table .= '</tbody></table></div>';
            return $table;
        } else {
            return false;
        }

    }

    public function getTableNeutralidad_1()
    {
        $getParam = (object)$_POST;

        //Valores iniciales , libreria flexigrid
        $page = 1;
        $sortname = 'plan';
        $sortorder = 'asc';
        $qtype = '';
        $query = '';
        $rp = 15;

        // Validaciones de los parametros enviados por la libreria flexigrid
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
        }
        if (isset($_POST['sortname']) && ($_POST['sortname'] != 'undefined') && ($_POST['sortname'] != '')) {
            $sortname = $_POST['sortname'];
        }
        if (isset($_POST['sortorder']) && ($_POST['sortorder'] != 'undefined') && ($_POST['sortorder'] != '')) {
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

        // Setup sort and search SQL using posted data
        $sortSql = "ORDER BY $sortname $sortorder";

        //Parametros Table

        $data = array();
        $data['page'] = $page;
        $data['total'] = 0;
        $pageStart = ($page - 1) * $rp;
        $limitSql = " LIMIT $pageStart, $rp";

        //Filtros

        if (!isset($getParam->fmGrupo_neutralidad)) {
            return false;
        }

        if (!isset($getParam->fmMeses_neutralidad)) {
            return false;
        } else {
            if ($getParam->fmMeses_neutralidad < 10) {
                $getParam->fmMeses_neutralidad = '0' . $getParam->fmMeses_neutralidad;
            }
        }

        if (!isset($getParam->fmAno_neutralidad)) {
            return false;
        }

        $clock = $getParam->fmAno_neutralidad . "/" . $getParam->fmMeses_neutralidad;
        $getTable_sql = "SELECT  P.`id_plan`, P.`plan`,S.`exitosa`,S.`fallida`,S.`cumple`
					FROM `bm_plan` P LEFT OUTER JOIN  bm_stat1 S USING(`id_plan`)
					LEFT JOIN `bm_plan_groups` PG ON P.`id_plan`=PG.`id_plan`
					WHERE PG.`groupid`=$getParam->fmGrupo_neutralidad AND  ( S.`clock`='$clock' OR S.`clock` IS NULL) ORDER BY nacD DESC $limitSql;";

        $this->plantilla->cacheJSON($getTable_sql, 10101);

        $getTableValues = $this->conexion->queryFetch($getTable_sql);

        $data['rows'] = array();

        if ($getTableValues) {
            $data['total'] = count($getTableValues);

            foreach ($getTableValues as $key => $value) {

                $dispo = number_format($value['cumple'], 2, DEC_POINT, THOUSANDS_SEP);
                $com_q = number_format($value['exitosa'] + $value['fallida'], 0, DEC_POINT, THOUSANDS_SEP);
                $exitoso = number_format($value['exitosa'], 0, DEC_POINT, THOUSANDS_SEP);
                $fallida = number_format($value['fallida'], 0, DEC_POINT, THOUSANDS_SEP);

                $data['rows'][] = array(
                    'id' => $value['id_plan'],
                    'cell' => array(
                        $value['plan'],
                        $clock,
                        $dispo,
                        $com_q,
                        $exitoso,
                        $fallida
                    )
                );
            }
        }

        echo json_encode($data);
        $this->plantilla->cacheClose();
    }

    public function getTableNeutralidad_monitor()
    {
        $getParam = (object)$_POST;
        $this->plantilla->cacheJSON($_POST, 10101);
        //Valores iniciales , libreria flexigrid
        $page = 1;
        $sortname = 'plan';
        $sortorder = 'asc';
        $qtype = '';
        $query = '';
        $rp = 15;

        // Validaciones de los parametros enviados por la libreria flexigrid
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
        }
        if (isset($_POST['sortname']) && ($_POST['sortname'] != 'undefined') && ($_POST['sortname'] != '')) {
            $sortname = $_POST['sortname'];
        }
        if (isset($_POST['sortorder']) && ($_POST['sortorder'] != 'undefined') && ($_POST['sortorder'] != '')) {
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

        // Setup sort and search SQL using posted data
        $sortSql = "ORDER BY $sortname $sortorder";

        //Parametros Table

        $data = array();
        $datos['page'] = $page;
        $datos['total'] = 0;
        $pageStart = ($page - 1) * $rp;
        $limitSql = " LIMIT $pageStart, $rp";

        //Filtros

        if (!isset($getParam->fmGrupo_neutralidad)) {
            return false;
        }

        if (!isset($getParam->fmMeses_neutralidad)) {
            return false;
        } else {
            if ($getParam->fmMeses_neutralidad < 10) {
                $getParam->fmMeses_neutralidad = '0' . $getParam->fmMeses_neutralidad;
            }
        }

        if (!isset($getParam->fmAno_neutralidad)) {
            return false;
        }

        if (isset($getParam->fmEscala_neutralidad)) {
            //$optionValues = $this->conexion->queryFetch("SELECT
            // `id_option`,`option` FROM `bm_option` WHERE
            // `option_group`='escalas' ORDER BY `orden` ASC");
            $factor = $getParam->fmEscala_neutralidad;
        } else {
            return false;
        }

        if (isset($getParam->fmPeso_neutralidad)) {
            if ((int)$getParam->fmPeso_neutralidad === 1) {
                $peso = false;
            } elseif ((int)$getParam->fmPeso_neutralidad === 2) {
                $peso = true;
            }
        } else {
            return false;
        }

        if (isset($getParam->item)) {
            $item = $getParam->item;
        }

        if (isset($getParam->type)) {
            $type = $getParam->type;
        }

        $clock = $getParam->fmAno_neutralidad . "/" . $getParam->fmMeses_neutralidad;

        switch ($type) {
        case "vel" :
            $getStats_sql = "SELECT * from `bm_stat2` where `valid`=1 AND `groupid`='$getParam->fmGrupo_neutralidad' and `clock`='$clock' and `description` = '$item'";
            $getStats_result = $this->conexion->queryFetch($getStats_sql);
            foreach ($getStats_result as $key => $qos_data) {
                $avg[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["vavg"] * 1;
                $min[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["vmin"] * 1;
                $max[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["vmax"] * 1;
                $exi[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["exitosa"] * 1;
                $p5[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["p5"] * 1;
                $p80[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["p80"] * 1;
                $p95[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["p95"] * 1;
                $std[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["std_samp"] * 1;
                $err[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["error"] * 1;
                $int[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["intervalo"] * 1;
            }

            if ($peso) {
                $getStats_sql = "SELECT * FROM `bm_stat` WHERE `groupid`='$getParam->fmGrupo_neutralidad' and `clock`='$clock' and `description` = '$item'";
                $getStats_result = $this->conexion->queryFetch($getStats_sql);
                foreach ($getStats_result as $key => $qos_data) {
                    $std[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["std1"] * 1;
                    $avg[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["avg"] * 1;
                    $err[$qos_data["clock"] . " " . $qos_data["id_plan"]] = 1.96 * $qos_data["std1"] / sqrt($qos_data["cnt"]);
                    $int[$qos_data["clock"] . " " . $qos_data["id_plan"]] = 2 * $qos_data["std1"] / sqrt($qos_data["cnt"]);
                }

            }
            break;
        case "ping" :
            $getStats_sql = "SELECT * from `bm_stat2` where `valid`=1 AND `groupid`='$getParam->fmGrupo_neutralidad' and `clock`='$clock' and `description` = '$item'";
            $getStats_result = $this->conexion->queryFetch($getStats_sql);
            foreach ($getStats_result as $key => $qos_data) {
                $avg[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["vavg"] * 1 / 2;
                $min[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["vmin"] * 1 / 2;
                $max[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["vmax"] * 1 / 2;
                $exi[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["exitosa"] * 1;
                $p5[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["p5"] * 1 / 2;
                $p80[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["p80"] * 1 / 2;
                $p95[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["p95"] * 1 / 2;
                $std[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["std"] * 1 / 2;
                $err[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["error"] * 1 / 2;
                $int[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["intervalo"] * 1 / 2;
            }

            switch ($item) {
            case "NACPINGavg" :
                $ndesc = "NACPINGstd";
                break;
            case "LOCPINGavg" :
                $ndesc = "LOCPINGstd";
                break;
            case "INTPINGavg" :
                $ndesc = "INTPINGstd";
                break;
            }
			
            $getStats_sql = "SELECT * from `bm_stat2` where `valid`=1 AND `groupid`='$getParam->fmGrupo_neutralidad' and `clock`='$clock' and `description` = '$ndesc'";
            $getStats_result = $this->conexion->queryFetch($getStats_sql);
            foreach ($getStats_result as $key => $qos_data) {
                $std[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["std"] * 1 / 2;
                $err[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["error"] * 1 / 2;
                $int[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["intervalo"] * 1 / 2;
            }

            $getStats_sql = "SELECT * from `bm_stat2` where `valid`=0 AND `groupid`='$getParam->fmGrupo_neutralidad' and `clock`='$clock' and `description` = '$item'";
            $getStats_result = $this->conexion->queryFetch($getStats_sql);
            foreach ($getStats_result as $key => $qos_data) {
                $fal[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["fallida"] * 1;
            }

            if ($peso) {
                $getStats_sql = "SELECT * FROM `bm_stat` WHERE `groupid`='$getParam->fmGrupo_neutralidad' and `clock`='$clock' and `description` = '$ndesc'";
                $getStats_result = $this->conexion->queryFetch($getStats_sql);
                foreach ($getStats_result as $key => $qos_data) {
                    $std[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["std2"] / 2;
                    $err[$qos_data["clock"] . " " . $qos_data["id_plan"]] = 1.96 * $qos_data["std2"] / sqrt($qos_data["cnt"]) / 2;
                    $int[$qos_data["clock"] . " " . $qos_data["id_plan"]] = 2 * $qos_data["std2"] / sqrt($qos_data["cnt"]) / 2;
                }
                $getStats_sql = "SELECT * FROM `bm_stat` WHERE `groupid`='$getParam->fmGrupo_neutralidad' and `clock`='$clock' and `description` = '$item'";
                $getStats_result = $this->conexion->queryFetch($getStats_sql);
                foreach ($getStats_result as $key => $qos_data) {
                    $avg[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["avg"] / 2;
                }

            }
            break;

        case "login" :
            $getStats_sql = "SELECT * from `bm_stat2` where `valid`=1 AND `groupid`='$getParam->fmGrupo_neutralidad' and `clock`='$clock' and `description` = '$item'";
            $getStats_result = $this->conexion->queryFetch($getStats_sql);
            foreach ($getStats_result as $key => $qos_data) {
                $avg[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["vavg"] * 1;
                $min[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["vmin"] * 1;
                $max[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["vmax"] * 1;
                $exi[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["exitosa"] * 1;
                $p5[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["p5"] * 1;
                $p80[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["p80"] * 1;
                $p95[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["p95"] * 1;
                $std[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["std_samp"] * 1;
                $err[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["error"] * 1;
                $int[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["intervalo"] * 1;
            }

            if ($peso) {
                $getStats_sql = "SELECT * FROM `bm_stat` WHERE `groupid`='$getParam->fmGrupo_neutralidad' and `clock`='$clock' and `description` = '$item'";
                $getStats_result = $this->conexion->queryFetch($getStats_sql);
                foreach ($getStats_result as $key => $qos_data) {
                    $std[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["std1"];
                    $avg[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["avg"];
                    $err[$qos_data["clock"] . " " . $qos_data["id_plan"]] = 1.96 * $qos_data["std1"] / sqrt($qos_data["cnt"]);
                    $int[$qos_data["clock"] . " " . $qos_data["id_plan"]] = 2 * $qos_data["std1"] / sqrt($qos_data["cnt"]);
                }

            }
            break;
        }

        $getStats_result = $this->conexion->queryFetch($getStats_sql);

        foreach ($getStats_result as $key => $qos_data) {
            if (isset($qos_data["fallida"])) {
                $fal[$qos_data["clock"] . " " . $qos_data["id_plan"]] = $qos_data["fallida"] * 1;
            } else {
                $fal[$qos_data["clock"] . " " . $qos_data["id_plan"]] = 0;
            }
        }

        if ($factor == 1) {
            $dec = 0;
        } else {
            $dec = 2;
        }

        $getTable_sql = "SELECT  P.`id_plan`, P.`plan`,S.`exitosa`,S.`fallida`,S.`cumple`
					FROM `bm_plan` P LEFT OUTER JOIN  bm_stat1 S USING(`id_plan`)
					LEFT JOIN `bm_plan_groups` PG ON P.`id_plan`=PG.`id_plan`
					WHERE PG.`groupid`=$getParam->fmGrupo_neutralidad AND  ( S.`clock`='$clock' OR S.`clock` IS NULL) $sortSql $limitSql;";

        $getTableValues = $this->conexion->queryFetch($getTable_sql);

        $datos['rows'] = array();

        if ($getTableValues) {
            $datos['total'] = count($getTableValues);

            foreach ($getTableValues as $key => $data) {

                $e = 0;
                if (isset($fal[$clock . " " . $data["id_plan"]]) && $fal[$clock . " " . $data["id_plan"]] > 0) {
                    $p = $fal[$clock . " " . $data["id_plan"]] / $exi[$clock . " " . $data["id_plan"]];
                    $np = $fal[$clock . " " . $data["id_plan"]];
                    $q = 1 - $np;
                    $d2 = $np * $q;
                    $cnt = $exi[$clock . " " . $data["id_plan"]] + $fal[$clock . " " . $data["id_plan"]];
                    if ($fal[$clock . " " . $data["id_plan"]] == 0) {
                        $e = 0;
                    } else {
                        if ($d2 < 9) {
                            $e = 1.96 * sqrt(($p * (1 - $p) / $cnt)) * 100;
                        } else {
                            $lp = "select * from bsw_poisson where h=" . $fal[$clock . " " . $data["id_plan"]];
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
                if (isset($exi[$clock . " " . $data["id_plan"]]) && isset($fal[$clock . " " . $data["id_plan"]])) {
                    $campo1 = number_format($exi[$clock . " " . $data["id_plan"]] + $fal[$clock . " " . $data["id_plan"]], 0, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo1 = '';
                }
                if (isset($exi[$clock . " " . $data["id_plan"]])) {
                    $campo2 = number_format($exi[$clock . " " . $data["id_plan"]], 0, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo2 = '';
                }
                if (isset($fal[$clock . " " . $data["id_plan"]])) {
                    $campo3 = number_format($fal[$clock . " " . $data["id_plan"]], 0, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo3 = '';
                }
                if (isset($fal[$clock . " " . $data["id_plan"]]) && isset($exi[$clock . " " . $data["id_plan"]])) {
                    $campo4 = number_format($fal[$clock . " " . $data["id_plan"]] / ($exi[$clock . " " . $data["id_plan"]] + $fal[$clock . " " . $data["id_plan"]]) * 100, 2, DEC_POINT, THOUSANDS_SEP) . "%";
                } else {
                    $campo4 = '';
                }
                if (isset($fal[$clock . " " . $data["id_plan"]])) {
                    $campo5 = $color . number_format($e, 4, DEC_POINT, THOUSANDS_SEP) . "%</FONT>";
                } else {
                    $campo5 = '';
                }
                if (isset($avg[$clock . " " . $data["id_plan"]]))
                    $campo6 = number_format($avg[$clock . " " . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                else {
                    $campo6 = '';
                }
                if (isset($min[$clock . " " . $data["id_plan"]])) {
                    $campo7 = number_format($min[$clock . " " . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP) . "</td>";
                } else {
                    $campo7 = '';
                }
                if (isset($max[$clock . " " . $data["id_plan"]]))
                    $campo8 = number_format($max[$clock . " " . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                else {
                    $campo8 = '';
                }
                if (isset($std[$clock . " " . $data["id_plan"]])) {
                    $campo9 = number_format($std[$clock . " " . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo9 = '';
                }
                if (isset($p5[$clock . " " . $data["id_plan"]])) {
                    $campo10 = number_format($p5[$clock . " " . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo10 = '';
                }
                if (isset($p80[$clock . " " . $data["id_plan"]])) {
                    $campo11 = number_format($p80[$clock . " " . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo11 = '';
                }
                if (isset($p95[$clock . " " . $data["id_plan"]])) {
                    $campo12 = number_format($p95[$clock . " " . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo12 = '';
                }
                if (isset($int[$clock . " " . $data["id_plan"]])) {
                    $campo13 = number_format($int[$clock . " " . $data["id_plan"]] / $factor, $dec, DEC_POINT, THOUSANDS_SEP);
                } else {
                    $campo13 = '';
                }
                if (isset($int[$clock . " " . $data["id_plan"]]) && isset($avg[$clock . " " . $data["id_plan"]]) && $avg[$clock . " " . $data["id_plan"]] > 0) {
                    if ($int[$clock . " " . $data["id_plan"]] / $avg[$clock . " " . $data["id_plan"]] * 100 > 5) {
                        $color = '<FONT COLOR="red">';
                    } else {
                        $color = '<FONT COLOR="green">';
                    }
                    $campo14 = $color . number_format($int[$clock . " " . $data["id_plan"]] / $avg[$clock . " " . $data["id_plan"]] * 100, 4, DEC_POINT, THOUSANDS_SEP) . "%</FONT>";
                } else {
                    $campo14 = '';
                }

                $datos['rows'][] = array(
                    'id' => $data['id_plan'],
                    'cell' => array(
                        $data["plan"],
                        $clock,
                        $campo1,
                        $campo2,
                        $campo3,
                        $campo4,
                        $campo5,
                        $campo6,
                        $campo7,
                        $campo8,
                        $campo9,
                        $campo10,
                        $campo11,
                        $campo12,
                        $campo13,
                        $campo14
                    )
                );
            }
        }

        echo json_encode($datos);
        $this->plantilla->cacheClose();
    }

    public function getPesos()
    {
        
        $valida = $this->protect->access_page('NEUTRALIDAD_PESOS');

        $this->plantilla->loadSec("neutralidad/neutralidad_pesos", $valida ,false);

        $tps_index_control['option_groups'] = $this->bmonitor->getAllGroupsHost('NEUTRALIDAD', 'option', '-first-');

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

        $optionM .= '<option value="Q1">Q1</option>';
        $optionM .= '<option value="Q2">Q2</option>';
        $optionM .= '<option value="Q3">Q3</option>';
        $optionM .= '<option value="Q4">Q4</option>';

        $tps_index_control['option_meses'] = $optionM;
        $tps_index_control['option_anos'] = $this->getOptionAnios();
        
        
        $tps_index_control['SAVED_DATA_SUCCESSFULLY'] = $this->language->SAVED_DATA_SUCCESSFULLY;
        $tps_index_control['ITERNAL_ERROR'] = $this->language->ITERNAL_ERROR;
        
        
        $tps_index_control['GROUP'] = $this->language->GROUP;
        $tps_index_control['MONTH'] = $this->language->MONTH;
        $tps_index_control['WEIGHTED'] = $this->language->WEIGHTED;
        $tps_index_control['YEAR'] = $this->language->YEAR;
        $tps_index_control['WEIGHING'] = $this->language->WEIGHING;
        $tps_index_control['SUNDAY'] = $this->language->SUNDAY;
        $tps_index_control['MONDAY'] = $this->language->MONDAY;
        $tps_index_control['TUESDAY'] = $this->language->TUESDAY;
        $tps_index_control['WEDNESDAY'] = $this->language->WEDNESDAY;
        $tps_index_control['THURSDAY'] = $this->language->THURSDAY;
        $tps_index_control['FRIDAY'] = $this->language->FRIDAY;
        $tps_index_control['SATURDAY'] = $this->language->SATURDAY;
        $tps_index_control['SAVE'] = $this->language->SAVE;

        $this->plantilla->set($tps_index_control);

        echo $this->plantilla->get();
    }

    public function getPesosTable()
    {
        $getParam = (object)$_POST;

        if (is_numeric($getParam->fmMeses_neutralidad_pesos) && $getParam->fmMeses_neutralidad_pesos < 10) {
            $getParam->fmMeses_neutralidad_pesos = "0" . $getParam->fmMeses_neutralidad_pesos;
        }

        $clock = $getParam->fmAno_neutralidad_pesos . "/" . $getParam->fmMeses_neutralidad_pesos;

        $getPesos_sql = "SELECT `hora`,`dia`,`value` FROM `bsw_pesos` WHERE `clock`='$clock' AND `groupid`=$getParam->fmGrupo_neutralidad_pesos";

        $getPesos_result = $this->conexion->queryFetch($getPesos_sql);
        $data = array();
        if ($getPesos_result) {
            $data['status'] = true;
            $count = 0;
            foreach ($getPesos_result as $key => $value) {
                $row_name = "H" . $value['hora'] . "_D" . $value['dia'];
                $data['rows'][$count]['name'] = $row_name;
                $data['rows'][$count]['value'] = $value['value'];
                $count++;
            }
        } else {
            $data['status'] = false;
        }

        echo json_encode($data);
    }

    public function putPesosTable()
    {
        $getParam = (object)$_POST;

        if (is_numeric($getParam->fmMeses_neutralidad_pesos) && $getParam->fmMeses_neutralidad_pesos < 10) {
            $getParam->fmMeses_neutralidad_pesos = "0" . $getParam->fmMeses_neutralidad_pesos;
        }

        $clock = $getParam->fmAno_neutralidad_pesos . "/" . $getParam->fmMeses_neutralidad_pesos;

        $insert_pesos_sql = "INSERT INTO `bsw_pesos` (`groupid`, `clock`, `hora`, `dia`, `value`) VALUES";
        foreach ($getParam as $key => $value) {
            if (preg_match("/^H/", $key)) {
                $valores = explode('_', $key);

                $valores_1 = explode('H', $valores[0]);
                $hora = $valores_1[1];

                $valores_2 = explode('D', $valores[1]);
                $dia = $valores_2[1];
                $datos = array();
                if (is_numeric($hora) && is_numeric($dia)) {

                    $insert_pesos_value[] = "($getParam->fmGrupo_neutralidad_pesos, '$clock', '$hora', $dia, '$value')";

                } else {
                    $datos['status'] = false;
                }
            }
        }

        $insert_pesos_result = $this->conexion->query($insert_pesos_sql . join(',', $insert_pesos_value) . " ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

        if ($insert_pesos_result) {
            $datos['status'] = true;
        } else {
            $datos['status'] = false;
        }

        echo json_encode($datos);
    }

    public function getSubtel()
    {

        $valida = $this->protect->access_page('NEUTRALIDAD_SUBTEL');

        $this->plantilla->loadSec("neutralidad/neutralidad_subtel", $valida, false);

        $tps_index_control['option_groups'] = $this->bmonitor->getAllGroupsHost('NEUTRALIDAD', 'option', '-first-');

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

        $optionM .= '<option value="Q1">Q1</option>';
        $optionM .= '<option value="Q2">Q2</option>';
        $optionM .= '<option value="Q3">Q3</option>';
        $optionM .= '<option value="Q4">Q4</option>';

        $tps_index_control['option_meses'] = $optionM;
        $tps_index_control['option_anos'] = $this->getOptionAnios();
        $tps_index_control['option_escalas'] = $this->getOption('escalas');

        $tps_index_control['SCALE'] = $this->language->SCALE;
        $tps_index_control['GROUP'] = $this->language->GROUP;
        $tps_index_control['MONTH'] = $this->language->MONTH;
        $tps_index_control['YEAR'] = $this->language->YEAR;

        $this->plantilla->set($tps_index_control);

        echo $this->plantilla->get();
    }

    public function getSubtelTable($json = true)
    {

        $getParam = (object)$_POST;

        if (isset($getParam->fmEscala_neutralidad_subtel)) {
            if (is_numeric($getParam->fmEscala_neutralidad_subtel)) {
                $escala = $getParam->fmEscala_neutralidad_subtel;
            } else {
                $escala = 1024 * 1024;
            }
        } else {
            $escala = 1024 * 1024;
        }

        if (is_numeric($getParam->fmMeses_neutralidad_subtel) && $getParam->fmMeses_neutralidad_subtel < 10) {
            $getParam->fmMeses_neutralidad_subtel = "0" . $getParam->fmMeses_neutralidad_subtel;
        }

        $clock = $getParam->fmAno_neutralidad_subtel . "/" . $getParam->fmMeses_neutralidad_subtel;

        $groupid = $getParam->fmGrupo_neutralidad_subtel;

        $clock_file = str_replace("/", "_", $clock);

        $get_plantes_sql = "SELECT DISTINCT  P.`id_plan`, P.`plan` as 'planName' , CONCAT('planid_', P.`id_plan`) as 'plan',  P.`plandesc`, P.`planname`, P.`tecnologia`
                    FROM `bm_host` H
                        LEFT JOIN `bm_plan` P ON H.`id_plan`=P.`id_plan`
                    WHERE H.`borrado` = 0 AND H.`groupid` =$groupid";

        $get_planes = $this->conexion->queryFetch($get_plantes_sql);

        $datos = array();
        $datos['status'] = true;

        $table = '<table class="tableBSW"  cellspacing="0" cellpadding="0"><tr><th>&nbsp;</th>';

        foreach ($get_planes as $key => $planes) {
            $table .= '<th>' . $planes['planName'] . '</th>';
        }
        $table .= '</tr>';

        $get_subtel_neutralidad_sql = "SELECT * FROM `bm_neutralidad` 
										JOIN `bm_neutralidad_item` USING(`id_item`) 
									WHERE `clock`='$clock'
									ORDER BY id_item;";

        $get_subtel_neutralidad = $this->conexion->queryFetch($get_subtel_neutralidad_sql);

        if ($get_subtel_neutralidad) {

            foreach ($get_subtel_neutralidad as $value) {
                $table .= '<tr>';

                if ($value['type'] == 'title') {
                    $class = 'class="down"';
                } elseif ($value['type'] == 'subtitle') {
                    $class = 'class="over"';
                } elseif ($value['type'] == 'data') {
                    $class = '';
                }

                $table .= '<th ' . $class . '>' . $value['item'] . '</th>';

                $planes = '';

                $retandlat = array(
                    "113",
                    "213",
                    "313",
                    "114",
                    "214",
                    "314"
                );

                $tecn = array(
                    101,
                    201,
                    301
                );
                $speedDownload = array(
                    102,
                    202,
                    302
                );
                $speedUpload = array(
                    107,
                    207,
                    307
                );

                for ($i = 0; $i < count($get_planes); $i++) {
                    $format_value = '';
                    $plan = str_replace("-", "", $get_planes[$i]["plan"]);
                    $plan=str_replace(" ","_",$plan);
                    if ($value['format_number'] == 'true') {
                        if (isset($value[$plan])) {
                            $format_value = $value[$plan] / $escala;
                            $format_value = number_format($format_value, 2, DEC_POINT, THOUSANDS_SEP);
                        } else {
                            $format_value = 0;
                        }

                    } else {
                        if (in_array($value["id_item"], $tecn)) {
                            if (str_replace("FTTH", "", $plan) != $plan) {
                                $format_value = "FTTH";
                            } elseif (str_replace("DSL", "", $plan) != $plan) {
                                $format_value = "ADSL";
                            } else if (str_replace("BAM", "", $plan) != $plan) {
                                $format_value = "BAM";
                            } else if (str_replace("ALCATEL", "", $plan) != $plan) {
                               $format_value = "ALCATEL"; 
                            }       
                        } elseif (in_array($value["id_item"], $speedDownload)) {
                            $t = explode("/", $get_planes[$i]["plandesc"]);
                            $format_value = $t[0];
                        } elseif (in_array($value["id_item"], $speedUpload)) {
                            $t = explode("/", $get_planes[$i]["plandesc"]);
                            if (isset($t[1])) {
                                $format_value = $t[1];
                            } else {
                                $format_value = '';
                            }
                        } elseif (in_array($value["id_item"], $retandlat)) {
                            $format_value = str_replace(".", ",", $value[$plan]);
                        } else {
                            $format_value = $value[$plan];
                        }
                    }

                    $table .= '<td ' . $class . '>' . $format_value . '</td>';
                }

                $table .= '</tr>';
            }

        } else {
            $get_item_sql = "SELECT * FROM `bm_neutralidad_item` ORDER BY id_item;";
            $get_item = $this->conexion->queryFetch($get_item_sql);
            foreach ($get_item as $value) {
                $table .= '<tr>';

                if ($value['type'] == 'title') {
                    $class = 'class="down"';
                } elseif ($value['type'] == 'subtitle') {
                    $class = 'class="over"';
                } elseif ($value['type'] == 'data') {
                    $class = '';
                }

                $table .= '<th ' . $class . '>' . $value['item'] . '</th>';

                for ($i = 0; $i < count($get_planes); $i++) {
                    $table .= '<td></td>';
                }
                $table .= '</tr>';
            }

        }

        $table .= '<tr>';

        $table .= '<th colspan="1"><button type="button" id="subtel-download">'.$this->language->EXPORT.'(,)</button><button type="button" id="subtel-download2">'.$this->language->EXPORT.'(;)</button></th>';
        $table .= '</tr>';

        $table .= '</table>';

        $filename = SITE_PATH . "upload/subtel." . $clock_file . ".csv";

        if (file_exists($filename)) {
            $datos['url_status'] = true;
        } else {
            $datos['url_status'] = false;
        }

        $datos['datos'] = $this->basic->fixEncoding($table);
        $datos['url'] = "/upload/subtel." . $clock_file . ".csv";
        $datos['url2'] = "/upload/subtel." . $clock_file . ".excel.csv";

        $this->basic->jsonEncode($datos);
        exit;
    }

    public function getAllPlan()
    {
        $getParam = (object)$_POST;

        echo $this->bmonitor->getAllPlan('option', 'all', false, 'NEUTRALIDAD', $getParam->id);
    }

    public function getAllHost()
    {
        $getParam = (object)$_POST;

        $getHost = $this->bmonitor->getAllHost(false, false, $getParam->id);

        if ($getHost) {
            $return['status'] = TRUE;
            $return['first'] = $getHost[0]['id_host'];
            $return['option'] = $this->basic->getOption($getHost, 'id_host', 'host', 'all');
        } else {
            $return['status'] = FALSE;
            $return['msg'] = $this->basic->getOption(array(0 => 'Error interno'));
        }

        $this->basic->jsonEncode($return);
    }

    public function getExportar()
    {

        $valida = $this->protect->access_page('NEUTRALIDAD_EXPORT');

        $this->plantilla->loadSec("neutralidad/neutralidad_exportar", $valida, false);

        $tps_index_control['option_groups'] = $this->bmonitor->getAllGroupsHost('NEUTRALIDAD', 'option', '-first-');

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

        $tps_index_control['option_medicion'] = $this->bmonitor->getItemMonitor('permit', true);

        $tps_index_control['option_sondas'] = '<option selected value="0">' . $this->language->SELECT . '</option>';

        $tps_index_control['option_planes'] = '<option selected value="0">' . $this->language->SELECT . '</option>';
        
        $tps_index_control['GROUP'] = $this->language->GROUP;
        $tps_index_control['MONTH'] = $this->language->MONTH;
        $tps_index_control['YEAR'] = $this->language->YEAR;
        $tps_index_control['NEUTRALITY'] = $this->language->NEUTRALITY;
        $tps_index_control['FORM_ALL_PARAM_REQUIRED'] = $this->language->FORM_ALL_PARAM_REQUIRED;
        $tps_index_control['FIELD'] = $this->language->FIELD;
        $tps_index_control['SELECTION'] = $this->language->SELECTION;
        $tps_index_control['MEASUREMENT'] = $this->language->MEASUREMENT;
        $tps_index_control['AGENT'] = $this->language->AGENT;
        $tps_index_control['PLAN'] = $this->language->PLAN; 
        $tps_index_control['STATISTICS'] = $this->language->STATISTICS;
        $tps_index_control['ALL_MEASURED_DATA'] = $this->language->ALL_MEASURED_DATA;
        $tps_index_control['DOWNLOAD'] = $this->language->DOWNLOAD;

        $this->plantilla->set($tps_index_control);

        echo $this->plantilla->get();
    }

    public function getSti()
    {
        $valida = $this->protect->access_page('NEUTRALIDAD_STI', FALSE);

        if ($valida->redirect == TRUE) {
            header("HTTP/1.1 302 Found");
            echo "Session close";
            exit ;
        }

        $this->plantilla->load_sec("neutralidad/neutralidad_sti", "denegado", $valida->access);

        $tps_index_control['option_groups'] = $this->bmonitor->getAllGroupsHost('NEUTRALIDAD', 'option', '-first-');

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

        $tps_index_control['option_report'] = $this->basic->getOptionValue('sti_export');

        $this->plantilla->set($tps_index_control);

        echo $this->plantilla->get();
    }

    public function getExportNeutralidad()
    {
        $getParam = (object)$_POST;

        $id_item = (int)$_POST['neutralidad_export_medicion'];

        if (is_numeric($getParam->fmMeses_export) && $getParam->fmMeses_export < 10) {
            $mes = '0' . $getParam->fmMeses_export;
        } else {
            $mes = $getParam->fmMeses_export;
        }

        $clock = $getParam->fmAno_export . "/" . $mes;

        $host = $_POST["neutralidad_export_sonda"];

        if ($host > 0) {
            $host_filter = ' AND H.`id_host`=' . $host;
        } else {
            $host_filter = '';
        }

        $plan = $_POST["neutralidad_export_plan"];
        if ($plan > 0) {
            $plan_filter = ' AND H.`id_plan`=' . $plan;
        } else {
            $plan_filter = '';
        }

        $estadisticas = (int)$getParam->neutralidad_export_estadisticas;
        if ($estadisticas === 0) {
            $get_export_sql = "SELECT H.`host`,replace(IT.`description`,'.sh','') as Descripcion,PL.`plan`,from_unixtime(HI.`clock`) as fecha,DATE_FORMAT(from_unixtime(HI.`clock`),'%Y/%m/%d %H') medicion,
									replace(replace(replace(format(HI.`value`,2),'.','#'),',','.'),'#',',') valor,HI.`valid`,IT.`unit` as unidad
								FROM `bm_host` H 
									LEFT JOIN `bm_history` HI USING(`id_host`)
									LEFT JOIN `bm_plan` PL ON PL.`id_plan`=H.`id_plan`
									LEFT JOIN `bm_items` IT ON IT.`id_item`=HI.`id_item`
								WHERE H.`groupid` = $getParam->fmGrupo_export AND HI.`id_item`=$id_item AND DATE_FORMAT(from_unixtime(HI.`clock`),'%Y/%m')='$clock' $plan_filter $host_filter";
        } else {
            $get_export_sql = "replace(replace(replace(format(HI.`value`,2),'.','#'),',','.'),'#',',') as valor";
        }

        $get_export_result = $this->conexion->queryFetch($get_export_sql, 'logs_neutralidad');

        if ($get_export_result) {

            $file = 'neutralidad_' . $getParam->fmAno_export . "_" . $mes . "_" . $host . "_" . $id_item . '.csv';
            $path = SITE_PATH . '/upload/';

            $fp = fopen($path . $file, 'w');

            foreach ($get_export_result as $fields) {

                $line = array();

                $line[] = (string)$fields['host'];
                $line[] = (string)$fields['Descripcion'];
                $line[] = (string)$fields['plan'];
                $line[] = (string)$fields['fecha'];
                $line[] = (string)$fields['medicion'];
                $line[] = (int)$fields['valor'];
                $line[] = (int)$fields['valid'];
                $line[] = (string)$fields['unidad'];

                fputcsv($fp, $line);
            }
            fclose($fp);

            $return['status'] = true;
            $return['msg'] = $this->language->OK_NEUTRALIDAD_EXPORT;
            $return['url'] = $file;
        } else {
            $return['msg'] = $this->language->ERROR_NEUTRALIDAD_EXPORT;
            $return['status'] = false;
        }

        echo json_encode($return);
    }

    public function getImportar()
    {
        
        $valida = $this->protect->access_page('NEUTRALIDAD_IMPORT');

        $this->plantilla->loadSec("neutralidad/neutralidad_import", $valida, false);
        
        $tps_index_control['option_groups'] = $this->bmonitor->getAllGroupsHost('NEUTRALIDAD', 'option', '-first-');

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

        $optionM .= '<option value="Q1">Q1</option>';
        $optionM .= '<option value="Q2">Q2</option>';
        $optionM .= '<option value="Q3">Q3</option>';
        $optionM .= '<option value="Q4">Q4</option>';

        $tps_index_control['option_meses'] = $optionM;
        $tps_index_control['option_anos'] = $this->getOptionAnios();
        
        $tps_index_control['NEUTRALITY'] = $this->language->NEUTRALITY;
        $tps_index_control['FIELD'] = $this->language->FIELD;
        $tps_index_control['SELECTION'] = $this->language->SELECTION;
        $tps_index_control['GROUP'] = $this->language->GROUP;
        $tps_index_control['MONTH'] = $this->language->MONTH;
        $tps_index_control['YEAR'] = $this->language->YEAR;
        $tps_index_control['SEPARATOR'] = $this->language->SEPARATOR;
        $tps_index_control['FILE_UPLOAD'] = $this->language->FILE_UPLOAD;
        $tps_index_control['SUNDAY'] = $this->language->SUNDAY;
        $tps_index_control['MONDAY'] = $this->language->MONDAY;
        $tps_index_control['TUESDAY'] = $this->language->TUESDAY;
        $tps_index_control['WEDNESDAY'] = $this->language->WEDNESDAY;
        $tps_index_control['THURSDAY'] = $this->language->THURSDAY;
        $tps_index_control['FRIDAY'] = $this->language->FRIDAY;
        $tps_index_control['SATURDAY'] = $this->language->SATURDAY;
        $tps_index_control['FILE_UPLOAD'] = $this->language->FILE_UPLOAD;
        
        $this->plantilla->set($tps_index_control);

        echo $this->plantilla->get();
    }

    public function setImport()
    {
        $getParam = (object)$_POST;
        $msg = "";
        $resutl['error'] = '';
        if (!empty($_FILES["import_file"]['error'])) {
            if ($_FILES["import_file"]['error'] > 0 AND $_FILES["import_file"]['error'] < 9) {
                $error_name = "ERROR_FILLE_UPLOAD_" . $_FILES["import_file"]['error'];
                $resutl['error'] = $this->language->$error_name;
            } else {
                $resutl['error'] = 'No error code avaiable';
            }
        } elseif (empty($_FILES['import_file']['tmp_name']) || $_FILES['import_file']['tmp_name'] == 'none') {
            $resutl['error'] = 'No file was uploaded..';

        } else {

            $target_name = $_FILES['import_file']['name'];

            $target_path = SITE_PATH . '/upload/' . basename($target_name);

            if (!move_uploaded_file($_FILES['import_file']['tmp_name'], $target_path)) {
                $resutl['error'] = $this->language->ERROR_FILLE_UPLOAD_7;
            } else {
                if ($handle = fopen($target_path, "r")) {

                    if (($getParam->separador == ';') || ($getParam->separador == ',')) {
                        $separador = $getParam->separador;
                    } else {
                        $separador = ',';
                    }

                    //Variables

                    if ($getParam->mes < 10) {
                        $getParam->mes = '0' . $getParam->mes;
                    }

                    $clock = $getParam->ano . "/" . $getParam->mes;

                    $fila = 1;
                    $hora = 0;
                    $continue = true;

                    $values = array();
                    while (($data = fgetcsv($handle, 1000, $separador)) !== FALSE) {
                        $numero = count($data);
                        //Omitit Cabecera
                        if ($fila > 1) {
                            for ($c = 2; $c < $numero; $c++) {

                                $dia = $c - 2;
                                $valor = str_replace(",", ".", $data[$c]);
                                if ($hora < 10) {
                                    $hora_filter = "0" . $hora;
                                } else {
                                    $hora_filter = $hora;
                                }

                                $values[] = "($getParam->grupo, '$clock', '$hora_filter', $dia, $valor)";

                                $this->logs->debug('Validadno valor: ', $valor, 'logs_neutralidad');

                                if (!is_numeric($valor)) {
                                    $this->logs->error('Error , el siguiente dato no es numerico', $valor, 'logs_neutralidad');
                                    $continue = false;
                                }
                            }
                            $hora++;
                        }

                        $fila++;
                    }

                    if ($continue) {

                        $insert = 'INSERT INTO `bsw_pesos` (`groupid`, `clock`, `hora`, `dia`, `value`) VALUES';

                        //Generado query final

                        $insert_final = $insert . join(',', $values) . " ON DUPLICATE KEY UPDATE  `value` = VALUES(value)";

                        $insert_result = $this->conexion->query($insert_final, false, 'logs_neutralidad');

                        if ($insert_result) {

                            //Generado la tabla con los datos ingresados (Para
                            // validar, que mas)

                            $getPesos_sql = "SELECT `hora`,`dia`,`value` FROM `bsw_pesos` WHERE `clock`='$clock' AND `groupid`=$getParam->grupo";

                            $getPesos_result = $this->conexion->queryFetch($getPesos_sql);
                            if ($getPesos_result) {
                                $resutl['status'] = true;
                                $count = 0;
                                foreach ($getPesos_result as $key => $value) {
                                    $row_name = "H" . $value['hora'] . "_D" . $value['dia'];
                                    $resutl['rows'][$count]['name'] = $row_name;
                                    $resutl['rows'][$count]['value'] = $value['value'];
                                    $count++;
                                }
                            } else {
                                $resutl['status'] = false;
                            }

                        } else {
                            $resutl['error'] = $this->language->ERROR_FILLE_UPLOAD_INSERT;
                        }

                    } else {
                        $resutl['error'] = $this->language->ERROR_FILLE_UPLOAD_CVS;
                    }
                } else {

                    $resutl['error'] = $this->language->ERROR_FILLE_UPLOAD_7;
                }
            }
            @unlink($_FILES['import_file']);
        }

        echo json_encode($resutl);
    }

}
?>