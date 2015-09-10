<p class="{form_id_parameter}_validateTips">All form elements are required.</p>
<form id="{form_id_parameter}" class="form_sonda">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="parameter_name" class="col1">Name</label>
			<span class="col2"><input type="text" name="parameter_name" id="parameter_name" value="{parameter_name}" class="input-basic input-long ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="parameter_desc" class="col1">Description</label>
			<span class="col2"><input type="text" name="parameter_desc" id="parameter_desc" value="{parameter_desc}" class="input-basic input-long ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="parameter_type" class="col1">Type</label>
			<span class="col2">
				<select name="parameter_type" id="parameter_type" class="input-basic input-long ui-widget-content ui-corner-all"> 
					<option value="string">String</option>
					<option value="int">Integer</option>
					<option value="bool">Boolean</option>
					<option value="object">Object</option>
					<option value="array">Array</option>
					<option value="float">Float</option>
					<option value="unset">Unset</option>
				</select>
			</span>
		</div>
		<div class="row">
			<label for="parameter_value" class="col1">Value</label>
			<span class="col2">
				<input type="text" name="parameter_value" id="parameter_value" value="{parameter_value}" class="input-basic input-long ui-widget-content ui-corner-all" />
			</span>
		</div>
	</fieldset>
</form>