<?php

	$sourceJsonFile="RU_sections/tablaResumenSondasNewYork/tablaResumenSondasNewYork.json";
	
	$string = file_get_contents($sourceJsonFile);
	$elementsHtml=json_decode($string);
	$results = count($elementsHtml->data);
	
?>

<div id="tablaResumenSondasNewYork" style="display: none;" >
	<div id="cabecera1" style="background-color:#FFA726 ">

		<div align="left" style="font-size: 12pt">
			<strong>Total agents in the state of <?php echo $elementsHtml->informacionGeneral->nombreEstado; ?>: </strong>
			<span><?php echo $elementsHtml->informacionGeneral->totalSondasEstado; ?></span>
			<br>
		</div>

		<strong>Registered agents: </strong>
		<span><?php echo $elementsHtml->informacionGeneral->totalSondasEstado; ?></span>

		<strong>| Available agents: </strong>
		<span><?php echo $elementsHtml->informacionGeneral->sondasDisponibles; ?></span>

		<strong>| Not available agents: </strong>
		<span><?php echo $elementsHtml->informacionGeneral->sondasNoDisponibles; ?></span>

		<strong>| % availability: </strong>
		<span><?php echo $elementsHtml->informacionGeneral->porcentajeDisponibilidad; ?></span>

	</div>
	<table id="table-sparkline">
		<thead>
			<tr>
				<th>Name agent</th>

				<th>% Oc. hard disk</th>
				<th>% Oc. memory</th>

				<th>Nª restart agent</th>
				<th>Nª restart app.</th>
				<th>last update app.</th>

				<th>Upload graphic</th>
				<th>Download graphic</th>
				<th>Latency graphic</th>

			</tr>
		</thead>
		<tbody id="tbody-sparkline" class="click">
			
			<?php for ($r = 0; $r < 16; $r++){ ?>
			<tr>
				<th><i class="fa fa-circle" style="color:<?php echo $elementsHtml->data[$r]->color; ?>"></i> <?php echo $elementsHtml->data[$r]->nombreSonda; ?></th>

				<td><?php echo $elementsHtml->data[$r]->porcentajeOcupacionDisco; ?></td>
				<td><?php echo $elementsHtml->data[$r]->porcentajeOcupacionMemoria; ?></td>

				<td><?php echo $elementsHtml->data[$r]->numeroReiniciosSonda; ?></td>
				<td><?php echo $elementsHtml->data[$r]->numeroReiniciosAplicacion; ?></td>
				<td><?php echo $elementsHtml->data[$r]->fechaUltimaActualizacion; ?></td>

				<td data-sparkline="<?php echo $elementsHtml->data[$r]->graficoBajada; ?> "/>
				<td data-sparkline="<?php echo $elementsHtml->data[$r]->graficoSubida; ?> "/>
				<td data-sparkline="<?php echo $elementsHtml->data[$r]->graficoLatencia; ?> "/>
				
			</tr>
			<?php } ?>
		
		</tbody>
	</table>

</div>