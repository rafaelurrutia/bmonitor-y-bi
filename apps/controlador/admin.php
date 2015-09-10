<?php

class admin extends Control {

	public function index( ) {
		$valida = $this->protect->access_page('ADMINISTRACION');

		$this->plantilla->loadSec("administracion/administracion", $valida, 36000);

		$section = new Section( );

		$tps_index_control['menu'] = $section->menu('ADMINISTRACION');
		$tps_index_control['header'] = $section->header();
		$tps_index_control['footer'] = $section->footer();

		$tabs[] = array(
			'title' => $this->language->USERS,
			'urlBase' => true,
			'href' => 'admin/user',
			'protec' => 'ADMINISTRATION_USER'
		);

		$tabs[] = array(
			'title' => $this->language->GROUP,
			'urlBase' => true,
			'href' => 'admin/groups',
			'protec' => 'ADMINISTRATION_GROUPS'
		);

		$tabs[] = array(
			'title' => $this->language->AUDIT,
			'urlBase' => true,
			'href' => 'admin/audit',
			'protec' => 'ADMINISTRATION_AUDIT'
		);

/*		Bmonitor 2.5
		$tabs[] = array(
			'title' => $this->language->TEMPLATE_LANG,
			'urlBase' => true,
			'href' => 'admin/template',
			'protec' => 'ADMINISTRATION_TEMPLATE'
		);

		$tabs[] = array(
			'title' => $this->language->FIRMWARE,
			'urlBase' => true,
			'href' => 'admin/firmware',
			'protec' => 'ADMINISTRATION_FIRMWARE'
		);

		$tabs[] = array(
			'title' => $this->language->OPTIONS,
			'urlBase' => true,
			'href' => 'admin/option',
			'protec' => 'ADMINISTRATION_OPTION'
		);
*/
		$tabs[] = array(
			'title' => 'Parametros',
			'urlBase' => true,
			'href' => 'admin/parameter',
			'protec' => 'ADMINISTRATION_PARAM'
		);
/*		Bmonitor 2.5
		$tabs[] = array(
			'title' => 'Feature',
			'urlBase' => true,
			'href' => 'admin/feature',
			'protec' => 'ADMINISTRATION_FEATURE'
		);
*/
		$tps_index_control['tabs'] = $this->bmonitor->getTabs($tabs, 'tabsAdmin');

		$tps_index_control['user_lang'] = $this->language->USER;
		$tps_index_control['groups_lang'] = $this->language->GROUPS;
		$tps_index_control['page_lang'] = $this->language->PAGINA;
		$tps_index_control['audi_lang'] = $this->language->AUDITORIA;
		$tps_index_control['templates_lang'] = $this->language->TEMPLATE_LANG;
		$tps_index_control['parameters_lang'] = $this->language->PARAMETROS;
		$tps_index_control['import_lang'] = $this->language->IMPORT;

		$this->plantilla->set($tps_index_control);

		$this->plantilla->finalize();
	}

	public function user( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_USER');
		$this->plantilla->loadSec("administracion/admin_user", $valida, 360000);

		$valueForm['optionGroups'] = $this->getGroup();
        
		$valueForm['formID'] = 'form_user_new';

		$value["admin_user_form"] = $this->plantilla->getOne("administracion/admin_user_form", $valueForm);
        
        $valueForm['formID'] = 'form_user_new_pass';
        
        $value["admin_user_form_pass"] = $this->plantilla->getOne("administracion/admin_user_form_pass", $valueForm);

		$button[] = array(
			'name' => 'NEW',
			'bclass' => 'add',
			'protec' => 'ADMINISTRATION_USER_NEW',
			'onpress' => 'dialogUserBox'
		);

		$button[] = array(
			'name' => 'DELETE',
			'bclass' => 'delete',
			'protec' => 'ADMINISTRATION_USER_DELETE',
			'onpress' => 'dialogUserBox'
		);

		$value["button"] = $this->generate->getButton($button);

		$this->plantilla->set($value);
		$this->plantilla->finalize();
	}

    public function editPassUser($idUser = false)
    {
        $ADMINISTRACION_CHANGE_PASS_USER = $this->protect->allowed('ADMINISTRATION_USER_CHANGE_PASS');
        $respond['status'] = false;
        if($ADMINISTRACION_CHANGE_PASS_USER) {
            $respond = $this->protect->newPass($_POST['password'], $_POST['confirmation'] , $idUser);
            $respond['status'] = false;
            if ($respond['error'] === 0) {
                $respond['msg'] = $this->language->NEW_PASS_OK;
                $respond['status'] = true;
            } elseif ($respond['error'] === 1) {
                $respond['msg'] = $this->language->NEW_PASS_ERROR_1;
            } elseif ($respond['error'] === 2) {
                $respond['msg'] = $this->language->NEW_PASS_ERROR_2;
            } elseif ($respond['error'] === 3) {
                $respond['msg'] = $this->language->NEW_PASS_ERROR_3;
            }
        } else {
            $respond['error'] = $this->language->access_deny;
        }
        $this->basic->jsonEncode($respond);
        exit;
    }

