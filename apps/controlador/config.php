<?php
class config extends Control {

    public function index()
    {
        $valida = $this->protect->access_page('CONFIGURACION');
         
        $this->plantilla->loadSec("configuracion/configuracion",$valida,360000);
            
        $section = new Section();
   
        $tpsIndexControl['menu'] = $section->menu('CONFIGURACION');
        $tpsIndexControl['header'] =  $section->header();
        $tpsIndexControl['footer'] = $section->footer();

        $tabs[] = array(
            'title' => $this->language->AGENTS,
            'urlBase' => true,
            'href' => 'config/cfgSondas',
            'protec' => 'CONFIG_AGENT'
        );
/* inutil, deprecado
        $tabs[] = array(
            'title' => $this->language->MONITORS,
            'urlBase' => true,
            'href' => 'config/cfgMonitores',
            'protec' => 'CONFIG_MONITOR'
        );
*/
        $tabs[] = array(
            'title' => $this->language->CHARTS,
            'urlBase' => true,
            'href' => 'config/cfgGraph',
            'protec' => 'CONFIG_GRAPH'
        );

        $tabs[] = array(
            'title' => $this->language->SCREENS,
            'urlBase' => true,
            'href' => 'configScreen/cfgPantallas',
            'protec' => 'CONFIG_SCREENS'
        );

        $tabs[] = array(
            'title' => $this->language->PLAN,
            'urlBase' => true,
            'href' => 'config/cfgPlanes',
            'protec' => 'CONFIG_PLAN'
        );

        $tabs[] = array(
            'title' => $this->language->PROFILE,
            'urlBase' => true,
            'href' => 'profiles2/cfgProfiles',
            'protec' => 'CONFIG_PROFILES'
        );

        $tabs[] = array(
            'title' => $this->language->GROUPS,
            'urlBase' => true,
            'href' => 'config/cfgGrupos',
            'protec' => 'CONFIG_GROUPS'
        );
		
		$tabs[] = array(
			'title' => $this->language->LOCATION,
			'urlBase' => true,
			'href' => 'qoe/locations',
			'protec' => array(
				"code" => 'CONFIG_LOCATION',
				"title" => 'Configuration',
				"description" => "Show Location"
			)
		);
			
		/* inutul deprecado
        $tabs[] = array(
            'title' => $this->language->LOCATION,
            'urlBase' => true,
            'href' => 'config/cfgLocation',
            'protec' => 'CONFIG_LOCATION'
        );*/
        /*
        $tabs[] = array(
            'title' => $this->language->MAP,
            'urlBase' => true,
            'href' => 'config/getMaps',
            'protec' => 'CONFIG_TABS_MAPS'
        );*/

        $tpsIndexControl['tabs'] = $this->bmonitor->getTabs($tabs,'tabsConfigurationHome');
       
        $this->plantilla->set($tpsIndexControl);
        
        $this->plantilla->finalize();
    }
    	
	public function getPlanOption($type = "select",$groupid = false)
	{
		$result = array();
		
		if($groupid) {
			$groupid_value = $groupid;
		} else {
			$groupid_value = $_POST['groupid'];
		}
		
		if(is_numeric($groupid_value)) {
			
			$select_plan = "SELECT `bm_plan`.`id_plan`, `bm_plan`.`plan` 
								FROM `bm_plan` JOIN `bm_plan_groups` USING(`id_plan`) 
									WHERE `bm_plan_groups`.`groupid`=".$groupid_value." AND `bm_plan_groups`.`borrado` = 0";
			$planes_array = $this->conexion->queryFetch($select_plan);
			
			if($planes_array) {
				$option = $this->basic->getOption($planes_array, 'id_plan', 'plan',$type);
				$result['datos'] = $option;
				$result['status'] = true;
			} else {
				$result['status'] = false;
			}
		} else {
			$result['status'] = false;
		}
		
		if($groupid) {
			return   (isset($option)) ? $option : '' ;
		} else {
			$this->basic->jsonEncode($result);
            exit;
		}
	}
    
    public function getProfileOption($type = "select",$groupid = false,$json = true, $old = true)
    {
        
        $result = array();
        
        if($groupid !== false && ($groupid !== 'false') ) {
            $groupid_value = $groupid;
        } else {
            $groupid_value = $_POST['groupid'];
        }

        if($json === 'false') {
            $json = false;
        }
        
        if(is_numeric($groupid_value)) {
           /*    
            $selectProfileOld = "SELECT `id_profile_groups` as 'id', CONCAT(`groups`, ' (old)') as 'name' FROM `bm_profile_groups` WHERE `subgroups` IS NULL";
          
            $selectProfileNew = "SELECT P.`id_profile` as 'id',P.`profile` as 'name' 
                            FROM `bm_profiles` P
                                LEFT OUTER JOIN `bm_profiles_permit` PP ON P.`id_profile`=PP.`id_profile`
                            WHERE PP.`groupid` = $groupid_value";
            
 
            $selectProfileResultOld = $this->conexion->queryFetch($selectProfileOld);
            $selectProfileResultNew = $this->conexion->queryFetch($selectProfileNew);
            
            
            if($selectProfileResultOld && $selectProfileResultNew) {
                $selectProfileResult = array_merge($selectProfileResultOld, $selectProfileResultNew);
            } elseif ($selectProfileResultOld) {
                $selectProfileResult = $selectProfileResultOld;
            } elseif ($selectProfileResultNew) {
                $selectProfileResult = $selectProfileResultNew;
            }*/
            
            $selectProfileNew = "SELECT P.`id_profile` as 'id',P.`profile` as 'name' 
                            FROM `bm_profiles` P
                                LEFT OUTER JOIN `bm_profiles_permit` PP ON P.`id_profile`=PP.`id_profile`
                            WHERE PP.`groupid` = $groupid_value";
                            
            $selectProfileResult = $this->conexion->queryFetch($selectProfileNew);
			     
            if($selectProfileResult) {
                $option = $this->basic->getOption($selectProfileResult, 'id', 'name',$type);
                $result['datos'] = $option;
                $result['status'] = true;
            } else {
                $result['status'] = false;
            }
            
        } else {
            $result['status'] = false;
        }
        
        if($json == false) {
            return (isset($option)) ? $option : false ;
        } else {
            $this->basic->jsonEncode($result);
            exit;
        }
    }

	public function getLocation($type = false,$id = false)
	{
        $return['status'] = false;
        $location = $this->basic->getLocation($type,$id,false);
        if($location){
            $return['data'] = $location;
            $return['status'] = true;
        } 
        $this->basic->jsonEncode($return);
        exit;
	}

    public function getOptionGroups($type = false,$id = false)
    {
        $return['status'] = false;
        $groups = $this->bmonitor->getAllGroups()->formatOption();
        if($groups){
            $return['data'] = $groups;
            $return['status'] = true;
        } 
        $this->basic->jsonEncode($return);
        exit;
    }
    
    public function getLocationValue($geonameid)
    {
        $return['status'] = false;
        $location = $this->basic->getLocationValue($geonameid);
        if($location){
            $return = (array)$location;
            $return['status'] = true;
        } 
        $this->basic->jsonEncode($return);
        exit;
    }
    		
	public function cfgSondas()
	{
		$valida = $this->protect->accessPage('CONFIG_AGENT',FALSE);
        
        $this->plantilla->loadSec("configuracion/config_equipos", $valida, 86000);
		
        //START PARAM FORM NEW
		$grupos = $this->bmonitor->getGroupsHost();
		
		$value = array();
		
		$STI_POWER = $this->parametro->get('STI_POWER',FALSE);
		
		if($STI_POWER) {
			$valueFormNew["sti_codigosonda"] = 'style=""';
		} else {
			$valueFormNew["sti_codigosonda"] = 'style="display: none;"';
		}
		
		$valueFormNew["option_group"] = $this->basic->getOption($grupos,'groupid','name','select');
			
		$valueFormNew["input_config"] = $this->getConfigSonda();
		
		$valueFormNew["form_id"] = 'form_new_sonda';
		
		$valueFormNew["option_plan"] =  '<option value="0">'.$this->language->SELECT_A_GROUP.'</option>';
        
        // Grupo tipo de sonda
        $valueFormNew["option_type"] = $this->basic->getOptionValue('type_machine',1);
        $valueFormNew["sonda_ip_lan"] = '192.168.1.1';
        $valueFormNew["sonda_netmask_lan"] = '255.255.255.0';
        
        $valueFormNew["sonda_tags"] = 'all';
        
        $getTagsRESULT = $this->conexion->queryFetch("SELECT `tags` FROM `bm_host`");
 
        if($getTagsRESULT) {
            $sampleTags = array();
            foreach ($getTagsRESULT as $key => $value) {
                $tags = explode(",", $value['tags']);
                foreach ($tags as $keyTag => $tag) {
                    if($tag != ''){
                        $sampleTags[] = $tag;
                    }
                }  
            }
            $sampleTags = array_unique($sampleTags);
            $value["sampleTags"] = 'sampleTags = [\''.join("','",$sampleTags).'\'];';
        } else {
            $value["sampleTags"] = "sampleTags = ['speedtest','youtube','dns'];";
        }
        /*$this->logs->error("Parametros que van al new form sonda....:");  
		foreach ($valueFormNew as $key12 => $value12) {
			$this->logs->error("-- $key12 => $value12");
		}	 
		*/
		//END PARAM FORM NEW
        $value["form_new_sonda"] = $this->plantilla->getOne("configuracion/config_equipos_form",$valueFormNew);
        
        $value["form_trigger_sonda"] = $this->plantilla->getOne("configuracion/config_equipos_trigger");
	
		$value["option_group"] = $this->basic->getOption($grupos,'groupid','name','all');

		$value["displayColum"] = $this->getTableColum('config_sondas');
            
        $button[] = array(
            'name' => 'NEW',
            'bclass' => 'add',
            'protec' => 'CONFIG_AGENT_NEW',
            'onpress' => 'toolboxSonda'
        );

        $button[] = array(
            'name' => 'DELETE',
            'bclass' => 'delete',
            'protec' => 'CONFIG_AGENT_DELETE',
            'onpress' => 'toolboxSonda'
        );
/* Bmonitor2.5 (botones inutiles)  
        $button[] = array(
            'name' => 'CONFIGURATION',
            'bclass' => 'ui-icon ui-icon-wrench',
            'protec' => 'CONFIG_AGENT_CONFIG',
            'onpress' => 'toolboxSonda'
        );

        $button[] = array(
            'name' => 'ALL',
            'bclass' => 'ui-icon ui-icon-circle-check',
            'protec' => 'CONFIG_AGENT_DELETE',
            'onpress' => 'toolboxSonda'
        );
*/
        $button[] = array(
            'name' => 'UPDATE',
            'bclass' => 'ui-icon ui-icon-key',
            'protec' => 'CONFIG_AGENT_UPGRADE',
            'onpress' => 'toolboxSonda'
        );

                
        $value["button"] = $this->generate->getButton($button);
            
		$this->plantilla->set($value);
		
		$this->plantilla->finalize();
	}

	private function getTableColum($table) {
	
		$getColumn = "select hf.`feature`, hf.`display`, ht.`width`, ht.`sortable`, ht.`align` 
						FROM `bm_host_table` ht JOIN `bm_host_feature` hf USING(`id_feature`)
						WHERE ht.`view_table` = 'true' AND ht.`table`='$table' 
						ORDER BY ht.`orden` asc;";
		$Columns = $this->conexion->queryFetch($getColumn);
		$option = array();
		foreach ($Columns as $key => $Colum) {
			if($Colum['width'] == 'none') {
				$width = '';
			} else {
				$width = ", width : '".$Colum['width']."'";
			}
			$option[] = "{display: '".$Colum['display']."', name : '".$Colum['feature']."' ".$width."  , sortable : ".$Colum['sortable'].", align: '".$Colum['align']."'}";
		}
		$optionF = join(',',$option);
		return $optionF;
	}
			
	public function getSondas()
	{
		$getParam = (object)$_POST;	
				
		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST,'id_host');
		
		//Parametros Table
		
		$data = array();
		
		$data['page'] = $var->page;
		
		$data['rows'] = array();
		
		//Filtros
		
		if(isset($getParam->groupid)) {
			if($getParam->groupid == 0) {
				$WHERE = "AND H.`groupid` IN ".$this->bmonitor->getAllGroupsHost(false,'sql');
			} else {
				$WHERE = "AND H.`groupid`=".$getParam->groupid;
			}
		}
		
		//Total Sondas 
		$sondas_sql_total =	"SELECT count(*) as Total FROM `bm_host` H WHERE `borrado`=0 $var->searchSql ".$WHERE;
					
		$host_totales = $this->conexion->queryFetch($sondas_sql_total);
		
		if($host_totales) {
			$data['total'] = $host_totales[0]['Total'];
		} else {
			$data['total'] = 0;
		}
		
		//Features
		
		$query_feature = "SELECT HF.`feature`
							FROM `bm_host_table`  HT
							LEFT JOIN `bm_host_feature` HF USING(`id_feature`)
							WHERE `table`='config_sondas' ORDER BY HT.`orden` ASC;";
							
		$featuresTable = $this->conexion->queryFetch($query_feature);
		
		//Sondas
		
		$query_get_sondas = "SELECT H.*,G.`name` as grupo ,P.`plan` ,GROUP_CONCAT(HF.`feature`)  AS feature,GROUP_CONCAT(HD.`value`) AS valueFeature
				FROM `bm_host` H
									LEFT JOIN `bm_host_detalle` HD USING(`id_host`)
									LEFT JOIN `bm_host_feature` HF ON HF.`id_feature`=HD.`id_feature`
									LEFT JOIN `bm_plan` P ON P.`id_plan`=H.`id_plan`
									LEFT JOIN `bm_host_groups` G ON G.`groupid`=H.`groupid`
				WHERE  
						H.`borrado`=0 AND HF.`feature_type` = 'other' $var->searchSql $WHERE
				GROUP BY H.`id_host`  ".$var->sortSql.$var->limitSql;
				
		$sondas = $this->conexion->queryFetch($query_get_sondas);
		
		$profile = $this->protect->isSecret();
		
        $CONFIG_AGENT_TRIGGER = $this->protect->allowed('CONFIG_AGENT_TRIGGER',true,'Configuration','Agent -> Trigger');  //nombre no mostrados
        $CONFIG_AGENT_CONFIG = $this->protect->allowed('CONFIG_AGENT_CONFIG',true,'Configuration','Agent -> Config');//nombre no mostrados
        $CONFIG_AGENT_EDIT = $this->protect->allowed('CONFIG_AGENT_EDIT',true,'Configuration','Agent -> Edit');
        $CONFIG_AGENT_CLONE = $this->protect->allowed('CONFIG_AGENT_CLONE',true,'Configuration','Agent -> Clone');//nombre no mostrados
        $CONFIG_AGENT_STATUS = $this->protect->allowed('CONFIG_AGENT_STATUS',true,'Configuration','Agent -> Status');
        
		foreach ($sondas as $keySonda => $sonda) {
			
			$valores = array();
			$valores['host'] = $sonda['host'];
			$valores['ip_wan'] = $sonda['ip_wan'];
			$valores['plan'] = $sonda['plan'];
			$valores['dns'] = $sonda['dns'];
			$valores['mac'] = $sonda['mac'];
			$valores['grupo'] = $sonda['grupo'];
            $valores['codigosonda'] = $sonda['codigosonda'];
			
			if($sonda['availability'] == 1) {
				$valores['availability'] = '<span class="status_on">'.$this->language->AVAILABLE.'<span>';
			} else {
				$valores['availability'] = '<span class="status_off">'.$this->language->NOT_AVAILABLE.'<span>';
			}
			
            if($CONFIG_AGENT_STATUS){
                if($sonda['status']) {
                    $valores['estado'] = '<a onclick="statusSonda('.$sonda['id_host'].',true)" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" aria-disabled="false" title="Button on/off"> <span class="ui-button-icon-primary ui-icon ui-icon-power"></span><span class="ui-button-text">'.$this->language->ENABLED.'</span> </a>';
                } else {
                    $valores['estado'] = '<a onclick="statusSonda('.$sonda['id_host'].',false)" class="ui-button ui-widget ui-state-error ui-corner-all ui-button-icon-only" role="button" aria-disabled="false" title="Button on/off"> <span class="ui-button-icon-primary ui-icon ui-icon-power"></span><span class="ui-button-text">'.$this->language->DISABLED.'</span> </a>';
                }
            } else {
                $valores['estado'] = '';
            }

			$option = '<span id="toolbarSet">';

            if($CONFIG_AGENT_EDIT){
                $option .= '<button id="editSonda" onclick="editSonda('.$sonda['id_host'].',false)" name="editSonda">'.$this->language->EDIT.'</button>';               
            }
            /*
            if($CONFIG_AGENT_CLONE){
                $option .= '<button id="clonarSonda" onclick="editSonda('.$sonda['id_host'].',true)" name="clonarSonda">'.$this->language->CLONE.'</button>';               
            }
                                                  			
            if($CONFIG_AGENT_CONFIG){
                $option .= '<button id="configSonda" onclick="configSonda('.$sonda['id_host'].',true)" name="configSonda">'.$this->language->BUTTON_SET.'</button>';               
            }
            				
			if($CONFIG_AGENT_TRIGGER) {
				$option .= '<button id="triggerSonda" onclick="triggerSonda('.$sonda['id_host'].')" name="triggerSonda">'.$this->language->TRIGGER.'</button>';
			}
			*/ 
			$option .= '</span>';
			
			$valores['opciones'] = $option;

			$features=explode(',',$sonda['feature']);
			$valoresFeature=explode(',',$sonda['valueFeature']);

			foreach ($features as $keyF => $feature) {
				$valores[$feature] = $valoresFeature[$keyF];
			}
			
			$datos = array();
			
			foreach ($featuresTable as $table) {
				$datos[$table['feature']] = $valores[$table['feature']];
			}
			
			$data['rows'][] = array(
				'id' => $sonda['id_host'],
				'cell' => $datos
			);
			
		}
		
