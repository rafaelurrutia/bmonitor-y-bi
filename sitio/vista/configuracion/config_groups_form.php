<form id="{form_id_group}" class="formUI">

	<div id="{form_id_group}_validateTips" class="validateTips ui-widget">
		<div class="ui-state-highlight ui-corner-all">
			<p>
				<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
				{FORM_ALL_PARAM_REQUIRED}
			</p>
		</div>
	</div>

	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="group_name" class="col1">{NAME}</label>
			<span class="col2 ">
				<input type="text" name="group_name" id="group_name" value="{group_name}" class="input ui-widget-content ui-corner-all" />
			</span>
		</div>
		<div class="row">
			<label for="group_type" class="col1">{TYPE_MONITORS}</label>
			<span class="col2">
				<select name="group_type" id="group_type"  class="select">
					{option_type}
				</select></span>
		</div>
		<div class="row" id="group_items" style="display: none">
			<label for="group_item" class="col1">Items</label>
			<span class="col2">
				<select name="group_item" id="group_item"  class="select">
					<option value="1">{NONE}</option>
					<option value="2">{SHARE}</option>
					<option value="3">{CLONE}</option>
				</select></span>
		</div>
		<div class="row">
			<label for="group_snmp_monitor" class="col1">SNMP Monitor</label>
			<span class="col2">
				<input type="checkbox" class="checkbox" name="group_snmp_monitor" value="1" {group_snmp_monitor}>
			</span>
		</div>
		<div style="display: none" class="row">
			<label for="group_id_template" class="col1">Template</label>
			<span class="col2">
				<select name="group_id_template" id="group_id_template" class="select">
					<option selected value="3">QoS</option>
					{option_template}
				</select> </span>
		</div>
	</fieldset>
</form>