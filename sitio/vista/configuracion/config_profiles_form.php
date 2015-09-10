<script>
jQuery(function($){ 
$( "#tabs_form_edit_group" ).tabs({
		ajaxOptions: {
			error: function( xhr, status, index, anchor ) {
				$( anchor.hash ).html( "Server conection error" );
			}
		}
});
})
</script>
<p class="{form_id_group}_validateTips">All form fields are required.</p>
<div id="tabs_{form_id_group}">
	<form id="{form_id_group}" class="form_bsw">
		<ul>
			<li><a href="#new_group_tabs_1">General</a></li>
			{tabs_new_group_tabs_2_menu}
		</ul>
		<div id="new_group_tabs_1">		
			<fieldset class="ui-widget-content ui-corner-all">
				<div class="row">
					<label for="group_name" class="col1">Name</label>
					<span class="col2 short"><input type="text" name="group_name" id="group_name" value="{group_name}" class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="group_type" class="col1">Monitor Type</label>
					<span class="col2 short"><select name="group_type" id="group_type"  class="select">
						{option_type}
					</select></span>
				</div>
				<div class="row">
					<label for="group_snmp_monitor" class="col1">SNMP Monitor</label>
					<span class="col2 short"><input type="checkbox" class="checkbox" name="group_snmp_monitor" value="1" {group_snmp_monitor}></span>
				</div>
				<div class="row">
					<label for="group_id_template" class="col1">Template</label>
					<span class="col2 short"><select name="group_id_template" id="group_id_template" class="select">
						{option_template}
					</select></span>
				</div>
			</fieldset>
		</div>
		{tabs_new_group_tabs_2}
	</form>
</div>