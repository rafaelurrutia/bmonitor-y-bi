<p class="validateTipsPlanEdit">Todo los elementos del formulario son requeridos.</p>
<form id="form_edit_plan" class="form_sonda">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="plan_edit_plan" class="col1">Nombre</label>
			<span class="col2"><input type="text" name="plan_edit_plan" id="plan_edit_plan" value="{plan_edit_plan}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="plan_edit_plandesc" class="col1">Descripción</label>
			<span class="col2"><input type="text" name="plan_edit_plandesc" id="plan_edit_plandesc" value="{plan_edit_plandesc}"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="plan_edit_planname" class="col1">Descripción corta</label>
			<span class="col2"><input type="text" name="plan_edit_planname" id="plan_edit_planname" value="{plan_edit_planname}"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
        <div class="row">
            <label for="plan_edit_codedown" class="col1">Codigo Plan Bajada</label>
            <span class="col2"><input type="text" name="plan_edit_codedown" id="plan_edit_codedown" value="{plan_edit_codedown}" class="input ui-widget-content ui-corner-all" /></span>
        </div>
        <div class="row">
            <label for="plan_edit_codeuplo" class="col1">Codigo Plan Subida</label>
            <span class="col2"><input type="text" name="plan_edit_codeuplo" id="plan_edit_codeuplo" value="{plan_edit_codeuplo}" class="input ui-widget-content ui-corner-all" /></span>
        </div>        <div class="row">
            <label for="plan_edit_codclass" class="col1">Codigo Clase</label>
            <span class="col2"><input type="text" name="plan_edit_codclass" id="plan_edit_codclass" value="{plan_edit_codclass}" class="input ui-widget-content ui-corner-all" /></span>
        </div>
		<div class="row">
			<label for="plan_edit_groupid" class="col1">Grupos: (Arrastre los grupos a la derecha para asignar)</label>
		</div>
		<div class="row">
			<div id="demo">
			<ul class="monitor_groupid_1" id="plan_edit_groupid_1" class="ui-corner-all">
				{menu_group_inactive}
			</ul>
			<ul class="monitor_groupid_2" id="plan_edit_groupid_2" class="ui-corner-all">
				{menu_group_active}
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
					<td><input type="text" name="plan_edit_nacD" id="plan_edit_nacD" value="{plan_edit_nacD}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_nacDS" id="plan_edit_nacDS" value="{plan_edit_nacDS}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_nacDT" id="plan_edit_nacDT" value="{plan_edit_nacDT}" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th>NAC Subida</th>
					<td><input type="text" name="plan_edit_nacU" id="plan_edit_nacU" value="{plan_edit_nacU}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_nacUS" id="plan_edit_nacUS" value="{plan_edit_nacUS}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_nacUT" id="plan_edit_nacUT" value="{plan_edit_nacUT}" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th>LOC Bajada</th>
					<td><input type="text" name="plan_edit_locD" id="plan_edit_locD" value="{plan_edit_locD}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_locDS" id="plan_edit_locDS" value="{plan_edit_locDS}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_locDT" id="plan_edit_locDT" value="{plan_edit_locDT}" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th>LOC Subida</th>
					<td><input type="text" name="plan_edit_locU" id="plan_edit_locU" value="{plan_edit_locU}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_locUS" id="plan_edit_locUS" value="{plan_edit_locUS}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_locUT" id="plan_edit_locUT" value="{plan_edit_locUT}" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th>INT Bajada</th>
					<td><input type="text" name="plan_edit_intD" id="plan_edit_intD" value="{plan_edit_intD}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_intDS" id="plan_edit_intDS" value="{plan_edit_intDS}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_intDT" id="plan_edit_intDT" value="{plan_edit_intDT}" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
				<tr>
					<th>INT Subida</th>
					<td><input type="text" name="plan_edit_intU" id="plan_edit_intU" value="{plan_edit_intU}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_intUS" id="plan_edit_intUS" value="{plan_edit_intUS}" class=" ui-widget-content ui-corner-all" /></td>
					<td><input type="text" name="plan_edit_intUT" id="plan_edit_intUT" value="{plan_edit_intUT}" class=" ui-widget-content ui-corner-all" /></td>
				</tr>
			</table>
		</div>
		<div class="row">
			<label for="plan_edit_sysctl" class="col1">sysctl</label>
		</div>		
		<div class="row">
			<textarea name="plan_edit_sysctl" id="plan_edit_sysctl" rows="30" class="textarea ui-corner-all" {plan_sysctl_disable}>{plan_edit_sysctl}</textarea>
		</div>
		<div class="row">
			<label for="plan_edit_ppp" class="col1">ppp</label>
		</div>		
		<div class="row">
			<textarea name="plan_edit_ppp" id="plan_edit_ppp" rows="30" class="textarea ui-corner-all" {plan_ppp_disable}>{plan_edit_ppp}</textarea>
		</div>
	</fieldset>
</form>