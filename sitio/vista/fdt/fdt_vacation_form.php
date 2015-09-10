<p class="{form_id_template}_validateTips">Todo los elementos del formulario son requeridos.</p>
<form id="{form_id_template}" class="form_sonda">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="vacation_host" class="col1">Colegio</label>
			<span class="col2">	
				<select {vacation_host_disabled} name="vacation_host" id="vacation_host" class="select">
					{option_vacation_host}
				</select>
			</span>
		</div>
		<div class="row">
			<label for="vacation_fecha_inicio" class="col1">Fecha Inicio</label>
			<span class="col2"><input type="text" name="{form_id_template}_vacation_fecha_inicio" id="{form_id_template}_vacation_fecha_inicio" value="{vacation_fecha_inicio}" class="input ui-widget-content ui-corner-all" {vacation_fecha_inicio_disabled}/></span>
		</div>
		<div class="row">
			<label for="vacation_fecha_fin" class="col1">Fecha Fin</label>
			<span class="col2"><input type="text" name="{form_id_template}_vacation_fecha_fin" id="{form_id_template}_vacation_fecha_fin" value="{vacation_fecha_fin}" class="input ui-widget-content ui-corner-all" {vacation_fecha_fin_disabled}/></span>
		</div>
		<div class="row">
			<label for="vacation_motivo" class="col1">Motivo</label>
			<span class="col2">
				<select {vacation_motivo_disabled} name="vacation_motivo" id="vacation_motivo" class="select">
					{option_vacation_motivo}
				</select>
			</span>
		</div>
	</fieldset>
</form>