	public function perm( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_GROUPS_PERM');
		$this->plantilla->loadSec("administracion/adminPerm", $valida);

		if(isset($_GET['id'])) {
			$idGroups = $_GET['id'];
		} elseif(isset($_POST['id'])) {
			$idGroups = $_POST['id'];
		} else {
			$idGroups = false;
		}

		$columns = array();

		if(is_numeric($idGroups)) {

			$select_groups_section = 'SELECT * FROM `' .$this->protect->table_access .'` WHERE `section_display` is not null GROUP BY `group_section` ORDER BY `group_section`;';

			$section_groups = $this->conexion->queryFetch($select_groups_section);

			foreach ($section_groups as $key => $section_group) {

				$column = '<div class="portlet">' ."\n";
				$column .= '<div class="portlet-header">' .$section_group['section_display'] ."</div>\n";
				$column .= '<div class="portlet-content">' ."\n";

				$select_groups_section = "SELECT section.`id_section`, `section`, `description`,`id_group`,  MAX(IF(`id_group` = $idGroups, 1, 0)) as active " .'FROM ' .$this->protect->table_access .' as section ' .'LEFT OUTER JOIN  ' .$this->protect->table_access_profile .' as profile ' .'ON section.`id_section`=profile.`id_section`' .' WHERE `group_section`= ' .$section_group['group_section'] ." AND (`id_group`=$idGroups  OR `id_group` IS NULL OR `id_group` > 0) " .' GROUP BY `description` ORDER BY `description`';

				$sections = $this->conexion->queryFetch($select_groups_section);

				foreach ($sections as $key => $section) {

					if($section['active'] == '1') {
						$checked = 'checked';
					} else {
						$checked = '';
					}

					$column .= '<div class="row">' ."<label class='col1' for='" .$section['section'] ."'>" .$section['description'] ."</label>\n";

					$column .= '<span class="col2 small">' ."<input $checked type='checkbox' id='" .$section['section'] ."' name='" .$section['section'] ."' value='" .$section['id_section'] ."'></span>\n";

					$column .= '</div>';
				}

				$column .= '</div>' ."\n";
				$column .= '</div>' ."\n";
				$columns[] = $column;
			}
			$display = "<input type='hidden' value='$idGroups' name='groupID' />";

			$count_colum = 3;
			$count_columns = count($columns);
			$count_columns_cut = round($count_columns / $count_colum);

			$pagina_2 = array_chunk($columns, $count_columns_cut, false);

			foreach ($pagina_2 as $columnas) {
				$display .= '<div class="column">';

				foreach ($columnas as $filas) {

					$display .= $filas;
				}

				$display .= '</div>' ."\n";
			}

			/*
			 for ($i=0; $i < $count_columns; $i++) {
			 $pagina_1 = array_slice($columns, $i, $count_colum,false);

			 $display .= '<div class="column">';

			 for ($f=0; $f <=  $count_columns_cut; $f++) {

			 $display .= $pagina_1[$f];
			 }

			 $display .= '</div>'."\n";

			 $i = $i+$count_colum;
			 }*/

			$value['columns'] = utf8_encode($display);
		} else {
			$value['columns'] = "Grupo invalido";
		}

		$this->plantilla->set($value);
		$this->plantilla->finalize();
	}

	public function permissions( $idGroups = false ) {
		$valida = $this->protect->access_page('ADMINISTRATION_GROUPS_PERM');
		$this->plantilla->loadSec("administracion/admin_perm", $valida);

		if( ! $idGroups) {
			$idGroups = $_GET['id'];
		}

		if(is_numeric($idGroups)) {

			$select_groups_section = 'SELECT * FROM `' .$this->protect->table_section .'` WHERE `section_display` is not null GROUP BY `section_display` ORDER BY `section_display`;';

			$section_groups = $this->conexion->queryFetch($select_groups_section);

			$columns = "<input type='hidden' value='$idGroups' name='groupID' />";

			foreach ($section_groups as $key => $section_group) {

				$columns .= '<div class="columns ui-corner-all" >';

				$columns .= '<div class="nombrePerm">' .$section_group['section_display'] .'</div>';

				$select_groups_section = "SELECT section.`id_section`, `section`, `description`,`id_group`,  MAX(IF(`id_group` = $idGroups, 1, 0)) as active " .'FROM ' .$this->protect->table_section .' as section ' .'LEFT OUTER JOIN  ' .$this->protect->table_group_profile .' as profile ' .'ON section.`id_section`=profile.`id_section`' .' WHERE `group_section`= ' .$section_group['group_section'] ." AND (`id_group`=$idGroups  OR `id_group` IS NULL OR `id_group` > 0) " .' GROUP BY `description` ORDER BY `section_display`,`description`';

				$sections = $this->conexion->queryFetch($select_groups_section);

				foreach ($sections as $key => $section) {

					if($section['active'] == '1') {
						$checked = 'checked';
					} else {
						$checked = '';
					}

					$columns .= "<label for='" .$section['section'] ."'>" .$section['description'] ."</label>";

					$columns .= "<input $checked type='checkbox' id='" .$section['section'] ."' name='" .$section['section'] ."' value='" .$section['id_section'] ."'>";

					$columns .= '<br style="clear:both" />';
				}

				$columns .= '</div>';
			}
			$value['columns'] = $columns;
		} else {
			$value['columns'] = "Grupo invalido";
		}

		$this->plantilla->set($value);
		$this->plantilla->finalize();
	}

	public function getGroup( ) {
		$groups = $this->protect->getGroupAll();

		if($groups) {
			return $this->basic->getOption($groups, 'id_group', 'group');
		} else {
			return '<option value="00">Error</option>';
		}
	}

