<?php

class generate
{

    function __construct($parametro, $conexion, $logs, $language, $protect, $basic, $plantilla)
    {
        $this->parametro = $parametro;
        $this->conexion = $conexion;
        $this->logs = $logs;
        $this->language = $language;
        $this->protect = $protect;
        $this->basic = $basic;
        $this->plantilla = $plantilla;
    }

    public function getMenu($menus, $active = false)
    {
        if (is_array($menus)) {
            $result_menu = '';
            $this->plantilla->load("basic/menuStyle");

            if (count($menus) > 0) {
                foreach ($menus as $key => $menu) {
                    $valida = true;

                    if (isset($menu['protec'])) {
                        $valida = $this->protect->access_page($menu['protec']);
                    }

                    if ($valida && (isset($menu['href'])) && (isset($menu['title']))) {

                        if (isset($menu['urlBase']) && $menu['urlBase'] === true) {
                            $href = URL_BASE . $menu['href'];
                        } else {
                            $href = $menu['href'];
                        }

                        if ($menu['protec'] == $active) {
                            $class = ' ui-state-active';
                        } else {
                            $class = '';
                        }

                        $result_menu .= "<li><a class='ui-state-default ui-corner-all $class' href='" . $href . "'>" . $menu['title'] . "</a></li>";

                    }
                }

                $tps_index_control['menu_permitido'] = $result_menu;
            } else {
                $tps_index_control['menu_inicio'] = '<!--';
                $tps_index_control['menu_fin'] = '-->';
            }

            if(!isset($this->language->WELCOME) || $this->language->WELCOME == '' ) {
                $tps_index_control['welcome'] = 'Welcome';
            } else {
                $tps_index_control['welcome'] = $this->language->WELCOME;
            }

            if(!isset($this->language->EXIT) || $this->language->EXIT == '' ) {
                $tps_index_control['exit'] = 'Exit';
            } else {
                $tps_index_control['exit'] = $this->language->EXIT;
            }
            
            if(!isset($this->language->PROFILE) || $this->language->PROFILE == '' ) {
                $tps_index_control['profile'] = 'Profile';
            } else {
                $tps_index_control['profile'] = $this->language->PROFILE;
            }
			
			$valida = $this->protect->allowed('APPS_BI');
			
			if($valida) {
				$tps_index_control['bmonitorLink'] = '<li style="float: right;">
					<a class="ui-state-default ui-corner-all" href="/bi" id="bi">Bi</a>
				</li>';
			}
                        
            $this->plantilla->set($tps_index_control);
            return $this->plantilla->get();
        } else {
            return false;
        }
    }

    public function getMenuBmonitor($menus, $active = false)
    {
        if (is_array($menus)) {
            $result_menu = '';
            $this->plantilla->load("basic/menu");

            if (count($menus) > 0) {
                foreach ($menus as $key => $menu) {
                    $valida = true;

                    if (isset($menu['protec'])) {
                        $valida = $this->protect->access_page($menu['protec']);
                    }

                    if ($valida && (isset($menu['href'])) && (isset($menu['title']))) {

                        if (isset($menu['urlBase']) && $menu['urlBase'] === true) {
                            $href = URL_BASE . $menu['href'];
                        } else {
                            $href = $menu['href'];
                        }

                        if ($menu['title'] == $active) {
                            $class = ' active';
                        } else {
                            $class = '';
                        }

                        $result_menu[] = "<li class='tabsMenu'><a class='$class' href='" . $href . "'>" . $menu['title'] . "</a></li>";

                    }
                }
                
                if (is_array($result_menu) && count($result_menu) > 0) {
                    $resultMenu = join('<li class="sep tabsMenu">|</li>', $result_menu);
                }

                $tps_index_control['menu_permitido'] = $resultMenu;
            } else {
                $tps_index_control['menu_inicio'] = '<!--';
                $tps_index_control['menu_fin'] = '-->';
            }

            $tps_index_control['welcome'] = $this->language->WELCOME;
            $tps_index_control['exit'] = $this->language->EXIT;
            $tps_index_control['profile'] = $this->language->PROFILE;

            $this->plantilla->set($tps_index_control);
            return $this->plantilla->get();
        } else {
            return false;
        }
    }

    public function getTabs($tabs, $idTabs = 'tabs')
    {
        if (is_array($tabs)) {

            $result_tabs = '<div id="' . $idTabs . '"><ul>';
            foreach ($tabs as $key => $tab) {
                $valida = true;

                if (isset($tab['protec'])) {
                    $valida = $this->protect->access_page($tab['protec']);
                }

                if ($valida && (isset($tab['href'])) && (isset($tab['title']))) {

                    if (isset($tab['urlBase']) && $tab['urlBase'] === true) {
                        $href = (URL_BASE == '/') ? '' : URL_BASE;
                        $href .= '/' . $tab['href'];

                    } else {
                        $href = $tab['href'];
                    }

                    $result_tabs .= '<li><a href="' . $href . '">' . $tab['title'] . '</a></li>';

                }
            }
            $result_tabs .= '</ul></div>';

            return $result_tabs;
        } else {
            return false;
        }
    }
    
    public function getButton($array,$libs = 'flexigrid'){
        if(is_array($array)) {
            
            if($libs == 'flexigrid') {
                 foreach ($array as $key => $button) {
                     $valida = $this->protect->access_page($button['protec']);
                     
                     if($valida) {
                         $generator[] = "{
                            name : '".$this->language->$button['name']."',
                            bclass : '".$button['bclass']."',
                            onpress : ".$button['onpress']."
                        }";
                     }
                     
                 }
                 
                 if(isset($generator)){
                     return 'buttons : ['.join(',', $generator).'],';
                 } else {
                     return '';
                 } 
            }
        } else {
            return false;
        }
    }

}
