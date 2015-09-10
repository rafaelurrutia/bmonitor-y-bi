<p class="{form_id_firmware}_validateTips">All form elements are required.</p>
<form id="{form_id_firmware}" class="form_sonda">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="firmware_version" class="col1">Version</label>
			<span class="col2"><input type="text" name="firmware_version" id="firmware_version" value="{firmware_version}" class="input-basic input-long ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="firmware_responsable" class="col1">Responsible</label>
			<span class="col2">
				<input type="text" name="firmware_responsable" id="firmware_responsable" value="{firmware_responsable}" class="input-basic input-long ui-widget-content ui-corner-all" />
			</span>
		</div>
		<div class="row">
			<label for="firmeware_branch" class="col1">Branch</label>
			<span class="col2">
				<select name="firmeware_branch" id="firmeware_branch" class="input-basic input-long ui-widget-content ui-corner-all"> 
					<option value="1">1</option>
					<option value="2">2</option>
				</select>
			</span>
		</div>
		<div class="row">
			<label for="firmeware_upload" class="col1">Firmware</label>
			<span class="col2">
				<input type="file" name="firmeware_upload" id="firmeware_upload" size="40" class="input-basic input-long ui-widget-content ui-corner-all" />
			</span>
		</div>
	</fieldset>
</form>