<p class="validateTips">All form fileds are requiered.</p>
<form id="form_edit_group" class="form_sonda">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="group_edit_name" class="col1">Name</label>
			<span class="col2"><input type="text" name="group_edit_name" id="group_edit_name" value="{group_edit_name}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="group_edit_type" class="col1">Monitor Type</label>
			<span class="col2"><select name="group_edit_type" id="group_edit_type" class="select">
				{option_type}
			</select></span>
		</div>
		<div class="row">
			<label for="group_edit_timezone" class="col1">Timezone</label>
			<span class="col2"><select name="group_edit_timezone" id="group_edit_timezone" class="select">
				{option_timezone}
			</select></span>
		</div>
		<div class="row"><label for="group_edit_snmp_monitor" class="col1">SNMP Monitor</label>
		<span class="col2"><input type="checkbox" class="checkbox" name="group_edit_snmp_monitor"  value="1" {group_edit_snmp_monitor}></span></div>
	</fieldset>
</form>