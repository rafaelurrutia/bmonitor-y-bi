<?php
class history extends Control  {

	public function graph()
	{
		$iditem = $_GET['idItem'];
		$host = $_GET['host'];
		//$name = $_GET['name'];
		
		$nombre = $this->conexion->queryFetch("SELECT `description`,`unit` FROM `bm_items` WHERE `id_item`=$iditem");
		
		$this->plantilla->load("monitor/history");
		$vars['idItem'] = $iditem;
		$vars['host'] = $host;
		$vars['title'] = $nombre[0]['description'];
		$vars['unit'] = $nombre[0]['unit'];
		
		$this->plantilla->set($vars);
		echo $this->plantilla->get();
	}

	public function table()
	{
		$iditem = $_GET['idItem'];
		$host = $_GET['host'];
        $type = $_GET['type'];
		$nombre = $this->conexion->queryFetch("SELECT `description`,`unit` FROM `bm_items` WHERE `id_item`=$iditem");
		
		$this->plantilla->load("monitor/table");
		$vars['idItem'] = $iditem;
		$vars['host'] = $host;
        $vars['type'] = $type;
		$vars['title'] = $nombre[0]['description'];
		$vars['unit'] = $nombre[0]['unit'];
		$this->plantilla->set($vars);
		echo $this->plantilla->get();
	}
		
	public function getGraph()
	{ 
		$iditem = $_GET['idItem'];
		$host = $_GET['host'];
		$callback = $_GET['callback'];
		$callback = explode("&", $callback);
		$callback = $callback[0];
		$name = $_GET['name'];
		 
		
		$delay_query = 'SELECT `delay` FROM `bm_items` WHERE `id_item`='.$iditem;
		
		$delay = $this->conexion->queryFetch($delay_query);
		$delay = $delay[0]['delay'];
		
	
	
		if((string)$name === (string)"hide") {
			$date = $this->graph->getGraph2("HIDE","fila1", false);
		} else {
			$date = $this->graph->getGraph2($iditem,$host,"fila1", false);
		}
		
		header("Content-type: text/json");
		if($date) {
			echo $callback.'(/* API Bmonitor */'.$date.')';
		} else {
			echo $callback.'(/* API Bmonitor */  [[0,0.0000]])';
		}
	}

	public function getTable()
	{ 

		$page = 1;
		$sortname = 'clock'; 
		$sortorder = 'ASC'; 
		$qtype = ''; 
		$query = ''; 
		$rp = 15;
		$iditem = $_POST['idItem'];
		$host = $_POST['host'];
        $type = $_POST['type'];
		
		// Validaciones de los parametros enviados por la libreria flexigrid
		if (isset($_POST['page'])) {
		        $page = $_POST['page'];
		}
		if (isset($_POST['sortname']) && ($_POST['sortname']  != 'undefined') && ($_POST['sortname']  != '')) {
		        $sortname = $_POST['sortname'];
		}
		if (isset($_POST['sortorder']) && ($_POST['sortorder']  != 'undefined') && ($_POST['sortorder']  != '')) {
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
		
		$data = array();
		$data['page'] = $page;
		$sortSql = " ORDER BY $sortname $sortorder";
		$pageStart = ($page-1)*$rp;
		$limitSql = " LIMIT $pageStart, $rp";
        
        //Validando
		
		if($type == 'float'){
		    $table = 'bm_history';
		} else {
		    $table = 'bm_history_str';
		}
		
		
		//Total
		
		$totalItems = "SELECT COUNT(*) as total FROM `$table` WHERE `id_host` = $host  AND `id_item` = '$iditem';";
		
		$itemsTotal = $this->conexion->queryFetch($totalItems);
				
							
		$data['total'] = $itemsTotal[0]['total'];
		
		//Get Rows
		 
		$select_inicial = "SELECT `clock`,`value` FROM `$table` WHERE `id_host` = $host  AND `id_item` = '$iditem'";
		
		$sql_final = $select_inicial.$sortSql.$limitSql;
		
		$valuetable = $this->conexion->queryFetch($sql_final);
										
		if($valuetable) {

			$data['rows'] = array();
			
			foreach ($valuetable as $key => $value) {
				
				$data['rows'][] = array(
					'id' => $key,
					'cell' => array($this->basic->date2str($this->parametro->get('DATE_FORMAT','d M Y H:i:s'),$value['clock']), $value['value'])
				);
			}
		}
		
		echo json_encode($data);
	}
}
?>