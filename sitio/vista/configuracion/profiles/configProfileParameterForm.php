<form id="{formID}" class="formUI">

	<div id="{formID}_validateTips" class="validateTips" class="ui-widget">
		<div class="ui-state-highlight ui-corner-all">
			<p>
				<span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
				{FORM_ALL_PARAM_REQUIRED}.
			</p>
		</div>
	</div>

	<fieldset class="ui-widget-content ui-corner-all">
		<div class="row">
			<label for="name" class="col1">{NAME}</label>
			<span class="col2 set pmedium">
				<input type="text" name="name" id="name" value="{name}" class="input ui-widget-content ui-corner-all" />
			</span>
		</div>
		<div class="row">
			<label for="description" class="col1">{DESCRIPTION}</label>
			<span class="col2 set pmedium">
				<input type="text" name="description" id="description" value="{description}" class="input ui-widget-content ui-corner-all" />
			</span>
		</div>
		<div class="row">
			<label for="type" class="col1">{TYPE}</label>
			<span class="col2 ">
				<select id="type" class="select" name="type">
					{optionTypeData}
				</select> </span>
		</div>
		<div class="row">
			<label for="value" class="col1">{VALUE}</label>
			<span class="col2 set pmedium">
				<input type="text" name="value" id="value" value="{value}" class="input ui-widget-content ui-corner-all" />
			</span>
		</div>
	</fieldset>
</form>