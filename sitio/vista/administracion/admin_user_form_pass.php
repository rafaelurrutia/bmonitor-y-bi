<form id="{formID}" class="formUI">
	<div id="{formID}_validateTips" class="validateTips ui-widget">
		<div class="ui-state-highlight ui-corner-all">
			<p>
				<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
				{FORM_ALL_PARAM_REQUIRED}
			</p>
		</div>
	</div>

	<fieldset>
		<div class="row">
			<label for="password" class="col1">{USER_PASS}</label>
			<span class="col2 set pmedium">
				<input type="password" name="password" id="password" value="{password}" class="input ui-widget-content ui-corner-all" />
			</span>
		</div>
        <div class="row">
            <label for="confirmation" class="col1">{CONFIRMATION}</label>
            <span class="col2 set pmedium">
                <input type="password" name="confirmation" id="confirmation"  class="input ui-widget-content ui-corner-all" />
            </span>
        </div>
	</fieldset>
</form>