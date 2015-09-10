<p class="validateTips">All form fields are required.</p>
<form id="form_new_group" class="form_sonda">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="group_name" class="col1">Name</label>
			<span class="col2"><input type="text" name="group_name" id="group_name" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="group_type" class="col1">Monitor Type</label>
			<span class="col2"><select name="group_type" id="group_type" class="select">
				{option_type}
			</select></span>
		</div>
		<div class="row">
			<label for="group_timezone" class="col1">Timezone</label>
			<span class="col2"><select name="group_timezone" id="group_timezone" class="select">
				{option_timezone}
			</select></span>
		</div>
		<div class="row"><label for="group_snmp_monitor" class="col1">SNMP Monitor</label>
		<span class="col2"><input type="checkbox" class="checkbox" name="group_snmp_monitor" value="1" checked></span></div>
	</fieldset>
</form>