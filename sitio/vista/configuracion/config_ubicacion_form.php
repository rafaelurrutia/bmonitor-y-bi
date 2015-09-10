<p id="{config_id_form}_validateTips" class="validateTips">Todo los elementos del formulario son requeridos.</p>
<form id="{config_id_form}" class="form_sonda">
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="location_establecimiento" class="col1">Nombre Establecimiento</label>
			<span class="col2">
				<input type="text" name="location_establecimiento" id="location_establecimiento" value="{establecimiento}" class="input ui-widget-content ui-corner-all" />
			</span>
		</div>
		<div class="row">
			<label for="location_sostenedor" class="col1">Nombre Contacto</label>
			<span class="col2"><input type="text" name="location_sostenedor" id="location_sostenedor" value="{sostenedor}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="location_rut" class="col1">RUT</label>
			<span class="col2"><input type="text" name="location_rut" id="location_rut" value="{rut}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="location_nodo" class="col1">Nodo</label>
			<span class="col2"><input type="text" name="location_nodo" id="location_nodo" value="{nodo}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="location_cuadrante" class="col1">Cuadrante</label>
			<span class="col2"><input type="text" name="location_cuadrante" id="location_cuadrante" value="{cuadrante}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="location_rdb" class="col1">Código localidad (RDB)</label>
			<span class="col2"><input type="text" name="location_rdb" id="location_rdb" value="{rdb}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="location_direccion" class="col1">Dirección</label>
			<span class="col2"><input type="text" name="location_direccion" id="location_direccion" value="{direccion}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="location_REGION_ID" class="col1">Region</label>
			<span class="col2">
				<select name="location_REGION_ID" id="location_REGION_ID" class="select">
					{option_region}
				</select>
			</span>
		</div>
		<div class="row">
			<label for="location_PROVINCIA_ID" class="col1">Provincia</label>
			<span class="col2">
				<select name="location_PROVINCIA_ID" id="location_PROVINCIA_ID" class="select">
					{option_provincia}
				</select>
			</span>
		</div>
		<div class="row">
			<label for="location_COMUNA_ID" class="col1">Comuna</label>
			<span class="col2">
				<select name="location_COMUNA_ID" id="location_COMUNA_ID" class="select">
					{option_comuna}
				</select>
			</span>
		</div>
		<div class="row">
			<label for="location_tarifa" class="col1">Renta Mensual $</label>
			<span class="col2"><input type="text" onkeypress="return isNumber(event);" name="location_tarifa" id="location_tarifa" value="{tarifa}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="location_viviendaid" class="col1">ID Vivienda</label>
			<span class="col2"><input type="text" name="location_viviendaid" id="location_viviendaid" value="{viviendaid}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="location_planfdt" class="col1">Plan</label>
			<span class="col2">
				<select name="location_planfdt" id="location_planfdt" class="select">
					{option_plan}
				</select>
			</span>
		</div>
        <div class="row">
            <label for="location_planfdt" class="col1">Tecnología</label>
            <span class="col2">
                <select name="location_planfdt" id="location_planfdt" class="select">
                    {option_technology}
                </select>
            </span>
        </div>
	</fieldset>
</form>