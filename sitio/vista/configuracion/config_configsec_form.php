<div id="table_config_w" class="ui-grid ">
<p id="wizard_validateTips" class="validateTips">Todo los elementos del formulario son requeridos.</p>	
<form id="form_wizard_sonda" accept-charset="utf-8" method="post" class="form_sonda">
<table style="width: 100%" class="ui-grid-content ui-widget-content">
<thead>
	<tr>
		<th class="ui-state-default">Campo</th>
		<th class="ui-state-default">Seleccion</th>
	</tr>
</thead>
<tbody>
	<tr>
		<td class="ui-widget-content"><label for="config_group" accesskey="g">Grupo</label></td>
		<td class="ui-widget-content">
			<select name="config_group" id="config_group">
				{config_groups}
			</select></td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_ip" accesskey="i">IP:</label></td>
		<td class="ui-widget-content">		
			<input type="text" name="config_ip" id="config_ip" value="{config_ip}" disabled="disabled" />				
		</td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_mac" accesskey="m">MAC:</label></td>
		<td class="ui-widget-content">
			<input type="text" name="config_mac" id="config_mac" value="{config_mac}" disabled="disabled" />
		</td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_region" accesskey="s">Region:</label></td>
		<td class="ui-widget-content">						
			<select name="config_region" id="config_region">
				{config_region}
			</select>
		</td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_school" accesskey="s">Establecimiento:</label></td>
		<td class="ui-widget-content">						
			<select name="config_school" id="config_school">
				{config_school}
			</select>
		</td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_cparental" accesskey="s">Control Parental:</label></td>
		<td class="ui-widget-content">						
			<select name="config_cparental" id="config_cparental">
				{config_cparental}
			</select>
		</td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_wifi" accesskey="s">WiFi:</label></td>
		<td class="ui-widget-content">						
			<select name="config_wifi" id="config_wifi">
				<option value='1' selected>Mixto 802.11b/g</option>
			</select>
		</td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_ssid" accesskey="s">SSID:</label></td>
		<td class="ui-widget-content">						
			<select name="config_ssid" id="config_ssid">
				{config_ssid}
			</select>
		</td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_wifi_key" accesskey="s">Clave WPA2:</label></td>
		<td class="ui-widget-content">						
			<select name="config_wifi_key" id="config_wifi_key">
				{config_wifi_key}
			</select>
		</td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_lan_ip" accesskey="s">LAN IP:</label></td>
		<td class="ui-widget-content">						
			<select name="config_lan_ip" id="config_lan_ip">
				{config_lan_ip}
			</select>
		</td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_mascara" accesskey="s">Mascara:</label></td>
		<td class="ui-widget-content">						
			<select name="config_mascara" id="config_mascara">
				{config_mac_lan}
			</select>
		</td>
	</tr>
	<tr>
		<td class="ui-widget-content"><label for="config_plan" accesskey="s">Plan:</label></td>
		<td class="ui-widget-content">						
			<select name="config_plan" id="config_plan">
				{config_plan}
			</select>
		</td>
	</tr>
</tbody>
</table>
</form>
</div>