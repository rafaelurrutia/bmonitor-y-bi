<p class="validateTips_edit">Todo los elementos del formulario son requeridos.</p>
<form id="form_edit_monitor" accept-charset="utf-8" method="post" class="form_sonda">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="monitor_name" class="col1">Nombre descriptivo</label>
			<span class="col2"><input type="text" name="monitor_name" id="monitor_name" value="{monitor_name}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="monitor_description" class="col1">Monitor</label>
			<span class="col2"><input type="text" name="monitor_description" id="monitor_description" value="{monitor_description}"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="monitor_type" class="col1">Tipo monitor</label>
			<span class="col2"><select name="monitor_type" id="monitor_type" class="select">
				{option_type_monitor}
			</select></span>
		</div>

		<div class="row">
			<label for="monitor_type_item" class="col1">Tipo dato</label>
			<span class="col2"><select name="monitor_type_item" id="monitor_type_item" class="select">
				{option_type_item}
			</select></span>
		</div>
				
		<div id="snmp_edit_1" class="row ui-widget-content ui-corner-all">
			<label for="monitor_snmp_community" class="col1">Comunidad SNMP</label>
			<span class="col2"><input type="text" name="monitor_snmp_community" value="{monitor_snmp_community}" id="monitor_snmp_community"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
		
		<div id="snmp_edit_2" class="row">
			<label for="monitor_snmp_oid" class="col1">SNMP OID</label>
			<span class="col2"><input type="text" name="monitor_snmp_oid" id="monitor_snmp_oid" value="{monitor_snmp_oid}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		
		<div id="snmp_edit_3" class="row">
			<label for="monitor_snmp_port" class="col1">Puerto SNMP</label>
			<span class="col2"><input type="text" name="monitor_snmp_port" value="{monitor_snmp_port}" id="monitor_snmp_port" class="input ui-widget-content ui-corner-all" /></span>
		</div>

		<div class="row">
			<label for="monitor_unit" class="col1">Unidad</label>
			<span class="col2"><input type="text" name="monitor_unit" id="monitor_unit" value="{monitor_unit}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="monitor_delay" class="col1">Intervalo de actualización (en segundos)</label>
			<span class="col2"><input type="text" name="monitor_delay" id="monitor_delay" value="{monitor_delay}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="monitor_history" class="col1">Conservar el histórico durante (en días)</label>
			<span class="col2"><input type="text" name="monitor_history" id="monitor_history" value="{monitor_history}" class="input ui-widget-content ui-corner-all" value="365" /></span>
		</div>
		<div class="row">
			<label for="monitor_trend" class="col1">Conservar las tendencias durante (en días)</label>
			<span class="col2"><input type="text" name="monitor_trend" id="monitor_trend" value="{monitor_trend}" class="input ui-widget-content ui-corner-all" value="365" /></span>
		</div>
		<div class="row">
			<label for="monitor_status" class="col1">Estado</label>
			<span class="col2"><select name="monitor_status" id="monitor_status" class="select">
				<option selected value="1">Activo</option>
				<option value="2">Desactivado</option>
			</select></span>
		</div>
		<div class="row">
			Grupos: (Arrastre los grupos a la derecha para asignar)
		</div>
		<div class="row">
			<div id="demo">
			<ul class="monitor_groupid_1" id="monitor_groupid_1" class="ui-corner-all">

				{monito_edit_group_inactive}
			</ul>
			
			<ul class="monitor_groupid_2" id="monitor_groupid_2" class="ui-corner-all">
				{monito_edit_group_active}
			</ul>
			</div>
		</div>
	</fieldset>
</form>