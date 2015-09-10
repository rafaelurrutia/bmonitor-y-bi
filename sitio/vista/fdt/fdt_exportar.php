<script type="text/javascript" src="{url_base}sitio/js/view.fdt_export.js"></script>
<div id="loading"></div>
<div id="imgLoading">
	<img widht="32" height="32" src="/sitio/img/ajax-loader.gif" alt="loading" title="loading" />
</div>
	
<div class="paneles ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
	
	<form id="fmFilter_export">
	<label for="fmGrupo_export" accesskey="g">Grupo</label>
	<select name="fmGrupo_export" id="fmGrupo_export">
		{option_groups}
	</select>
	
	<label for="fmMeses_export" accesskey="m">Mes</label>
	<select name="fmMeses_export" id="fmMeses_export">
		{option_meses}
	</select>
	
	<label for="fmAno_export" accesskey="a">Año</label>
	<select name="fmAno_export" id="fmAno_export">
		{option_anos}
	</select>
	</form>
</div>
<div id="tableResumenConsFDT" class="ui-grid ui-widget ui-widget-content ui-corner-all">
	<form id="table_export">
	<div class="ui-grid-header ui-widget-header ui-corner-top">Exportar</div>
	
	<table id="tableResumenConsFDT_int" class="ui-grid-content ui-widget-content">
		<thead>
			<tr>
				<th class="ui-state-default">Campo</th>
				<th class="ui-state-default">Seleccion</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td class="ui-widget-content"><label for="fdt_export_medicion" accesskey="m">Medición</label></td>
				<td class="ui-widget-content">
					<select name="fdt_export_medicion" id="fdt_export_medicion">
						{option_medicion}
					</select>
				</td>
			</tr>
			<tr>
				<td class="ui-widget-content"><label for="fdt_export_colegio" accesskey="a">Colegio</label></td>
				<td class="ui-widget-content">
					<select name="fdt_export_colegio" id="fdt_export_colegio">
						{option_colegios}
					</select>
				</td>
			</tr>
			<tr>
				<td colspan="2" class="ui-widget-content">
					<button style="float: left;" type='button' id='export-download'>Descargar</button>
					{excel}
					{cabecera}
				</td>
			</tr>
		</tbody>
	</table>
	</form>
</div>