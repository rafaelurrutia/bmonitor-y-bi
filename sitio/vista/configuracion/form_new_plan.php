<p class="validateTipsPlan">Todo los elementos del formulario son requeridos.</p>
<form id="form_new_plan" class="form_sonda">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="plan_plan" class="col1">Nombre</label>
			<span class="col2"><input type="text" name="plan_plan" id="plan_plan" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="plan_plandesc" class="col1">Descripción</label>
			<span class="col2"><input type="text" name="plan_plandesc" id="plan_plandesc"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="plan_planname" class="col1">Descripción corta</label>
			<span class="col2"><input type="text" name="plan_planname" id="plan_planname"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
        <div class="row">
            <label for="plan_codedown" class="col1">Codigo Plan Bajada</label>
            <span class="col2"><input type="text" name="plan_codedown" id="plan_codedown"  class="input ui-widget-content ui-corner-all" /></span>
        </div>
        <div class="row">
            <label for="plan_codeuplo" class="col1">Codigo Plan Subida</label>
            <span class="col2"><input type="text" name="plan_codeuplo" id="plan_codeuplo"  class="input ui-widget-content ui-corner-all" /></span>
        </div>        <div class="row">
            <label for="plan_codclass" class="col1">Codigo Clase</label>
            <span class="col2"><input type="text" name="plan_codclass" id="plan_codclass"  class="input ui-widget-content ui-corner-all" /></span>
        </div>
		<div class="row">
			<label for="plan_groupid" class="col1">Grupos: (Arrastre los grupos a la derecha para asignar)</label>
		</div>
		<div class="row">
			<div id="demo">
			<ul class="monitor_groupid_1" id="plan_groupid_1" class="ui-corner-all">
				{menu_group_inactive}
			</ul>
			<ul class="monitor_groupid_2" id="plan_groupid_2" class="ui-corner-all">
		
			</ul>
			</div>
		</div>
		<div class="row">
			<form id="neutralidad_pesos_form">
			<table class="tableBSW" cellspacing="0" cellpadding="0">
				<tr>
					<th>Destino</th>
					<th>Velocidad (kbps)</th>
					<th>Sesiones</th>
					<th>Tiempo (seg)</th>
				</tr>
				<tr>
					<th>NAC Bajada</th>
					<td><input type="text" name="plan_nacD" id="plan_nacD" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_nacDS" id="plan_nacDS" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_nacDT" id="plan_nacDT" value="0" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th>NAC Subida</th>
					<td><input type="text" name="plan_nacU" id="plan_nacU" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_nacUS" id="plan_nacUS" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_nacUT" id="plan_nacUT" value="0" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th>LOC Bajada</th>
					<td><input type="text" name="plan_locD" id="plan_locD" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_locDS" id="plan_locDS" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_locDT" id="plan_locDT" value="0" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th>LOC Subida</th>
					<td><input type="text" name="plan_locU" id="plan_locU" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_locUS" id="plan_locUS" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_locUT" id="plan_locUT" value="0" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th>INT Bajada</th>
					<td><input type="text" name="plan_intD" id="plan_intD" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_intDS" id="plan_intDS" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_intDT" id="plan_intDT" value="0" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th>INT Subida</th>
					<td><input type="text" name="plan_intU" id="plan_intU" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_intUS" id="plan_intUS" value="0" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_intUT" id="plan_intUT" value="0" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
			</table>
		</div>
		<div class="row">
			<label for="plan_sysctl" class="col1">sysctl</label>
		</div>		
		<div class="row">
			<textarea name="plan_sysctl" id="plan_sysctl" rows="30" class="textarea ui-corner-all" value="">{value_sysctl}</textarea>
		</div>
		<div class="row">
			<label for="plan_ppp" class="col1">ppp</label>
		</div>		
		<div class="row">
			<textarea name="plan_ppp" id="plan_ppp" rows="30" class="textarea ui-corner-all" value="">{value_ppp}</textarea>
		</div>
	</fieldset>
</form>