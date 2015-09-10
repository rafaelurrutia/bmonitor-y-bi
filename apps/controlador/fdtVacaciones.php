<?php

class fdtVacaciones extends Control {
	
	/*******
	 * 
	 * Pantallas
	 * 
	 */
	
	public function index()
	{
		$value = array();
		
		//Formulario nuevo 
		$this->plantilla->load("fdt/fdt_vacation_form");
		
		$get_host = $this->bmonitor->getHostForGroup("ENLACES");
		
		$value['option_vacation_host'] = $this->basic->getOption($get_host, 'id_host', 'host');
		$value['option_vacation_motivo'] = $this->basic->getOptionValue('fdt_motivo');
		$value['form_id_template'] = 'form_new_vacation';
		
		$this->plantilla->set($value);
		
		$value["form_new_vacation"] = $this->plantilla->get();
				
		//Tabla
		
		$this->basic->setTableId('table_vacation');
		$this->basic->setTableUrl("/fdtVacaciones/getTableVacation");
		$this->basic->setTableColModel('fdt_vacation');
		$this->basic->setTableToolbox('toolboxVacation');
		
		$value["table_vacation"] = $this->basic->getTableFL('Licencias Asignadas','auto',100);
		
		
		$this->basic->setTableId('table_vacation_active');
		$this->basic->setTableUrl("/fdtVacaciones/getTableVacation");
		$this->basic->setTableColModel('fdt_vacation');
		
		$value["table_vacation_active"] = $this->basic->getTableFL('Listado de colegios con Licencias','auto',200);
		
		//Generar Plantilla
		
		$valida = $this->protect->access_page('FDT_VACACTION_LIST');
		
		$this->plantilla->load_sec("fdt/fdt_vacation", "denegado",$valida);
	
		$this->plantilla->set($value);
		
		echo $this->plantilla->get();		
	}
	
	public function getTableVacation()
	{
		$getParam = (object)$_POST;
		
		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST,'name');
		
		//Parametros Table
		
		$data = array();
		
		$data['page'] = $var->page;
		
		$data['rows'] = array();
		
		//Filtros 
		
		if($getParam->callId == 'table_vacation_active') {
			$WHERE = ' AND V.`fecha_inicio` <= NOW() AND V.`fecha_fin` >= NOW() ';
			$getTotalRows_sql = "SELECT COUNT(*) as Total FROM `bm_vacation` V WHERE V.`fecha_inicio` <= NOW() AND V.`fecha_fin` >= NOW()";
		} else {
			$getTotalRows_sql = "SELECT COUNT(*) as Total FROM `bm_vacation` V";
			$WHERE = '';
		}