	public function getUserTable( ) {
		$getParam = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'id_user', 'ASC');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		if( ! empty($getParam->fmUser)) {
			$finder = 1;
			if(empty($var->searchSql)) {
				$var->searchSql = " WHERE `name` LIKE '%" .$getParam->fmUser ."%' ";
			} else {
				$var->searchSql .= " AND `name` LIKE '%" .$getParam->fmUser ."%' ";
			}
		}

		if( ! empty($getParam->toGroup)) {
			$finder = 1;
			if(empty($var->searchSql)) {
				$var->searchSql = " WHERE `group` LIKE '%" .$getParam->toGroup ."%' ";
			} else {
				$var->searchSql .= " AND `group` LIKE '%" .$getParam->toGroup ."%' ";
			}
		}

		if(empty($var->searchSql)) {
			$var->searchSql = " WHERE `is_admin` = 'false' ";
		} else {
			$var->searchSql .= " AND `is_admin` = 'false' ";
		}

		if($this->protect->isSecret()){
			$idcompany = ' > 0';	
		} else {
			$idcompany = ' = '.$_SESSION['idcompany'];
		}

		if(empty($var->searchSql)) {
			$var->searchSql = " WHERE `id_company` $idcompany ";
		} else {
			$var->searchSql .= " AND `id_company` $idcompany ";
		}
						
		$sql_count = 'SELECT COUNT(*) as Total ' .'FROM ' .$this->protect->table_user. '  AS users LEFT OUTER JOIN '.$this->protect->table_group .' as groups ON  `users`.`id_group`=`groups`.`id_group` '. $var->searchSql;

		$count = $this->conexion->queryFetch($sql_count);

		$data['total'] = $count[0]['Total'];

		$sqlSelect = 'SELECT `id_user`,`is_admin`,`name`, `group`, user.`active` ' .'FROM ' .$this->protect->table_user .' as user LEFT OUTER JOIN  ' .$this->protect->table_group .' as groups ' ."USING(`id_group`) " .$var->searchSql .$var->sortSql .$var->limitSql;

		$users = $this->conexion->queryFetch($sqlSelect);

		$ADMINISTRACION_CHANGE_PASS_USER = $this->protect->allowed('ADMINISTRATION_USER_CHANGE_PASS');

		$ADMINISTRACION_SCHEDULE_USER = $this->protect->allowed('ADMINISTRATION_USER_SCHEDULE');

