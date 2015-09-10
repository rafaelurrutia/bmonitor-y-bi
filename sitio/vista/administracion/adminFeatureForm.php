<form id="{FormID}" class="formUI">	
    <div id="validateTips" class="ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
            {FORM_ALL_PARAM_REQUIRED}.</p>
        </div>
    </div>
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="feature" class="col1">{NAME}</label>
			<span class="col2"><input type="text" name="feature" id="feature" value="{feature}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="display" class="col1">{DISPLAY}</label>
			<span class="col2"><input type="text" name="display" id="display" value="{display}"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="default_value" class="col1">{SELECT_BY_DEFAULT}</label>
			<span class="col2"><input type="text" name="default_value" id="default_value" value="{default_value}"  class="input ui-widget-content ui-corner-all" /></span>
		</div>
		
        <div class="row">
            <label for="type_feature" class="col1">{GROUP}</label>
            <span class="col2">
            	<select class="select ui-widget-content ui-corner-all" id="type_feature" name="type_feature">
            		<option value="ENLACES">FDT</option>
            		<option value="NEUTRALIDAD">NEUTRALIDAD</option>
            		<option value="ENLACES,NEUTRALIDAD'">FDT/NEUTRALIDAD</option>
            		<option selected="selected" value="ALL">{ALL}</option>
            	</select>
            </span>
        </div>
        <div class="row">
            <label for="orden" class="col1">Orden</label>
            <span class="col2"><input type="text" name="orden" id="orden" value="{orden}" class="input ui-widget-content ui-corner-all" /></span>
        </div>
	</fieldset>
</form>