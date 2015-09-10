<div id="{configScreenIDForm}_validateTips" class="ui-widget">
    <div class="ui-state-highlight ui-corner-all">
        <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
       {FORM_ALL_PARAM_REQUIRED}.</p>
    </div>
</div>
<form id="{configScreenIDForm}" class="formUI">		
	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="name" class="col1">{NAME}</label>
			<span class="col2"><input type="text" name="name" id="name" value="{name}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="hsize" class="col1">{COLUMNS}</label>
			<span class="col2"><input type="text" name="hsize" id="hsize" value="{hsize}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
		<div class="row">
			<label for="vsize" class="col1">{ROWS}</label>
			<span class="col2"><input type="text" name="vsize" id="vsize" value="{vsize}" class="input ui-widget-content ui-corner-all" /></span>
		</div>
        <div class="row">
            <label for="groupid" class="col1">{GROUP}</label>
            <span class="col2">
                <select name="groupid" id="groupid" class="select">
                    {optionGroupid}
                </select>
            </span>
        </div>
	</fieldset>
</form>