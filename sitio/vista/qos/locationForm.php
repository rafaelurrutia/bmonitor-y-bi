<form id="{formID}" class="formUI">
    <div id="{formID}_validateTips" class="ui-widget">
        <div class="ui-state-highlight ui-corner-all">
            <p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
            {FORM_ALL_PARAM_REQUIRED}.</p>
        </div>
    </div>
    <fieldset class="ui-widget-content ui-corner-all">
       <div class="row">
			<label for="continent" class="col1">{CONTINENT}</label>	
			<span class="col2">
			    <select name="continent" id="continent" class="select">{optionContinen}</select>
            </span>
		</div>
		<div class="row">
			<label for="country" class="col1">{COUNTRY}</label><span class="col2">
				<select name="country" id="country" class="select">
					{optionCountry}
				</select></span>
		</div>
		<div class="row">
			<label for="state" class="col1">{STATE_PROVINCE}</label>
			<span class="col2">
				<select name="state" id="state" class="select">
					{optionState}
				</select>
			</span>
		</div>
		<div class="row">
			<label for="city" class="col1">{MUNICIPALITY}</label>
			<span class="col2">
				<input type="text" style="width: 400px" name="city" id="city" value="{city}" class="input ui-widget-content ui-corner-all">
			</span>
		</div>
		<div class="row">
			<label for="latitude" class="col1">{LATITUDE}</label>
			<span class="col2">
				<input type="text" style="width: auto" name="latitude" id="latitude" value="{latitude}" class="input ui-widget-content ui-corner-all">
			</span>
		</div>
		<div class="row">
			<label for="longitude" class="col1">{LONGITUDE}</label>
			<span class="col2">
				<input type="text" style="width: auto" name="longitude" id="longitude" value="{longitude}" class="input ui-widget-content ui-corner-all">
			</span>
		</div>
		<div class="row">
			<label for="minTest" class="col1">{MNIMUM_TESTING_SET}</label><span class="col2">
				<input type="text" style="width: 60px" name="minTest" id="minTest" value="{minTest}" class="input ui-widget-content ui-corner-all">
			</span>
		</div> 
		{additionalInput}    
    </fieldset>
</form>