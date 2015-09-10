<?php
	class inventario extends Control {

		public function index( ) {
			$valida = $this->protect->access_page('INVENTARIO');
			$this->plantilla->loadSec("inventario/inventario", $valida, 360000);
			$section = new Section( );
			$tps_index_control['menu'] = $section->menu('INVENTARIO');
			$tps_index_control['header'] = $section->header();
			$tps_index_control['footer'] = $section->footer();
			$this->plantilla->set($tps_index_control);
			$this->plantilla->finalize();
		}

        public function getGrupoEquipos( ) {
            $this->plantilla->load("inventario/inventario_equipo");
            $grupos = $this->bmonitor->getAllGroups();
            $tps_index_control['option_group'] = $grupos->formatOption().'<option value="0">' .$this->language->ALL .'</option>';
            $this->plantilla->set($tps_index_control);
            $this->plantilla->finalize();
        }
        
		public function getEquipoTable( ) {
			$getParam = (object)$_POST;
            
			//Valores iniciales , libreria flexigrid
			$var = $this->basic->setParamTable($_POST, 'id_host', 'asc');

            $data = array();
    
            $data['page'] = $var->page;

			if(isset($getParam->fmGrupo_inventario)) {
				if($getParam->fmGrupo_inventario > 0) {
					$WHERE_GROUP = ' AND H.`groupid` =' .$getParam->fmGrupo_inventario;
				} else {
					$WHERE_GROUP = ' AND H.`groupid` IN ' .$this->bmonitor->getAllGroups()->formatINI();
				}
			} else {
				return false;
			}

			//Host totales

			$host_sql_total = "SELECT count(1) as total  FROM `bm_host` H WHERE H.`borrado`=0  $var->searchSql $WHERE_GROUP ; ";
			//$this->logs->error("consulta1 Host /apps/controlador/inventario.php getEquipoTable: $host_sql_total");    
			$host_totales = $this->conexion->queryFetch($host_sql_total);

			$data['total'] = $host_totales[0]['total'];

			if( ! $host_totales || $data['total'] == 0) {
				$data['total'] = 0;
				$data['rows'] = array();
				$this->basic->jsonEncode($data);
				exit ;
			}		

			$sqlHostDetalle = "SELECT 
		         H.`id_host`,
		         H.`host`, 
		         H.`codigosonda`,
		         HG.`name` as groupname , 
		         H.`groupid` , 
		         H.`mac`, 
		         H.`ip_wan`, 
		         H.`ip_lan`,
		         H.`mac_lan`,
		         H.`dns`,
		         P.`plan`,
		         H.`availability`,
		         H.`status`,
		         GROUP_CONCAT(F.`feature` ORDER BY HD.`id_feature`) AS featureN,
		         GROUP_CONCAT(HD.`value` ORDER BY HD.`id_feature`) AS featureV
		    FROM 
		        `bm_host` H
		            LEFT JOIN `bm_plan` P ON H.`id_plan`=P.`id_plan`
		            LEFT JOIN `bm_host_groups` HG ON HG.`groupid`=H.`groupid`
		            LEFT JOIN `bm_host_detalle` HD ON H.`id_host`=HD.`id_host`
		            LEFT JOIN `bm_host_feature` F ON F.`id_feature`=HD.`id_feature`
		    WHERE 
		        H.`borrado`=0  AND 
		        HD.`id_feature` IN (1,2,3,4,5,22) $var->searchSql $WHERE_GROUP 
		    GROUP BY H.`id_host` $var->sortSql $var->limitSql";

			$hostDetalle = $this->conexion->queryFetch($sqlHostDetalle);   
			//$this->logs->error("consulta1 Host Detalle /apps/controlador/inventario.php getEquipoTable: $host_sql_total");
			if($hostDetalle) {

			    $data['rows'] = array();

				foreach ($hostDetalle as $key => $value) {

					//IP
					$ip_wan = "<a target='_blank' class='ip_format' href='http://" .$value['ip_wan'] ."'>" .$value['ip_wan'] ."</a>";
					$ip_lan = "<a target='_blank' class='ip_format' href='http://" .$value['ip_lan'] ."'>" .$value['ip_lan'] ."</a>";

					if($value['availability'] == '0') {
						$availability = $this->language->NOT_AVAILABLE;
					} elseif($value['availability'] == '1') {
						$availability = $this->language->AVAILABLE;
					} else {
						$availability = $this->language->NOT_AVAILABLE;
					}
					 
					//Feature
					$featureN = explode(',', $value['featureN']);
					$featureV = explode(',', $value['featureV']);
					
					foreach ($featureN as $keyfeature => $valuefeature) {
						$feature[$valuefeature] =  $featureV[$keyfeature];
					}

					$cell = array(
							'host' => $value['host'],
							'ip_wan' => $ip_wan,
							'availability' => $availability,
							'ip_lan' => $ip_lan,
							'mac_lan' => $value['mac_lan'],
							'plan' => $value['plan'],
							'mac' => $value['mac'],
							'codigosonda' => $value['codigosonda'],
							'status' => $value['status']
					);
					
					$cell = array_merge($feature,$cell);

					//Wifi QoE Deprecate
					/*
					if(isset($cell['wifi'])){
						if($cell['wifi'] == '0') {
							$cell['wifi'] = $this->language->DISABLED;
						} elseif($cell->wifi == '1') {
							$cell['wifi'] = $this->language->ENABLED;
						} else {
							$cell['wifi'] = $this->language->DISABLED;
						}
					} else {
						$cell->wifi = $this->language->DISABLED;
					}
					
					$cell->wifi = '<button id="editWifi" onclick="editWifi(' .$keyD .',false)" name="editWifi">'.$cell->wifi.'</button>';
					*/					 
					$data['rows'][] = array(
						'id' => $value['id_host'],
						'cell' => $cell
					);

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

		public function getFormWifi( ) {
			$getParam = (object)$_POST;

			$this->plantilla->load('inventario/inventario_equipo_wifi_form');

			$select_sql = 'SELECT `feature`, `display`, `value` FROM `bm_host_feature`  LEFT JOIN `bm_host_detalle` USING(  `id_feature`) WHERE  `id_host` =' .$getParam->IDSelect ." AND `feature` IN ('wifi','wl_ssid','cparental','wl_key');";

			$select_result = $this->conexion->queryFetch($select_sql);

			foreach ($select_result as $key => $graph) {
				$value[$graph['feature']] = $graph['value'];
			}

			$value['PARENTAL_CONTROL'] = $this->language->PARENTAL_CONTROL;
			$value['PASS'] = $this->language->USER_PASS;
			$value['FORM_ALL_PARAM_REQUIRED'] = $this->language->FORM_ALL_PARAM_REQUIRED;
			$value['option_inventario_cparental'] = $this->basic->getOptionValue('status', $value['cparental']);
			$value['option_inventario_wifi'] = $this->basic->getOptionValue('wifi', $value['wifi']);
			$value['option_inventario_ssid'] = $this->basic->getOptionValue('ssid', $value['wl_ssid']);
			$value['inventario_pass_wifi'] = $value['wl_key'];

			$this->plantilla->set($value);

			$this->plantilla->finalize();
		}

		public function setFormWifi( ) {
			$getParam = (object)$_POST;

			$valida = $this->protect->access_page('INVENTARIO_EDIT_WIFI');

			if($valida) {

				$insert = "INSERT INTO bm_host_detalle (`id_host`,`id_feature`,`value`)  VALUES (:id_host, :id_feature, :value)
									ON DUPLICATE KEY UPDATE `value`= :value";

				$updateFeature = $this->conexion->prepare($insert);

				$updateFeature->bindValue(':id_host', $getParam->id, PDO::PARAM_INT);
				$updateFeature->bindValue(':id_feature', 3, PDO::PARAM_INT);
				$updateFeature->bindValue(':value', $getParam->inventario_wifi, PDO::PARAM_STR);

				$valid = $updateFeature->execute();

				$updateFeature = $this->conexion->prepare($insert);

				$updateFeature->bindValue(':id_host', $getParam->id, PDO::PARAM_INT);
				$updateFeature->bindValue(':id_feature', 5, PDO::PARAM_INT);
				$updateFeature->bindValue(':value', $getParam->inventario_cparental, PDO::PARAM_STR);

				$valid = $valid && $updateFeature->execute();

				$updateFeature = $this->conexion->prepare($insert);

				$updateFeature->bindValue(':id_host', $getParam->id, PDO::PARAM_INT);
				$updateFeature->bindValue(':id_feature', 4, PDO::PARAM_INT);
				$updateFeature->bindValue(':value', $getParam->inventario_ssid, PDO::PARAM_STR);

				$valid = $valid && $updateFeature->execute();

				$updateFeature = $this->conexion->prepare($insert);

				$updateFeature->bindValue(':id_host', $getParam->id, PDO::PARAM_INT);
				$updateFeature->bindValue(':id_feature', 11, PDO::PARAM_INT);
				$updateFeature->bindValue(':value', $getParam->inventario_pass_wifi, PDO::PARAM_STR);

				$valid = $valid && $updateFeature->execute();

				/*$wifi = $updateFeature->execute(array($getParam->id,
				 * 3,$getParam->inventario_wifi,$getParam->inventario_wifi));

				 $updateFeature->execute(array($getParam->id,
				 5,$getParam->inventario_cparental,$getParam->inventario_cparental));

				 $updateFeature->execute(array($getParam->id,
				 4,$getParam->inventario_ssid,$getParam->inventario_ssid));

				 $updateFeature->execute(array($getParam->id,
				 11,$getParam->inventario_pass_wifi,$getParam->inventario_pass_wifi));
				 */

				$result['status'] = $valid;
			} else {
				$result['status'] = false;
				$result['msg'] = $this->language->access_deny;
			}

			header("Content-type: application/json");
			echo json_encode($result);
			exit ;
		}

	}
?>