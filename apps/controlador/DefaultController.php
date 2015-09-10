<?php

class DefaultController extends Control
{

    public function index()
    {
        $valida = $this->protect->access_page('INDEX');
        
        if(isset($_SESSION['iduser'])){
            $idUser = $_SESSION['iduser'];
        } else {
            $idUser = 0;
        }
  
        $this->plantilla->loadSec("monitor/monitor", $valida, array("time" => 86400 , "code" => $idUser) );
  
        $section = new Section();
   
        $tps_index_control['menu'] = $section->menu('INDEX');
        $tps_index_control['header'] =  $section->header();
        $tps_index_control['footer'] = $section->footer();
        
        $tabs[] = array(
            'title' => 'Dashboard',
            'urlBase' => true,
            'href' => 'monitor/getDashboard',
            'protec' => 'INDEX_DASHBOARD'
        );
		
        $tabs[] = array(
            'title' => $this->language->ALERT,
            'urlBase' => true,
            'href' => 'monitor/alert',
            'protec' => 'INDEX_DASHBOARD'
        );

        $tabs[] = array(
            'title' => $this->language->LAST_DATE,
            'urlBase' => true,
            'href' => 'monitor/ultimafecha',
            'protec' => 'INDEX_ULTIMAFECHA'
        );

        $tabs[] = array(
            'title' => $this->language->GROUPED_CHARTS,
            'urlBase' => true,
            'href' => 'graphc/getGraphPage',
            'protec' => 'INDEX_GRAPH'
        );

        $tabs[] = array(
            'title' => $this->language->SIMPLE_CHARTS,
            'urlBase' => true,
            'href' => 'graphc/getGraphPageNew',
            'protec' => 'INDEX_GRAPH'
        );

        $tabs[] = array(
            'title' => $this->language->SCREENS,
            'urlBase' => true,
            'href' => 'graphc/getGraphWindows',
            'protec' => 'INDEX_WINDOWS'
        );
        
        $tabs[] = array(
            'title' => $this->language->MAP,
            'urlBase' => true,
            'href' => 'qoe/getMaps',
            'protec' => array(
                "code" => 'INDEX_MAPS',
                "title" => 'Home',
                "description" => "Show Maps"
            )
        );

        $tps_index_control['tabs'] = $this->bmonitor->getTabs($tabs);

        $this->plantilla->set($tps_index_control);
        $this->plantilla->finalize();
    }

}
?>