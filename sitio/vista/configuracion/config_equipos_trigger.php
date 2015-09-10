<form id="config_equipos_trigger" class="form_sonda">	
	<p id="config_equipos_trigger_validateTips" class="validateTips">Todo los elementos del formulario son requeridos.</p>	
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="trigger_responsable" class="col1">Responsable</label>
			<span class="col2"><input type="text" name="trigger_responsable" id="trigger_responsable" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="trigger_id" class="col1">Trigger</label>
			<span class="col2"><select id="trigger_id" name="trigger_id" class="input ui-widget-content ui-corner-all">
				<option value="0">Selecccionar</option>
				<option value="ssh">SSH Revesa (Install)</option>
				<option value="ssh_reverse">SSH Revesa (Iniciar)</option>
				<option value="reboot">Reiniciar</option>
				<option value="bamrescue">BAM Rescue</option>
				<option value="upgrade">Upgrade</option>
			</select></span>
		</div>
	</fieldset>
</form>