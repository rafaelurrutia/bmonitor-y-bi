<p class="validateTips_wifi">{FORM_ALL_PARAM_REQUIRED}</p>
<form id="form_edit_wifi" class="form_bsw">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="inventario_cparental" class="col1">{PARENTAL_CONTROL}</label>
			<span class="col2 short">
				<select name="inventario_cparental" id="inventario_cparental">
					{option_inventario_cparental}
				</select>
			</span>
		</div>
		<div class="row">
			<label for="inventario_wifi" class="col1">WiFi</label>
			<span class="col2 short">
				<select name="inventario_wifi" id="inventario_wifi">
					{option_inventario_wifi}
				</select>
			</span>
		</div>
		<div class="row">
			<label for="inventario_ssid" class="col1">SSID</label>
			<span class="col2 short">
				<select name="inventario_ssid" id="inventario_ssid">
					{option_inventario_ssid}
				</select>
			</span>
		</div>
		<div class="row">
			<label for="inventario_pass_wifi" class="col1">{PASS} WPA2</label>
			<span class="col2 short"><input type="text" name="inventario_pass_wifi" id="inventario_pass_wifi" value="{inventario_pass_wifi}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
	</fieldset>
</form>