		//Total rows

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);
		
		if($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['Total'];
		else {
			$data['total'] = 0;
		}
		
		//Rows
		
		//Features
		
		$query_feature = "SELECT HF.`feature`
							FROM `bm_host_table`  HT
							LEFT JOIN `bm_host_feature` HF USING(`id_feature`)
							WHERE `table`='fdt_vacation' ORDER BY HT.`orden` ASC;";
							
		$features_table = $this->conexion->queryFetch($query_feature,'logs_fdt');
					
		//Host a desplegar
		
		$groupid = $this->bmonitor->getAllGroupsHost("ENLACES",'sql');
		
		$host_sql = "SELECT H.* , V.* , P.plan, P. `id_plan`
						FROM `bm_vacation` V 
							LEFT OUTER JOIN `bm_host` H  USING(`id_host`)
							LEFT JOIN `bm_plan` P ON P.`id_plan`=H.`id_plan`
						WHERE
							H.`groupid` IN $groupid AND H.`borrado`=0 $WHERE";
							
		$hosts_result = $this->conexion->queryFetch($host_sql,'logs_fdt');

		foreach ($hosts_result as $key => $host) {
			$hosts[] = $host['id_host'];
			$datos[$host['id_vacation']] = $host;
		}
		
		if(isset($hosts) && count($hosts) > 0) {
			
			$feature_sql = "SELECT   V.`id_vacation`, H.`id_host`, H.`host`, HF.`feature`,HD.`value`
								FROM `bm_host` H
												LEFT JOIN `bm_host_detalle` HD USING(`id_host`)
												LEFT JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature`
												LEFT JOIN `bm_host_table` HT ON HT.`id_feature`=HD.`id_feature`
												LEFT JOIN `bm_vacation` V ON V.`id_host` = H.`id_host`
								WHERE 
												HT.`table`='fdt_vacation' AND
												HF.`feature_type` = 'other' AND  
												H.`id_host` IN ".$this->conexion->arrayToIN($hosts).$WHERE ;
												
			$feature_result = $this->conexion->queryFetch($feature_sql,'logs_fdt');
												
			foreach ($feature_result as $key => $feature) {
				$datos[$feature['id_vacation']][$feature['feature']] =  $feature['value'];
			}
		
		}

		$data['rows'] = array();
		
		if($hosts_result) {
			foreach ($datos as $dato) {
				
				$dato['region'] = $this->basic->getLocalidad('region', $dato['region']);
				$dato['comuna'] = $this->basic->getLocalidad('comuna', $dato['comuna']);
				
				$dato['motivo'] = utf8_encode($this->basic->getOptionName('fdt_motivo',$dato['motivo']));
				
				$option = '<span id="toolbar" class="ui-widget-header ui-corner-all">';
				$option .= '<span id="toolbarSet"><button id="editVacation" onclick="edit('.$dato['id_vacation'].',false)" name="editVacation">Editar</button></span>';
				$option .= '</span>';
					
				if($getParam->callId == 'table_vacation') {
					$dato['opciones'] = $option;
				}
				
				$datosTable = array();
				foreach ($features_table as $table) {
					if(isset($dato[$table['feature']])) {
						$datosTable[] = $dato[$table['feature']];
					} else {
						$datosTable[] = '';
					}
				}
	
				$data['rows'][] = array(
					'id' => $dato['id_vacation'],
					'cell' => $this->basic->fixEncoding($datosTable)
				);
			}
		}
	
		echo json_encode($data);
	}

	public function newVacation() 
	{
		$valida = $this->protect->access_page('FDT_NEW_VACATION');
		if($valida) {
			$getParam = (object)$_POST;
			$result = array();
			
			$result['status'] = false;
			
			if(!is_numeric($getParam->id)) {
				/* Creando nuevo Grafico */ 
				
				$this->conexion->InicioTransaccion();
				
				//Validando Vaciones activas 
				
				$selec_sql = sprintf("SELECT COUNT(1) as Total FROM `bm_vacation` WHERE `id_host` = %s AND ( (`fecha_inicio` <= '%s'  AND `fecha_fin` >= '%s') OR (`fecha_inicio` <= '%s'  AND `fecha_fin` >= '%s')) ",
										$getParam->vacation_host,
										$getParam->form_new_vacation_vacation_fecha_inicio.' 00:00:00',
										$getParam->form_new_vacation_vacation_fecha_fin.' 23:59:59',
										$getParam->form_new_vacation_vacation_fecha_inicio,
										$getParam->form_new_vacation_vacation_fecha_inicio);
										
				$select_result = $this->conexion->queryFetch($selec_sql);
				
				if($select_result) {
					$conflictos = $select_result[0]['Total'];
				} else {
					$conflictos = 0;
				}
				
				if($conflictos  > 0) {
					$result['status'] = false;
					$result['error'] = str_replace("{CONFLICT}",$conflictos,$this->language->VACATION_CONFLICT);
					echo json_encode($result);
					exit;
				}
				
				$insert_sql = sprintf("INSERT INTO `bm_vacation` (`id_host`, `fecha_inicio`, `fecha_fin`,`motivo`,`id_user`)
											VALUES ('%s', '%s', '%s', '%s', '%s')",
											$getParam->vacation_host,
											$getParam->form_new_vacation_vacation_fecha_inicio.' 00:00:00',
											$getParam->form_new_vacation_vacation_fecha_fin.' 23:59:59',
											$getParam->vacation_motivo,
											$_SESSION['iduser']);
											
				$insert_result = $this->conexion->query($insert_sql);
				
				$idInsert = $this->conexion->lastInsertId();
				
				if($insert_result) {
					
					//Config Sonda :
					$insert_sql = "INSERT INTO `bm_trigger` (`id_host`, `fecha_modificacion`, `type`) VALUES
							($getParam->vacation_host, NOW(), 'vacation');";
					$this->conexion->query($insert_sql);
					
					$result['status'] = true;
				}
			} else {
				/* Editando Grafico */ 
				$this->conexion->InicioTransaccion();
				
				$update_sql = sprintf(/* BSW */ "UPDATE `bm_vacation` SET `fecha_fin` = '%s' WHERE `id_vacation` = '%s'",
								$getParam->form_edit_vacation_vacation_fecha_fin.' 23:59:59',
								$getParam->id);
											
				$update_result = $this->conexion->query($update_sql);
				
				if($update_result) {
					//Config Sonda :
					
					$insert_sql = sprintf(/* BSW */ "INSERT INTO `bm_trigger` (`id_host`, `fecha_modificacion`, `type`) VALUES
														('%s',NOW(),'vacation')",
															$_SESSION['select_id_host_fdt_vacation']);
					$this->conexion->query($insert_sql);
					
					$result['status'] = true;
				}
			}
		} else {
			$result['status'] = false;
			$result['error'] = $this->language->access_deny;
			echo json_encode($result);
			exit;
		}
		
		if($result['status']) {
			if(!is_numeric($getParam->id))  {
				$this->protect->setAudit($_SESSION['uid'],'new_vacation',"Usuario asigno vacaciones: [$idInsert] ".$getParam->vacation_host);
			} else {
				$this->protect->setAudit($_SESSION['uid'],'edit_vacation',"Usuario edito una vacacion: [$getParam->id] ".$getParam->id);
			}
			$this->conexion->commit();
		} else {
			$this->conexion->rollBack();
		}
		
		echo json_encode($result);
	}

	public function deleteVacation()
	{
		$valida = $this->protect->access_page('FDT_DELETE_VACATION');
		
		$data['status'] = true ;
		
		if($valida) { 
			$getParam = (object)$_POST;
			
			foreach ($getParam->idSelect as $key => $value) {
				if(is_numeric($value)) {
					
					$select_valida_sql = 'SELECT count(*) as Total FROM `bm_vacation` WHERE `fecha_inicio` <= NOW() AND `fecha_fin` >= NOW() AND `id_vacation` = '.$value;
					
					$select_valida_result = $this->conexion->queryFetch($select_valida_sql);
					
					if($select_valida_result && $select_valida_result[0]['Total'] > 0) {
						$data['status'] = false;
						$data['msg'] = $this->language->VACATION_CONFLICT_DELETE;
					} else {
						
						$delete_sql = "/* BMONITOR */ DELETE FROM `bm_vacation` WHERE `id_vacation` IN ('$value')";
											
						$valida = $this->conexion->query($delete_sql);
						
						if(!$valida) {
							$data['status'] = false;
							$data['msg'] = $this->language->ITERNAL_ERROR;
						}
					
					}
					
				}
			}
					
		} else {
			$data['msg'] = $this->language->NEW_PASS_ERROR_1;
			$data['status'] = false;
		}
		echo json_encode($data);
	}
	
	public function getVacationForm($form_ext = 'screen')
	{
		if(is_numeric($_POST['IDSelect'])) {
			$IDSelect = $_POST['IDSelect'];
		} else {
			return false;
		}
		
		$valida = $this->protect->access_page('FDT_EDIT_VACATION');
		
		$this->plantilla->load_sec('fdt/fdt_vacation_form', 'denegado', $valida);
		

		$get_host = $this->bmonitor->getHostForGroup(2);
		

		$value['form_id_template'] = 'form_edit_vacation';
				

		// Valores
		
		$get_value_sql = "SELECT * FROM `bm_vacation`  WHERE `id_vacation` = $IDSelect";
						
		$get_value_result = $this->conexion->queryFetch($get_value_sql);

		if($get_value_result) {
			$get_value_rows = $get_value_result[0];
			
			//Calculando diferencia;
			
			$fecha_inicio_array = explode('-', $get_value_rows['fecha_inicio']);
			$timestamp_inicio = mktime(0,0,0,$fecha_inicio_array[1],$fecha_inicio_array[2],$fecha_inicio_array[0]); 
			
			$segundos_diferencia = $timestamp_inicio - time();
			
			$dias_diferencia = $segundos_diferencia / (60 * 60 * 24);
			//$dias_diferencia = abs($dias_diferencia); 

			$dias_diferencia = floor($dias_diferencia); 
			
			if($dias_diferencia > 0) {
				$dias_diferencia = $dias_diferencia+1;
			} else {
				$dias_diferencia = 0;
			}
			
			$result['diferencia'] = $dias_diferencia;
			
			$get_host = $this->bmonitor->getHostForGroup(2);
			$value['option_vacation_host'] = $this->basic->getOption($get_host, 'id_host', 'host',$get_value_rows['id_host']);
			$_SESSION['select_id_host_fdt_vacation'] = $get_value_rows['id_host'];
			$value['vacation_host_disabled'] = 'disabled';
			
			$fecha_inicio = substr ($get_value_rows['fecha_inicio'], 0,10);  
			
			$value['vacation_fecha_inicio'] =$fecha_inicio;
			$value['vacation_fecha_inicio_disabled'] = 'disabled';
			
			$fecha_fin = substr ($get_value_rows['fecha_fin'], 0,10); 
			
			$value['vacation_fecha_fin'] = $fecha_fin;
			
			$value['option_vacation_motivo'] = $this->basic->getOptionValue('fdt_motivo',$get_value_rows['motivo']);
			$value['vacation_motivo_disabled'] = 'disabled';
			
		}
		
		$this->plantilla->set($value);
		$result['html'] = $this->plantilla->get();
		$result['status'] = true;
		echo json_encode($result);
	}
}
?>