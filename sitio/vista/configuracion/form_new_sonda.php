<p class="validateTips">Todo los elementos del formulario son requeridos.</p>
	<div id="tabs_form_new_sonda">
		<form id="form_new_sonda" class="form_sonda">
		
		<ul>
			<li><a href="#new_sonda_tabs_1">General</a></li>
			<li><a href="#new_sonda_tabs_2">Configuracion</a></li>
		</ul>
		
		<div id="new_sonda_tabs_1">	
				
			<fieldset class="ui-widget-content ui-corner-all">
				<div class="row">
					<label for="sonda_name" class="col1">Nombre</label>
					<span class="col2"><input type="text" name="sonda_name" id="sonda_name" value="{sonda_name}" class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_group" class="col1">Grupo</label>
					<span class="col2"><select name="sonda_group" id="sonda_group" value="{sonda_group}" class="select">
						{option_group}
					</select></span>
				</div>
				<div class="row">
					<label for="sonda_plan" class="col1">Plan</label>
					<span class="col2"><select name="sonda_plan" id="sonda_plan" class="select">
						<option value="0">Selecciona un grupo</option>
					</select></span>
				</div>
				<div class="row">
					<label for="sonda_dns" class="col1">Nombre DNS</label>
					<span class="col2"><input type="text" name="sonda_dns" id="sonda_dns"  class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_ip_wan" class="col1">Dirección IP WAN</label>
					<span class="col2"><input type="text" name="sonda_ip_wan" id="sonda_ip_wan"  class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_mac_wan" class="col1">Mac WAN</label>
					<span class="col2"><input type="text" name="sonda_mac_wan" id="sonda_mac_wan" class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_ip_lan" class="col1">Dirección IP LAN</label>
					<span class="col2"><input type="text" name="sonda_ip_lan" id="sonda_ip_lan"  class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_netmask_lan" class="col1">Macara LAN</label>
					<span class="col2"><input type="text" name="sonda_netmask_lan" id="sonda_netmask_lan" class="input ui-widget-content ui-corner-all" /></span>
				</div>
				<div class="row">
					<label for="sonda_mac_lan" class="col1">Mac LAN</label>
					<span class="col2"><input type="text" name="sonda_mac_lan" id="sonda_mac_lan" class="input ui-widget-content ui-corner-all" /></span>
				</div>
			</fieldset>
			
		</div>
		
		<div id="new_sonda_tabs_2">
			<fieldset class="ui-widget-content ui-corner-all">
			{input_config}
			</fieldset>
		</div>
		
		</form>
	</div>