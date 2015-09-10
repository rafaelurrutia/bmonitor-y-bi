<script>
jQuery(function($){ 	
	$( "#tabs_form_edit_sonda" ).tabs({
			ajaxOptions: {
				error: function( xhr, status, index, anchor ) {
					$( anchor.hash ).html( "Error de conexion con el servidor" );
				}
			}
	});
	$("#sonda_mac_wan").mask("**:**:**:**:**:**");
	$("#sonda_mac_lan").mask("**:**:**:**:**:**");
	$('#sonda_ip_lan').ipAddress();
	$('#sonda_netmask_lan').ipAddress();
	$('#sonda_ip_wan').ipAddress();
})
</script>

<p class="validateTips">All form fields are required.</p>
<div id="tabs_form_sonda">
		<form id="form_sonda" class="form_sonda">
		
		<ul>
			<li><a href="#sonda_tabs_1">General</a></li>
			<li><a href="#sonda_tabs_2">Configuration</a></li>
			<li><a href="#sonda_tabs_3">{sonda_name}</a></li>
		</ul>
		
		<div id="sonda_tabs_1">	
				
			<fieldset class="ui-widget-content ui-corner-all">
				<div class="row ui-widget-content ui-corner-all">
					<label for="sonda_name" class="col1">Name</label>
					<span class="col2"><input type="text" name="sonda_name" id="sonda_name" value="{sonda_host}" class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row ui-widget-content ui-corner-all">
					<label for="sonda_group" class="col1">Group</label>
					<span class="col2"><select name="sonda_group" id="sonda_group" value="{sonda_group}" class="select">
						{option_group}
					</select></span>
				</div>
				<div class="row">
					<label for="sonda_plan" class="col1">Plan</label>
					<span class="col2"><select name="sonda_plan" id="sonda_plan" value="{sonda_plan}" class="select">
						{option_plan}
					</select></span>
				</div>
				<div class="row">
					<label for="sonda_dns" class="col1">DNS Name</label>
					<span class="col2"><input type="text" name="sonda_dns" id="sonda_dns" value="{sonda_dns}"  class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_ip_wan" class="col1">WAN IP Address</label>
					<span class="col2"><input type="text" name="sonda_ip_wan" id="sonda_ip_wan" value="{sonda_ip_wan}"  class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_mac_wan" class="col1">WAN MAC</label>
					<span class="col2"><input type="text" name="sonda_mac_wan" id="sonda_mac_wan" value="{sonda_mac_wan}" class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_ip_lan" class="col1">LAN IP Address</label>
					<span class="col2"><input type="text" name="sonda_ip_lan" id="sonda_ip_lan" value="{sonda_ip_lan}"  class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_netmask_lan" class="col1">LAN Mask</label>
					<span class="col2"><input type="text" name="sonda_netmask_lan" id="sonda_netmask_lan"  value="{sonda_netmask_lan}" class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_mac_lan" class="col1">LAN MAC</label>
					<span class="col2"><input type="text" name="sonda_mac_lan" id="sonda_mac_lan" value="{sonda_mac_lan}" class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<input type="hidden" name="sonda_id" id="sonda_id" value="{sonda_id_sonda}" class="input ui-widget-content ui-corner-all" />
			</fieldset>
			
		</div>
		
		<div id="sonda_tabs_2">
			<fieldset class="ui-widget-content ui-corner-all">
			{input_config}
			</fieldset>
		</div>

		<div id="sonda_tabs_3">
			{input_opcional}
		</div>
				
	</form>
</div>