<?php

	$sourceJsonFile="RU_sections/tablaResumenSondasPorEstados/tablaResumenSondasPorEstados.json";
	
	$string = file_get_contents($sourceJsonFile);
	$elementsHtml=json_decode($string);
	$results = count($elementsHtml->data);
	
?>
<div id="tablaResumenSondasPorEstados" >
	<div id="cabecera1" style="background-color:#2196F3; ">

		<div align="left">
			<strong>Summary of agent by state</strong> 
			<span style="float: right">
				Select package:
				<select>
				  <option value="0" selected="">Mobile broadband</option>
				  <option value="1">5 Mbps</option>
				  <option value="2">10 Mbps</option>
				  <option value="3">15 Mbps</option>
				</select>
			</span>	
			
			
			<br>
		</div>

	</div>
	<table id="table-sparkline">
		<thead>
			<tr>
				<th>Nª</th>
				<th>State</th>
				
				<th>Registered agents</th>
				<th>Available agents</th>
				<th>Not available agents</th>
				<th>% availability</th>

				<th>Download graphic</th>
				<th>Upload graphic</th>
				<th>Latency graphic</th>

			</tr>
		</thead>
		<tbody id="tbody-sparkline">
			<?php 		
						
				for ($r = 0; $r < $results; $r++){
			
			?>
			
			<tr>
				<th><?php echo($r + 1); ?></th>
				<th><i class="fa fa-circle" style="color:<?php echo $elementsHtml->data[$r]->color; ?>"></i> <?php echo $elementsHtml->data[$r]->nombreEstado; ?></th>

				<td><?php echo $elementsHtml->data[$r]->sondasRegistradas; ?></td>
				<td><?php echo $elementsHtml->data[$r]->sondasDisponibles; ?></td>
				<td><?php echo $elementsHtml->data[$r]->sondasNoDisponibles; ?></td>
				<td><?php echo $elementsHtml->data[$r]->porcentajeDisponibilidad; ?></td>

				<td data-sparkline="<?php echo $elementsHtml->data[$r]->graficoBajada; ?> "/>
				<td data-sparkline="<?php echo $elementsHtml->data[$r]->graficoSubida; ?>"/>
				<td data-sparkline="<?php echo $elementsHtml->data[$r]->graficoLatencia; ?> "/>
			</tr>
			
<?php } ?>
		</tbody>
	</table>

</div>