<?php 

	class menu extends Control {
		
		public function monitor()
		{
			$valida = $this->protect->access_page('MONITOR');

			if($valida) {
				$this->plantilla->cacheJSON('page_monitor_ok',8640000);	
			} else {
				$this->plantilla->cacheJSON('page_monitor_nok',8640000);	
			}
		
			$this->plantilla->load("header");
			
			$tps_index_control['header'] = $this->plantilla->get();
			
			$tps_index_control['menu'] = $this->bmonitor->getMenu('monitor');
			
			$this->plantilla->load("footer", 'sitio/vista/');
		
			$tps_index_control['footer'] = $this->plantilla->get();
			
			$valida = $this->protect->access_page('INDEX');

			$this->plantilla->load_sec("index", "denegado",$valida);
			
			$this->plantilla->set($tps_index_control);
			echo $this->plantilla->get();
			$this->plantilla->cacheClose();
		}

		public function inventario()
		{
			$valida = $this->protect->access_page('INVENTARIO');

			if($valida) {
				$this->plantilla->cacheJSON('page_inventario_ok',8640000);	
			} else {
				$this->plantilla->cacheJSON('page_inventario_nok',8640000);	
			}
			
			$this->plantilla->load_sec("header", "denegado",$valida);
			
			$tps_index_control['header'] = $this->plantilla->get();
			
			$tps_index_control['menu'] = $this->bmonitor->getMenu('inventario');
			
			$this->plantilla->load("footer", 'sitio/vista/');
			$tps_index_control['footer'] = $this->plantilla->get();
			
			$this->plantilla->load("inventario/inventario");
		
			$this->plantilla->set($tps_index_control);
			echo $this->plantilla->get();
			$this->plantilla->cacheClose();
		}
		
		public function fdt()
		{
			$valida = $this->protect->access_page('FDT');

			if($valida) {
				$this->plantilla->cacheJSON('page_fdt_ok',8640000);	
			} else {
				$this->plantilla->cacheJSON('page_fdt_nok',8640000);	
			}
			
			$this->plantilla->load_sec("header", "denegado",$valida);
			
			$tps_index_control['header'] = $this->plantilla->get();
			
			$tps_index_control['menu'] = $this->bmonitor->getMenu('fdt');
			
			$this->plantilla->load("footer", 'sitio/vista/');
			$tps_index_control['footer'] = $this->plantilla->get();

			$this->plantilla->load("fdt/fdt");
			$this->plantilla->set($tps_index_control);
			echo $this->plantilla->get();
			$this->plantilla->cacheClose();
		}
		
		public function neutralidad()
		{
			$valida = $this->protect->access_page('NEUTRALIDAD');

			if($valida) {
				$this->plantilla->cacheJSON('page_neutralidad_ok',8640000);	
			} else {
				$this->plantilla->cacheJSON('page_neutralidad_nok',8640000);	
			}
			
			$this->plantilla->load_sec("header", "denegado",$valida);
			
			$tps_index_control['header'] = $this->plantilla->get();
			
			$tps_index_control['menu'] = $this->bmonitor->getMenu('neutralidad');
			
			$this->plantilla->load("footer", 'sitio/vista/');
            
			$tps_index_control['footer'] = $this->plantilla->get();

			
			$this->plantilla->load("neutralidad/neutralidad");
			$this->plantilla->set($tps_index_control);
			echo $this->plantilla->get();
			$this->plantilla->cacheClose();
		}

		public function configuracion()
		{
			$valida = $this->protect->access_page('CONFIGURACION');

			if($valida) {
				$this->plantilla->cacheJSON('page_configuracion_ok',8640000);	
			} else {
				$this->plantilla->cacheJSON('page_configuracion_nok',8640000);	
			}
						
			$this->plantilla->load_sec("header", "denegado",$valida);
			
			$tps_index_control['header'] = $this->plantilla->get();
			
			$tps_index_control['menu'] = $this->bmonitor->getMenu('configuracion');
			
			$this->plantilla->load("footer", 'sitio/vista/');
			$tps_index_control['footer'] = $this->plantilla->get();
			
			if(isset($_SESSION['name']) && $_SESSION['name'] != 'Administrator') {
			   $tps_index_control['tabs_monitor_active'] =  'var tab = $( "#tabs" ).find( ".ui-tabs-nav li:eq(1)" ).remove(); 
			    var panelId = tab.attr( "aria-controls" );
                $( "#" + panelId ).remove();
                $( "#tabs" ).tabs( "refresh" );';
			}
			
			$this->plantilla->load("configuracion/configuracion");
			
			$tabs[] = array( 
				'title' => 'Sondas',
				'urlBase' => true,
				'href'	=> 'config/cfgSondas',
				'protec' => 'CONFIG_TABS_SONDA'
			);
			
			$tabs[] = array( 
				'title' => 'Monitores',
				'urlBase' => true,
				'href'	=> 'config/cfgMonitores',
				'protec' => 'CONFIG_TABS_MONITORES'
			);
			
			$tabs[] = array( 
				'title' => 'Graficos',
				'urlBase' => true,
				'href'	=> 'config/cfgGraph',
				'protec' => 'CONFIG_TABS_GRAPH'
			);
			
			$tabs[] = array( 
				'title' => 'Pantallas',
				'urlBase' => true,
				'href'	=> 'configScreen/cfgPantallas',
				'protec' => 'CONFIG_TABS_WINDOWS'
			);

			$tabs[] = array( 
				'title' => 'Plan',
				'urlBase' => true,
				'href'	=> 'config/cfgPlanes',
				'protec' => 'CONFIG_TABS_PLANES'
			);
            
            $tabs[] = array( 
                'title' => 'Profile',
                'urlBase' => true,
                'href'  => 'config/cfgProfiles',
                'protec' => 'CONFIG_TABS_PROFILE'
            );
			
			$tabs[] = array( 
				'title' => 'Grupos',
				'urlBase' => true,
				'href'	=> 'config/cfgGrupos',
				'protec' => 'CONFIG_TABS_GRUPOS'
			);
			
			$tabs[] = array( 
				'title' => 'Ubicación',
				'urlBase' => true,
				'href'	=> 'config/cfgUbicacion',
				'protec' => 'CONFIG_TABS_UBICACION'
			);
			
			$tabs[] = array( 
				'title' => 'Mapa',
				'urlBase' => true,
				'href'	=> 'config/getMaps',
				'protec' => 'CONFIG_TABS_MAPS'
			);
						
			$tps_index_control['tabs'] = $this->bmonitor->getTabs($tabs);
			
			$this->plantilla->set($tps_index_control);
			echo $this->plantilla->get();
			$this->plantilla->cacheClose();
		}

		public function administracion()
		{
			$valida = $this->protect->access_page('ADMINISTRACION');

			if($valida) {
				$this->plantilla->cacheJSON('page_administracion_ok',8640000);	
			} else {
				$this->plantilla->cacheJSON('page_administracion_nok',8640000);	
			}
			
			$this->plantilla->load_sec("header", "denegado",$valida);
			
			$tps_index_control['header'] = $this->plantilla->get();
			
			$tps_index_control['menu'] = $this->bmonitor->getMenu('administracion');
			
			$this->plantilla->load("footer", 'sitio/vista/');
			$tps_index_control['footer'] = $this->plantilla->get();

			$this->plantilla->load("administracion/administracion");
			
			$tabs[] = array( 
					'title' => 'Usuarios',
					'urlBase' => true,
					'href'	=> 'admin/user',
					'protec' => 'ADMINISTRACION_TAB_USER'
			);
			
			$tabs[] = array( 
					'title' => 'Grupos',
					'urlBase' => true,
					'href'	=> 'admin/groups',
					'protec' => 'ADMINISTRACION_TAB_GROUPS'
			);

			$tabs[] = array( 
					'title' => 'Auditoria',
					'urlBase' => true,
					'href'	=> 'admin/auditoria',
					'protec' => 'ADMINISTRACION_TAB_AUDIT'
			);
			
			$tabs[] = array( 
					'title' => 'Plantilla',
					'urlBase' => true,
					'href'	=> 'admin/template',
					'protec' => 'ADMINISTRACION_TAB_TEMPLATE'
			);

			$tabs[] = array( 
					'title' => 'Firmware',
					'urlBase' => true,
					'href'	=> 'admin/firmware',
					'protec' => 'ADMINISTRACION_TAB_FIRMWARE'
			);
			
			$tps_index_control['tabs'] = $this->bmonitor->getTabs($tabs);
			
			$tps_index_control['user_lang'] = $this->language->USER;
			$tps_index_control['groups_lang'] = $this->language->GROUPS;
			$tps_index_control['page_lang'] = $this->language->PAGINA;
			$tps_index_control['audi_lang'] = $this->language->AUDITORIA;
			$tps_index_control['templates_lang'] = $this->language->TEMPLATE_LANG;
			$tps_index_control['parameters_lang'] = $this->language->PARAMETROS;
			$tps_index_control['import_lang'] = $this->language->IMPORT;
			$this->plantilla->set($tps_index_control);
			echo $this->plantilla->get();
			$this->plantilla->cacheClose();
		}
	}

?>