		$this->basic->jsonEncode($data);
		exit;
	}

	public function getConfigSonda($idsonda=false)
	{
		
		if($idsonda) {
			$query_get_config = "SELECT `id_feature`, `feature`, `display` , IF(`value` = '' ,  `default_value` , value )  as default_value
                                    FROM `bm_host_feature` HF
                                    LEFT OUTER JOIN  `bm_host_detalle` HD USING(`id_feature`)
                                    WHERE  HD.`id_host`=$idsonda   AND HF.`type_feature` = 'config' AND HF.`feature_type` = 'other' 
                                    UNION DISTINCT 
                                    SELECT `id_feature`,`feature`, `display`,`default_value` 
                                    FROM `bm_host_feature` 
                                    WHERE `type_feature`='config' AND `feature_type` = 'other'";
		} else {
			$query_get_config = "SELECT `id_feature`,`feature`, `display`,`default_value` 
									FROM `bm_host_feature` 
									WHERE `type_feature`='config'";			
		}

		$config = $this->conexion->queryFetch($query_get_config);
		
		
		$configArray = array();
		foreach ($config as $key => $value) {
			$config_name = $value['feature'];
			$configArray[$config_name]['id_feature'] = $value['id_feature'];
			$configArray[$config_name]['display'] = $value['display'];
			$configArray[$config_name]['default_value'] = $value['default_value'];
		}
		$checked = '';
		if($configArray['wifi']['default_value'] === "1") {
			$checked = 'checked';
		}

		$input = '<div class="row"><label for="cfg_'.$configArray['wifi']['id_feature'].'" class="col1">'.$configArray['wifi']['display'].'</label>';
		$input .= '<span class="col2"><input type="checkbox" class="checkbox" name="cfg_'.$configArray['wifi']['id_feature'].'" value="1" '.$checked.'></span></div>';
		
		$input .= '<div class="row"><label for="cfg_'.$configArray['wl_channel']['id_feature'].'" class="col1">'.$configArray['wl_channel']['display'].'</label>';
		$input .= '<span class="col2"><select name="cfg_'.$configArray['wl_channel']['id_feature'].'" id="cfg_'.$configArray['wl_channel']['id_feature'].'" class="select">';
		
		for ($i=1; $i < 12; $i++) {
			if($configArray['wl_channel']['default_value'] == $i) {
				$select = 'selected';
			} else {
				$select = '';
			}
			$input .= "<option $select value='$i'>$i</option>\n";
		}

		$input .= '</select></span></div>';
		
		$input .= '<div class="row"><label for="cfg_'.$configArray['wl_ssid']['id_feature'].'" class="col1">'.$configArray['wl_ssid']['display'].'</label>';
		$input .= '<span class="col2"><input type="text" name="cfg_'.$configArray['wl_ssid']['id_feature'].'" id="cfg_'.$configArray['wl_ssid']['id_feature'].'" value="'.$configArray['wl_ssid']['default_value'].'" class="input ui-widget-content ui-corner-all" /></span></div>';
		
		$input .= '<div class="row"><label for="cfg_'.$configArray['wl_key']['id_feature'].'" class="col1">'.$configArray['wl_key']['display'].'</label>';
		$input .= '<span class="col2"><input type="text" name="cfg_'.$configArray['wl_key']['id_feature'].'" id="cfg_'.$configArray['wl_key']['id_feature'].'" value="'.$configArray['wl_key']['default_value'].'" class="input ui-widget-content ui-corner-all" /></span></div>';

		$input .= '<div class="rowC"><label for="cfg_'.$configArray['block_ip']['id_feature'].'" class="col1">'.$configArray['block_ip']['display'].'</label>';
		$input .= '<span class="col2"><textarea class="textarea ui-widget-content ui-corner-all" name="cfg_'.$configArray['block_ip']['id_feature'].'" cols="50" rows="7">'.$configArray['block_ip']['default_value'].'</textarea></span></div>';
		
		$checked = '';
		if($configArray['cparental']['default_value'] === "1") {
			$checked = 'checked';
		}
		
		$input .= '<div class="row"><label for="cfg_'.$configArray['cparental']['id_feature'].'" class="col1">'.$configArray['cparental']['display'].'</label>';
		$input .= '<span class="col2"><input type="checkbox" class="checkbox" name="cfg_'.$configArray['cparental']['id_feature'].'" value="1" '.$checked.'></span></div>'; 

		$input .= '<div class="row"><label for="cfg_'.$configArray['timezone']['id_feature'].'" class="col1">'.$configArray['timezone']['display'].'</label>';
		$input .= '<span class="col2"><select name="cfg_'.$configArray['timezone']['id_feature'].'" id="cfg_'.$configArray['timezone']['id_feature'].'" class="select">';
		
		for ($i=-11; $i < 12; $i++) {
			$val = "GMT".$i;
			if($val == 'GMT0') {
				$val = 'GMT';
			}
			if($configArray['timezone']['default_value'] == $val) {
				$select = 'selected';
			} else {
				$select = '';
			}
			
			$input .= "<option $select value='$val'>$val</option>\n";
		}

		$input .= '</select></span></div>';		

		$input .= '<input type="hidden" name="cfg_'.$configArray['encryption']['id_feature'].'" id="cfg_'.$configArray['encryption']['id_feature'].'" value="'.$configArray['encryption']['default_value'].'"  class="input ui-widget-content ui-corner-all" />';
		$input .= '<input type="hidden" name="cfg_'.$configArray['wl_net_mode']['id_feature'].'" id="cfg_'.$configArray['wl_net_mode']['id_feature'].'" value="'.$configArray['wl_net_mode']['default_value'].'"  class="input ui-widget-content ui-corner-all" />';				
		
		return $input;
	}

    public function getFormProfile($idsonda = false, $profileid = false , $json = true) {
        $value['status'] = false;
        
        $idsondaActive = false;
    
        if(isset($_POST['profileid'])) {
            $profileid = $_POST['profileid'];
        } elseif (!$profileid) {
            $value['error'] = "Perfil incorrecto";
            
            if($json) {
                $this->basic->jsonEncode($value);
                exit;   
            } else {
                return FALSE;
            }
        }

        if(isset($_POST['idsonda']) && is_numeric($_POST['idsonda'])) {
            $idsonda = $_POST['idsonda'];
        } elseif ($idsonda != false && !is_numeric($idsonda)) {
            $value['error'] = "Id Sonda incorrecta";
            
            if($json) {
                $this->basic->jsonEncode($value);
                exit;   
            } else {
                return false;
            } 
        }    
        
        if($json === 'false'){
            $json = false;
        }
        
        //GetProfileDefault
        $getProfileSQL = 'SELECT PCT.`count`, PV.`id_value`, PCIES.`class`, PCIES.`category`, PI.`item`, PI.`item_display`, PI.`type`, PI.`type_result`, 
                        CONCAT(PCIES.`class`, " - ", PCT.`count` , " - " , PI.`item_display`) as display,
                        IF(PHOST.`value` IS NOT NULL , PHOST.`value`, PV.`value`) AS value,
                        IF(PHOST.`value` IS NOT NULL , false, true) AS "default",
                        PV.`value` as "defaultValue"
                FROM 
                    `bm_profiles` P
                LEFT JOIN `bm_profiles_categories`  PCIES ON P.`id_profile` = PCIES.`id_profile`
                LEFT JOIN `bm_profiles_item` PI ON PI.`id_categories`=PCIES.`id_categories`
                LEFT JOIN `bm_profiles_category` PCT ON PCIES.`id_categories`= PCT.`id_categories`
                LEFT JOIN `bm_profiles_values` PV ON (PI.`id_item`=PV.`id_item` AND PCT.`id_category`=PV.`id_category`)
                LEFT OUTER JOIN (SELECT `id_value`,`value` FROM `bm_profiles_host` WHERE `id_host` = ?) AS PHOST ON PV.`id_value`=PHOST.`id_value`
                WHERE P.`id_profile` = ? AND PI.`type` = "param"
                GROUP BY PV.`id_value`
                ORDER BY PCIES.`class`, PCT.`count`, PI.`type`';
        $getProfileRESULT = $this->conexion->queryFetch($getProfileSQL,$idsonda,$profileid);

        if($getProfileRESULT) {
            $form = '<div id="accordion">';
            $class = '';
            foreach ($getProfileRESULT as $key => $profile) {
                if ($class !== $profile['class']) {              
                    $form .= '<h3>'.$profile['category'].'</h3>';
                    $form .= '<div><fieldset>';
                    $class = $profile['class'];
                }
                $form .= '<div class="row"><label for="sonda_profile_'.$profile['id_value'].'" class="col1">'.$profile['display'].'</label>';
                    
                    if($profile['default'] == true){
  
                        $form .= '<span class="col2"><button id="profileDefault" onclick="$.profileDefault(\'sonda_profile_'.$profile['id_value'].'\',\''.$profile['defaultValue'].'\');">Default</button>
                            <input type="text" style="color:#99CCCC;" 
                                name="sonda_profile_'.$profile['id_value'].'" 
                                id="sonda_profile_'.$profile['id_value'].'" 
                                value="'.$profile['value'].'"
                                title="'.$profile['item_display'].'"
                                class="input ui-widget-content ui-corner-all" /></span></div>'."\n";
          
                    } else {
                  
                        $form .= '<span class="col2"><button id="profileDefault" onclick="$.profileDefault(\'sonda_profile_'.$profile['id_value'].'\',\''.$profile['defaultValue'].'\');">Default</button>
                        <input type="text" style="color:#000000;" 
                            name="sonda_profile_'.$profile['id_value'].'" 
                            id="sonda_profile_'.$profile['id_value'].'" 
                            value="'.$profile['value'].'"
                            title="'.$profile['item_display'].'"
                            class="input ui-widget-content ui-corner-all" /></span></div>'."\n";
                            
                    }

                if(!isset($getProfileRESULT[$key+1]['class']) || ($getProfileRESULT[$key+1]['class'] != $class)) {
                        $form .= '</fieldset></div>';
                }
            }
            $form .= '</div>';
            
            $value['status'] = true;
            $value['datos'] = $form;
        
        
            if(!$json) {
                return $form;    
            } else {
                echo $this->basic->jsonEncode($value);
                exit;
            }
        } else {
            $value['error'] = "Error al obtener el profile seleccionado";
             
            if($json) {
                $this->basic->jsonEncode($value);
                exit;   
            } else {
                return false;
            }
        } 
    }

    public function getFormProfileParam($idsonda = false, $profileid = false , $json = true) {
        $value['status'] = false;
        
        $idsondaActive = false;
    
        if(isset($_POST['profileid'])) {
            $profileid = $_POST['profileid'];
        } elseif (!$profileid) {
            $value['error'] = "Perfil incorrecto";
            
            if($json) {
                $this->basic->jsonEncode($value);
                exit;   
            } else {
                return FALSE;
            }
        }

        if(isset($_POST['idsonda']) && is_numeric($_POST['idsonda'])) {
            $idsonda = $_POST['idsonda'];
        } elseif ($idsonda != false && !is_numeric($idsonda)) {
            $value['error'] = "Id Sonda incorrecta";
            
            if($json) {
                $this->basic->jsonEncode($value);
                exit;   
            } else {
                return false;
            } 
        } else {
        	$idsonda = 0;
        }    
        
        if($json === 'false'){
            $json = false;
        }
          
        //GetProfileDefault
        $getProfileParamSQL = "SELECT PP.`id_param`, PP.`name`,  PP.`description`, IF(PPH.`value` IS NOT NULL, PPH.`value`, PP.`value`)  as 'value', PP.`value` as 'valueDefault' , `id_host` , IF(`id_host` IS NULL, true,false) as 'default'
                            FROM `bm_profiles_param` PP
                                LEFT OUTER JOIN `bm_profiles_param_host` PPH ON PP.`id_param`=PPH.`id_param` AND `id_host` = $idsonda
                            WHERE `id_profile` = $profileid AND `visible` = 'true' AND `name` NOT IN ('SCHEDULE','SERIAL','HOLIDAY')";
							
							
        $getProfileParamRESULT = $this->conexion->queryFetch($getProfileParamSQL);

        if($getProfileParamRESULT) {
            $form = '<div><fieldset>';
            $class = '';
            foreach ($getProfileParamRESULT as $key => $profile) {
                $form .= '<div class="row"><label for="sonda_profile_'.$profile['id_param'].'" class="col1">'.$profile['description'].'</label>';
                    
                    if($profile['default'] == true){
  
                        $form .= '<span class="col2"><button id="profileDefault" onclick="$.profileDefault(\'sonda_profileParam_'.$profile['id_param'].'\',\''.$profile['valueDefault'].'\');">Default</button>
                            <input type="text" style="color:#99CCCC;" 
                                name="sonda_profileParam_'.$profile['id_param'].'" 
                                id="sonda_profileParam_'.$profile['id_param'].'" 
                                value="'.$profile['value'].'"
                                title="'.$profile['description'].'"
                                class="input ui-widget-content ui-corner-all" /></span></div>'."\n";
          
                    } else {
                  
                        $form .= '<span class="col2"><button id="profileDefault" onclick="$.profileDefault(\'sonda_profileParam_'.$profile['id_param'].'\',\''.$profile['valueDefault'].'\');">Default</button>
                        <input type="text" style="color:#000000;" 
                            name="sonda_profileParam_'.$profile['id_param'].'" 
                            id="sonda_profileParam_'.$profile['id_param'].'" 
                            value="'.$profile['value'].'"
                            title="'.$profile['description'].'"
                            class="input ui-widget-content ui-corner-all" /></span></div>'."\n";
                            
                    }
            }
            $form .= '</fieldset></div>';
            $value['status'] = true;
            $value['datos'] = $form;
               
            if(!$json) {
                return $form;    
            } else {
                echo $this->basic->jsonEncode($value);
                exit;
            }
        } else {
            $value['error'] = "Error al obtener el profile seleccionado";
             
            if($json) {
                $this->basic->jsonEncode($value);
                exit;   
            } else {
                return false;
            }
        } 
    }

	public function getFormFeature($idsonda = false, $groupid = false , $json = true) {
				
		$result['status'] = false;
	
		if(isset($_POST['groupid'])) {
			$groupid = $_POST['groupid'];
		} elseif (!$groupid) {
			$result['error'] = "Grupo incorrecto";
			
			if($json) {
				$this->basic->jsonEncode($result);
				exit;	
			} else {
				return FALSE;
			}
		}

		if(isset($_POST['id'])) {
			$idsonda = $_POST['id'];
		}
        
        //Get param groups (Arreglar , mejorar y optimizar ) !!!!*        
        $getParmGroups_S = 'SELECT `plan_active`,`profile_active`,`method_auth`, `configTab`,`lanConfig` FROM `bm_host_groups` WHERE `groupid` = '.$groupid.' LIMIT 1';
        //$this->logs->error("apps/config.php Parametros grupos: ".$getParmGroups_S);
        $getParmGroups_R = $this->conexion->queryFetch($getParmGroups_S);
        
        if(!$getParmGroups_R) {
           $result['error'] = "Error al obtener parametros del grupo";
           $this->basic->jsonEncode($result);
           exit; 
        } else {
           $groupsParam = (object)$getParmGroups_R[0];
           $result['profile'] = $groupsParam->profile_active;
           $result['plan'] = $groupsParam->plan_active;
           $result['method_auth'] = $groupsParam->method_auth;
           $result['configTab'] = $groupsParam->configTab;
           $result['lanConfig'] = $groupsParam->lanConfig;
        }
        
		//Get Feature
		
		$get_feature_sql = "SELECT HG.`groupid`, HF.`id_feature`, HF.`feature`, HF.`display`,HF.`default_value`, HF.`width`, HF.`type_value`, HF.`obligatorio`
    FROM `bm_host_feature` HF
        LEFT OUTER JOIN `bm_host_groups` HG ON HF.`type_feature`=HG.`type`
    WHERE  
        (`feature_type`='other' AND  `type_feature`  != 'config' ) AND 
        ( 
            ( HG.`groupid`=:groupid )  OR 
            ( HG.`groupid` IS NULL AND ( HF.`type_feature` = 'ALL' OR HF.`type_feature` LIKE (SELECT CONCAT('%',`type`,'%') FROM `bm_host_groups` WHERE `groupid` = :groupid)  )) 
        )
    ORDER BY HF.`orden`";
    
 		//$this->logs->error("apps/config.php Features del grupo: ".$get_feature_sql);
        $get_feature_result = $this->conexion->queryExecuteFetch($get_feature_sql,array('groupid' => $groupid));

		if($get_feature_result) {
			
			if(($idsonda !== false) && is_numeric($idsonda)) {
				$get_value_feature_sql = "SELECT HF.`id_feature`, `value` 
                            FROM `bm_host_feature` HF
                                LEFT OUTER JOIN `bm_host_groups` HG ON HF.`type_feature`=HG.`type`
                                INNER JOIN `bm_host_detalle` HD ON HF.`id_feature`=HD.`id_feature`
                            WHERE 
                                ( HD.`id_host`=:idHost AND  `feature_type`='other' AND  `type_feature`  != 'config' ) AND  
                                (
                                    (HG.`groupid`=:groupid) OR (  HG.`groupid` IS NULL AND  ( HF.`type_feature` = 'ALL' OR  HF.`type_feature` LIKE (
            SELECT CONCAT('%',`type`,'%') FROM `bm_host_groups` WHERE `groupid` = :groupid
            ) )))
                                    ORDER BY HF.`orden` ASC";
				
				$get_value_feature_result = $this->conexion->queryExecuteFetch($get_value_feature_sql,array('groupid' => $groupid,'idHost' => $idsonda));
							
				if($get_value_feature_result) {

					foreach ($get_value_feature_result as $key => $value) {
						$feature_value[$value['id_feature']] = $value['value'];
					}	

					foreach ($get_feature_result as $key => $feature) {
								
						$features[$key]['id_feature'] = $feature['id_feature'];
						$features[$key]['feature'] = $feature['feature'];
						$features[$key]['display'] = $feature['display'];
						$features[$key]['width'] = $feature['width'];
						
						if(isset($feature_value[$feature['id_feature']])) {
							$features[$key]['default_value'] = $feature_value[$feature['id_feature']];
						} else {
							$features[$key]['default_value'] = $feature['default_value'];
						}
						
					}		
						
				} else {
					if($json) {
						echo $this->basic->jsonEncode($result);
						exit;	
					} else {
						return FALSE;
					}
				}
			} else  {
				$features = $get_feature_result;
			}	
		} else {
			if($json) {
				echo $this->basic->jsonEncode($result);
				exit;	
			} else {
				return FALSE;
			}
		}
		
		if($features) {
			
			$form = '<fieldset class="ui-widget-content ui-corner-all">';
			
            foreach ($features as $key => $value) {
                $formSelect[$value['feature']] = $value['default_value'];
            }

			foreach ($features as $key => $feature) {
				
				$form .= '<div class="row"><label for="sonda_opc_'.$feature['id_feature'].'" class="col1">'.$feature['display'].'</label>';
				
				if (($feature['feature'] == 'continent') || ($feature['feature'] == 'country') || ($feature['feature'] == 'state') || ($feature['feature'] == 'region') || ($feature['feature'] == 'city') || ($feature['feature'] == 'location')) {
				    $form .= '<span class="col2"><select name="sonda_opc_'.$feature['id_feature'].'" id="'.$feature['feature'].'" class="select">';
					
                    if(!isset($formSelect[$feature['feature']]) && ($feature['feature'] !== 'continent')) {
                        $form .= '<option selected value="0">'.$this->language->SELECT.'</option>';
                    } else {
                        if($feature['feature'] == 'country') {
                            $form .= $this->basic->getLocation($feature['feature'],$formSelect['continent'],$feature['default_value']);
                        } elseif ($feature['feature'] == 'state') {
                            $form .= $this->basic->getLocation($feature['feature'],$formSelect['country'],$feature['default_value']);
                        } elseif ($feature['feature'] == 'region') {
                            if($feature['default_value'] == '-1'){
                                $form .= '<option selected="" value="-1">'.$this->language->OTHERS.'</option>';
                            } else {
                                $form .= $this->basic->getLocation($feature['feature'],$formSelect['state'],$feature['default_value']);
                            }                           
                        } elseif ($feature['feature'] == 'city') {
                            if($formSelect['region'] == '-1') {
                                $form .= $this->basic->getLocation('other',$formSelect['state'],$feature['default_value']);
                            } else {
                                $form .= $this->basic->getLocation($feature['feature'],$formSelect['region'],$feature['default_value']);
                            }
                            
                        } elseif ($feature['feature'] == 'location') {
                            $form .= $this->basic->getLocation('bmLocation',$formSelect['state'],$feature['default_value']);                                               
                        } else {
                            $form .= $this->basic->getLocation($feature['feature'],false,$feature['default_value']);
                        }                
                    }
                    $form .= '</select></span></div>'."\n"; 
				} else {
					if(is_numeric($feature['width'])){
						$width = $feature['width'].'px';
					} else {
						$width = $feature['width'];
					}
					$form .= '<span class="col2"><input type="text" style="width: '.$width.'" name="sonda_opc_'.$feature['id_feature'].'" id="sonda_opc_'.$feature['feature'].'" value="'.$feature['default_value'].'"  class="input ui-widget-content ui-corner-all" /></span></div>'."\n";
				}
				
				
				
			}

			$form .= '</fieldset>';
			//$form .= '<script type="text/javascript" src="'.URL_BASE.'sitio/js/form_config.js"></script>';

			$result['status'] = true;
			$result['datos'] = $form;
		}
		
		if(!$json) {
			return $this->basic->fixEncoding($form);
		} else {
			echo $this->basic->fixEncoding($this->basic->jsonEncode($result));
		}
		
	}

	public function statusSonda()
	{
		$valida = $this->protect->access_page('CONFIG_AGENT_STATUS',true,'Configuration','Agent -> Status');
		
		$data['status'] = true ;
		
		if($valida) { 
			$getParam = (object)$_POST;
			
			$update_item_group = "UPDATE `bm_host` SET `status` = '$getParam->status' WHERE `id_host` = '$getParam->id';";
			
			$valida = $this->conexion->query($update_item_group);
			
			if(!$valida) {
				$data['status'] = false;
			}
			
		} else {
			$data['status'] = false;
		}
		
		echo $this->basic->jsonEncode($data);
	}
	
	public function deleteSonda()
	{
		$valida = $this->protect->access_page('CONFIG_AGENT_DELETE',true,'Configuration','Agent -> Delete');
		
		$data['status'] = true ;
		
		if($valida) { 
			$getParam = (object)$_POST;
			
			foreach ($getParam->idSonda as $key => $value) {
				if(is_numeric($value)) {
					
					$update_sonda = "UPDATE `bm_host` SET `borrado` = '1' 
										WHERE `id_host` = '$value';";										
					$valida = $this->conexion->query($update_sonda);					
					if(!$valida) {
						$data['status'] = false;
					}else{
						$select_sonda = "select * from `bm_disponibilidad` WHERE `id_host` = '$value';";
						$validaselect = $this->conexion->query($select_sonda);			
						if($validaselect) {
							$delete_sonda = "delete from bm_disponibilidad WHERE `id_host` = '$value';";
							$validadelete = $this->conexion->query($delete_sonda);	
							if(!$validadelete) {
								$data['status'] = false;
								$this->logs->error("Se elimino la sonda id_host=$value, pero no pudo ser eliminado el registro de la sonda id_host=$value de la tabla bm_disponibilidad (eliminar manualmente)"); 
							}				
						}			
					}
					
				}
			}
					
		} else {
			$data['status'] = false;
		}
		echo $this->basic->jsonEncode($data);
	}

	public function createSonda() 
	{
		$valida = $this->protect->access_page('CONFIG_AGENT_NEW',true,'Configuration','Agent -> New');
		
		if($valida) {
			$getParam = (object)$_POST;
			//me esta llegando lo siguiente
			/*$this->logs->error("VOY ALMACENAR LO SIGUIENTE: ");
			foreach ($getParam as $key14 => $value14) {
				$this->logs->error("_ $key14 => $value14 ;");	
			}
			$this->logs->error(" ...  FIN PARAMETROS ALMACENAR ..... "); 
			*/
			
			//Validar valores			
			$getParam->cfg_5 = (isset($_POST['cfg_5'])) ? 1 : 0;
			$getParam->cfg_3 = (isset($_POST['cfg_3'])) ? 1 : 0;
			
			//Categoriza resultado	
			
			$config = array();
			$op = array();
			$host = array();
			
			foreach ($getParam as $key => $value) {
				if(preg_match("/^cfg_/i",$key)) {
					$keyName = explode("_", $key,2);
					$config[$keyName[1]] = $value;
				} elseif (preg_match("/^sonda_opc_/i",$key)) {
					$keyName = explode("sonda_opc_", $key,2);
					$op[$keyName[1]] = $value;
				} else{
					$keyName = explode("sonda_", $key,2);
					if(isset($keyName[1])) {
						$host[$keyName[1]] = $value;
					}
				} 
			}
			
			//Valdando Mac Existente
			
			$host['codigosonda'] = trim($host['codigosonda']);
			
			if(!isset($host['mac_lan']) || ($host['mac_lan'] == '')) {
			    $macLan = strtoupper($host['mac_wan']);
			} else {
                $macLan = strtoupper($host['mac_lan']);
			}
			
			$host['mac_wan'] = strtoupper($host['mac_wan']);
			
			$BSWID = "BSW".str_replace(":", '', $host['mac_wan']);
			
			$select_result = $this->conexion->queryFetch("SELECT count(*) as Total , `host`  FROM `bm_host` WHERE ( `mac` = ? OR `mac_lan` = ? OR `dns` = ? OR `codigosonda` = ?) AND `borrado` = 0 ",$host['mac_wan'],$macLan,$BSWID,$host['codigosonda']);
			
			if($select_result) {
				
				if($select_result[0]['Total'] > 0) {
					
					$result['status'] = false;	
					$result['msg'] =  str_replace('{NAME_SONDA}',$select_result[0]['host'],$this->language->CONFIG_SONDA_CREATE_CONFLICT_MAC);
					
					header("Content-type: application/json");
					echo $this->basic->jsonEncode($result);
					exit;
				}
				
			}
            
            if(!isset($host['plan']) || $host['plan'] == ''){
                $host['plan'] = 0;
            }
            
            if(!isset($host['identificator'])) {
                $host['identificator'] = 0;
            }
    
            if(!isset($host['codigosonda'])) {
                $host['codigosonda'] = 0;
            }        

			//Generando Host
			
			$result = array();
			
			$this->conexion->InicioTransaccion();
			
			$insert_host = sprintf("INSERT INTO `bm_host` (`groupid`, `host`, `dns`, `mac`, `ip_wan`, `ip_lan`, `mac_lan`, `netmask_lan`, `id_plan`, `status`, `borrado`,`codigosonda`, `tags`,`identificator`,`id_profile`)
										VALUE (%s, '%s', '%s', '%s', '%s', '%s', '%s','%s', %s , 1, 0,'%s','%s','%s','%s')",
										$host['group'],
										$host['name'],
										$host['dns'],
										$host['mac_wan'],
										$host['ip_wan'],
										$host['ip_lan'],
										$host['mac_lan'],
										$host['netmask_lan'],
										$host['plan'],
                                        $host['codigosonda'],
                                        $host['tags'],
                                        $host['identificator'],
                                        (isset($host['profile'])) ? $host['profile'] : 0);
										
			$host_result = $this->conexion->query($insert_host);
			$idHost = (int)$this->conexion->lastInsertId();
			
			if($host_result) {
				
				
				//Cargando feature 
				
				$insert_feture = "INSERT INTO `bm_host_detalle` (`id_host`, `id_feature`, `value`) VALUES ";
				
				// Inicio Config
				$value_feature_config = array();
				
				foreach ($config as $key => $value) {
					if(is_numeric($key) && is_numeric($idHost)) {
						$value_feature_config[] = "($idHost,$key,'".$value."')"; 
					}
				}
			
				$insert_feture_config = $insert_feture.join(',',$value_feature_config);
				
				$feature_insert_config = $this->conexion->query($insert_feture_config);
				
				// Fin Config
				
				if($feature_insert_config) {
					$value_feature_op = array();
					foreach ($op as $key => $value) {
						if(is_numeric($key) && is_numeric($idHost)) {
							$value_feature_op[] = "($idHost,$key,'".$value."')";
						}
					}
				
					$insert_feture_op = $insert_feture.join(',',$value_feature_op);
					
					$feature_insert_op = $this->conexion->query($insert_feture_op);	
					
					if($feature_insert_op) {
						
						//Creando Monitores
						
						$get_monitores = 'SELECT I.`id_item`, I.`type_poller`,  I.`snmp_oid` 
									FROM `bm_items` I 
									LEFT JOIN `bm_items_groups` IG USING(`id_item`) 
									WHERE  IG.`groupid` = '.$host['group'];
									
						$monitores = $this->conexion->queryFetch($get_monitores);
						
						if($monitores) {
							$insert_monitor_host = 'INSERT INTO `bm_item_profile` (`id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, `nextcheck`) VALUES ';
							$values_insert = array();
							foreach ($monitores as $key => $monitor) {
								if($monitor['type_poller'] == 'snmp') {
									$snmp_oid = $monitor['snmp_oid'];
									$values_insert [] = '('.$monitor['id_item'].','.$idHost.','."'".$monitor['snmp_oid']."','1161','public','1970-01-01 00:00:00')";
								} else {
									$values_insert [] = '('.$monitor['id_item'].','.$idHost.",NULL,NULL,NULL,'1970-01-01 00:00:00')";
								}
								
							}
							
							$insert_monitor_host = $insert_monitor_host.join(',',$values_insert);
							
							$insert_monitor_host_result = $this->conexion->query($insert_monitor_host);
							
							if($insert_monitor_host_result) {
								$result['status'] = true;
							} else {
								$result['error'] = "Error al insertar el profile de las sondas";
								$result['status'] = false;
							}
                            
                            
						} else {
							$result['error'] = "Error al obtener los items del grupo";
							$result['status'] = false;
						}
					} else {
						$result['error'] = "Error al insertar parametros opcionales";
						$result['status'] = false;
					}
				} else {
					$result['error'] = "Error al insertar parametros de configuracion";
					$result['status'] = false;
				}
			} else {
				$result['error'] = "Error al insertar host";
				$result['status'] = false;
			}
			
		} else {
			$result['status'] = false;
		}
		
		if($result['status']) {
			$this->protect->setAudit($_SESSION['uid'],'create_sonda',"Usuario creo la sonda: [$idHost] ".$host['name']);
			$this->conexion->commit();
            $this->tagsCreation();
		} else {
		    if($valida) {
		       $this->conexion->rollBack(); 
		    }
		}
		
		if(!$result['status']) {
			$result['msg'] = $this->language->CONFIG_SONDA_CREATE;
		}
		$this->plantilla->cacheClean();
		header("Content-type: application/json");
		echo $this->basic->jsonEncode($result);
		exit;
	}

	public function getSondaForm()
	{
		if(is_numeric($_POST['sondaID'])) {
			$idsonda = $_POST['sondaID'];
		} else {
			return false;
		}

        $valida = $this->protect->allowed('CONFIG_AGENT_EDIT');
        
        $this->plantilla->loadSec("configuracion/config_equipos_form", $valida,false,$idsonda);
        

		// Detalle Sonda
		
		$sonda_sql = "SELECT H.`id_host` as id_sonda, H.`host`, H.`dns`, H.`mac` as mac_wan, 
		                      H.`ip_wan`,H.`ip_lan`, H.`mac_lan`, H.`netmask_lan` ,  H.`identificator`,  H.`tags` ,
		                      H.`id_plan`,HG.`name` , HG.`groupid`, P.`plan`,H.`codigosonda`,H.`id_profile`
						FROM `bm_host` H
						LEFT JOIN `bm_host_groups` HG USING(`groupid`)
						LEFT JOIN `bm_plan` P ON H.`id_plan`=P.`id_plan`
						WHERE H.`id_host`=$idsonda";
						
		$sonda_detalles = $this->conexion->queryFetch($sonda_sql);

		if($sonda_detalles) {
			
			$sonda_detalles = $sonda_detalles[0];
			
			foreach ($sonda_detalles as $key => $sonda) {
				$value["sonda_".$key] = $sonda;
			}
		}
		
		$STI_POWER = $this->parametro->get('STI_POWER',FALSE);
		
		if($STI_POWER) {
			$value["sti_codigosonda"] = 'style=""';
		} else {
			$value["sti_codigosonda"] = 'style="display: none;"';
		}

		// Grupo Sonda

		$profile = $this->getProfileOption($value["sonda_id_profile"],$value["sonda_groupid"],false,true);
                
        if($profile) {
            $value["option_profile"] = $profile;
        } else {
            $value["option_profile"] = '<option selected value="0">Error</option>';
        }    
               		
		if($_POST['type'] ==  'clone') {
   
           $grupos = $this->bmonitor->getGroupsHost();  
    		
    		if($grupos) {
    			$value["option_group"] = $this->basic->getOption($grupos, 'groupid', 'name',$value["sonda_groupid"]);
    		} else {
    			$value["option_group"] = '<option selected value="'.$value["sonda_groupid"].'">'.$value["sonda_name"].'</option>';
    		}
		
		} else {
			$value["option_group"] = '<option selected value="'.$value["sonda_groupid"].'">'.$value["sonda_name"].'</option>';
		}
		
		// Grupo Plan
		$value["option_plan"] = $this->getPlanOption($value["sonda_id_plan"],$value["sonda_groupid"]);
		
		$value["form_id"] = 'form_edit_sonda';
		
		// Formulario configuracion
		$value["input_config"] = $this->getConfigSonda($idsonda);
		
		// Formulario opcionales
		$value["input_opcional"] = $this->getFormFeature($idsonda,$value["sonda_groupid"],false);
        
		$this->plantilla->set($value);
		echo $this->plantilla->get();
	}

	public function editSonda() 
	{
	    $valida = $this->protect->access_page('CONFIG_AGENT_EDIT',true,'Configuration','Agent -> Edit');
		
		if($valida) {
			$getParam = (object)$_POST;
			
			//Validar valores
			
			$getParam->cfg_5 = (isset($_POST['cfg_5'])) ? 1 : 0;
			$getParam->cfg_3 = (isset($_POST['cfg_3'])) ? 1 : 0;
			
			//Categoriza resultado	
			
			$config = array();
			$op = array();
			$host = array();
            $profileStatus = false;
            
			foreach ($getParam as $key => $value) {
				if(preg_match("/^cfg_/i",$key)) {
					$keyName = explode("_", $key,2);
					$config[$keyName[1]] = $value;
				} elseif (preg_match("/^sonda_opc_/i",$key)) {
					$keyName = explode("sonda_opc_", $key,2);
					$op[$keyName[1]] = $value;
				} elseif (preg_match("/^sonda_profile_/i",$key)) {
                    $keyName = explode("sonda_profile_", $key,2);
                    if($value != '') {
                        $profile[$keyName[1]] = $value;
                        $profileStatus = true;  
                    }                    
                } elseif (preg_match("/^sonda_profileParam_/i",$key)) {
                    $keyName = explode("sonda_profileParam_", $key,2);
                    if($value != '') {
                        $profileParam[$keyName[1]] = $value;
                        $profileParamStatus = true;  
                    }                    
                } elseif (preg_match("/^sonda_/i",$key)) {
                    $keyName = explode("sonda_", $key,2);
                    $host[$keyName[1]] = $value;
                }
			}
			
			//Generando Host
			
			$result = array();
			
			$this->conexion->InicioTransaccion();
			
			$idHost = $getParam->id;
			
			$update_host = sprintf("UPDATE `bm_host` SET `host` = '%s', `dns` = '%s', `mac` = '%s', `ip_wan` = '%s', `ip_lan` = '%s', `mac_lan` = '%s', `netmask_lan` = '%s' , `id_plan` = '%s', `codigosonda` = '%s', `tags` = '%s', `identificator` = '%s' , `id_profile` = %s WHERE (`id_host`='$idHost') LIMIT 1;",
									$host['name'],
									$host['dns'],
									$host['mac_wan'],
									$host['ip_wan'],
									$host['ip_lan'],
									$host['mac_lan'],
									$host['netmask_lan'],
									(isset($host['plan'])) ? $host['plan'] : 0,
									$host['codigosonda'],
									$host['tags'],
                                    (isset($host['identificator'])) ? $host['identificator'] : 0,
                                    (isset($host['profile'])) ? $host['profile'] : 0);

			$host_result = $this->conexion->query($update_host);
		
			if($host_result) {
				
				// Inicio Config
				
				//Areglar codigo sin validar**************************************
				$feature_insert_config = 'INSERT INTO `bm_host_detalle` (`id_host`,`id_feature`,`value`) VALUES ';
				$feature_insert_config_value = array();
				foreach ($config as $key => $value) {
					if($key != '') {
						$feature_insert_config_value[] =  "($idHost,$key,'$value')";
					}
				}
				$feature_insert_config = $feature_insert_config.join(',', $feature_insert_config_value)." ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)";
				$feature_insert_config = $this->conexion->query($feature_insert_config);
			
				// Fin Config
				
				
				if($feature_insert_config) {
						
					$feature_insert_op = 'INSERT INTO `bm_host_detalle` (`id_host`,`id_feature`,`value`) VALUES ';
					$feature_insert_op_value = array();
					foreach ($op as $key => $value) {
						if($key != '') {
							$feature_insert_op_value[] =  "($idHost,$key,'$value')";
						}
					}
					
					$feature_insert_op = $feature_insert_op.join(',', $feature_insert_op_value)." ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)";
					$feature_insert_op = $this->conexion->query($feature_insert_op);
				
					
					if($feature_insert_op) {
						$result['status'] = true;
					} else {
						$result['status'] = false;
					}
    				
    				if($profileStatus){
							
						$getProfileSQL = "SELECT PV.`id_value`,PV.`value` 
									FROM `bm_profiles_values`  PV
										LEFT JOIN `bm_profiles_item` PI ON PI.`id_item`=PV.`id_item`
										LEFT JOIN `bm_profiles_categories` PC ON PC.`id_categories`=PI.`id_categories`
									WHERE PV.`id_monitor` = 0 AND PC.`id_profile`=".$host['profile'];
                         
                        $getProfileRESULT = $this->conexion->queryFetch($getProfileSQL);
                        
                        $defaultValue = array();
                        if($getProfileRESULT) {
                            foreach ($getProfileRESULT as $key => $value) {
                                $defaultValue[$value['id_value']] = $value['value'];
                            }
                        }
                        
						$profileInsert = 'INSERT INTO `bm_profiles_host` (`id_host`,`id_value`,`value`) VALUES ';
                       	$profileInsertValue = array();
						
                        foreach ($profile as $key => $value) {
                            if($key != '') {
                                if (isset($defaultValue[$key]) && $defaultValue[$key] !== $value) {
                                    $profileInsertValue[] =  "($idHost,$key,'$value')";
                                } else {
                                	$delete[] = $key; 
                                }	
                            }
                        }

						if(count($profileInsertValue) > 0) {
                            $profileInsert = $profileInsert.join(',', $profileInsertValue)." ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)";
                            $profileInsert = $this->conexion->query($profileInsert);                           
                        } else {
                            $profileInsert = true;
                        }
                        
                        if(isset($delete) && count($delete) > 0) {
                            $deleteProfile = "DELETE FROM `bm_profiles_host` WHERE  `id_host` = ".$idHost." AND  `id_value` IN ( ".join(',', $delete).");";
							$this->conexion->query($deleteProfile);
                        } 
		
                        if($profileInsert) {
                            $result['status'] = true;
                        } else {
                            $result['status'] = false;
                        }							   				 	
							
    				}
										
					if($profileParamStatus) {
						$getProfile_S = "SELECT `id_param`, `value` FROM `bm_profiles_param` WHERE `id_profile` =".$host['profile'];
                         
                        $getProfile_R = $this->conexion->queryFetch($getProfile_S);
                        
                        $defaultValue = array();
						$delete = array();
                        if($getProfile_R) {
                            foreach ($getProfile_R as $key => $value) {
                                $defaultValue[$value['id_param']] = $value['value'];
                            }
                        }
                        
						$profile_insert = 'INSERT INTO `bm_profiles_param_host` (`id_host`,`id_param`,`value`) VALUES ';
                        $profile_insert_value = array();
                        foreach ($profileParam as $key => $value) {
                            if($key != '') {
                                
                                if (isset($defaultValue[$key]) && $defaultValue[$key] !== $value) {
                                    $profile_insert_value[] =  "($idHost,$key,'$value')";
                                } else {
                                	$delete[] = $key; 
                                }
 				
                            }
                        }
				
						if(count($profile_insert_value) > 0) {
                            $profile_insert = $profile_insert.join(',', $profile_insert_value)." ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)";
                            $profile_insert = $this->conexion->query($profile_insert);                           
                        } else {
                            $profile_insert = true;
                        }
                        
                        if(isset($delete) && count($delete) > 0) {
                            $deleteProfile = "DELETE FROM `bm_profiles_param_host` WHERE  `id_host` = ".$idHost." AND  `id_param` IN ( ".join(',', $delete).");";
							$this->conexion->query($deleteProfile);
                        } 
		
                        if($profile_insert) {
                            $result['status'] = true;
                        } else {
                            $result['status'] = false;
                        }						
					}
                 
				} 
				else {
					$result['status'] = false;
				}
			} else {
				$result['status'] = false;
			}
			
		} else {
			$result['status'] = false;
		}
		
		if($result['status']) {						
			//////------- llamado a reconfiguración de la sonda en caso de ser de tipo QoS -------//////
			$esQoS_laSonda_sql = "SELECT G.`name` FROM bm_host_groups G 
								  WHERE G.groupid=(SELECT H.groupid from bm_host H WHERE H.id_host=".$idHost.") 
								  AND G.type='QoS'
								  AND G.borrado=0";
            $esQoS_laSonda = $this->conexion->queryFetch($esQoS_laSonda_sql);
			if(isset($esQoS_laSonda)){//la sonda es de tipo QoS???
				$insert_confQoS_sql = "INSERT INTO bm_trigger (id_host,fecha_modificacion,type,command,responsable,fecha_consume) 
				VALUES (".$idHost.",NOW(),'config','configure','trigger',NULL)";
	            $insert_confQoS = $this->conexion->queryFetch($insert_confQoS_sql);
				if(isset($insert_confQoS)){//se ingreso el cambio 
					$this->logs->info("Se reportó correctamente el cambio del host (".$idHost." - nombre= ".$host['name'].") en tabla bm_trigger ");
				}				
				else
				{
					$this->logs->error("No se pudo insertar el cambio del host (".$idHost." - nombre= ".$host['name'].") en tabla bm_trigger ");
				}				
			}			
			//////------- FIN a llamado de reconfiguración de la sonda en caso de ser de tipo QoS -------//////
			$this->protect->setAudit($_SESSION['uid'],'edit_sonda',"Usuario modifico la sonda: [$idHost] ".$host['name']);
			$this->conexion->commit();
            $this->tagsCreation();
		} else {
			$this->conexion->rollBack();
		}
		
		if(!$result['status']) {
			$result['msg'] = $this->language->CONFIG_SONDA_CREATE;
		}

        $this->plantilla->cacheClean();
		
		header("Content-type: application/json");
		$this->basic->jsonEncode($result);
		exit;
	}


	public function setConfigSonda()
	{
	    $valida = $this->protect->access_page('CONFIG_AGENT_CONFIG',true,'Configuration','Agent -> Config');
		
		if($valida) { 
			$getParam = (object)$_POST;
			$data['status'] = true;
			foreach ($getParam->idSonda as $key => $value) {
				if(is_numeric($value)) {
					
					$update_sonda = "INSERT INTO `bm_trigger` (`id_host`, `fecha_modificacion`, `type`) VALUES ('$value', NOW(), 'config')";
										
					$valida = $this->conexion->query($update_sonda);
					
					if(!$valida) {
						$data['status'] = false;
					}
					
				}
			}
					
		} else {
			$data['status'] = false;
		}
		header("Content-type: application/json");
		echo $this->basic->jsonEncode($data);
		exit;
	}

	public function setUpgradeSonda()
	{
	    $valida = $this->protect->access_page('CONFIG_AGENT_UPGRADE',true,'Configuration','Agent -> Upgrade');
		
		if($valida) { 
			$getParam = (object)$_POST;
			$data['status'] = true;
			
			$VERSION_QOS = $this->parametro->get('VERSION_QOS','3.51');
			
			$VERSION_QOS = $VERSION_QOS+0.01;
			
			$this->parametro->setdb('VERSION_QOS',$VERSION_QOS);
				
		} else {
			$data['status'] = false;
		}
		header("Content-type: application/json");
		echo $this->basic->jsonEncode($data);
		exit;
	}
	
	public function setTriggerSonda()
	{
	    $valida = $this->protect->access_page('CONFIG_AGENT_TRIGGER',true,'Configuration','Agent -> Trigger');

		if($valida) { 
			$getParam = (object)$_POST;
			$data['status'] = true;
			if(is_numeric($getParam->idSonda)) {
				
				$update_sonda = "INSERT INTO `bm_trigger` (`id_host`, `fecha_modificacion`, `command`,`responsable`) VALUES ('$getParam->idSonda', NOW(), '$getParam->trigger_id','$getParam->trigger_responsable')";
									
				$valida = $this->conexion->query($update_sonda);
				
				if(!$valida) {
					$data['status'] = false;
				}
				
			}
		} else {
			$data['status'] = false;
		}
		header("Content-type: application/json");
		echo $this->basic->jsonEncode($data);
		exit;
	}
	
	public function cfgMonitores()
	{
        $valida = $this->protect->access_page('CONFIG_MONITOR');

        $this->plantilla->loadSec("configuracion/config_monitores", $valida , 86000);

		//Formulario nuevo 
		//Items
		
		$grupos = $this->bmonitor->getGroupsHost();;
		
		if($grupos) {
			$valueFormNew["menu_groupid_list"] = '';
			foreach ($grupos as $key => $grupo) {				
				$valueFormNew["menu_groupid_list"] .= '<option value="'.$grupo['groupid'].'">'.$grupo['name'].'</option>';
			}
		}
		
		$valueFormNew["lang"] = $this->protect->getLang();
		
		$valueFormNew["form_id"] = 'config_monitores_form_new';
		
		$valueFormNew['option_type_monitor'] = '<option selected value="1">Snmp</option><option value="2">Agente BSW</option>';
		$valueFormNew["option_type_item"] = $this->basic->getOptionValue('type_data');
        
        //LANG FORM
        
             
        	
		$value["form_new_monitor"] = $this->plantilla->getOne("configuracion/config_monitores_form",$valueFormNew);	

		$value["option_group"] = $this->basic->getOption($grupos,'groupid','name','all');
        
        $button[] = array(
            'name' => 'NEW',
            'bclass' => 'add',
            'protec' => 'CONFIG_MONITOR_NEW',
            'onpress' => 'toolboxMonitor'
        );

        $button[] = array(
            'name' => 'DELETE',
            'bclass' => 'delete',
            'protec' => 'CONFIG_MONITOR_DELETE',
            'onpress' => 'toolboxMonitor'
        );
        
        $value["button"] = $this->generate->getButton($button);
	
		$this->plantilla->set($value);
        $this->plantilla->finalize();
	}
	
	public function getMonitores()
	{
		$getParam = (object)$_POST;
				
		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST,'id_item');
		
		//Parametros Table
		
		$data = array();
		
		$data['page'] = $var->page;
		
		$data['rows'] = array();
		
						
		//Filtros
		
		if(isset($getParam->fmGrupoMonitores)) {
			if($getParam->fmGrupoMonitores == 0) {
				$WHERE = "";
			} else {
				$WHERE = "WHERE ITG.`groupid`=".$getParam->fmGrupoMonitores;
			}
		}
		
		//Total Monitorese 
		$monitores_sql_total =	"SELECT count(DISTINCT `id_item`)  as Total
									FROM  `bm_items_groups` ITG ".$WHERE;
					
		$monitores_totales = $this->conexion->queryFetch($monitores_sql_total);
		
		if($monitores_totales) {
			$data['total'] = $monitores_totales[0]['Total'];
		} else {
			$data['total'] = 0;
		}
		
		//Monitores
		
		$monitores_sql =	"SELECT IT.*,ITG.`status`
							FROM `bm_items` IT
							LEFT JOIN `bm_items_groups` ITG USING(`id_item`)
							".$WHERE.
							" GROUP BY ITG.`id_item` $var->sortSql $var->limitSql ";

		$monitores = $this->conexion->queryFetch($monitores_sql);
        
        $CONFIG_MONITOR_EDIT = $this->protect->allowed('CONFIG_MONITOR_EDIT');
        $CONFIG_MONITOR_CLONE = $this->protect->allowed('CONFIG_MONITOR_CLONE');
				
		foreach ($monitores as $keyMonitor => $monitor) {
			
			
			$valores['opciones'] = 
			$datos['id_item'] = $monitor['id_item'];
			$datos['descriptionLong'] = $monitor['descriptionLong'];
			$datos['description'] = $monitor['description'];
			$datos['delay'] = $monitor['delay'];
			$datos['history'] = $monitor['history'];
			$datos['trend'] = $monitor['trend'];
			$datos['type_poller'] = $monitor['type_poller'];
            

            if($monitor['status']) {
                $status = '<a onclick="statusMonitor('.$monitor['id_item'].','.$getParam->fmGrupoMonitores.',true)" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" aria-disabled="false" title="Button on/off"> <span class="ui-button-icon-primary ui-icon ui-icon-power"></span><span class="ui-button-text">'.$this->language->ENABLED.'</span> </a>';
            } else {
                $status = '<a onclick="statusMonitor('.$monitor['id_item'].','.$getParam->fmGrupoMonitores.',false)" class="ui-button ui-widget ui-state-error ui-corner-all ui-button-icon-only" role="button" aria-disabled="false" title="Button on/off"> <span class="ui-button-icon-primary ui-icon ui-icon-power"></span><span class="ui-button-text">'.$this->language->DISABLED.'</span> </a>';
            }

			$datos['status'] = $status;
			
			$option = '<span id="toolbar" class="ui-widget-header ui-corner-all">';
			$option .= '<span id="toolbarSet">';
            
            if($CONFIG_MONITOR_EDIT){
                 $option .='<button id="editMonitor" onclick="editar('.$monitor['id_item'].',false)" name="editMonitor">'.$this->language->EDIT.'</button>';
            }

            if($CONFIG_MONITOR_CLONE){
                 $option .='<button id="clonarMonitor" onclick="editar('.$monitor['id_item'].',true)" name="clonarMonitor">'.$this->language->CLONE.'</button>';
            }							
            
			$option .= '</span></span>';
			$datos['option'] = $option;

			$data['rows'][] = array(
				'id' => $monitor['id_item'],
				'cell' => $datos
			);
			
		}
		$this->basic->jsonEncode($data);
		exit;
	}
	
	private function fixMonitores()
	{
		$clean_item_error_asigg_sql = "DELETE IP FROM `bm_item_profile` IP
	LEFT JOIN `bm_host` H ON H.`id_host`=IP.`id_host`
	LEFT OUTER JOIN `bm_items_groups` IG ON ( IG.`id_item` = IP.`id_item` AND IG.`groupid` = H.`groupid`)
	WHERE IG.id IS NULL AND IP.`id_item` NOT IN  (1,3,4)";
	
		$clean_item_error_asigg_result = $this->conexion->queryFetch($clean_item_error_asigg_sql);
		
		$getMonitores_error_sql = "INSERT INTO `bm_item_profile` (`id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, `nextcheck`) 
					 SELECT IG.`id_item`,H.`id_host`,I.`snmp_oid`, I.`snmp_port`,I.`snmp_community`,NOW() as nextcheck
										FROM `bm_host` H
										LEFT JOIN `bm_items_groups` IG USING(`groupid`) 
										LEFT JOIN `bm_items` I ON  I.`id_item`=IG.`id_item`
										LEFT OUTER JOIN `bm_item_profile` IP ON IP.`id_host`=H.`id_host` AND IP.`id_item`=IG.`id_item`
										WHERE IP.`id_item` IS NULL AND H.`borrado` = 0";
										
		$getMonitores_error_result = $this->conexion->queryFetch($getMonitores_error_sql);
		
	}
	
	private function generaMonitores($iditem,$groupID,$snmp_oid,$snmp_port,$snmp_community) 
	{
		if(is_array($groupID)) {
				
			$groupID = $this->conexion->arrayToIN($groupID);
			
			$getHosts_sql = "SELECT `id_host` FROM `bm_host` WHERE `groupid` IN $groupID;";
			
			$getHosts = $this->conexion->queryFetch($getHosts_sql);
			if($getHosts) {
				$insert_monitor_profile = "INSERT INTO `bm_item_profile` (`id_item`, `id_host`, `snmp_oid`, `snmp_port`, `snmp_community`, `nextcheck`) VALUES ";
				$insert_monitor_value = array();
				foreach ($getHosts as $key => $host) {
					$insert_monitor_value[] = '('.$iditem.','.$host['id_host'].',\''.$snmp_oid.'\',\''.$snmp_port.'\',\''.$snmp_community.'\', NOW())';
				}
				$insert_monitor_profile_f = join(",", $insert_monitor_value);
				$insert_monitor_profile_f = $insert_monitor_profile.$insert_monitor_profile_f." ON DUPLICATE KEY UPDATE `snmp_oid`=VALUES(`snmp_oid`), `snmp_port`=VALUES(`snmp_port`), `snmp_community`=VALUES(`snmp_community`)";
				$result_monitor_profile = $this->conexion->query($insert_monitor_profile_f,true);
				
				$this->plantilla->cacheClean();
				
				if($result_monitor_profile) {
					$this->fixMonitores();
					return true;
				} else {
					return false;
				}
			}

		}
	}

	public function createMonitor() 
	{
		$valida = $this->protect->access_page('CONFIG_MONITOR_NEW');
		if($valida) {
			$getParam = (object)$_POST;
			$result = array();
			
			$result['status'] = true;
			
			foreach ($getParam as $key => $value) {
                $values[$key] = $value;
			}
			
			if(!is_numeric($getParam->id)) {
				/* Creando nuevo Grafico */ 
				
				$this->conexion->InicioTransaccion();
                
                $insertMonitorsDefaultSQL = "INSERT IGNORE INTO `bm_items` (`id_item`, `name`, `description`, `descriptionLong`, `type_item`, `delay`, `history`, `trend`, `type_poller`, `unit`, `snmp_oid`, `snmp_community`, `snmp_port`, `display`, `tags`)
                        VALUES
                            (1, 'Availability.bsw', 'Availability.bsw', 'A.Core - Availability', 'float', 600, 90, 365, 'snmp', ' ', '.1.3.6.1.2.1.1.5.0', 'public', 1161, 'none', NULL),
                            (3, 'DurationOfTheTest.sh', 'DurationOfTheTest.sh', 'A.Core - DurationOfTheTest.sh', 'float', 600, 90, 365, 'bsw_agent', ' ', '', '', 0, 'none', NULL),
                            (4, 'FinishTest', 'FinishTest', 'A.Core - FinishTest', 'float', 600, 90, 365, 'bsw_agent', ' ', '', '', 0, 'none', NULL);
                        ";
                
				$insertMonitorsDefaultRESULT = $this->conexion->query($insertMonitorsDefaultSQL);
                
				$insert_sql = sprintf("INSERT INTO `bm_items` (`name`, `description`, `descriptionLong`, `type_item`, `delay`, `history`, `trend`, `type_poller`, `unit`, `snmp_oid`, `snmp_community`, `snmp_port`, `tags`)
										VALUE ('%s' ,'%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s','%s')",
										$values['description'],
										$values['description'],
										$values['descriptionLong'],
										$values['type_item'],
										$values['delay'],
										$values['history'],
										$values['trend'],
										$values['type'],
										utf8_decode((string)$values['unit']),
										$values['snmp_oid'],
										$values['snmp_community'],
										$values['snmp_port'],
										$values['tags']);
										
				$sql_result = $this->conexion->query($insert_sql);
				$idInsert = $this->conexion->lastInsertId();
				
				if($sql_result) {
							
					if((int)$values['status'] == (int)1) {
						$status = 1;
					} else {
						$status = 0;
					}
							
					$insert_sql = "INSERT INTO `bm_items_groups` (`id_item`, `groupid`, `status`) VALUES";
					
					foreach ($values['groupid'] as  $groupid) {
						$insert_values[] = "($idInsert,$groupid,$status)";
					}
					
					$insert_values = join(',',$insert_values);
					
					$sql_result = $this->conexion->query($insert_sql.$insert_values);
					
					if(!$sql_result) {
						$result['status'] = false;
					} else {
						
						$result_monitor = $this->generaMonitores($idInsert,$values['groupid'],$values['snmp_oid'],$values['snmp_community'],$values['snmp_port']);
						
					}
				}
			} else {
				/* Editando Grafico */ 
				$this->conexion->InicioTransaccion();

				$update_sql = sprintf("UPDATE `bm_items` SET `name` = `description`, `descriptionLong` = '%s', `description` = '%s', `type_item` = '%s', `delay` = '%s', `history` = '%s', `trend` = '%s', `type_poller` = '%s', `unit` = '%s', `snmp_oid` = '%s', `snmp_community` = '%s', `snmp_port` = '%s', `tags` = '%s'  WHERE `id_item` = '%s'",
										$values['descriptionLong'],
										$values['description'],
										$values['type_item'],
										$values['delay'],
										$values['history'],
										$values['trend'],
										$values['type'],
										$values['unit'],
										$values['snmp_oid'],
										$values['snmp_community'],
										$values['snmp_port'],
										$values['tags'],
										$getParam->id);
											
				$update_result = $this->conexion->query($update_sql);
				
				if($update_result) {
					
					$delete_sql = "/* BSW  */ DELETE FROM `bm_items_groups` WHERE `id_item` = '$getParam->id';";
					$delete_result = $this->conexion->query($delete_sql);
					
					if((int)$values['status'] == (int)1) {
						$status = 1;
					} else {
						$status = 0;
					}
							
					$insert_sql = "INSERT INTO `bm_items_groups` (`id_item`, `groupid`, `status`) VALUES";
					
					foreach ($values['groupid'] as  $groupid) {
						$insert_values[] = "($getParam->id,$groupid,$status)";
					}
					
					$insert_values = join(',',$insert_values);
					
					$sql_result = $this->conexion->query($insert_sql.$insert_values);
					
					if(!$sql_result) {
						$result['status'] = false;
					}
				}
			}
		} else {
			$result['status'] = false;
			$result['error'] = $this->language->access_deny;
			echo $this->basic->jsonEncode($result);
			exit;
		}
		
		if($result['status']) {
			if(!is_numeric($getParam->id))  {
				$this->protect->setAudit($_SESSION['uid'],'new_monitor',"Usuario creo el monitor: [$idInsert] ".$values['descriptionLong']);
			} else {
				$this->protect->setAudit($_SESSION['uid'],'edit_monitor',"Usuario edito el monitor: [$getParam->id] ".$values['descriptionLong']);
			}
			$this->conexion->commit();
            $this->tagsCreation();
		} else {
			$this->conexion->rollBack();
		}
		$this->plantilla->cacheClean();
		echo $this->basic->jsonEncode($result);
	}
	
	public function deleteMonitor()
	{
		$valida = $this->protect->access_page('CONFIG_MONITOR_DELETE');
		
		$data['status'] = true ;
		
		if($valida) { 
			$getParam = (object)$_POST;
            
			foreach ($getParam->idSelect as $key => $value) {
				if(is_numeric($value)) {
					
					//Validando 
					
					$select_sql = "SELECT count(*) As Total FROM `bm_items_groups` WHERE `id_item`='$value';";
					
					$select_result = $this->conexion->queryFetch($select_sql);
					
					if($select_result) {
						
						if($select_result[0]['Total'] > 1) {
							$delete_monitor = "DELETE FROM `bm_items_groups` WHERE `id_item` = '$value' AND `groupid` = $getParam->groupid;";
						} else {
							$delete_monitor = "DELETE FROM `bm_items` WHERE `id_item` = '$value';";
						}
					} 
														
					$valida = $this->conexion->query($delete_monitor);
					
					if(!$valida) {
						$data['status'] = false;
					} else {
						$this->protect->setAudit($_SESSION['uid'],'delete_monitor',"Usuario borro el monitor: [$value] del grupo  $getParam->groupid");
					}
				}
			}
		} else {
			$data['status'] = false;
		}
		$this->plantilla->cacheClean();
		echo $this->basic->jsonEncode($data);
	}

	public function statusMonitor()
	{
		$valida = $this->protect->access_page('CONFIG_MONITOR_STATUS');
		
		$data['status'] = true ;
		
		if($valida) { 
			$getParam = (object)$_POST;
			
			$update_item_group = "UPDATE `bm_items_groups` SET `status` = '$getParam->status' WHERE `id_item` = '$getParam->idmonitor' AND `groupid` = '$getParam->groupid';";
			
			$valida = $this->conexion->query($update_item_group);
			
			if(!$valida) {
				$data['status'] = false;
			}
			
		} else {
			$data['status'] = false;
		}

		echo $this->basic->jsonEncode($data);
	}

	public function getMonitorForm()
	{
		if(is_numeric($_POST['IDSelect'])) {
			$IDSelect = $_POST['IDSelect'];
		} else {
			return false;
		}
        
		$valida = $this->protect->access_page('CONFIG_MONITOR_EDIT');
        
        $this->plantilla->loadSec("configuracion/config_monitores_form", $valida,false);
		
		// Detalle Sonda
		
		$monitor_sql = "SELECT * FROM `bm_items`  WHERE `id_item` = $IDSelect";
						
		$monitor_result = $this->conexion->queryFetch($monitor_sql);

		if($monitor_result) {
			$monitor_rows = $monitor_result[0];
			foreach ($monitor_rows as $key => $monitor) {
				$value["monitor_".$key] = $monitor;
			}
		}
		
		if($value["monitor_type_poller"] == 'bsw_agent') {
			$value['option_type_monitor'] = '<option value="1">Snmp</option><option selected value="2">Agente BSW</option>';
		} else {
			$value['option_type_monitor'] = '<option selected value="1">Snmp</option><option value="2">Agente BSW</option>';
		}
			
		//Tipos de datos
		
		$value['option_type_item'] = $this->basic->getOptionValue('type_data',$value["monitor_type_item"]);
		
		//Grupos
		
		$get_grupos_activos = $this->conexion->queryFetch(" SELECT DISTINCTROW `groupid`,  `name`, selected  FROM ( SELECT DISTINCT HG.`groupid` ,HG.`name` , IF(IG.`id_item` IS NULL, '', 'selected') as selected
		FROM `bm_host_groups` HG
		LEFT OUTER JOIN `bm_items_groups` IG ON HG.`groupid`=IG.`groupid`
		WHERE ( IG.`id_item`=$IDSelect OR IG.`id_item` IS NULL ) HAVING selected = 'selected'
				UNION DISTINCT
				SELECT DISTINCT HG.`groupid`,HG.`name` , '' as selected
				FROM `bm_host_groups` HG  ) AS Perro GROUP BY `groupid` ORDER BY selected DESC");
		//$get_grupos_activos = $this->basic->arrayKeyToValue($get_grupos_activos, 'groupid', 'name');
		
		if($get_grupos_activos) {
			$value["menu_groupid_list"] = '';
			foreach ($get_grupos_activos as $key => $grupo) {				
				$value["menu_groupid_list"] .= '<option '.$grupo['selected'].' value="'.$grupo['groupid'].'">'.$grupo['name'].'</option>';
			}
		}
		
		//Status 
		
		if($value["monitor_type_poller"] == 'bsw_agent') {
			$value['option_edit_status'] = '<option value="1">Activo</option><option selected value="2">Desactivado</option>';
		} else {
			$value['option_edit_status'] = '<option selected value="1">Activo</option><option value="2">Desactivado</option>';
		}
		
		$value["lang"] = $this->protect->getLang();
		
		$value["form_id"] = 'config_monitores_form_edit';
        
		$this->plantilla->set($value);
		echo $this->plantilla->get();
	}

	public function cfgPlanes()
	{
	    
        $valida = $this->protect->accessPage('CONFIG_PLAN',FALSE);
        
        $this->plantilla->loadSec("configuracion/config_planes", $valida);
        		
		//Formulario nuevo 
		//Items
		
		$grupos = $this->bmonitor->getAllGroups();

        $valueFormNew["menu_groups_inactive"] = $grupos->formatOption();

		$valueFormNew["planes_id_menu"] = 'planes_groups';
        
		$valueFormNew["config_planes_id_form"] = 'config_planes_form_new';
		
		$valueFormNew["plan_sysctl"] = $this->parametro->get('SYSCTL_DEFAULT');
        
		$valueFormNew["plan_ppp"] = $this->parametro->get('PPP_CONFIG_DEFAULT');
           
        $valueFormNew["lang"] = $this->protect->getLang();  
         
        $value["form_new_plan"] = $this->plantilla->getOne("configuracion/config_planes_form",$valueFormNew);
		
		$grupos_option =  $grupos->formatOption();

		$value["combobox_groups"]  = $grupos_option;
		
		//Tabla
		$value["lang"] = $this->protect->getLang();  
        
        $type = $this->bmonitor->getTypeGroups();
        
        if($type['code'] === 2){
            $value["DISPLAYCOMPLETEFORM"] = 'false';
        } else {
            $value["DISPLAYCOMPLETEFORM"] = 'true';
        }
        
        $button[] = array(
            'name' => 'NEW',
            'bclass' => 'add',
            'protec' => 'CONFIG_PLAN_NEW',
            'onpress' => 'toolboxPlanes'
        );

        $button[] = array(
            'name' => 'DELETE',
            'bclass' => 'delete',
            'protec' => 'CONFIG_PLAN_DELETE',
            'onpress' => 'toolboxPlanes'
        );
                
        $value["button"] = $this->generate->getButton($button);
    
		$this->plantilla->set($value);
		
		$this->plantilla->finalize();
	}
	
	public function getTablePlanes()
	{
		$getParam = (object)$_POST;
		
		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST,'plan');
		
		//Parametros Table
		
		$data = array();
		
		$data['page'] = $var->page;
		
		$data['rows'] = array();
				
		//Filtros
		
		$getGroups_in = $this->bmonitor->getGroupsHost(true,true);
		
		if(isset($getParam->groupid)) {
			if($getParam->groupid == 0) {
				$WHERE = "";
			} else {
				$WHERE = " AND HG.`groupid`=".$getParam->groupid;
			}
		}
		
		//Total rows
		
		$getTotalRows_sql = "SELECT COUNT(*) as Total
				FROM `bm_plan_groups` HG
				WHERE `groupid` IN $getGroups_in AND `borrado`=0 $WHERE";

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);
		
		if($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['Total'];
		else {
			$data['total'] = 0;
		}
		
		//Rows
		
		$getRows_sql =	"SELECT P.`id_plan`, P.`plan`, HG.`name` as groupname
							FROM `bm_plan` P
							LEFT JOIN `bm_plan_groups` PG USING(`id_plan`)
							LEFT JOIN `bm_host_groups` HG ON PG.`groupid`=HG.`groupid`
							WHERE PG.`groupid` IN $getGroups_in AND PG.`borrado`=0  $WHERE $var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);

        $CONFIG_PLAN_CREATE = $this->protect->allowed('CONFIG_PLAN_NEW');
        $CONFIG_PLAN_CLONE = $this->protect->allowed('CONFIG_PLAN_CLONE');
        		
		if($getRows_result) {
			foreach ($getRows_result as $key => $row) {

				$option = '<span id="toolbarSet">';
                
                if($CONFIG_PLAN_CREATE){
				    $option .= '<button id="editPlan" onclick="editar('.$row['id_plan'].',false)" name="editPlan">'.$this->language->EDIT.'</button>';
                }
                if($CONFIG_PLAN_CLONE){
				    $option .= '<button id="clonarPlan" onclick="editar('.$row['id_plan'].',true)" name="clonarPlan">'.$this->language->CLONE.'</button>';
				}
				$option .= '</span>';

				$data['rows'][] = array(
					'id' => $row['id_plan'],
					'cell' => array( 
					   "groupname" => $row['groupname'], 
					   "plan" => $row['plan'],
					   "option" => $option
                    )
				);
			}			
		}
		$this->basic->jsonEncode($data);
		exit;
	}

	public function createPlan() 
	{
		$valida = $this->protect->allowed('CONFIG_PLAN_NEW');
		if($valida) {
			$getParam = (object)$_POST;
			$result = array();
			
			$result['status'] = true;
			
            $valueSet = array(
                    'nacD' => 0,
                    'nacU' => 0,
                    'locD' => 0,
                    'locU' => 0,
                    'intD' => 0,
                    'intU' => 0,
                    'nacDS' => 0,
                    'nacDT' => 0,
                    'nacUS' => 0,
                    'nacUT' => 0,
                    'locDS' => 0,
                    'locDT' => 0,
                    'locUS' => 0,
                    'locUT' => 0,
                    'intDS' => 0,
                    'intDT' => 0,
                    'intUS' => 0,
                    'intUT' => 0
            );

			foreach ($getParam as $key => $value) {
				if(preg_match("/^plan_/i",$key)) {
						$keyName = explode("_", $key,2);
						if(is_numeric($value)){
							$values[$keyName[1]] = (int)$value;
						} elseif (is_array($value)) {
							$values[$keyName[1]] = $value;
						} else {
						    if(isset($valueSet[$keyName[1]])){
						        $value = $valueSet[$keyName[1]];
						    }
                            $values[$keyName[1]] = utf8_encode(trim($value));
						}
						
				}
			}
            
			if(!is_numeric($getParam->id)) {
				/* Creando nuevo Grafico */ 
				
				$this->conexion->InicioTransaccion();
				
				/*$insert_sql = sprintf("INSERT INTO `bm_plan` (`plan`, `plandesc`, `planname`, `nacD`, `nacU`, `locD`, `locU`, `intD`, `intU`, `nacDS`, `nacDT`, `nacUS`, `nacUT`, `locDS`, `locDT`, `locUS`, `locUT`, `intDS`, `intDT`, `intUS`, `intUT`, `sysctl`, `ppp`) VALUE ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
											$values['plan'],
											$values['plandesc'],
											$values['planname'],
											$values['nacD'],
											$values['nacU'],
											$values['locD'],
											$values['locU'],
											$values['intD'],
											$values['intU'],
											$values['nacDS'],
											$values['nacDT'],
											$values['nacUS'],
											$values['nacUT'],
											$values['locDS'],
											$values['locDT'],
											$values['locUS'],
											$values['locUT'],
											$values['intDS'],
											$values['intDT'],
											$values['intUS'],
											$values['intUT'],
											$this->basic->limpiarMetas($values['sysctl']),
											$this->basic->limpiarMetas($values['ppp']));*/
											
				$insert_sql = "INSERT INTO bm_plan (plan, plandesc, planname, tipotecnologia, tecnologia, codigoclase, codigoplans, codigoplanb, nacD, nacU, locD, locU, intD, intU, nacDS, nacDT, nacUS, nacUT, locDS, locDT, locUS, locUT, intDS, intDT, intUS, intUT, sysctl, ppp) 
				VALUE (
				'".$values['plan']."',
				'".$values['plandesc']."',
				'".$values['planname']."',
				'0','0',null,null,null,
				'".$values['nacD']."',
				'".$values['nacU']."',
				'".$values['locD']."',
				'".$values['locU']."',
				'".$values['intD']."',
				'".$values['intU']."',
				'".$values['nacDS']."',
				'".$values['nacDT']."',
				'".$values['nacUS']."',
				'".$values['nacUT']."',
				'".$values['locDS']."',
				'".$values['locDT']."',
				'".$values['locUS']."',
				'".$values['locUT']."',
				'".$values['intDS']."',
				'".$values['intDT']."',
				'".$values['intUS']."',
				'".$values['intUT']."',
				'".$this->basic->limpiarMetas($values['sysctl'])."',
				'".$this->basic->limpiarMetas($values['ppp'])."')";
				//$insert_sql = "INSERT INTO `tabla1` (`campo2`, `campo3`) VALUES ('1111567','5555555')";
									
				//$sql_result = $this->conexion->query($insert_sql, false); 
				//$insert_confQoS = $this->conexion->queryFetch($insert_confQoS_sql);
				$sql_result = $this->conexion->queryFetch($insert_sql);
				
				$idInsert = $this->conexion->lastInsertId();
				$this->logs->error("consulta de insercion nuevo: ".$insert_sql); 
				if(isset($sql_result)) {
					$this->logs->error("hay insercion en tabla bm_plan, ultimo idInsert es: $idInsert");    
					$insert_sql = "INSERT INTO `bm_plan_groups` (`id_plan`, `groupid`, `borrado`) VALUES ";
					
					foreach ($values['groupid'] as  $groupid) {
						$insert_values[] = "($idInsert,$groupid,0)";
					}
					
					$insert_values = join(',',$insert_values);
					
					$sql_result = $this->conexion->query($insert_sql.$insert_values);
					$this->logs->error("consulta de insercion  bm_plan_groups: ".$insert_sql.$insert_values); 
					
					if(!$sql_result) {
					    $this->logs->error("no se inserto en eultima tabla....nooooo");
					    $result['error'] = $this->language->ITERNAL_ERROR;
						$result['status'] = false;
					}
				} else {
                    $this->logs->error("NOOOO hay insercion en tabla bm_plan");	
                    $result['error'] = $this->language->ITERNAL_ERROR;
                    $result['status'] = false;
				}
			} else {
				/* Editando Grafico */ 
				$this->conexion->InicioTransaccion();
				
				$update_sql = sprintf("UPDATE `bm_plan` SET `plan` = '%s', `plandesc` = '%s', `planname` = '%s', `nacD` = '%s', `nacU` = '%s', `locD` = '%s', `locU` = '%s', `intD` = '%s', `intU` = '%s', `nacDS` = '%s', `nacDT` = '%s', `nacUS` = '%s', `nacUT` = '%s', `locDS` = '%s', `locDT` = '%s', `locUS` = '%s', `locUT` = '%s', `intDS` = '%s', `intDT` = '%s', `intUS` = '%s', `intUT` = '%s', `sysctl` = '%s', `ppp` = '%s' WHERE `id_plan` = '$getParam->id'",
						$values['plan'],
						$values['plandesc'],
						$values['planname'],
						$values['nacD'],
						$values['nacU'],
						$values['locD'],
						$values['locU'],
						$values['intD'],
						$values['intU'],
						$values['nacDS'],
						$values['nacDT'],
						$values['nacUS'],
						$values['nacUT'],
						$values['locDS'],
						$values['locDT'],
						$values['locUS'],
						$values['locUT'],
						$values['intDS'],
						$values['intDT'],
						$values['intUS'],
						$values['intUT'],
						$this->basic->limpiarMetas($values['sysctl']),
						$this->basic->limpiarMetas($values['ppp']));
				
				
											
				$update_result = $this->conexion->query($update_sql);
				
				if($update_result) {
					
					$delete_sql = "/* BSW  */ DELETE FROM `bm_plan_groups` WHERE `id_plan` = '$getParam->id';";
					$delete_result = $this->conexion->query($delete_sql);
					
					$insert_sql = "INSERT INTO `bm_plan_groups` (`id_plan`, `groupid`, `borrado`) VALUES";
					
					foreach ($values['groupid'] as  $groupid) {
						$insert_values[] = "($getParam->id,$groupid,0)";
					}
					
					$insert_values = join(',',$insert_values);
					
					$sql_result = $this->conexion->query($insert_sql.$insert_values);
					
					if(!$sql_result) {
					    $result['error'] = $this->language->ITERNAL_ERROR;
						$result['status'] = false;
					}
				}
			}
		} 
		else {
			$result['status'] = false;
			$result['error'] = $this->language->access_deny;
			echo $this->basic->jsonEncode($result);
			exit;
		}
		
		if($result['status']) {
			if(!is_numeric($getParam->id))  {
				$this->protect->setAudit($_SESSION['uid'],'new_pla',"Usuario creo el plan: [$idInsert] ".$values['plan']);
			} else {//es edit.
				//INICIO modificacion automatica de cambios en sonda QoS.				
				$haySondas_enPlan_sql = "SELECT H.id_host, H.`host` from bm_host H WHERE H.`id_plan`= '$getParam->id';"; 				
				$haySondas_enPlan = $this->conexion->queryFetch($haySondas_enPlan_sql); 
				//$this->logs->error("Hay sondas en pLan $haySondas_enPlan_sql y son:".var_dump($haySondas_enPlan));			
				if(isset($haySondas_enPlan)){					
					foreach ($haySondas_enPlan as $key => $valueIdSonda) {
						$idHost=$valueIdSonda['id_host'];
						$nameHost=$valueIdSonda['host'];			
						//////------- llamado a reconfiguración de la sonda en caso de ser de tipo QoS -------//////         
						$esQoS_laSonda_sql = "SELECT G.`name` FROM bm_host_groups G 
											  WHERE G.groupid=(SELECT H.groupid from bm_host H WHERE H.id_host=".$idHost.") 
											  AND G.type='QoS'
											  AND G.borrado=0";
						//$this->logs->error("esQoS_laSonda_sql= $esQoS_laSonda_sql");  
			            $esQoS_laSonda = $this->conexion->queryFetch($esQoS_laSonda_sql);
						if(isset($esQoS_laSonda)){//la sonda es de tipo QoS???
							$insert_confQoS_sql = "INSERT INTO bm_trigger (id_host,fecha_modificacion,type,command,responsable,fecha_consume) 
							VALUES (".$idHost.",NOW(),'config','configure','trigger',NULL)";
				            $insert_confQoS = $this->conexion->queryFetch($insert_confQoS_sql);
							if(isset($insert_confQoS)){//se ingreso el cambio 
								$this->logs->info("Se reportó correctamente el cambio del host (".$idHost." - nombre= ".$nameHost.") en tabla bm_trigger ");
							}							
							else{
								$this->logs->error("No se pudo insertar el cambio del host (".$idHost." - nombre= ".$nameHost.") en tabla bm_trigger ");
							}				
						}			
						//////------- FIN a llamado de reconfiguración de la sonda en caso de ser de tipo QoS -------//////
					}		
				}
				//FIN modificacion automatica de cambios en sonda QoS.							
				$this->protect->setAudit($_SESSION['uid'],'edit_plan',"Usuario edito el plan: [$getParam->id] ".$values['plan']);				
			}
			//aqui se realizan los cambios ya sea nuevo o edit
			$this->conexion->commit();
			
		} else {
			$this->conexion->rollBack();
		}
		$this->plantilla->cacheClean();
        $this->basic->jsonEncode($result);
	}

	public function createPlan2() 
	{
		$valida = $this->protect->access_page('CONFIG_PLAN_NEW');
		if($valida) {
			$getParam = (object)$_POST;
			$result = array();
			
			//Categoriza resultado	
			
			$getParam->form = explode("&", $getParam->cadena_1);
			$group = explode("|", $getParam->cadena_2);

			$monitor = array();
			
			if(($getParam->idValue == 'clone') || (is_numeric($getParam->idValue))) {
				foreach ($getParam->form as $key => $value) {
					$form = explode('=',$value);
					if(preg_match("/^plan_edit_/i",$form[0])) {
						$keyName = explode("_edit_", $form[0],2);
						$monitor[$keyName[1]] = $form[1];
					}
				}				
			} else {
				foreach ($getParam->form as $key => $value) {
					$form = explode('=',$value);
					if(preg_match("/^plan_/i",$form[0])) {
						$keyName = explode("_", $form[0],2);
						$monitor[$keyName[1]] = $form[1];
					}
				}				
			}
			
			$this->conexion->InicioTransaccion();
			
			if(($getParam->idValue == 'clone') || (!is_numeric($getParam->idValue)))  {
				//Generando nuevo registro
				$insert_plan = sprintf("INSERT INTO `bm_plan` (`plan`, `plandesc`, `planname`, `nacD`, `nacU`, `locD`, `locU`, `intD`, `intU`, `nacDS`, `nacDT`, `nacUS`, `nacUT`, `locDS`, `locDT`, `locUS`, `locUT`, `intDS`, `intDT`, `intUS`, `intUT`, `sysctl`, `ppp`)
											VALUE ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
											$this->basic->limpiarMetas($monitor['plan']),
											$this->basic->limpiarMetas($monitor['plandesc']),
											$this->basic->limpiarMetas($monitor['planname']),
											$monitor['nacD'],
											$monitor['nacU'],
											$monitor['locD'],
											$monitor['locU'],
											$monitor['intD'],
											$monitor['intD'],
											$monitor['nacDS'],
											$monitor['nacDT'],
											$monitor['nacUS'],
											$monitor['nacUT'],
											$monitor['locDS'],
											$monitor['locDT'],
											$monitor['locUS'],
											$monitor['locUT'],
											$monitor['intDS'],
											$monitor['intDT'],
											$monitor['intUS'],
											$monitor['intUT'],
											$this->basic->limpiarMetas($monitor['sysctl']),
											$this->basic->limpiarMetas($monitor['ppp']));
				$plan_result = $this->conexion->query($insert_plan);
				$idPlan = $this->conexion->lastInsertId();
			} else {
				//Actualizando registro
				$update_sql = sprintf("UPDATE `bm_plan` SET `plan` = '%s', `plandesc` = '%s', `planname` = '%s', `nacD` = '%s', `nacU` = '%s', `locD` = '%s', `locU` = '%s', `intD` = '%s', `intU` = '%s', `nacDS` = '%s', `nacDT` = '%s', `nacUS` = '%s', `nacUT` = '%s', `locDS` = '%s', `locDT` = '%s', `locUS` = '%s', `locUT` = '%s', `intDS` = '%s', `intDT` = '%s', `intUS` = '%s', `intUT` = '%s', `sysctl` = '%s', `ppp` = '%s' WHERE `id_plan` = '$getParam->idValue'",
				$monitor['plan'],
				$monitor['plandesc'],
				$monitor['planname'],
				$monitor['nacD'],
				$monitor['nacU'],
				$monitor['locD'],
				$monitor['locU'],
				$monitor['intD'],
				$monitor['intD'],
				$monitor['nacDS'],
				$monitor['nacDT'],
				$monitor['nacUS'],
				$monitor['nacUT'],
				$monitor['locDS'],
				$monitor['locDT'],
				$monitor['locUS'],
				$monitor['locUT'],
				$monitor['intDS'],
				$monitor['intDT'],
				$monitor['intUS'],
				$monitor['intUT'],
				$this->basic->limpiarMetas($monitor['sysctl']),
				$this->basic->limpiarMetas($monitor['ppp']));
				
				$plan_result = $this->conexion->query($update_sql);
				$idPlan = $getParam->idValue;
			}

			if($plan_result) {
				$result['status'] = true;
				
				foreach ($group as $value) {
					list($order,$groupId) = explode(":", $value);
					
					$insert_plan_group = sprintf("INSERT INTO `bm_plan_groups` (`id_plan`, `groupid`, `borrado`)
													VALUES (%s, %s, 0)",
													$idPlan,
													$groupId);
					
					$plan_group_result = $this->conexion->query($insert_plan_group,true);
					
					if(!$plan_group_result) {
						$result['status'] = false;
					}
				}
			} else {
				$result['status'] = false;
			}	
		} else {
			$result['status'] = false;
			$result['error'] = $this->language->access_deny;
			echo $this->basic->jsonEncode($result);
			exit;
		}
		
		if($result['status']) {
			if(($getParam->idValue == 'clone') || (!is_numeric($getParam->idValue)))  {
				$this->protect->setAudit($_SESSION['uid'],'create_plan',"Usuario creo el plan: [$idPlan] ".$monitor['plan']);
			} else {
				$this->protect->setAudit($_SESSION['uid'],'edit_plan',"Usuario edito el plan: [$idPlan] ".$monitor['plan']);
			}
			$this->conexion->commit();
			$this->plantilla->cacheClean();
		} else {
			$this->conexion->rollBack();
		}
		
		echo $this->basic->jsonEncode($result);
	}

	public function deletePlan()
	{
	    $getParam = (object)$_POST;
    
	    if(!isset($getParam->idSelect) && !isset($getParam->groupID)){
	        $data['status'] = false;
            $data['error'] = $this->language->ERROR_INVALID_PARAM;
            $this->basic->jsonEncode($data);
            exit;
	    }
	    
		$valida = $this->protect->allowed('CONFIG_PLAN_DELETE');
		
		$data['status'] = true ;
		
		if($valida) { 
			
			
			foreach ($getParam->idSelect as $key => $value) {
				if(is_numeric($value)) {
					
					//Validando Plan:
					
					$get_plan_active_host_sql = "SELECT count(*) as Total FROM `bm_host` WHERE `id_plan` = '$value' AND `borrado`=0";
					
					$get_plan_active_host = $this->conexion->queryFetch($get_plan_active_host_sql);
					
					$get_plan_active_host = (int)$get_plan_active_host[0]['Total'];
					
					if($get_plan_active_host === 0) {
						$delete_sql = "UPDATE `bm_plan_groups` SET `borrado` = '1' WHERE `id_plan` = '$value' AND `groupid` = '$getParam->groupID';";
											
						$valida = $this->conexion->query($delete_sql);		
						
						if(!$valida) {
							$data['status'] = false;
						}else {
							$this->protect->setAudit($_SESSION['uid'],'delete_plan',"Usuario borro el plan: [$value] del grupo $getParam->groupID");
						}
									
					} else {
						$data['status'] = false;
						$data['error'] = $this->language->SONDA_DELETE_BLOCK_HOST;
					}
				}
			}
		} else {
			$data['status'] = false;
			$data['error'] = $this->language->access_deny;
		}
		$this->plantilla->cacheClean();
		$this->basic->jsonEncode($data);
		exit;
	}

	public function getPlanForm()
	{
		if(is_numeric($_POST['IDSelect'])) {
			$IDSelect = $_POST['IDSelect'];
		} else {
			return false;
		}

		$valida = $this->protect->allowed('CONFIG_PLAN_NEW');
		
        $this->plantilla->loadSec("configuracion/config_planes_form", $valida);

		// Detalle Sonda
		
		$select_sql = "SELECT * FROM `bm_plan`  WHERE `id_plan` = $IDSelect";
						
		$select_result = $this->conexion->queryFetch($select_sql);

		if($select_result) {
			$select_rows = $select_result[0];
			foreach ($select_rows as $key => $row) {
				$value["plan_".$key] = utf8_encode($row);
			}
		}
		   
        $value["lang"] = $this->protect->getLang();  

		//Grupos
		
		$group_permit_user = $this->bmonitor->getGroupsHost(true,true);
		
		$get_grupos_active = $this->conexion->queryFetch("SELECT  DISTINCT HG.`groupid` , HG.`name` , 'selected' as selected
				FROM `bm_host_groups` HG
				LEFT OUTER JOIN `bm_plan_groups` PG ON HG.`groupid`=PG.`groupid` WHERE HG.`groupid` IN $group_permit_user AND PG.`id_plan` = $IDSelect");
				
		$group_select = $get_grupos_active[0]['groupid'];
				
		$get_grupos_inactive = $this->conexion->queryFetch("SELECT  HG.`groupid` , HG.`name`, '' as selected FROM `bm_host_groups` HG WHERE HG.`groupid` IN $group_permit_user AND HG.`groupid` != $group_select");
		
		//$get_grupos_activos = $this->basic->arrayKeyToValue($get_grupos_activos, 'groupid', 'name');
		
		$get_grupos = array_merge($get_grupos_active,$get_grupos_inactive);
		
		if($get_grupos) {
			$value["menu_groups_inactive"] = '';
			foreach ($get_grupos as $key => $grupo) {				
				$value["menu_groups_inactive"] .= '<option '.$grupo['selected'].' value="'.$grupo['groupid'].'">'.$grupo['name'].'</option>';
			}
		}
		
		$value["planes_id_menu"] = 'planes_groups_edit';
		$value["config_planes_id_form"] = 'config_planes_form_edit';
		
		//Ocultando cosas hho jojo
		if($_SESSION['name'] != 'Administrator') {
			$value["plan_sysctl_disable"] = 'disabled="disabled"';
			$value["plan_ppp_disable"]  = 'disabled="disabled"';	
		}
		
		$this->plantilla->set($value);
		$this->plantilla->finalize();
	}

	/*******
	 * 
	 * Grupos
	 * 
	 */
	
	public function cfgGrupos()
	{
		$valida = $this->protect->access_page('CONFIG_GROUPS',FALSE);
		
		if($valida->redirect == TRUE){
			header("HTTP/1.1 302 Found");
			echo "Session close";
			exit;
		}
        
        $this->plantilla->loadSec("configuracion/config_groups", $valida->access);
		
		$value = array();
		
		//Formulario nuevo 
		
		$select_sql = 'SELECT  `id_template`, `name` FROM `bm_template_config` ';
		$select_result = $this->conexion->queryFetch($select_sql);
		$valueForm["option_template"] = $this->basic->getOption($select_result, 'id_template', 'name');
		
		//$valueForm["option_type"] = $this->basic->getOptionValue('type_group','select');
		
		$optionType = array( 0 => array( "type" => 'QoE' , 'value' => 'QoE'), 1 =>  array( "type" => 'QoS' , 'value' => 'QoS'), 2 =>  array( "type" => 'QoE Mobile' , 'value' => 'QoE Mobile'));
		
		$valueForm["option_type"] = $this->basic->getOption($optionType, 'type', 'value');
		
		$valueForm["form_id_group"] = 'form_new_group';
		
		$value["form_new_group"] = $this->plantilla->getOne("configuracion/config_groups_form",$valueForm);
		
        $button[] = array(
            'name' => 'NEW',
            'bclass' => 'add',
            'protec' => 'CONFIG_GROUPS_NEW',
            'onpress' => 'toolboxGroups'
        );

        $button[] = array(
            'name' => 'DELETE',
            'bclass' => 'delete',
            'protec' => 'CONFIG_GROUPS_DELETE',
            'onpress' => 'toolboxGroups'
        );
                
        $value["button"] = $this->generate->getButton($button);

		$this->plantilla->set($value);
		
		$this->plantilla->finalize();
	}
	
	public function getTableGroups()
	{
		$getParam = (object)$_POST;
		
		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST,'name');
		
		//Parametros Table
		
		$data = array();
		
		$data['page'] = $var->page;
		
		$data['rows'] = array();
		
				
		//Filtros
		
		$getGroups_in = $this->bmonitor->getGroupsHost(true,true);
		//$this->logs->error("getGroups: ".$getGroups_in);		
		
		//Total rows
		
		$getTotalRows_sql = "SELECT COUNT(*) as Total
				FROM `bm_host_groups` HG
				WHERE HG.`groupid` IN $getGroups_in AND HG.`borrado`=0";

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);
		
		if($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['Total'];
		else {
			$data['total'] = 0;
		}
		
		//Rows
		
		$getRows_sql =	"SELECT * FROM `bm_host_groups` HG 
							WHERE HG.`groupid` IN $getGroups_in AND HG.`borrado`=0 $var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);

        $CONFIG_GROUPS_EDIT = $this->protect->allowed('CONFIG_GROUPS_EDIT');
        $CONFIG_GROUPS_CLONE = $this->protect->allowed('CONFIG_GROUPS_CLONE');
        		
		if($getRows_result) {
			foreach ($getRows_result as $key => $row) {
				/*  Aqui no mostramos los botones de Editar y clonar.  --- cambio para bmonitor2.5
				$option = '<span id="toolbarSet">';
                if($CONFIG_GROUPS_EDIT){
                   $option .= '<button id="editGroup" onclick="editar('.$row['groupid'].',false)" name="editGroup">'.$this->language->EDIT.'</button>'; 
                }
				if($CONFIG_GROUPS_CLONE){
				   $option .= '<button id="clonarGroup" onclick="editar('.$row['groupid'].',true)" name="clonarGroup">'.$this->language->CLONE.'</button>';
				}
				$option .= '</span>';  
				*/
				$data['rows'][] = array(
					'id' => $row['groupid'],
					'cell' => array("name" => $row['name'], "type" => $row['type'] , "option" => $option)
				);
			}			
		}
		$this->basic->jsonEncode($data);
		exit;
	}
	/*
	public function createGroup() 
	{
		$valida = $this->protect->access_page('CONFIG_CREATE_GROUP');
		if($valida) {
			$getParam = (object)$_POST;
			$result = array();
			
			//Categoriza resultado	
			
			$monitor = array();
						
			
			$this->conexion->InicioTransaccion();
			
			if((!is_numeric($getParam->id)))  {
				//Generando nuevo registro
				$insert_sql = sprintf("INSERT INTO `bm_host_groups` (`name`, `type`, `snmp_monitor`, `timezone`)
											VALUE ('%s', '%s', '%s', '%s')",
											$getParam->group_name,
											$getParam->group_type,
											$getParam->group_snmp_monitor,
											$getParam->group_timezone
											);
				$sql_result = $this->conexion->query($insert_sql);
				$idInsert = $this->conexion->lastInsertId();
			} else {
				//Actualizando registro
				$update_sql = sprintf("UPDATE `bm_host_groups` SET `name` = '%s', `type` = '%s', `snmp_monitor` = '%s', `timezone` = '%s' WHERE `groupid` = '$getParam->id'",
				$getParam->group_name,
				$getParam->group_type,
				$getParam->group_snmp_monitor,
				$getParam->group_timezone);
				
				$sql_result = $this->conexion->query($update_sql);
				$idInsert = $getParam->idValue;
			}

			if($sql_result) {
				$result['status'] = true;
			} else {
				$result['status'] = false;
			}	
		} else {
			$result['status'] = false;
			$result['error'] = $this->language->access_deny;
			echo $this->basic->jsonEncode($result);
			exit;
		}
		
		if($result['status']) {
			if(($getParam->idValue == 'clone') || (!is_numeric($getParam->idValue)))  {
				$this->protect->setAudit($_SESSION['uid'],'create_group',"Usuario creo el grupo: [$idInsert] ".$getParam->group_name);
			} else {
				$this->protect->setAudit($_SESSION['uid'],'edit_group',"Usuario edito el grupo: [$getParam->id] ".$getParam->group_name);
			}
			$this->conexion->commit();
		} else {
			$this->conexion->rollBack();
		}
		
		echo $this->basic->jsonEncode($result);
	}*/

	public function createGroup()
	{
		$getParam = (object)$_POST;
		
		$valida = $this->protect->access_page('CONFIG_GROUPS_NEW');

		$result['status'] = true;
		
		if($valida) {
			$getParam = (object)$_POST;
			
			//$this->logs->error(" ESTE es el Arreglo que viene como POST --- nueva FUNCIÓN");
			/*foreach ($getParam as $key => $value) {
					$this->logs->error(" $key : ".$value);	
			}*/
			
			if(!is_numeric($getParam->id)) { //si no tengo id es un nuevo grupo 
				//$this->logs->error(" ES un numero el parametro");		
				foreach ($getParam as $key => $value) {
					if(preg_match("/^template_/i",$key) || preg_match("/^group_/i",$key)) {                       
                        
						$keyName = explode("_", $key,2);
                        
                        if($keyName[1] == 'item') {
                            $config[$keyName[1]] = $value;
                            continue;
                        }
                        
						$column[] = '`'.$keyName[1].'`';
					
						$value = "'".$value."'";
						
						$valueC[] = $value;
						$config[$keyName[1]] = $value;
						
					} 
				}
                
                if(!is_numeric($getParam->idClone)) {
                    //$this->logs->error(" ES un clon el parametro getParam->idClone: ".$getParam->idClone);
                    $config['item'] = 1;
                }
				//$this->logs->error("NO es clon"); 
				$column = join(',', $column);
				$valueC = join(',', $valueC);
                //estaba antes, pero habia un problema al crear una nueva sonda con otros grupos que no sean de tipo QoS. 
                /*if($getParam->group_type == 'QoS') {
                    $column = $column.',`profile_active`';
                    $valueC = $valueC.',1';
                } else {
                    $column = $column.',`profile_active`';
                    $valueC = $valueC.',0';                     
                }*/
                //al ser profile_active=1 se activa para ingresar nuevos agentes
                $column = $column.',`profile_active`';
                $valueC = $valueC.',1';
				//antes de insertar en bm_host_group
				//$this->logs->error(" las columnas arrary: ".$column);			
				//$this->logs->error(" los values arrary: ".$valueC);
						
				//OJO. Esta tabla puede ingresar solo type=(NEUTRALIDAD,ENLACES,QoE,QoE Movile,QoS) ya que estos valores estan a fierro en la misma tabla bm_host_groups campo type
				//si desea crear otro grupo con cualquier nombre no se mostrara correctamente 
				$insert_sql = 'INSERT INTO `bm_host_groups` ('.$column.') VALUES ('.$valueC.');';
				//$this->logs->error("insert de nuevo grupo bm_host_groups: $insert_sql");
				$valida = $this->conexion->query($insert_sql);                
                
				if(!$valida) {
					$result['status'] = false;
					$this->logs->error("error valida se corto secuencia. result=false /apps/controlador/config.php function createGroup...");
				} else {
				    //$this->logs->error("es valido todo continuo1..."); 
                    $idNewGroups = $this->conexion->lastInsertId();				    
                    //Permisos
                    //$this->logs->error("  inserto en bm_user_host_group... sesion[id_group]: ".$_SESSION['id_group']." ,groupid: ". $idNewGroups);
                    $insertPermHostGroupSQL ="INSERT INTO `bm_user_host_group` ( `id_group`, `groupid`)
                            VALUES
                                ( ".$_SESSION['id_group'].", $idNewGroups);";
                    //$this->logs->error("permisos en bm_user_host_group: ".$insertPermHostGroupSQL);
                    $valida = $this->conexion->query($insertPermHostGroupSQL);
                    //esto ya no funciona para nada... la saque y esta documentado en Montado Migracion bMonitor2.5.
                   // son 3 registros inservibles, lo unico que deben estar antes en la tabla 
                    /*if($valida) {
						$this->logs->error("es valida inserto en bm_items_groups...");                    			
                    	$this->conexion->query("INSERT INTO `bm_items_groups` (`id`, `id_item`, `groupid`, `status`) VALUES (NULL, '1', '29', '1'),(NULL, '3', '29', '1'),(NULL, '4', '29', '1')");
                        $this->bmonitor->unsetVarCache(); 
                    }*/
                    
                    switch ($config['item']) {
                        case 1:
                            $result['items']  = 'No se genero los item';
                            break;
                        case 2:
                                                      
                            $insertItems = "INSERT INTO `bm_items_groups` (`id_item`,`groupid`) 
                                                    SELECT I.`id_item` , '$idNewGroups' as 'groupid'  
                                                    FROM `bm_items` I LEFT JOIN `bm_items_groups` IG ON I.`id_item`=IG.`id_item` 
                                                    WHERE IG.`groupid`=".$getParam->idClone;
                            //$this->logs->error("compartiendo items_2: ".$insertPermHostGroupSQL);
                            $insertItemsRESULT = $this->conexion->query($insertItems);                            
                            if($insertItemsRESULT){
                               $result['items']  = 'Compartiendo items : OK'; 
                            } else {
                               $result['items']  = 'Compartiendo items : NOK';
                            }
                            
                            break;
                        case 3:
                                                        
                            $insertItems = "INSERT INTO `bm_items` 
                            ( `name`,`description`,`descriptionLong`,`type_item`,`delay`,`history`,`trend` ,`type_poller`,`unit` ,`snmp_oid` ,`snmp_community`,`snmp_port`,`display`,  `tags` )
                            SELECT `name`,`description`,`descriptionLong`,`type_item`,`delay`,`history`,`trend` ,`type_poller`,`unit` ,`snmp_oid` ,`snmp_community`,`snmp_port`,`display`,  `tags` 
                            FROM `bm_items` I LEFT JOIN `bm_items_groups` IG ON I.`id_item`=IG.`id_item` WHERE IG.`groupid`=".$getParam->idClone;
							//$this->logs->error("insert items_3: ".$insertItems);                                                    
                            $insertItemsRESULT = $this->conexion->insert($insertItems);
                            $insertItems = "INSERT INTO `bm_items_groups` (`id_item`,`groupid`) 
                                                    SELECT I.`id_item` , '$idNewGroups' as 'groupid'  
                                                    FROM `bm_items` I LEFT JOIN `bm_items_groups` IG ON I.`id_item`=IG.`id_item` 
                                                    WHERE I.`id_item` >= ".$insertItemsRESULT;
							//$this->logs->error("insert bm_items_groups_3): ".$insertItemsRESULT);                                                    
                            $insertItemsRESULT = $this->conexion->query($insertItems);                      
                            
                            if($insertItemsRESULT){
                               $result['items']  = 'Clonando items : OK'; 
                            } else {
                               $result['items']  = 'Clonando items : NOK';
                            }
                            
                            break;                                          
                        default:
                            $result['items']  = 'No se genero los item';
                            break;
                    }

                }

			} else {  //tengo id es un update de grupo 
				
				foreach ($getParam as $key => $value) {
					if(preg_match("/^template_/i",$key) || preg_match("/^group_/i",$key)) {
						$keyName = explode("_", $key,2);
                        if($keyName[1] != 'item') {
                            $update[] = '`'.$keyName[1].'` = '."'".$value."'"; 
                        }
					}
				}
                
                if(!isset($getParam->group_snmp_monitor)) {
                    $update[] = '`snmp_monitor` = '."'false'"; 
                }
						
				$update_sql = "UPDATE `bm_host_groups` SET ".join(',', $update)." WHERE `groupid` = '$getParam->id';";
				
				$update_result = $this->conexion->query($update_sql);
				
				if(!$update_result) {
					$result['status'] = false;
				}		
			}	
		}
		
        $this->bmonitor->unsetVarCache();
		$this->plantilla->cacheClean();
        
		$this->basic->jsonEncode($result);
		exit;
	}

	public function deleteGroups()
	{
		$valida = $this->protect->access_page('CONFIG_GROUPS_DELETE');
		
		$data['status'] = true ;
		
		if($valida) { 
			$getParam = (object)$_POST;
			
			foreach ($getParam->idSelect as $key => $value) {
				if(is_numeric($value)) {
					
					//Validando Plan:
					
					$get_active_host_sql = "SELECT count(*) as Total FROM `bm_host` H WHERE H.`groupid` = '$value' AND H.`borrado`=0";
					
					$get_active_host = $this->conexion->queryFetch($get_active_host_sql);
					
					$get_active_host = (int)$get_active_host[0]['Total'];
					
					if($get_active_host === 0) {
						$delete_sql = "UPDATE `bm_host_groups` SET `borrado` = '1' WHERE `groupid` = '$value';";
											
						$valida = $this->conexion->query($delete_sql);		
						
						if(!$valida) {
							$data['status'] = false;
						}else {
							$this->protect->setAudit($_SESSION['uid'],'delete_groups',"Usuario borro el grupo: [$value]");
						}
									
					} else {
						$data['status'] = false;
						$data['error'] = $this->language->GRUPO_DELETE_BLOCK_HOST;
					}
				}
			}
		} else {
			$data['status'] = false;
			$data['error'] = $this->language->access_deny;
		}
        $this->bmonitor->unsetVarCache();
		$this->plantilla->cacheClean();
		$this->basic->jsonEncode($data);
        exit;
	}

	public function getFormTemplate()
	{
		$getParam = (object)$_POST;
		
		$this->plantilla->load('configuracion/config_groups_template');
		
		$select_sql = 'select * FROM `bm_template_config` WHERE `id_template` = '.$getParam->id_template;
		$select_result = $this->conexion->queryFetch($select_sql);

		foreach ($select_result[0] as $key => $graph) {
			$value['template_'.$key] = $graph;
		}

		$value["form_id_template"] = 'form_edit_template';
		
		$this->plantilla->set($value);
			
		$data['datos'] = $this->plantilla->get();
		$data['status'] = true;
		
		echo $this->basic->jsonEncode($data);
	}
	
	public function getGroupForm()
	{
		if(is_numeric($_POST['IDSelect'])) {
			$IDSelect = $_POST['IDSelect'];
		} else {
			return false;
		}

        $valida = $this->protect->access_page('CONFIG_GROUPS_EDIT');
        $this->plantilla->loadSec("configuracion/config_groups_form", $valida);
        				
		$value["form_id_group"] = 'form_edit_group';
		
		// Detalle Grupo
		
		$monitor_sql = "SELECT HG.*,TC.`name` AS Template FROM `bm_host_groups` HG  LEFT JOIN `bm_template_config` TC USING(`id_template`) WHERE HG.`groupid` = $IDSelect";
						
		$monitor_result = $this->conexion->queryFetch($monitor_sql);

		if($monitor_result) {
			$monitor_rows = $monitor_result[0];
			foreach ($monitor_rows as $key => $monitor) {
				$value["group_".$key] = $monitor;
			}
		}
		
		/*
		$input = "<option value='0'>Default</option>\n";
		for ($i=-11; $i < 12; $i++) {
			$val = "UTC".$i;
			if($val == 'UTC0') {
				$val = 'UTC';
			}
			if($i > 0) {
				$val = "UTC+".$i;
			}
			
			$value["group_timezone"];
			if((string)$value["group_timezone"] == (string)$val) {
				$input .= "<option selected value='$val'>$val</option>\n";
			} else {
				$input .= "<option value='$val'>$val</option>\n";
			}
			
		} */
		
		//Plantillas
		
	
		// Value Plantilla

		foreach ($monitor_rows as $key => $graph) {
			$valueTemplate['template_'.$key] = $graph;
		}
		
        $config_groups_template = $this->plantilla->getOne("configuracion/config_groups_template",$valueTemplate);
        
	
		
		$value["tabs_new_group_tabs_2_menu"] = '<li><a href="#new_group_tabs_2">'.$monitor_rows['type'].'</a></li>';
		
		$value["tabs_new_group_tabs_2"] = '<div id="new_group_tabs_2">'.$config_groups_template.'</div>';
		
		
		//Op Grupos
		
		$value["option_template"] = '<option value="'.$valueTemplate['template_id_template'].'">'.$valueTemplate['template_Template'].'</option>';
		
		$value["option_type"] = $this->basic->getOptionValue('type_group',$value["group_type"]);		

		
		if($value["group_snmp_monitor"] === 'true') {
			$value["group_snmp_monitor"] = 'checked';
		} else {
			$value["group_snmp_monitor"] = '';
		}
		

		$this->plantilla->set($value);
		$this->plantilla->finalize();
	}

	/*******
	 * 
	 * Graficos 
	 * 
	 */
	 
	public function cfgGraph()
	{
        $valida = $this->protect->access_page('CONFIG_GRAPH');

        $this->plantilla->loadSec("configuracion/config_graph", $valida, 86000);

        $grupos = $this->bmonitor->getAllGroups();
        
        $valueFormNew["optionGroupid"] = $grupos->formatOption();
    
		//Items
		
        $valueFormNew["menu_items_inactive"] = $this->bmonitor->getAllItems($grupos->firstValue())->formatOption();
	
		
		$valueFormNew["graph_id_menu"] = 'graph_items';
        $valueFormNew["graph_groupsid"] = 'graph_groupsid';
        
		$valueFormNew["config_graph_id_form"] = 'config_graph_form';
        

        $valueFormNew["lang"] = $this->protect->getLang();

        $value["form_new_graph"] = $this->plantilla->getOne("configuracion/config_graph_form",$valueFormNew); 
        
        $value["option_group"] = $grupos->formatOption();
		
        $value["lang"] = $this->protect->getLang();

        $button[] = array(
            'name' => 'NEW',
            'bclass' => 'add',
            'protec' => 'CONFIG_GRAPH_NEW',
            'onpress' => 'toolboxGraph'
        );

        $button[] = array(
            'name' => 'DELETE',
            'bclass' => 'delete',
            'protec' => 'CONFIG_GRAPH_DELETE',
            'onpress' => 'toolboxGraph'
        );
        
        $value["button"] = $this->generate->getButton($button);
                
        $this->plantilla->set($value);
        $this->plantilla->finalize();
	}

	public function getTableGraph()
	{
		$getParam = (object)$_POST;
		
		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST,'name');
		
		//Parametros Table
		
		$data = array();
		
		$data['page'] = $var->page;
		
		$data['rows'] = array();
		
		//Total rows
		
		$getTotalRows_sql = "SELECT COUNT(  G.`groupid`) as 'Total' FROM `bm_graphs` G
                        LEFT OUTER JOIN  `bm_host_groups`  HG ON G.`groupid`=HG.`groupid` 
                                  WHERE G.`groupid` IS NULL  OR G.`groupid` = $getParam->groupid";

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);
		
		if($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['Total'];
		else {
			$data['total'] = 0;
		}
		
		//Rows
		
		$getRows_sql =	"SELECT HG.`name` AS groupname  ,G.* FROM `bm_graphs` G LEFT JOIN `bm_host_groups`  HG ON G.`groupid`=HG.`groupid` 
		                          WHERE G.`groupid` IS NULL  OR G.`groupid` = $getParam->groupid $var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);
        
        $CONFIG_GRAPH_NEW = $this->protect->allowed('CONFIG_GRAPH_NEW');
		
		if($getRows_result) {
			foreach ($getRows_result as $key => $row) {
			    $option = '';    
			    if($CONFIG_GRAPH_NEW){
				    $option = '<span id="toolbarSet"><button id="editGraph" onclick="$.editGraph('.$row['id_graph'].',false)" name="editGraph">'.$this->language->EDIT.'</button></span>';
                }
				$data['rows'][] = array(
					'id' => $row['id_graph'],
					'cell' => array($row['groupname'],$row['name'],$row['graphtype'],$option)
				);
			}
		}
        $this->basic->jsonEncode($data);
		exit;
	}

    public function getConfigItems( $groupid ) {
        $return['status'] = false;
        if(is_numeric($groupid)) {
            $return['result'] = $this->bmonitor->getAllItems($groupid)->formatOption();
            $return['status'] = true;
        }
        $this->basic->jsonEncode($return);
        exit ;
    }

	public function createGraph() 
	{
		$valida = $this->protect->access_page('CONFIG_GRAPH_NEW');
		if($valida) {
			$getParam = (object)$_POST;
			$result = array();
			
			$result['status'] = false;
			
			if(!is_numeric($getParam->id)) {
				/* Creando nuevo Grafico */ 
				
				$this->conexion->InicioTransaccion();
				
				$insert_sql = sprintf("INSERT INTO `bm_graphs` (`name`, `width`, `height`, `graphtype`,`groupid`)
											VALUE (%s, '%s', '%s', '%s', '%s')",
											$this->conexion->quote(trim($getParam->name)),
											0,
											0,
											$getParam->graphtype,
                                            $getParam->groupid);
											
				$sql_result = $this->conexion->query($insert_sql);
				$idInsert = $this->conexion->lastInsertId();
				
				if($sql_result) {
						
					$insert_sql = 'INSERT INTO `bm_graphs_items` (`id_graph`, `id_item`) VALUES  ';
					
					$insert_value = array();
					
					
					foreach ($getParam->items as $key => $items) {
						$insert_value[] = "($idInsert,$items)";
					}
				
					$insert_value = join(',',$insert_value);
					
					$sql_result = $this->conexion->query($insert_sql.$insert_value);
					
					if($sql_result) {
						$result['status'] = true;
					} else {
					    $result['error'] = $this->language->ERROR_INSERT_OR_EDIT_DB;
					}
				} else {
				    $result['error'] = $this->language->ERROR_INSERT_OR_EDIT_DB;
				}
			} else {
				/* Editando Grafico */ 
				$this->conexion->InicioTransaccion();
				
				$update_sql = sprintf(/* BSW */ "UPDATE `bm_graphs` SET `name` = %s, `width` = '0', `height` = '0',  `graphtype` = '%s' , `groupid` = '%s' WHERE `id_graph` = '%s'",
							$this->conexion->quote(trim($getParam->name)),
							$getParam->graphtype,
							$getParam->groupid,
							$getParam->id);
											
				$update_result = $this->conexion->query($update_sql);
				
				if($update_result) {
					
					$delete_sql = "/* BSW  */ DELETE FROM `bm_graphs_items` WHERE `id_graph` = '$getParam->id';";
					$delete_result = $this->conexion->query($delete_sql);
					
					$insert_sql = 'INSERT INTO `bm_graphs_items` (`id_graph`, `id_item`) VALUES  ';
					
					$insert_value = array();
					
					foreach ($getParam->items as $key => $items) {
						$insert_value[] = "($getParam->id,$items)";
					}
				
					$insert_value = join(',',$insert_value);
					
					$sql_result = $this->conexion->query($insert_sql.$insert_value);
					
					if($sql_result) {
						$result['status'] = true;
					} else {
					    $result['error'] = $this->language->ERROR_INSERT_OR_EDIT_DB;
					}
				} else {
					$result['error'] = $this->language->ERROR_INSERT_OR_EDIT_DB;
				}
			}
		} else {
			$result['status'] = false;
			$result['error'] = $this->language->access_deny;
			echo $this->basic->jsonEncode($result);
			exit;
		}
		
		if($result['status']) {
			if(!is_numeric($getParam->id))  {
				$this->protect->setAudit($_SESSION['uid'],'new_graph',"Usuario creo el grafico: [$idInsert] ".$getParam->graph_name);
			} else {
				$this->protect->setAudit($_SESSION['uid'],'edit_graph',"Usuario edito el grafico: [$getParam->id] ".$getParam->graph_name);
			}
			$this->conexion->commit();
			$this->plantilla->cacheClean();
		} else {
			$this->conexion->rollBack();
		}
		
		$this->basic->jsonEncode($result);
		exit;
	}

	public function deleteGraph()
	{
		$valida = $this->protect->access_page('CONFIG_GRAPH_DELETE');
		if($valida) {
			$getParam = (object)$_POST;
											
			$graph_delete = $this->conexion->arrayToIN($getParam->idSelect,false,true);					
													
			$delete_sql =  "DELETE FROM `bm_graphs` WHERE `id_graph` IN $graph_delete";
			
			$delete_result = $this->conexion->query($delete_sql);
			
			if($delete_result){
				$result['status'] = true;
				echo $this->basic->jsonEncode($result);
				exit;
			}
			else {
				$result['status'] = false;
				$result['error'] = $this->language->ERROR_DELETE_GRAPH_CONFIG;
				echo $this->basic->jsonEncode($result);
				exit;
			}

		} else {
			$result['status'] = false;
			$result['error'] = $this->language->access_deny;
			echo $this->basic->jsonEncode($result);
			exit;
		}	
	}
	
	public function getGraphForm()
	{
		$getParam = (object)$_POST;
		
		if(is_numeric($getParam->IDSelect)) {
	
			//Formulario nuevo 
			$this->plantilla->load("configuracion/config_graph_form");
			
			// Value Graph
			
			$select_sql = 'SELECT `name`,`width`,`height`,`graphtype`, `groupid` FROM  `bm_graphs` WHERE `id_graph` = '.$getParam->IDSelect;
			$select_result = $this->conexion->queryFetch($select_sql);
	
			foreach ($select_result[0] as $key => $graph) {
				$value['graph_'.$key] = $graph;
			}
			
	        $grupo = (int)$select_result[0]['groupid'];

			//Items
				
			$get_items_sql = "SELECT I.`id_item`, I.`name`, I.`descriptionLong`, IFNULL(GI.selected,0)  as 'selected'
                  	FROM `bm_items` I 
                  		LEFT JOIN `bm_items_groups` IG ON I.`id_item`=IG.`id_item`
                  		LEFT OUTER JOIN (SELECT `id_item`, 1 as 'selected' FROM `bm_graphs_items` WHERE `id_graph` = $getParam->IDSelect) GI ON GI.`id_item`=I.`id_item` 
                  	WHERE IG.`groupid` = $grupo ORDER BY I.`descriptionLong`";
				
			$items = $this->conexion->queryFetch($get_items_sql);
			
			if($items) {
				$value["menu_items_inactive"] = '';
				foreach ($items as $key => $item) {
					if((int)$item['selected'] === 1 ) {
						$SELECTED = 'selected';
					} else {
						$SELECTED = '';
					}
							
					$value["menu_items_inactive"] .= '<option '.$SELECTED.' value="'.$item['id_item'].'">'.$item['descriptionLong'].'</option>';
				}
			}
			
			$grupos = $this->bmonitor->getAllGroups();
		
            $value["optionGroupid"] = $grupos->formatOption($grupo);
            
            $value["lang"] = $this->protect->getLang();

			$value["graph_id_menu"] = 'graph_items_edit';
			
			$value["graph_groupsid"] = 'graph_groupsid_edit';
            
			$value["config_graph_id_form"] = 'config_graph_form_edit';
			
			$this->plantilla->set($value);
			
			$this->plantilla->finalize();	
		
		}
	}

	public function cfgLocation()
	{

		$valida = $this->protect->access_page('CONFIG_LOCATION');

        $this->plantilla->loadSec("configuracion/config_ubicacion", $valida, 8600);

		$value = array();
		
		$valueForm["option_region"] = $this->basic->getLocalidadOption('region');
		$valueForm["option_provincia"] = '<option selected value="0">'.$this->language->SELECT.' Region</option>';
		$valueForm["option_comuna"] = '<option selected value="0">'.$this->language->SELECT.' Provincia</option>';
		$valueForm["option_plan"] = $this->bmonitor->getAllPlan('option',false,false,'ENLACES');
		
		$valueForm["config_id_form"] = 'form_new_location';

		
		$value["form_new_ubicacion"] = $this->plantilla->getOne("configuracion/config_ubicacion_form",$valueForm);
	
		$this->plantilla->set($value);
		
		$this->plantilla->finalize();		
	}
	
	public function getTableUbicaciones()
	{
		$getParam = (object)$_POST;
		
		//Order
		
		if($_POST['sortname'] == 'region'){
			$_POST['sortname'] = 'CO.REGION_ID';
		}
		
		if($_POST['sortname'] == 'provincia'){
			$_POST['sortname'] = 'CO.PROVINCIA_ID';
		}
		
		if($_POST['sortname'] == 'comuna'){
			$_POST['sortname'] = 'CO.COMUNA_ID';
		}
		
		if($_POST['sortname'] == 'name'){
			$_POST['sortname'] = 'establecimiento';
		}
		
		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST,'id_colegio','desc');
		
		//Parametros Table
		
		$data = array();
		
		$data['page'] = $var->page;
		
		$data['rows'] = array();
		
		//Total rows
		
		$getTotalRows_sql = "SELECT COUNT(*) as Total FROM `bm_colegios` CO LEFT JOIN `bm_comuna` C ON CO.`COMUNA_ID`=C.`COMUNA_ID`
							LEFT JOIN `bm_provincia` P ON CO.`PROVINCIA_ID` = P.`PROVINCIA_ID`
							LEFT JOIN `bm_region` R ON CO.`REGION_ID`=R.`REGION_ID` $var->searchSql";

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);
		
		if($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['Total'];
		else {
			$data['total'] = 0;
		}
		
		//Rows
		
		$getRows_sql =	"SELECT * FROM `bm_colegios` CO
							LEFT JOIN `bm_comuna` C ON CO.`COMUNA_ID`=C.`COMUNA_ID`
							LEFT JOIN `bm_provincia` P ON CO.`PROVINCIA_ID` = P.`PROVINCIA_ID`
							LEFT JOIN `bm_region` R ON CO.`REGION_ID`=R.`REGION_ID` $var->searchSql $var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);
		
		if($getRows_result) {
			foreach ($getRows_result as $key => $row) {
				$option = '<span id="toolbar" class="ui-widget-header ui-corner-all">';
				$option .= '<span id="toolbarSet"><button id="editUbicacion" onclick="edit('.$row['id_colegio'].',false)" name="editUbicacion">Editar</button></span>';
				$option .= '</span>';
				
				$data['rows'][] = array(
					'id' => $row['id_colegio'],
					'cell' => array($this->basic->fixEncoding($row['establecimiento']), $this->basic->fixEncoding($row['REGION_NOMBRE']),$this->basic->fixEncoding($row['PROVINCIA_NOMBRE']),$this->basic->fixEncoding($row['COMUNA_NOMBRE']),$option)
				);
			}
		}
        $this->basic->jsonEncode($data);
		exit;
	}

	public function ubicacion()
	{
		$getParam = (object)$_POST;

		echo $this->basic->getLocalidadOption($getParam->type,$getParam->id);
	}

	public function getUbicacionForm()
	{
		$getParam = (object)$_POST;
		
		//Formulario nuevo 
		$this->plantilla->load("configuracion/config_ubicacion_form");
		
		$value["config_id_form"] = 'form_edit_location';
		
		//Get value Colegio
		$value_result = $this->conexion->queryFetch("SELECT * FROM `bm_colegios` WHERE `id_colegio`=?",$getParam->IDSelect);
		
		if($value_result) {
			$colegio = (object)$value_result[0];
		} else {
			header("Content-type: application/json");
			$result["status"] = false;
			echo $this->basic->jsonEncode($result);
			exit;			
		}

		foreach ($value_result[0] as $ckey => $cvalue) {
			$value[$ckey] = $cvalue;
		}
		
		//Items
		
		$value["option_region"] = $this->basic->getLocalidadOption('region',false,$colegio->REGION_ID);
		$value["option_provincia"] = $this->basic->getLocalidadOption('provincia',$colegio->REGION_ID,$colegio->PROVINCIA_ID);
		$value["option_comuna"] = $this->basic->getLocalidadOption('comuna',$colegio->PROVINCIA_ID,$colegio->COMUNA_ID);
		
		//Buscando Plan
		
		if((int)$colegio->id_plan === 0) {
			
			$select_sql = "SELECT * FROM `bm_plan` WHERE `plan` =  '?'";
		
			$select_result = $this->conexion->queryFetch($select_sql,$colegio->planfdt);
			
			if($select_result) {
				$value["option_plan"] = $this->bmonitor->getAllPlan('option',$select_result[0]['id_plan'],false,'ENLACES');
				
			} else {
				$value["option_plan"] = $this->bmonitor->getAllPlan('option',false,false,'ENLACES').'<option selected value="0">Plan [ '.$colegio->planfdt.' ] No registrado</option>';
			}
		} else {

			$value["option_plan"] = $this->bmonitor->getAllPlan('option',$colegio->id_plan,false,'ENLACES');

        }
		
		$this->plantilla->set($value);
		
		$result["html"] = $this->plantilla->get();
		$result['status'] = true;
		
		echo $this->basic->jsonEncode($result);
	}

	public function createUbicacion() 
	{
		$valida = $this->protect->access_page('CONFIG_LOCATION_CREATE');
		if($valida) {
			$getParam = (object)$_POST;
			$result = array();
			
			$result['status'] = false;
			
			foreach ($getParam as $key => $value) {
				if(preg_match("/^location/i",$key)) {
    				$keyName = explode("_", $key,2);
    				$param[$keyName[1]] = $value;
				}
			}

			if(count($param) < 9) {
	            $result['status'] = false;
                $result['error'] = $this->language->ERROR_INVALID_PARAM;
                echo $this->basic->jsonEncode($result);
                exit;		    
			}

            $param = (object)$param;

			if(!is_numeric($getParam->id)) {
				/* Creando nuevo Grafico */ 
				
				$this->conexion->InicioTransaccion();
				
				$insert_sql = sprintf(/* BSW */ "INSERT INTO `bm_colegios` (`sostenedor`, `establecimiento`, `rdb`, `direccion`, `rut`, `tarifa`, `nodo`, `cuadrante`, `viviendaid`, `planfdt`, `REGION_ID`, `PROVINCIA_ID`, `COMUNA_ID`, `id_plan`) 
													VALUES ('%s', '%s', %s, '%s', '%s', '%s', %s, '%s', '%s', %s, '%s', %s, %s, %s)",
													$param->sostenedor,
													$param->establecimiento,
													$param->rdb,
													$param->direccion,
													$param->rut,
													$param->tarifa,
													$param->nodo,
													$param->cuadrante,
													$param->viviendaid,
													$param->planfdt,
													$param->REGION_ID,
													$param->PROVINCIA_ID,
													$param->COMUNA_ID,
													$param->planfdt);
											
				$sql_result = $this->conexion->query($insert_sql,false,'logs_config');
				
				$idInsert = $this->conexion->lastInsertId();
				
				if($sql_result) {
					$result['status'] = true;
				}
			} else {
				/* Editando Grafico */ 
				$this->conexion->InicioTransaccion();
				
				$update_sql = sprintf(/* BSW */ "UPDATE `bm_colegios` SET `sostenedor` = '%s', `establecimiento` = '%s', `rdb` = '%s', `direccion` = '%s', `rut` = '%s', `tarifa` = '%s', `nodo` = '%s', `cuadrante` = '%s', `viviendaid` = '%s', `planfdt` = '%s', `REGION_ID` = '%s', `PROVINCIA_ID` = '%s', `COMUNA_ID` = '%s', `id_plan` = '%s' WHERE `id_colegio` = '%s'",
							$param->sostenedor,
							$param->establecimiento,
							$param->rdb,
							$param->direccion,
							$param->rut,
							$param->tarifa,
							$param->nodo,
							$param->cuadrante,
							$param->viviendaid,
							$param->planfdt,
							$param->REGION_ID,
							$param->PROVINCIA_ID,
							$param->COMUNA_ID,
							$param->planfdt,
							$getParam->id);

				$update_result = $this->conexion->query($update_sql);
				
				if($update_result) {
					$result['status'] = true;
				}
				
			}
		} else {
			$result['status'] = false;
			$result['error'] = $this->language->access_deny;
			echo $this->basic->jsonEncode($result);
			exit;
		}
		
		if($result['status']) {
			if(!is_numeric($getParam->id))  {
				$this->protect->setAudit($_SESSION['uid'],'new_ubicacion',"Usuario creo la Ubicacion: [$idInsert] ".$param->establecimiento);
			} else {
				$this->protect->setAudit($_SESSION['uid'],'edit_ubicacion',"Usuario edito la Ubicacion: [$getParam->id] ".$param->establecimiento);
			}
			$this->conexion->commit();
			$this->plantilla->cacheClean();
		} else {
			$this->conexion->rollBack();
		}
		
		header("Content-type: application/json");
		echo $this->basic->jsonEncode($result);
		exit;
	}

	public function deleteubicacion()
	{
		$valida = $this->protect->access_page('CONFIG_LOCATION_DELETE');
		if($valida) {
			$getParam = (object)$_POST;
											
			$reg_delete = $this->conexion->arrayToIN($getParam->idSelect,false,true);					
													
			$delete_sql =  "DELETE FROM `bm_colegios` WHERE `id_colegio` IN $reg_delete";
			
			$delete_result = $this->conexion->query($delete_sql);
			
			if($delete_result){
				$result['status'] = true;
				echo $this->basic->jsonEncode($result);
				exit;
			}
			else {
				$result['status'] = false;
				$result['error'] = $this->language->ERROR_DELETE_LOCATION_CONFIG;
				echo $this->basic->jsonEncode($result);
				exit;
			}

		} else {
			$result['status'] = false;
			$result['error'] = $this->language->access_deny;
			$this->basic->jsonEncode($result);
			exit;
		}	
	}
	
    
/// Profiles

    private function tagsCreation()
    {
        $getTagSQL = "SELECT `tags`, 'item' as type, `id_item` as id FROM `bm_items` WHERE  `tags` is not null
UNION
SELECT `tags`, 'host' as type ,  `id_host` as id  FROM `bm_host` WHERE `borrado`=0 AND `tags` is not null;";
        
        $getTagRESULT = $this->conexion->queryFetch($getTagSQL);
        
             
        
        if($getTagRESULT){
            
            $insertTagsSQL = "INSERT INTO `bm_tags` (`section`, `tag`,`id` ) VALUES ";
            
            foreach ($getTagRESULT as $key => $value) {
                $tagsExplode = explode(',', $value['tags']);
                foreach ($tagsExplode as  $tag) {
                    $tags[] =  "('".$value['type']."', '".trim($tag)."', '".$value['id']."')";
                }    
            }


            //Delete Tags
            
            $deleteTagsSQL = 'DELETE FROM `bm_tags`';
            
            $this->conexion->query($deleteTagsSQL);
            
            //Insert Tags
            
            $this->conexion->query($insertTagsSQL.join(',', $tags));
            
            
        } else {
            return false;
        }

    }  
}
?>