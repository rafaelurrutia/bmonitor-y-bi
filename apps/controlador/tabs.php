<?php 

	class tabs extends Control {
		
		public function monitor()
		{
			$this->plantilla->load("monitor", 'sitio/vista/');
			echo $this->plantilla->get();
		}
		
		public function inventario()
		{
			$this->plantilla->load("inventario", 'sitio/vista/');
			echo $this->plantilla->get();
		}
		
		public function fdt()
		{
			$this->plantilla->load("fdt", 'sitio/vista/');
			echo $this->plantilla->get();
		}
	}

?>