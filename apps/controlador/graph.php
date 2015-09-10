<?php
class graphc extends Control  {

	public function getGraphPage()
	{
		$groups_arry = $this->bmonitor->getGroupsHost();
		
		
		
		$groups_option = $this->basic->getOption($groups_arry, 'groupid', 'name');
		
		$list_graph_arry = $this->graph->getGraphList();
		
		$list_graph_option = $this->basic->getOption($list_graph_arry, 'id_graph', 'name');
		
		$this->plantilla->load("graph_index");
		
		$tps_index_control['option_groups'] = $groups_option;
		
		$tps_index_control['option_graph'] = $list_graph_option;
		
		$this->plantilla->set($tps_index_control);
		
		echo $this->plantilla->get();
	}	

}