		foreach ($users as $key => $user) {
			if($user['is_admin'] == 'true') {
				$is_admin = 'Yes';
			} else {
				$is_admin = 'No';
			}

			if($user['active'] == 'true') {
				$estado = '<a onclick="statusUser(' .$user['id_user'] .',true)" class="ui-button ui-widget ui-state-default ui-corner-all ui-button-icon-only" role="button" aria-disabled="false" title="Button on/off"> <span class="ui-button-icon-primary ui-icon ui-icon-power"></span><span class="ui-button-text">' .$this->language->ENABLED .'</span> </a>';
			} else {
				$estado = '<a onclick="statusUser(' .$user['id_user'] .',false)" class="ui-button ui-widget ui-state-error ui-corner-all ui-button-icon-only" role="button" aria-disabled="false" title="Button on/off"> <span class="ui-button-icon-primary ui-icon ui-icon-power"></span><span class="ui-button-text">' .$this->language->DISABLED .'</span> </a>';
			}

			$option = '<span id="toolbarSet">';

			/*if($ADMINISTRACION_SCHEDULE_USER) {
				$option .= '<button id="userSchedule" onclick="$.userSchedule(' .$user['id_user'] .')" name="userSchedule">' .$this->language->SCHEDULE .'</button>';
			}*/ 
			if($ADMINISTRACION_CHANGE_PASS_USER) {
				$option .= '<button id="userPass" onclick="$.userPass(' .$user['id_user'] .')" name="userPass">' .$this->language->USER_PASS.'</button>';
			}

			$option .= '</span>';

			$data['rows'][] = array(
				'id' => $user['id_user'],
				'cell' => array(
					$user['id_user'],
					$user['name'],
					$user['group'],
					$is_admin,
					$estado,
					$option
				)
			);
		}
		$this->basic->jsonEncode($data);
		exit ;
	}

	public function deleteUser( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_USER_DELETE');

		if($valida) {
			$getParam = (object)$_POST;
			$data['status'] = true;
			foreach ($getParam->idSelect as $key => $idSelect) {
				if(is_numeric($idSelect)) {
					$valida = $this->protect->deleteUser($idSelect);
					if( ! $valida) {
						$data['error'] = "Error, deleting user.";
						$data['status'] = false;
					}
				}
			}

		} else {
			$data['error'] = $this->language->access_deny;
			$data['status'] = false;
		}

		$this->basic->jsonEncode($data, false);
		exit ;
	}

	public function createUser( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_USER_NEW');

		if($valida) {
			$getParam = (object)$_POST;

			if(empty($getParam->user_is_admin)) {
				$getParam->user_is_admin = 0;
			}

			$getParam->user_is_admin = 0;

			$data = $this->protect->newUser(trim($getParam->user_name), $getParam->user_email, $getParam->user_user, $getParam->user_passwd, $getParam->user_id_group, $getParam->user_is_admin);

		} else {
			$data['status'] = false;
		}

		$this->basic->jsonEncode($data);

	}

	public function statusUser( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_STATUS_USER');

		$data['status'] = false;

		if($valida) {
			$getParam = (object)$_POST;
			$user_table = $this->protect->table_user;

			if($getParam->status == 1) {
				$status = 'false';
			} else {
				$status = 'true';
			}

			$update_item_group = "UPDATE $user_table SET `active` = '$status' WHERE `id_user` = '$getParam->id';";

			$valida = $this->conexion->query($update_item_group);

			if($valida) {
				$data['status'] = true;
			}

		}

		$this->basic->jsonEncode($data);
	}

	public function groups( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_GROUPS');
		$this->plantilla->loadSec("administracion/admin_group", $valida);

		$grupos = $this->bmonitor->getGroupsHost();

		$paramForm['hostGroupsList'] = $this->basic->getOption($grupos, 'groupid', 'name', '-first-');

		$paramForm['formID'] = "groupFormNew";

		if($this->protect->isSecret()){
			$getCompanySQL = 'SELECT `id_company`,`name` FROM `admin_company`';
			$getCompanyRESULT = $this->conexion->queryFetch($getCompanySQL);
			$optionCompany  = $this->basic->getOption($getCompanyRESULT, 'id_company', 'name', '-first-');
			$paramForm['company'] = '<div class="row">
            <label for="default" class="col1">Company</label>
            <span class="col2">
                <select class="select ui-widget-content ui-corner-all" id="company" name="company">
                '.$optionCompany.'
            </select>
	        </span>
	        </div>'; 
		}

		$param['formNewGroups'] = $this->plantilla->getOne("administracion/admin_group_form", $paramForm);
        
        $button[] = array(
            'name' => 'NEW',
            'bclass' => 'add',
            'protec' => 'ADMINISTRATION_GROUPS_NEW',
            'onpress' => 'dialogGroupsBox'
        );

        $button[] = array(
            'name' => 'DELETE',
            'bclass' => 'delete',
            'protec' => 'ADMINISTRATION_GROUPS_DELETE',
            'onpress' => 'dialogGroupsBox'
        );
                
        $param["button"] = $this->generate->getButton($button);

		$this->plantilla->set($param);
		$this->plantilla->finalize();
	}

	public function getFormEditGroup( ) {
		$groupid = $_POST['groupid'];
		$valida = $this->protect->access_page('ADMINISTRATION_GROUPS_EDIT');
		$this->plantilla->loadSec("administracion/admin_group_form", $valida, false);

		$getGroupIDSQL = "SELECT hg.`groupid`, hg.`name` , SUM(IF(uhg.`id_group` = $groupid, 1, 0)) as selected
                FROM `bm_host_groups` hg 
                    LEFT OUTER JOIN `bm_user_host_group` uhg ON uhg.`groupid`= hg.`groupid`
                    WHERE 
                        hg.`borrado` = 0 AND 
                        hg.`name` != '' 
                GROUP BY hg.`groupid`";

		$grupos = $this->conexion->queryFetch($getGroupIDSQL);

		$optionGroups = '';
		foreach ($grupos as $key => $value) {
			if($value['selected'] == 1) {
				$selected = 'selected';
			} else {
				$selected = '';
			}
			$optionGroups .= '<option ' .$selected .' value="' .$value['groupid'] .'">' .$value['name'] .'</option>';
		}

		$getGroupUserSQL = 'SELECT `group`,`id_company` FROM `admin_groups` WHERE `id_group` =' .$groupid;

		$getGroupUserRESULT = $this->conexion->queryFetch($getGroupUserSQL);

		if($getGroupUserRESULT) {

			$getGroupValue = $getGroupUserRESULT[0];

			$param['groupName'] = $getGroupValue['group'];
			
			if($this->protect->isSecret()){
				$getCompanySQL = 'SELECT `id_company`,`name` FROM `admin_company`';
				$getCompanyRESULT = $this->conexion->queryFetch($getCompanySQL);
				$optionCompany  = $this->basic->getOption($getCompanyRESULT, 'id_company', 'name',$getGroupValue['id_company']);
				$param['company'] = '<div class="row">
	            <label for="default" class="col1">Company</label>
	            <span class="col2">
	                <select class="select ui-widget-content ui-corner-all" id="company" name="company">
	                '.$optionCompany.'
	            </select>
		        </span>
		        </div>'; 
			}

		}

		$param['hostGroupsList'] = $optionGroups;

		$param['formID'] = "groupFormEdit";
		
		$this->plantilla->set($param);
		$this->plantilla->finalize();
	}

	public function getGroupTable( ) {
		$getParam = (object)$_POST;
		
		$var = $this->basic->setParamTable($_POST,'id_group');

        $data = array();
        
        $data['page'] = $var->page;
        
        $data['rows'] = array();

		$sql_count = 'SELECT COUNT(*) as Total ' .'FROM '.$this->protect->table_group.' WHERE `id_company` > 0';
		
		$count = $this->conexion->queryFetch($sql_count);

		$data['total'] = $count[0]['Total'];
		
		if($this->protect->isSecret()){
			$idcompany = ' > 0';	
		} else {
			$idcompany = ' = '.$_SESSION['idcompany'];
		}
		
		if($var->searchSql != ''){
			$var->searchSql .= ' AND `id_company` '.$idcompany;
		} else {
			$var->searchSql .= ' WHERE `id_company` '.$idcompany;
		}
		
		$sqlSelect = "SELECT `id_group`,`group`,`default`, `active` FROM `" .$this->protect->table_group ."` $var->searchSql $var->sortSql $var->limitSql";

		$groups = $this->conexion->queryFetch($sqlSelect);

       // $ADMINISTRATION_GROUPS_NEW = $this->protect->allowed('ADMINISTRATION_GROUPS_NEW');
        $ADMINISTRATION_GROUPS_EDIT = $this->protect->allowed('ADMINISTRATION_GROUPS_EDIT');

		foreach ($groups as $key => $group) {
			if($group['active'] == 'true') {
				$active = $this->language->ENABLED;
			} else {
				$active = $this->language->DISABLED;
			}

			if($group['default'] == 'true') {
				$default = $this->language->YES;
			} else {
				$default = $this->language->NO;
			}
            
			$option = '<span id="toolbarSet">';
            
            if($ADMINISTRATION_GROUPS_EDIT){
                $option .= '<button id="editGroups" onclick="$.editGroup(' .$group['id_group'] .')" name="editGroups">'.$this->language->EDIT.'</button>';
                $option .= '<button id="permisosGroups" onclick="$.cargaPerm(' .$group['id_group'] .',\'' .$group['group'] .'\')" name="permisosGroups">'.$this->language->PERMISSIONS.'</button>';
            }
					
			$option .= '</span>';

			$data['rows'][] = array(
				'id' => $group['id_group'],
				'cell' => array(
					'id_group' => $group['id_group'],
					'group' => $group['group'],
					'default' => $default,
					'active' => $active,
					'option' => $option
				)
			);
		}
        $this->basic->jsonEncode($data);
		exit;
	}

	public function deleteGroups( ) {
		$getParam = (object)$_POST;

		$data['status'] = true;
		for ($i = 1; $i < count($getParam->idGroups); $i++) {
			if(is_numeric($getParam->idGroups[$i])) {
				$valida = $this->protect->deleteGroups($getParam->idGroups[$i]);
				if( ! $valida) {
					$data['status'] = false;
				} else {
					$this->plantilla->cacheDeleteAll();
				}
			}

		}

		echo json_encode($data);

	}

	public function createGroups( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_GROUPS_NEW');

		if($valida) {
			$getParam = (object)$_POST;

			if(isset($getParam->default)) {
				$getParam->default = 'true';
			} else {
				$getParam->default = 'false';
			}

			if(isset($getParam->gorupid)) {
				$groupid = $getParam->gorupid;
			} else {
				$groupid = false;
			}

			$checkNewGroup = $this->protect->newGroup($getParam->groupName, $getParam->grouplist, $getParam->default, $groupid);

			if($checkNewGroup) {
				$this->plantilla->cacheDeleteAll();

				$data['status'] = true;
			} else {
				$data['status'] = false;
			}

		} else {
			$data['status'] = false;
		}

		echo json_encode($data);

	}

	public function setperm( $value = '' ) {
		$getParams = (object)$_POST;

		if( ! is_numeric($getParams->groupID)) {
			return false;
		}

		$query_delete = 'DELETE FROM ' .$this->protect->table_access_profile .' WHERE `id_group`=' .$getParams->groupID;
		$this->conexion->query($query_delete);

		$insert_perm = "INSERT INTO `" .$this->protect->table_access_profile ."` (`id_group`, `id_section`) VALUES ";
		$insert_final = '';
		foreach ($getParams as $key => $params) {
			if($key != "groupID") {
				$insert_final = $insert_perm ."($getParams->groupID,$params);";
				$this->conexion->query($insert_final);
			}
		}

	}

	public function pagina( ) {

	}

	public function audit( ) {
		//Generar Plantilla

		$valida = $this->protect->access_page('ADMINISTRATION_AUDIT');
		$this->plantilla->loadSec("administracion/admin_audit", $valida);

		$value = array();

		//Tabla

		$this->basic->setTableId('table_auditoria');
		$this->basic->setTableUrl("/admin/getTableAuditoria");
		$this->basic->setTableColModel('admin_audit');

		$value["table_auditoria"] = $this->basic->getTableFL('Audit');

		$this->plantilla->set($value);

		$this->plantilla->finalize();
	}

	public function getTableAuditoria( ) {
		$getParam = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'AU.`id_audit`', 'DESC');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		$table_audit = $this->protect->table_audit_user;
		$table_user = $this->protect->table_user;

		//Total rows

		$getTotalRows_sql = "SELECT COUNT(*) as Total
			FROM $table_audit AU";

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

		if($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['Total'];
		else {
			$data['total'] = 0;
		}

		//Rows

		$getRows_sql = "SELECT AU.*,US.`name` As usuario
						FROM $table_audit AU 
						LEFT JOIN $table_user US ON AU.`id_user`=US.`id_user`
						$var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);

		if($getRows_result) {
			foreach ($getRows_result as $key => $row) {
				$data['rows'][] = array(
					'id' => $row['id_audit'],
					'cell' => array(
						$row['usuario'],
						$row['fechahora'],
						$row['action'],
						$row['details']
					)
				);
			}
		}
		$this->basic->jsonEncode($data);
		exit ;
	}

	public function template( ) {
		//Generar Plantilla
		$valida = $this->protect->access_page('ADMINISTRATION_TEMPLATE');
		$this->plantilla->loadSec("administracion/admin_template", $valida, 360000);
		//Formulario

		$valueForm["form_id_template"] = 'form_new_template';

		$value["form_new"] = $this->plantilla->getOne("administracion/admin_template_form", $valueForm);

		$value["NEW"] = $this->language->NEW;
		$value["DELETE"] = $this->language->DELETE;

		//Tabla

		$this->basic->setTableId('table_template');
		$this->basic->setTableUrl("/admin/getTableTemplate");
		$this->basic->setTableColModel('admin_template');
		$this->basic->setTableToolbox('toolbox_template');

		$value["table_template"] = $this->basic->getTableFL('Templates');

		$this->plantilla->set($value);

		$this->plantilla->finalize();
	}

	public function getTableTemplate( ) {
		$getParam = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'TP.`name`', 'DESC');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		//Total rows

		$getTotalRows_sql = "SELECT COUNT(*) as Total
			FROM `bm_template_config` TP";

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

		if($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['Total'];
		else {
			$data['total'] = 0;
		}

		//Rows

		$getRows_sql = "SELECT TP.`id_template` , TP.`name`
						FROM `bm_template_config` TP
						$var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);

		if($getRows_result) {
			foreach ($getRows_result as $key => $row) {

				$option = '<span id="toolbar" class="ui-widget-header ui-corner-all">';
				$option .= '<span id="toolbarSet">
							<button id="editTemplate" onclick="$.editarModal(' .$row['id_template'] .',false)" name="editTemplate">Edit</button>
							<button id="clonarTemplate" onclick="$.editarModal(' .$row['id_template'] .',true)" name="clonarTemplate">Clone</button>
						</span>';
				$option .= '</span>';

				$data['rows'][] = array(
					'id' => $row['id_template'],
					'cell' => array(
						$row['name'],
						$option
					)
				);
			}
		}
		$this->basic->jsonEncode($data);
		exit ;
	}

	public function createTemplate( ) {
		$getParam = (object)$_POST;

		$valida = $this->protect->access_page('ADMINISTRACION_CREATE_TEMPLATE');

		$result['status'] = true;

		if($valida) {

			if( ! is_numeric($getParam->id)) {

				foreach ($getParam as $key => $value) {
					if(preg_match("/^template_/i", $key)) {
						$keyName = explode("_", $key, 2);
						$column[] = '`' .$keyName[1] .'`';

						$value = "'" .$value ."'";

						$valueC[] = $value;
						$config[$keyName[1]] = $value;

					}
				}

				$column = join(',', $column);
				$valueC = join(',', $valueC);

				$insert_sql = 'INSERT INTO `bm_template_config` (' .$column .') VALUES (' .$valueC .');';

				$valida = $this->conexion->query($insert_sql);

				if( ! $valida) {
					$result['status'] = false;
				} else {
					$idTemplate = $this->conexion->lastInsertId();
					$this->protect->setAudit($_SESSION['uid'], 'create_template', "Usuario creo la plantilla: [$idTemplate] " .$getParam->template_name);
				}
			} else {

				foreach ($getParam as $key => $value) {
					if(preg_match("/^template_/i", $key)) {
						$keyName = explode("_", $key, 2);

						$update[] = '`' .$keyName[1] .'` = ' ."'" .$value ."'";
					}
				}

				$update_sql = "UPDATE `bm_template_config` SET " .join(',', $update) ." WHERE `id_template` = '$getParam->id';";

				$update_result = $this->conexion->query($update_sql);

				if( ! $update_result) {
					$result['status'] = false;
				} else {
					$this->protect->setAudit($_SESSION['uid'], 'edit_template', "Usuario edito la plantilla: [$getParam->id] " .$getParam->template_name);
				}

			}
		}

		echo json_encode($result);
		exit ;
	}

	public function deleteTemplate( ) {
		$getParam = (object)$_POST;

		$data['status'] = true;

		foreach ($getParam->idSelect as $key => $value) {

			if(is_numeric($value)) {
				$delete_sql = "/* BSW  */ DELETE FROM `bm_template_config` WHERE `id_template` = '$value';";
				$valida = $this->conexion->query($delete_sql);
				if( ! $valida) {
					$data['status'] = false;
				}

			}
		}
		echo json_encode($data);
	}

	public function getFormTemplate( ) {
		$getParam = (object)$_POST;

		if(is_numeric($getParam->IDSelect)) {

			//Formulario nuevo
			$this->plantilla->load("administracion/admin_template_form");

			// Value Graph

			$select_sql = 'select * FROM `bm_template_config` WHERE `id_template` = ' .$getParam->IDSelect;
			$select_result = $this->conexion->queryFetch($select_sql);

			foreach ($select_result[0] as $key => $graph) {
				$value['template_' .$key] = $graph;
			}

			$value["form_id_template"] = 'form_edit_template';

			$this->plantilla->set($value);

			echo $this->plantilla->get();

		}
	}

	public function firmware( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_FIRMWARE');

		$this->plantilla->loadSec("administracion/admin_firmware", $valida);

		//Formulario

		$valueForm["form_id_firmware"] = 'form_new_firmware';

		$value["form_new"] = $this->plantilla->getOne("administracion/admin_firmware_form", $valueForm);

		//Tabla

		$this->basic->setTableId('table_firmware');
		$this->basic->setTableUrl("/admin/getTableFirmware");
		$this->basic->setTableColModel('admin_firmware');

		$buttons[] = array(
			'name' => $this->language->NEW,
			'bclass' => 'add'
		);
		$buttons[] = array(
			'name' => $this->language->UPDATE,
			'bclass' => 'config'
		);

		$value["NEW"] = $this->language->NEW;
		$value["UPDATE"] = $this->language->UPDATE;

		$this->basic->setTableToolbox('toolbox_firmware', $buttons);

		$value["table_firmware"] = $this->basic->getTableFL('Firmware');

		$this->plantilla->set($value);

		$this->plantilla->finalize();
		exit ;
	}

	public function getTableFirmware( ) {
		$getParam = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'id_firmware', 'DESC');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		//Total rows

		$getTotalRows_sql = "SELECT COUNT(*) as Total
			FROM `bm_firmware` TP";

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

		if($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['Total'];
		else {
			$data['total'] = 0;
		}

		//Rows

		$getRows_sql = "SELECT `id_firmware`,`version` , `fecha_update` , `responsable`, `branch`
						FROM `bm_firmware` TP
						$var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);

		if($getRows_result) {
			foreach ($getRows_result as $key => $row) {
				$data['rows'][] = array(
					'id' => $row['id_firmware'],
					'cell' => array(
						$row['fecha_update'],
						$row['version'],
						$row['responsable'],
						$row['branch']
					)
				);
			}
		}
		$this->basic->jsonEncode($data);
		exit ;
	}

	public function createFirmware( ) {
		$getParam = (object)$_POST;

		if($getParam->firmeware_branch == 1) {
			$file_firmware = "upload/openbsw.tar";
		} elseif($getParam->firmeware_branch == 2) {
			$file_firmware = "upload/openbsw2.tar";
		}

		if(move_uploaded_file($_FILES["firmeware_upload"]["tmp_name"], $file_firmware)) {

			if($getParam->firmeware_branch == 2) {
				$md5_file = md5_file("upload/openbsw2.tar");

				$fp = fopen('upload/openbsw2.md5', 'w');

				if($fp) {
					fwrite($fp, $md5_file ."  openbsw2.tar");
					fclose($fp);
				}
			}

			$sql_insert = "INSERT INTO `bm_firmware` (`version`, `fecha_update`, `responsable`,`branch`)
				VALUES ( '$getParam->firmware_version', NOW(), '$getParam->firmware_responsable','$getParam->firmeware_branch');";

			$sql_result = $this->conexion->query($sql_insert);

			if($sql_result) {
				$result['status'] = true;
			} else {
				$result['status'] = false;
			}

		} else {
			$result['status'] = false;
		}

		echo json_encode($result);
	}

	public function setFirmware( ) {
		$sql_1 = 'SELECT `version` FROM `bm_firmware` WHERE `branch` = 1 ORDER BY `id_firmware` DESC LIMIT 1';

		$sql_2 = 'SELECT `version` FROM `bm_firmware` WHERE `branch` = 2 ORDER BY `id_firmware` DESC LIMIT 1';

		$sql_result_1 = $this->conexion->queryFetch($sql_1);
		$sql_result_2 = $this->conexion->queryFetch($sql_2);

		if($sql_result_1 && $sql_result_2) {

			$update_sql_1 = "UPDATE `Parametros` SET `valor` = '" .$sql_result_1[0]['version'] ."' WHERE `nombre` = 'VERSION';";
			$update_sql_2 = "UPDATE `Parametros` SET `valor` = '" .$sql_result_2[0]['version'] ."' WHERE `nombre` = 'VERSION_V2';";

			$update_result_1 = $this->conexion->query($update_sql_1);
			$update_result_2 = $this->conexion->query($update_sql_2);

			if($update_result_1 && $update_result_2) {
				$result['status'] = true;
			} else {
				$result['status'] = false;
			}

		} else {
			$result['status'] = false;
		}

		echo json_encode($result);
		exit ;
	}

	public function parameter( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_PARAM');

		$this->plantilla->loadSec("administracion/adminParameter", $valida);
		
		//Formulario

		$valueForm["form_id_parameter"] = 'form_new_parameter';

		$value["form_new"] = $this->plantilla->getOne("administracion/admin_parameters_form", $valueForm);

		//Tabla

		$this->basic->setTableId('table_parameter');
		$this->basic->setTableUrl("/admin/getTableParameter");
		$this->basic->setTableColModel('admin_parameter');

		$buttons[] = array(
			'name' => $this->language->NEW,
			'bclass' => 'add'
		);
		$buttons[] = array(
			'name' => $this->language->DELETE,
			'bclass' => 'delete'
		);

		$value["NEW"] = $this->language->NEW;
		$value["DELETE"] = $this->language->DELETE;

		$this->basic->setTableToolbox('toolbox_parameter', $buttons);

		$value["table_parameter"] = $this->basic->getTableFL('Parameter');

		$this->plantilla->set($value);

		$this->plantilla->finalize();
	}
	
	public function createParameter( ) {
		$getParam = (object)$_POST;
		
		$sql_insert = "INSERT INTO `Parametros` (`nombre`, `descripcion`, `type`,`valor`, `visible`)
				VALUES ( '$getParam->parameter_name', '$getParam->parameter_desc', '$getParam->parameter_type','$getParam->parameter_value', 'true')";
				
		$sql_result = $this->conexion->query($sql_insert);

		if($sql_result) {
			$result['status'] = true;
		} else {
			$result['status'] = false;
		}
		

		echo json_encode($result);
	}
	
	public function getFormEditParam(){
		$paramid = $_POST['paramid'];
		$valida = $this->protect->access_page('ADMINISTRATION_PARAM');
		
		$this->plantilla->loadSec("administracion/admin_parameters_form", $valida);
		
		$getParametersSQL = "SELECT `nombre`, `descripcion`, `type`, `valor` FROM `Parametros` WHERE `id` = $paramid";
		
		$params = $this->conexion->queryFetch($getParametersSQL);
		
		$parm["form_id_parameter"] = "form_edit_parameter";
		$parm["parameter_name"] = $params[0]["nombre"];
		$parm["parameter_desc"] = $params[0]["descripcion"];
		$parm["parameter_value"] = $params[0]["valor"];
		
		$this->plantilla->set($parm);
		$this->plantilla->finalize();
	}
	
	public function deleteParameter(){
		$getParam = (object)$_POST;
		$data['status'] = true;
		
		foreach ($getParam->idSelect as $key => $idSelect) {
			if(is_numeric($idSelect)) {
				$sql_insert = "DELETE FROM `Parametros` WHERE `id` = $idSelect";
				$sql_result = $this->conexion->query($sql_insert);
				
				if(!$sql_result) {
					$data['error'] = "Error, deleting parameter.";
					$data['status'] = false;
				}
			}
		}
		
		echo json_encode($data);
	}
	
	public function setParameter(){
		$getParam = (object)$_POST;
		$data['status'] = true;
		
		$sql_update = "UPDATE `Parametros` SET `nombre` = '$getParam->parameter_name', `descripcion` = '$getParam->parameter_desc', `type` = '$getParam->parameter_type', `valor` = '$getParam->parameter_value' WHERE `id` = $getParam->parameter_id";
		$sql_result = $this->conexion->query($sql_update);
				
		if(!$sql_result) {
			$data['error'] = "Error, updating parameter.";
			$data['status'] = false;
		}
		
		echo json_encode($data);
	}

	public function getTableParameter( ) {
		$getParam = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'id', 'DESC');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		//Total rows

		$getTotalRows_sql = "SELECT COUNT(*) as Total
            FROM `Parametros` P";

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

		if($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['Total'];
		else {
			$data['total'] = 0;
		}

		//Rows

		$getRows_sql = "SELECT `id`,`nombre` , `descripcion`
                        FROM `Parametros` P
                        $var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);
		
		$ADMINISTRATION_PARAM = $this->protect->allowed('ADMINISTRATION_PARAM');
		
		

		if($getRows_result) {
			if($ADMINISTRATION_PARAM){
				foreach ($getRows_result as $key => $row) {
					$data['rows'][] = array(
						'id' => $row['id'],
						'cell' => array(
							'nombre' => $row['nombre'],
							'descripcion' => $row['descripcion'],
							'options' => '<span id="toolbarSet"><button id="editGroups" onclick="$.editParameter(' .$row['id'] .')" name="editParameter">'.$this->language->EDIT.'</button></span>'
						)
					);
				}
			}else{
				foreach ($getRows_result as $key => $row) {
					$data['rows'][] = array(
						'id' => $row['id'],
						'cell' => array(
							'nombre' => $row['nombre'],
							'descripcion' => $row['descripcion'],
							'options' => '' 
						)
					);
				}
			}
		}
		$this->basic->jsonEncode($data);
		exit ;
	}

	public function option( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_OPTION');

		$this->plantilla->loadSec("administracion/adminOption", $valida);

		$this->plantilla->finalize();
	}

	public function deleteCache( $option = false ) {
		$this->plantilla->cacheDeleteAll();

		$result['status'] = true;

		$this->basic->jsonEncode($result, false);
	}

	public function feature( ) {
		$valida = $this->protect->access_page('ADMINISTRATION_FEATURE');

		$this->plantilla->loadSec("administracion/adminFeature", $valida);

		$valueForm['FormID'] = 'formFeatureNew';
		$value["formNew"] = $this->plantilla->getOne("administracion/adminFeatureForm",$valueForm);

		$this->plantilla->set($value);

		$this->plantilla->finalize();
	}

	public function getTableFeature( ) {
		$getParam = (object)$_POST;

		//Valores iniciales , libreria flexigrid
		$var = $this->basic->setParamTable($_POST, 'id_feature', 'DESC');

		//Parametros Table

		$data = array();

		$data['page'] = $var->page;

		$data['rows'] = array();

		//Total rows

		$getTotalRows_sql = "SELECT  COUNT(*) as 'Total' FROM `bm_host_feature` WHERE `feature_type` = 'other' AND `type_feature` IN ('ENLACES','NEUTRALIDAD','ENLACES,NEUTRALIDAD','ALL','STI')";

		$getTotalRows_result = $this->conexion->queryFetch($getTotalRows_sql);

		if($getTotalRows_result)
			$data['total'] = $getTotalRows_result[0]['Total'];
		else {
			$data['total'] = 0;
		}

		//Rows

		$getRows_sql = "SELECT `id_feature`,`feature`,`display`, `default_value`, `type_feature`,`orden` FROM `bm_host_feature` WHERE `feature_type` = 'other' AND `type_feature` IN ('ENLACES','NEUTRALIDAD','ENLACES,NEUTRALIDAD','ALL','STI')
                        $var->sortSql $var->limitSql";

		$getRows_result = $this->conexion->queryFetch($getRows_sql);

		if($getRows_result) {
			foreach ($getRows_result as $key => $row) {

				$option = '<div id="toolbarSet"><button id="editarFeature" onclick="$.editarFeature(' .$row['id_feature'] .')" name="editarFeature">'.$this->language->EDIT.'</button></div>';
				
				$data['rows'][] = array(
					'id' => $row['id_feature'],
					'cell' => array(
						'id_feature' => $row['id_feature'],
						'feature' => $row['feature'],
						'display' => $row['display'],
						'default_value' => $row['default_value'],
						'type_feature' => $row['type_feature'],
						'orden' => $row['orden'],
						'option' => $option
					)
				);
			}
		}
		$this->basic->jsonEncode($data);
		exit ;
	}
}
?>